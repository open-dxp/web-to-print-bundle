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

namespace OpenDxp\Bundle\WebToPrintBundle\Model\Document\Hardlink\Wrapper;

use OpenDxp\Model\Document\Hardlink\Wrapper;

/**
 * @method \OpenDxp\Model\Document\Hardlink\Dao getDao()
 */
class Printcontainer extends \OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer implements Wrapper\WrapperInterface
{
    use Wrapper;
}
