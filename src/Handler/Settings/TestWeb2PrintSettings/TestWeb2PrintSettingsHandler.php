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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\TestWeb2PrintSettings;

use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Bundle\WebToPrintBundle\Processor\Gotenberg;
use OpenDxp\Bundle\WebToPrintBundle\Processor\PdfReactor;
use Twig\Environment;

/**
 * @internal
 */
final class TestWeb2PrintSettingsHandler
{
    private const string TEMPLATE = '@OpenDxpWebToPrint/settings/test_web2print.html.twig';

    public function __construct(private readonly Environment $twig)
    {
    }

    public function __invoke(): TestWeb2PrintSettingsResult
    {
        $processor = Processor::getInstance();
        $html = $this->twig->render(self::TEMPLATE);

        return new TestWeb2PrintSettingsResult(
            pdfData: $processor->getPdfFromString($html, $this->processorParams($processor)),
        );
    }

    private function processorParams(Processor $processor): array
    {
        if ($processor instanceof PdfReactor) {
            return [
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
        }

        if ($processor instanceof Gotenberg) {
            return json_decode((string) (Config::getWeb2PrintConfig()['gotenbergSettings'] ?? ''), true) ?: [];
        }

        return [];
    }
}
