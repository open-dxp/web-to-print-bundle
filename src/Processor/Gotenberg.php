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

namespace OpenDxp\Bundle\WebToPrintBundle\Processor;

use Exception;
use Gotenberg\Gotenberg as GotenbergAPI;
use Gotenberg\Stream;
use OpenDxp;
use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Bundle\WebToPrintBundle\Event\DocumentEvents;
use OpenDxp\Bundle\WebToPrintBundle\Event\Model\PrintConfigEvent;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Logger;
use function array_merge;
use function file_exists;
use function json_decode;
use function key_exists;

class Gotenberg extends Processor
{
    /**
     * @internal
     */
    protected function buildPdf(PrintAbstract $document, object $config): string
    {
        $params = ['document' => $document];
        $this->updateStatus($document->getId(), 10, 'start_html_rendering');
        $html = $document->renderDocument($params);

        $this->updateStatus($document->getId(), 40, 'finished_html_rendering');

        try {
            $this->updateStatus($document->getId(), 50, 'pdf_conversion');
            $pdf = $this->getPdfFromString($html);
            $this->updateStatus($document->getId(), 100, 'saving_pdf_document');
        } catch (Exception $e) {
            Logger::error((string) $e);
            $document->setLastGenerateMessage($e->getMessage());

            throw new Exception('Error during PDF-Generation:' . $e->getMessage());
        }

        $document->setLastGenerateMessage('');

        return $pdf;
    }

    /**
     * @internal
     */
    public function getProcessingOptions(): array
    {
        $event = new PrintConfigEvent($this, [
            'options' => [],
        ]);
        OpenDxp::getEventDispatcher()->dispatch($event, DocumentEvents::PRINT_MODIFY_PROCESSING_OPTIONS);

        return (array)$event->getArgument('options');
    }

    /**
     * @internal
     */
    public function getPdfFromString(string $html, array $params = [], bool $returnFilePath = false): string
    {
        $web2printConfig = Config::getWeb2PrintConfig();

        $processParams = [
            'hostUrl' => $web2printConfig['gotenbergHostUrl'] ?? 'http://nginx:80',
        ];

        $html = $this->processHtml($html, $processParams);

        $gotenbergSettings = $web2printConfig['gotenbergSettings'] ?? '';
        $gotenbergSettings = json_decode($gotenbergSettings, true);

        if ($gotenbergSettings) {
            foreach (['header', 'footer'] as $item) {
                if (key_exists($item, $gotenbergSettings) && $gotenbergSettings[$item] &&
                    file_exists($gotenbergSettings[$item])) {
                    $gotenbergSettings[$item . 'Template'] = $gotenbergSettings[$item];
                }
                unset($gotenbergSettings[$item]);
            }

            $params = array_merge($params, $gotenbergSettings);
        }

        $params = $params ?: $this->getDefaultOptions();

        $event = new PrintConfigEvent($this, [
            'params' => $params,
            'html' => $html,
        ]);

        OpenDxp::getEventDispatcher()->dispatch($event, DocumentEvents::PRINT_MODIFY_PROCESSING_CONFIG);

        ['html' => $html, 'params' => $params] = $event->getArguments();

        $tempFileName = uniqid('web2print_');

        $chromium = GotenbergAPI::chromium(\OpenDxp\Config::getSystemConfiguration('gotenberg')['base_url']);
        $chromium = $chromium->pdf();

        $options = [
            'printBackground', 'landscape', 'preferCssPageSize', 'omitBackground', 'emulatePrintMediaType',
            'emulateScreenMediaType',
        ];

        foreach ($options as $option) {
            if (isset($params[$option]) && $params[$option] != false) {
                $chromium->$option();
            }
        }

        $chromium->margins(
            $params['marginTop'] ?? 0.39,
            $params['marginBottom'] ?? 0.39,
            $params['marginLeft'] ?? 0.39,
            $params['marginRight'] ?? 0.39
        );

        if (isset($params['scale'])) {
            $chromium->scale($params['scale']);
        }

        if (isset($params['nativePageRanges'])) {
            $chromium->nativePageRanges($params['nativePageRanges']);
        }

        foreach (['header', 'footer'] as $item) {
            if (isset($params[$item . 'Template'])) {
                $chromium->$item(Stream::path($params[$item . 'Template']));
            }
        }

        if ($params['paperWidth'] ?? isset($params['paperHeight'])) {
            $chromium->paperSize($params['paperWidth'] ?? 8.5, $params['paperHeight'] ?? 11);
        }

        if (isset($params['extraHttpHeaders'])) {
            $chromium->extraHttpHeaders($params['extraHttpHeaders']);
        }

        if (isset($params['metadata'])) {
            $chromium->metadata($params['metadata']);
        }

        $request = $chromium->outputFilename($tempFileName)->html(Stream::string('processor.html', $html));

        if ($returnFilePath) {
            $filename = GotenbergAPI::save($request, OPENDXP_SYSTEM_TEMP_DIRECTORY);

            return OPENDXP_SYSTEM_TEMP_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
        }
        $response = GotenbergAPI::send($request);

        return $response->getBody()->getContents();
    }

    private function getDefaultOptions(): array
    {
        return [
            //'paperWidth',
            //'paperHeight',
            //'marginTop',
            //'marginBottom',
            //'marginLeft',
            //'marginRight',
            //'preferCssPageSize',
            'printBackground' => true,
            //'omitBackground',
            'landscape' => false,
            //'scale' => 1,
            //'nativePageRanges',
            //'emulatePrintMediaType',
            //'emulateScreenMediaType',
            //'userAgent',
            //'extraHttpHeaders' => [],
            //'pdfFormat',
        ];
    }
}
