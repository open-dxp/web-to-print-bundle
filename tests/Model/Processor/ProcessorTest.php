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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Model\Processor;

use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Document\Adapter\Ghostscript;
use OpenDxp\Logger;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tool\Console;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessorTest extends ModelTestCase
{
    public function testGotenberg()
    {
        $this->checkProcessors('Gotenberg', ['landscape' => false]);
        $this->checkProcessors('Gotenberg', ['landscape' => true]);
    }

    public function testPdfReactor()
    {
        $config = Config::getWeb2PrintConfig();
        $config['pdfreactorServer'] = 'cloud.pdfreactor.com';
        $config['pdfreactorProtocol'] = 'https';
        $config['pdfreactorServerPort'] = '443';
        $config['pdfreactorApiKey'] = '';
        $config['pdfreactorLicence'] = '';
        $config['pdfreactorBaseUrl'] = '';
        $config['pdfreactorEnableDebugMode'] = true;
        $config['pdfreactorEnableLenientHttpsMode'] = true;
        Config::setWeb2PrintConfig($config);

        $pdfReactorConfig = [
            'adapterConfig' => [
                'javaScriptSettings' => [
                    'enabled' => false,
                ],
                'addLinks' => true,
                'appendLog' => true,
                'debugSettings' => [
                    'all' => true,
                ],
            ],
        ];
        $this->checkProcessors('PdfReactor', $pdfReactorConfig);
    }

    public function checkProcessors(string $processorName, array $config): void
    {

        $processorClass = 'OpenDxp\Bundle\WebToPrintBundle\Processor\\'.$processorName;
        $processor = new $processorClass();
        $pdfContent = $this->getPDFfromProcessor($processor, $config);

        $file = tmpfile();
        $tempMetadata = stream_get_meta_data($file);
        $tempPath = $tempMetadata['uri'];
        file_put_contents($tempPath, $pdfContent);

        $gs = new Ghostscript();
        $pdfText = $gs->getText(null, null, $tempPath);
        $this->assertStringContainsString('Pellentesque habitant morbi tristiqu', $pdfText, 'Check if pdf contains text from html template');

        $pdfInfo = $this->getPDFInfo($tempPath);
        $orientation = $this->getOrientationFromPDFInfo($pdfInfo);

        // for pdfReactor, there's no landscape config option
        if (isset($config['landscape']) && $config['landscape'] == true) {
            $this->assertEquals('landscape', $orientation, 'Check if pdf is in landscape orientation');
        } else {
            $this->assertEquals('portrait', $orientation, 'Check if pdf is in portrait orientation');
        }

    }

    private function getPDFfromProcessor(Processor $processor, array $config): string
    {
        $html =  file_get_contents(__DIR__.'/../../Support/Resources/test_web2print.html.twig');

        return $processor->getPdfFromString($html, $config);
    }

    private function getPDFInfo(string $assetPath): string
    {
        try {
            $cmd = [$this->getPdfInfoCli()];
            array_push($cmd, $assetPath);
            Console::addLowProcessPriority($cmd);
            $process = new Process($cmd);
            $process->setTimeout(120);
            $process->mustRun();

            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            Logger::debug($e->getMessage());
        }
    }

    private function getPdfInfoCli(): string
    {
        return Console::getExecutable('pdfinfo', true);
    }

    private function getOrientationFromPDFInfo(string $pdfInfo): string
    {
        preg_match('/Page size:\s+([0-9]{0,5}\.?[0-9]{0,3}) x ([0-9]{0,5}\.?[0-9]{0,3})/', $pdfInfo, $pagesizematches);
        $width = round($pagesizematches[1]/2.83);
        $height = round($pagesizematches[2]/2.83);
        if ($width > $height) {
            return 'landscape';
        } else {
            return 'portrait';
        }
    }
}
