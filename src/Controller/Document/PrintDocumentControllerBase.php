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

namespace OpenDxp\Bundle\WebToPrintBundle\Controller\Document;

use OpenDxp\Bundle\AdminBundle\Attribute\SessionIdentityAware;
use OpenDxp\Bundle\AdminBundle\Controller\Admin\Document\DocumentControllerBase;
use OpenDxp\Bundle\AdminBundle\Payload\Common\IdBodyPayload;
use OpenDxp\Bundle\AdminBundle\Payload\Common\IdQueryPayload;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\AddPrintDocument\AddPrintDocumentHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\AddPrintDocument\AddPrintDocumentPayload;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\CancelPdfGeneration\CancelPdfGenerationHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\CheckPdfDirty\CheckPdfDirtyHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\DownloadPdf\DownloadPdfHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\DownloadPdf\DownloadPdfPayload;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\GetGenerationStatus\GetGenerationStatusHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\GetPrintDocumentData\GetPrintDocumentDataHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\GetProcessingOptions\GetProcessingOptionsHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\SavePrintDocument\SavePrintDocumentHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\SavePrintDocument\SavePrintDocumentPayload;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\StartPdfGeneration\StartPdfGenerationHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\StartPdfGeneration\StartPdfGenerationPayload;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
abstract class PrintDocumentControllerBase extends DocumentControllerBase
{
    #[Route('/get-data-by-id', name: 'getdatabyid', methods: ['GET'])]
    #[SessionIdentityAware]
    public function getDataByIdAction(
        GetPrintDocumentDataHandler $handler,
        IdQueryPayload $payload,
    ): JsonResponse {
        return $this->apiJson($handler($payload), rootProperty: 'data');
    }

    #[Route('/save', name: 'save', methods: ['PUT', 'POST'])]
    #[SessionIdentityAware]
    public function saveAction(SavePrintDocumentPayload $payload, SavePrintDocumentHandler $handler): JsonResponse
    {
        return $this->apiJson($handler($payload));
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addAction(AddPrintDocumentPayload $payload, AddPrintDocumentHandler $handler): JsonResponse
    {
        return $this->apiJson($handler($payload));
    }

    #[Route('/active-generate-process', name: 'activegenerateprocess', methods: ['POST'])]
    public function activeGenerateProcessAction(IdBodyPayload $payload, GetGenerationStatusHandler $handler): JsonResponse
    {
        return $this->apiJson($handler($payload), envelope: false);
    }

    #[Route('/pdf-download', name: 'pdfdownload', methods: ['GET'])]
    public function pdfDownloadAction(DownloadPdfPayload $payload, DownloadPdfHandler $handler): BinaryFileResponse
    {
        $result = $handler($payload);

        $response = new BinaryFileResponse($result->filePath);
        $response->headers->set('Content-Type', 'application/pdf');

        if ($payload->download) {
            $response->setContentDisposition('attachment', $result->fileName);
        }

        return $response;
    }

    #[Route('/start-pdf-generation', name: 'startpdfgeneration', methods: ['POST'])]
    public function startPdfGenerationAction(
        StartPdfGenerationPayload $payload,
        StartPdfGenerationHandler $handler,
    ): JsonResponse {
        return $this->apiJson($handler($payload));
    }

    #[Route('/check-pdf-dirty', name: 'checkpdfdirty', methods: ['GET'])]
    public function checkPdfDirtyAction(IdQueryPayload $payload, CheckPdfDirtyHandler $handler): JsonResponse
    {
        return $this->apiJson($handler($payload), envelope: false);
    }

    #[Route('/get-processing-options', name: 'getprocessingoptions', methods: ['GET'])]
    public function getProcessingOptionsAction(IdQueryPayload $payload, GetProcessingOptionsHandler $handler): JsonResponse
    {
        return $this->apiJson($handler($payload), envelope: false);
    }

    #[Route('/cancel-generation', name: 'cancelgeneration', methods: ['DELETE'])]
    public function cancelGenerationAction(IdBodyPayload $payload, CancelPdfGenerationHandler $handler): JsonResponse
    {
        $handler($payload);

        return $this->apiOk();
    }
}
