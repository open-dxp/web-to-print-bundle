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

namespace OpenDxp\Bundle\WebToPrintBundle;

use Exception;
use OpenDxp;
use OpenDxp\Cache\RuntimeCache;
use OpenDxp\Config\LocationAwareConfigRepository;
use OpenDxp\Model\Exception\ConfigWriteException;

/**
 * @internal
 */
final class Config
{
    private const string CONFIG_ID = 'web_to_print';

    private static ?LocationAwareConfigRepository $locationAwareConfigRepository = null;

    private static function getRepository(): LocationAwareConfigRepository
    {
        if (!self::$locationAwareConfigRepository) {
            $config = [];
            $containerConfig = OpenDxp::getContainer()->getParameter('opendxp_web_to_print');
            if ($containerConfig['generalTool']) {
                $config = [
                    self::CONFIG_ID => $containerConfig,
                ];
            }

            $storageConfig = $containerConfig['config_location'][self::CONFIG_ID];

            self::$locationAwareConfigRepository = new LocationAwareConfigRepository(
                $config,
                'opendxp_web_to_print',
                $storageConfig
            );
        }

        return self::$locationAwareConfigRepository;
    }

    /**
     * @throws Exception
     */
    public static function isWriteable(): bool
    {
        return self::getRepository()->isWriteable();
    }

    public static function get(): array
    {
        $repository = self::getRepository();

        $config = $repository->loadConfigByKey(self::CONFIG_ID);

        return $config[0] ?? [];
    }

    /**
     * @throws Exception
     */
    public static function save(array $data): void
    {
        $repository = self::getRepository();

        unset($data['pdf_creation_php_memory_limit']);
        unset($data['default_controller_print_page']);
        unset($data['default_controller_print_container']);

        if (!$repository->isWriteable()) {
            throw new ConfigWriteException();
        }

        $repository->saveConfig(self::CONFIG_ID, $data, fn ($key, $data) => [
            'opendxp_web_to_print' => $data,
        ]);
    }

    /**
     * @static
     *
     * @internal
     */
    public static function getWeb2PrintConfig(): array
    {
        if (RuntimeCache::isRegistered('opendxp_bundle_web2print_config')) {
            $config = RuntimeCache::get('opendxp_bundle_web2print_config');
        } else {
            $config = self::get();
            self::setWeb2PrintConfig($config);
        }

        return $config;
    }

    /**
     * @static
     *
     * @internal
     */
    public static function setWeb2PrintConfig(array $config): void
    {
        RuntimeCache::set('opendxp_bundle_web2print_config', $config);
    }
}
