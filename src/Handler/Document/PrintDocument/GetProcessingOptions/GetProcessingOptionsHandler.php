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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\GetProcessingOptions;

use OpenDxp\Bundle\AdminBundle\Payload\Common\IdQueryPayload;
use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Bundle\WebToPrintBundle\Service\ProcessingOptionsStore;

/**
 * @internal
 */
final class GetProcessingOptionsHandler
{
    public function __construct(private readonly ProcessingOptionsStore $processingOptionsStore)
    {
    }

    public function __invoke(IdQueryPayload $payload): GetProcessingOptionsResult
    {
        $storedValues = $this->processingOptionsStore->load($payload->id);

        // re-index: a PRINT_MODIFY_PROCESSING_OPTIONS listener may hand back a keyed array,
        // the grid store expects a list
        $options = array_values(array_map(
            static fn (array $option) => [
                'name' => $option['name'],
                'label' => $option['name'],
                'value' => array_key_exists($option['name'], $storedValues)
                    ? $storedValues[$option['name']]
                    : $option['default'],
                'type' => $option['type'],
                'values' => $option['values'] ?? null,
            ],
            Processor::getInstance()->getProcessingOptions()
        ));

        return new GetProcessingOptionsResult(options: $options);
    }
}
