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

namespace OpenDxp\Bundle\WebToPrintBundle\Service;

use Exception;

/**
 * @internal
 */
final class FileExistenceChecker
{
    public function exists(string $filePath): bool
    {
        $this->invalidateFsCacheFor($filePath);

        return file_exists($filePath);
    }

    /**
     * Opening and closing the directory drops the cached entry. Without it a file written on
     * an NFS mount with caching enabled is not seen by the next file_exists() call.
     */
    private function invalidateFsCacheFor(string $filePath): void
    {
        try {
            if ($dh = opendir(dirname($filePath))) {
                closedir($dh);
            }
        } catch (Exception) {
        }
    }
}
