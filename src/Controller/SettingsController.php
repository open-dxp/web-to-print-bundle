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

namespace OpenDxp\Bundle\WebToPrintBundle\Controller;

use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Bundle\WebToPrintBundle\Processor\Chromium;
use OpenDxp\Bundle\WebToPrintBundle\Processor\Gotenberg;
use OpenDxp\Bundle\WebToPrintBundle\Processor\PdfReactor;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use OpenDxp\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route('/settings')]
class SettingsController extends UserAwareController
{
    use JsonHelperTrait;

    #[Route('/get-web2print', name: 'opendxp_bundle_web2print_settings_getweb2print', methods: ['GET'])]
    public function getWeb2printAction(Request $request): JsonResponse
    {
        $this->checkPermission('web2print_settings');

        $valueArray = Config::getWeb2PrintConfig();

        $response = [
            'values' => $valueArray,
        ];

        return $this->jsonResponse($response);
    }

    #[Route('/set-web2print', name: 'opendxp_bundle_web2print_settings_setweb2print', methods: ['PUT'])]
    public function setWeb2printAction(Request $request): JsonResponse
    {
        $this->checkPermission('web2print_settings');

        $values = $this->decodeJson($request->request->getString('data'));

        unset(
            $values['documentation'],
            $values['requirements'],
            $values['additions'],
            $values['json_converter'],
        );

        Config::save($values);

        return $this->jsonResponse(['success' => true]);
    }

    #[Route('/test-web2print', name: 'opendxp_bundle_web2print_settings_testweb2print', methods: ['GET'])]
    public function testWeb2printAction(Request $request): Response
    {
        $this->checkPermission('web2print_settings');

        $response = $this->render('@OpenDxpWebToPrint/settings/test_web2print.html.twig');
        $html = $response->getContent();

        $adapter = Processor::getInstance();
        $params = [];

        if ($adapter instanceof PdfReactor) {
            $params['adapterConfig'] = [
                'javaScriptSettings' => [
                    'enabled' => false,
                ],
                'addLinks' => true,
                'appendLog' => true,
                'debugSettings' => [
                    'all' => true,
                ],
            ];
        } elseif ($adapter instanceof Gotenberg) {
            $params = Config::getWeb2PrintConfig();
            $params = json_decode($params['gotenbergSettings'], true) ?: [];
        } elseif ($adapter instanceof Chromium) {
            $params = Config::getWeb2PrintConfig();
            $params = json_decode($params['chromiumSettings'], true) ?: [];
        }

        $responseOptions = [
            'Content-Type' => 'application/pdf',
        ];

        $pdfData = $adapter->getPdfFromString($html, $params);

        return new Response(
            $pdfData,
            200,
            $responseOptions

        );
    }
}
