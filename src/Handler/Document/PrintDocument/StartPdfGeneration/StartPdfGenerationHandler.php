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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\StartPdfGeneration;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Service\ProcessingOptionsStore;
use OpenDxp\Config;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class StartPdfGenerationHandler
{
    public function __construct(
        private readonly Config $config,
        private readonly RequestStack $requestStack,
        private readonly ProcessingOptionsStore $processingOptionsStore,
    ) {
    }

    public function __invoke(StartPdfGenerationPayload $payload): StartPdfGenerationResult
    {
        $document = PrintAbstract::getById($payload->id);
        if (!$document) {
            throw new NotFoundHttpException('Document with id ' . $payload->id . ' not found.');
        }

        // the processor renders the document over HTTP, so it needs to know where to reach it
        $request = $this->requestStack->getCurrentRequest();
        $params = [
            ...$payload->processingOptions,
            'hostName' => $this->config['general']['domain'] ?: $request?->getHttpHost(),
            'protocol' => $request?->isSecure() ? 'https' : 'http',
        ];

        $pdf = $document->getPdfFileName();
        if (is_file($pdf)) {
            unlink($pdf);
        }

        $started = $document->generatePdf($params);

        $this->processingOptionsStore->save($document->getId(), $params);

        return new StartPdfGenerationResult($started);
    }
}
