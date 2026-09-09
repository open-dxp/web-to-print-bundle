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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Settings\SaveWeb2PrintSettings;

use OpenDxp\Bundle\WebToPrintBundle\Config;

/**
 * @internal
 */
final class SaveWeb2PrintSettingsHandler
{
    /**
     * Fields the settings form renders but never stores.
     */
    private const array DISPLAY_ONLY_FIELDS = [
        'documentation',
        'requirements',
        'additions',
        'json_converter',
    ];

    public function __invoke(SaveWeb2PrintSettingsPayload $payload): void
    {
        Config::save(array_diff_key($payload->values, array_flip(self::DISPLAY_ONLY_FIELDS)));
    }
}
