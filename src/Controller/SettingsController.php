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

namespace OpenDxp\Bundle\WebToPrintBundle\Controller;

use OpenDxp\Bundle\AdminBundle\Controller\AdminAbstractController;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\GetWeb2PrintSettings\GetWeb2PrintSettingsHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\SaveWeb2PrintSettings\SaveWeb2PrintSettingsHandler;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\SaveWeb2PrintSettings\SaveWeb2PrintSettingsPayload;
use OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\TestWeb2PrintSettings\TestWeb2PrintSettingsHandler;
use OpenDxp\Bundle\WebToPrintBundle\Security\Web2PrintPermission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @internal
 */
#[IsGranted(Web2PrintPermission::Web2PrintSettings->value)]
#[Route('/settings', name: 'opendxp_bundle_web2print_settings_')]
class SettingsController extends AdminAbstractController
{
    #[Route('/get-web2print', name: 'getweb2print', methods: ['GET'])]
    public function getWeb2printAction(GetWeb2PrintSettingsHandler $handler): JsonResponse
    {
        return $this->apiJson($handler(), envelope: false);
    }

    #[Route('/set-web2print', name: 'setweb2print', methods: ['PUT'])]
    public function setWeb2printAction(
        SaveWeb2PrintSettingsPayload $payload,
        SaveWeb2PrintSettingsHandler $handler,
    ): JsonResponse {
        $handler($payload);

        return $this->apiOk();
    }

    #[Route('/test-web2print', name: 'testweb2print', methods: ['GET'])]
    public function testWeb2printAction(TestWeb2PrintSettingsHandler $handler): Response
    {
        return new Response($handler()->pdfData, 200, ['Content-Type' => 'application/pdf']);
    }
}
