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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Document;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Tests\Factory\PrintcontainerFactory;
use OpenDxp\Bundle\WebToPrintBundle\Tests\Factory\PrintpageFactory;
use OpenDxp\Model\Document;

it('loads a saved print document as its own type', function (PrintAbstract $document) {
    $loaded = Document::getById($document->getId(), ['force' => true]);

    expect($loaded)->toBeInstanceOf($document::class);
})->with([
    'a print container' => [fn () => PrintcontainerFactory::createOne()],
    'a print page' => [fn () => PrintpageFactory::createOne()],
]);
