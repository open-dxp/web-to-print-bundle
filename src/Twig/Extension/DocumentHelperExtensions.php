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

namespace OpenDxp\Bundle\WebToPrintBundle\Twig\Extension;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printpage;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * @internal
 */
class DocumentHelperExtensions extends AbstractExtension
{
    #[\Override]
    public function getTests(): array
    {
        return [
            new TwigTest('opendxp_document_print', static fn($object) => $object instanceof PrintAbstract),
            new TwigTest('opendxp_document_print_container', static fn($object) => $object instanceof Printcontainer),
            new TwigTest('opendxp_document_print_page', static fn($object) => $object instanceof Printpage),
        ];
    }
}
