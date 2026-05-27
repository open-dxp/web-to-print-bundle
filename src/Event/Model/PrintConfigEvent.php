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

namespace OpenDxp\Bundle\WebToPrintBundle\Event\Model;

use OpenDxp\Bundle\WebToPrintBundle\Processor;
use OpenDxp\Event\Traits\ArgumentsAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class PrintConfigEvent extends Event
{
    use ArgumentsAwareTrait;

    /**
     * DocumentEvent constructor.
     */
    public function __construct(
        protected Processor $processor,
        array $arguments = []
    ) {
        $this->arguments = $arguments;
    }

    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    /**
     * @return $this
     */
    public function setProcessor(Processor $processor): static
    {
        $this->processor = $processor;

        return $this;
    }
}
