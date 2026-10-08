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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Config;

use OpenDxp\Bundle\WebToPrintBundle\Config;

it('saves the configuration and reads it back', function () {
    $configuration = [
        ...Config::get(),
        'pdfreactorServer' => 'cloud.pdfreactor.com',
        'pdfreactorServerPort' => '443',
    ];

    Config::save($configuration);

    expect(Config::get())->toMatchArray([
        'pdfreactorServer' => 'cloud.pdfreactor.com',
        'pdfreactorServerPort' => '443',
    ]);
});
