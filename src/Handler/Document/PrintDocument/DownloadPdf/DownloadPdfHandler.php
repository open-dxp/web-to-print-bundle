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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\DownloadPdf;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Service\FileExistenceChecker;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class DownloadPdfHandler
{
    public function __construct(private readonly FileExistenceChecker $fileExistenceChecker)
    {
    }

    public function __invoke(DownloadPdfPayload $payload): DownloadPdfResult
    {
        $document = PrintAbstract::getById($payload->id);
        if (!$document) {
            throw new NotFoundHttpException('Document with id ' . $payload->id . ' not found.');
        }

        $filePath = $document->getPdfFileName();
        if (!$this->fileExistenceChecker->exists($filePath)) {
            throw new NotFoundHttpException('File does not exist');
        }

        return new DownloadPdfResult(filePath: $filePath, fileName: $document->getKey() . '.pdf');
    }
}
