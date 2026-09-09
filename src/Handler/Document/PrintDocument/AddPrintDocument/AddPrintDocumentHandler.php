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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\AddPrintDocument;

use Exception;
use OpenDxp\Bundle\AdminBundle\Exception\AdminOperationFailedException;
use OpenDxp\Bundle\AdminBundle\Factory\ElementServiceFactory;
use OpenDxp\Bundle\AdminBundle\Service\Admin\AdminUserContextInterface;
use OpenDxp\Model\Document;
use OpenDxp\Model\Element\Service;
use OpenDxp\Resolver\ResolverInterface;
use OpenDxp\Tool;

/**
 * @internal
 */
final class AddPrintDocumentHandler
{
    public function __construct(
        private readonly AdminUserContextInterface $userContext,
        private readonly ElementServiceFactory $serviceFactory,
        private readonly ResolverInterface $documentClassResolver,
        private readonly string $defaultPrintPageController,
        private readonly string $defaultPrintContainerController,
    ) {
    }

    public function __invoke(AddPrintDocumentPayload $payload): AddPrintDocumentResult
    {
        $parentDocument = Document::getById($payload->parentId);

        if (!$parentDocument || !$parentDocument->isAllowed('create')) {
            throw new AdminOperationFailedException('Prevented adding a document because of missing permissions');
        }

        $intendedPath = $parentDocument->getRealFullPath() . '/' . $payload->key;

        if (Document\Service::pathExists($intendedPath)) {
            throw new AdminOperationFailedException(
                sprintf('Prevented adding a document because document with same path+key [%s] already exists', $intendedPath)
            );
        }

        $adminUser = $this->userContext->getAdminUser();

        $createValues = [
            'userOwner' => $adminUser->getId(),
            'userModification' => $adminUser->getId(),
            'published' => false,
            'key' => Service::getValidKey($payload->key, 'document'),
        ];

        $docType = Document\DocType::getById($payload->docTypeId ?? '');

        if ($docType) {
            $createValues['template'] = $docType->getTemplate();
            $createValues['controller'] = $docType->getController();
        } else {
            $createValues['controller'] = $this->defaultController($payload->type);
        }

        if ($payload->inheritanceSource !== null) {
            $createValues['contentMainDocumentId'] = $payload->inheritanceSource;
        }

        $document = $this->createDocument($payload->type, $parentDocument, $createValues);

        if ($payload->translationsBaseDocumentId !== null) {
            $this->addTranslation($document, $payload->translationsBaseDocumentId, $payload->language);
        }

        return new AddPrintDocumentResult($document->getId(), $document->getType());
    }

    private function defaultController(string $type): string
    {
        return match ($type) {
            'printcontainer' => $this->defaultPrintContainerController,
            default => $this->defaultPrintPageController,
        };
    }

    private function createDocument(string $type, Document $parentDocument, array $createValues): Document
    {
        $className = $this->documentClassResolver->resolve($type);

        if ($className === null || !Tool::classExists($className)) {
            throw new AdminOperationFailedException(sprintf("Unknown document type '%s'", $type));
        }

        try {
            return $className::create($parentDocument->getId(), $createValues);
        } catch (Exception $e) {
            throw new AdminOperationFailedException($e->getMessage());
        }
    }

    private function addTranslation(Document $document, int $translationsBaseDocumentId, ?string $language): void
    {
        $translationsBaseDocument = Document::getById($translationsBaseDocumentId);
        if (!$translationsBaseDocument) {
            return;
        }

        $document->setProperties([...$translationsBaseDocument->getProperties(), ...$document->getProperties()]);
        $document->setProperty('language', 'text', $language, false, true);
        $document->save();

        $this->serviceFactory->createDocumentService()->addTranslation($translationsBaseDocument, $document);
    }
}
