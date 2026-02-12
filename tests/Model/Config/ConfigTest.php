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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Model\Config;

use OpenDxp\Bundle\WebToPrintBundle\Config;
use OpenDxp\Tests\Support\Test\ModelTestCase;

class ConfigTest extends ModelTestCase
{
    public function testConfig()
    {
        $config = Config::get();
        $this->assertFalse(isset($config['pdfreactorServer']), 'Check if pdfreactorServer config is undefined');

        $config['pdfreactorServer'] = 'cloud.pdfreactor.com';
        $config['pdfreactorProtocol'] = 'https';
        $config['pdfreactorServerPort'] = '443';
        $config['pdfreactorApiKey'] = '';

        Config::save($config);
        $config = Config::get();
        $this->assertEquals($config['pdfreactorServer'], 'cloud.pdfreactor.com', 'Check if config is saved correctly');
    }
}
