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

use OpenDxp\Bundle\AdminBundle\Payload\ExtJsPayloadInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The generate form posts its fields as JSON. Every field is a processing option for the
 * configured processor, so they travel on as an untyped map.
 *
 * @internal
 */
final readonly class StartPdfGenerationPayload implements ExtJsPayloadInterface
{
    public function __construct(
        public int $id,
        public array $processingOptions,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        $params = json_decode($request->getContent(), true);
        $params = is_array($params) ? $params : [];

        return new static(
            id: (int) ($params['id'] ?? 0),
            processingOptions: $params,
        );
    }
}
