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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\CheckPdfDirty;

use OpenDxp\Bundle\AdminBundle\Payload\Common\IdQueryPayload;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;

/**
 * @internal
 */
final class CheckPdfDirtyHandler
{
    public function __invoke(IdQueryPayload $payload): CheckPdfDirtyResult
    {
        $document = PrintAbstract::getById($payload->id);

        // an unknown document counts as dirty, there is nothing to preview yet
        return new CheckPdfDirtyResult(pdfDirty: $document === null || $document->pdfIsDirty());
    }
}
