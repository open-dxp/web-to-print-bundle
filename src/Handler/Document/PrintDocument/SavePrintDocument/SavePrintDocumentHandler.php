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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\SavePrintDocument;

use OpenDxp\Bundle\AdminBundle\Coordinator\Document\DocumentPersistenceCoordinator;
use OpenDxp\Bundle\AdminBundle\Mapper\Document\DocumentPayloadMapper;
use OpenDxp\Bundle\AdminBundle\Service\Element\ElementDraftService;
use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class SavePrintDocumentHandler
{
    public function __construct(
        private readonly ElementDraftService $elementDraftService,
        private readonly DocumentPayloadMapper $mapper,
        private readonly DocumentPersistenceCoordinator $coordinator,
    ) {
    }

    public function __invoke(SavePrintDocumentPayload $payload): SavePrintDocumentPublishedResult|SavePrintDocumentDraftResult
    {
        $loadedDocument = PrintAbstract::getById($payload->id);
        if (!$loadedDocument) {
            throw new NotFoundHttpException('Document not found');
        }

        $document = $this->elementDraftService->resolveDraft($loadedDocument);

        if ($payload->task !== 'save' && $this->isCleanupSaveMode()) {
            // drop every editable that is not part of the submitted data instead of merging
            // the submitted ones into what is stored
            $document->setEditables([]);
        }

        $this->mapper->applyPagePayload($payload, $document, $payload->task);

        $persistenceData = $this->coordinator->save($document, $payload->task);

        if ($payload->task === 'publish' || $payload->task === 'unpublish') {
            return new SavePrintDocumentPublishedResult(
                data: $persistenceData->data,
                treeData: $persistenceData->treeData,
            );
        }

        $this->elementDraftService->saveDocument($document);

        return new SavePrintDocumentDraftResult(draft: $persistenceData->draft ?? []);
    }

    private function isCleanupSaveMode(): bool
    {
        return (Config::get()['generalDocumentSaveMode'] ?? null) === 'cleanup';
    }
}
