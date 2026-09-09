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

namespace OpenDxp\Bundle\WebToPrintBundle\Handler\Document\PrintDocument\AddPrintDocument;

use OpenDxp\Bundle\AdminBundle\Payload\ExtJsPayloadInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final readonly class AddPrintDocumentPayload implements ExtJsPayloadInterface
{
    public function __construct(
        public int $parentId,
        public string $type,
        public string $key,
        public ?string $docTypeId,
        public ?int $translationsBaseDocumentId,
        public ?string $language,
        public ?int $inheritanceSource,
    ) {
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            parentId: $request->request->getInt('parentId'),
            type: $request->request->getString('type'),
            key: $request->request->getString('key'),
            docTypeId: $request->request->getString('docTypeId') ?: null,
            translationsBaseDocumentId: $request->request->getInt('translationsBaseDocument') ?: null,
            language: $request->request->getString('language') ?: null,
            inheritanceSource: $request->request->getInt('inheritanceSource') ?: null,
        );
    }
}
