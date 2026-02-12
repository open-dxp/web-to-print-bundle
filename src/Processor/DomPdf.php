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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\WebToPrintBundle\Processor;

use Dompdf\Dompdf as DompdfLib;
use Dompdf\Options;
use OpenDxp\Bundle\WebToPrintBundle\Event\DocumentEvents;
use OpenDxp\Bundle\WebToPrintBundle\Event\Model\PrintConfigEvent;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Logger;

class DomPdf extends Processor
{
    /**
     * @internal
     */
    protected function buildPdf(PrintAbstract $document, object $config): string
    {
        $this->updateStatus($document->getId(), 10, 'start_html_rendering');

        $params = ['document' => $document];
        $html = $document->renderDocument($params);

        $this->updateStatus($document->getId(), 40, 'finished_html_rendering');

        $this->updateStatus($document->getId(), 50, 'pdf_conversion');

        try {
            // Merge config into params for getPdfFromString
            $params = array_merge($params, (array) $config);
            $pdf = $this->getPdfFromString($html, $params);
        } catch (\Exception $e) {
            Logger::error((string) $e);
            $document->setLastGenerateMessage($e->getMessage());

            throw new \Exception('Error during PDF-Generation: ' . $e->getMessage());
        }

        $this->updateStatus($document->getId(), 100, 'saving_pdf_document');
        $document->setLastGenerateMessage('');

        return $pdf;
    }

    /**
     * @internal
     */
    public function getProcessingOptions(): array
    {
        $options = [];

        $options[] = [
            'name' => 'paper',
            'type' => 'select',
            'values' => ['A4', 'letter', 'legal', 'A3', 'A5'],
            'default' => 'A4',
        ];

        $options[] = [
            'name' => 'orientation',
            'type' => 'select',
            'values' => ['portrait', 'landscape'],
            'default' => 'portrait',
        ];

        $event = new PrintConfigEvent($this, [
            'options' => $options,
        ]);
        \OpenDxp::getEventDispatcher()->dispatch($event, DocumentEvents::PRINT_MODIFY_PROCESSING_OPTIONS);

        return (array) $event->getArguments()['options'];
    }

    /**
     * @internal
     */
    public function getPdfFromString(string $html, array $params = [], bool $returnFilePath = false): string
    {
        if (!class_exists(DompdfLib::class)) {
            throw new \Exception('Dompdf library is not installed. Please install it via "composer require dompdf/dompdf".');
        }

        // Process HTML (Twig rendering if needed, absolute paths)
        $processParams = [
            'document' => $params['document'] ?? null,
        ];
        $html = $this->processHtml($html, $processParams);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        // Allowed protocols can be adjusted if needed
        $options->set('allowedProtocols', [
            'http' => true,
            'https' => true,
            'file' => true,
        ]);

        $dompdf = new DompdfLib($options);
        $dompdf->loadHtml($html);

        $paper = $params['paper'] ?? 'A4';
        $orientation = $params['orientation'] ?? 'portrait';
        $dompdf->setPaper($paper, $orientation);

        $dompdf->render();
        $output = $dompdf->output();

        if ($returnFilePath) {
            $dstFile = OPENDXP_SYSTEM_TEMP_DIRECTORY . DIRECTORY_SEPARATOR . uniqid('web2print_') . '.pdf';
            file_put_contents($dstFile, $output);

            return $dstFile;
        }

        return (string) $output;
    }
}
