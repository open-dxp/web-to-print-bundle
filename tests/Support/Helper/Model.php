<?php

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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use OpenDxp\Bundle\WebToPrintBundle\Installer;
use OpenDxp\Model\DataObject\Customer;
use OpenDxp\Tests\Support\Helper\Model as OpenDxpTestModel;
use OpenDxp\Tests\Support\Helper\OpenDxp;
use OpenDxp\Tests\Support\Util\Autoloader;

class Model extends OpenDxpTestModel
{
    public function _beforeSuite($settings = []): void
    {
        /** @var OpenDxp $opendxpModule */
        $opendxpModule = $this->getModule('\\' . OpenDxp::class);

        // install web-to-print bundle
        $installer = $opendxpModule->getContainer()->get(Installer::class);
        $installer->install();

        Autoloader::load(Customer::class);
    }
}
