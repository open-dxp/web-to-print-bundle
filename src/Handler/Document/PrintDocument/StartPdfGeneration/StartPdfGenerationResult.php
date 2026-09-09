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

use OpenDxp\Bundle\AdminBundle\Handler\ConditionalResultInterface;

/**
 * @internal
 */
final readonly class StartPdfGenerationResult implements ConditionalResultInterface
{
    public function __construct(private bool $started)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->started;
    }
}
