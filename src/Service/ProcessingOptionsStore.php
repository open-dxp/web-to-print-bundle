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

use OpenDxp\Bundle\AdminBundle\Service\Admin\AdminUserContextInterface;
use OpenDxp\Tool\Serialize;

/**
 * Keeps the PDF processing options an editor last used, per document and per user.
 *
 * @internal
 */
final class ProcessingOptionsStore
{
    public function __construct(
        private readonly AdminUserContextInterface $userContext,
        private readonly FileExistenceChecker $fileExistenceChecker,
    ) {
    }

    public function load(int $documentId): array
    {
        $filePath = $this->filePath($documentId);

        if (!$this->fileExistenceChecker->exists($filePath)) {
            return [];
        }

        $options = Serialize::unserialize(file_get_contents($filePath));

        return is_array($options) ? $options : [];
    }

    public function save(int $documentId, array $options): void
    {
        file_put_contents($this->filePath($documentId), Serialize::serialize($options));
    }

    private function filePath(int $documentId): string
    {
        return OPENDXP_SYSTEM_TEMP_DIRECTORY
            . DIRECTORY_SEPARATOR
            . 'web2print-processingoptions-' . $documentId . '_' . $this->userContext->getAdminUser()?->getId() . '.psf';
    }
}
