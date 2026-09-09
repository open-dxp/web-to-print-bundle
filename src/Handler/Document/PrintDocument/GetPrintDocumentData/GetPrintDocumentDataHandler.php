<?php
declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\GetPrintDocumentData;

use OpenDxp\Bundle\AdminBundle\Enricher\Document\DocumentMetaEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Document\DraftEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Document\PropertiesEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Document\TranslationEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Element\AdminStyleEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Element\PhpMetaEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Element\PreSendDataEventEnricher;
use OpenDxp\Bundle\AdminBundle\Enricher\Element\UserNamesEnricher;
use OpenDxp\Bundle\AdminBundle\Event\AdminEvents;
use OpenDxp\Bundle\AdminBundle\Helper\DocumentVersionHelper;
use OpenDxp\Bundle\AdminBundle\Payload\Common\IdQueryPayload;
use OpenDxp\Bundle\AdminBundle\Service\Admin\AdminUserContextInterface;
use OpenDxp\Bundle\AdminBundle\Service\Element\EditLockService;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Model\Element;
use OpenDxp\Model\Schedule\Task;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class GetPrintDocumentDataHandler
{
    public function __construct(
        private readonly EditLockService $editLockService,
        private readonly AdminUserContextInterface $userContext,
        private readonly DocumentMetaEnricher $documentMetaEnricher,
        private readonly AdminStyleEnricher $adminStyleEnricher,
        private readonly UserNamesEnricher $userNamesEnricher,
        private readonly PropertiesEnricher $propertiesEnricher,
        private readonly TranslationEnricher $translationEnricher,
        private readonly DraftEnricher $draftEnricher,
        private readonly PhpMetaEnricher $phpMetaEnricher,
        private readonly PreSendDataEventEnricher $preSendDataEventEnricher,
    ) {
    }

    public function __invoke(IdQueryPayload $payload): GetPrintDocumentDataResult
    {
        $document = PrintAbstract::getById($payload->id);
        if (!$document) {
            throw new NotFoundHttpException('Document not found');
        }

        if (!$document->isAllowed('view')) {
            throw new AccessDeniedHttpException();
        }

        if ($document->isAllowed('save') || $document->isAllowed('publish') || $document->isAllowed('unpublish') || $document->isAllowed('delete')) {
            $this->editLockService->checkAndAcquire($document->getId(), 'document', AdminEvents::DOCUMENT_GET_IS_LOCKED, $document);
        }

        $document = clone $document;
        $draftVersion = null;
        $document = DocumentVersionHelper::resolveLatestDraft($document, $draftVersion, $this->userContext->getAdminUser()?->getId());

        $versions = Element\Service::getSafeVersionInfo($document->getVersions());
        $document->setVersions(array_splice($versions, -1, 1));
        $document->setParent(null);

        // unset useless data
        $document->setEditables(null);
        $document->setChildren(null);

        $data = $document->getObjectVars();
        $data['locked'] = $document->isLocked();
        $data['url'] = $document->getUrl();
        $data['scheduledTasks'] = array_map(
            static fn (Task $task) => $task->getObjectVars(),
            $document->getScheduledTasks()
        );

        if ($document->getContentMainDocument()) {
            $data['contentMainDocumentPath'] = $document->getContentMainDocument()->getRealFullPath();
        }

        $this->documentMetaEnricher->enrich($document, $data);
        $this->phpMetaEnricher->enrich($document, $data);
        $this->adminStyleEnricher->forEditor($document, $data);
        $this->userNamesEnricher->enrich($document, $data);
        $this->propertiesEnricher->enrich($document, $data);
        $this->translationEnricher->enrich($document, $data);
        $this->draftEnricher->enrich($document, $data, $draftVersion);

        $this->preSendDataEventEnricher->enrich($document, $data);

        return new GetPrintDocumentDataResult(data: $data);
    }
}
