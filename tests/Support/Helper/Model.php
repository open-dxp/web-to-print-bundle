<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
