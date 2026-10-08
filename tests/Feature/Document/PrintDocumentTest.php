<?php

declare(strict_types=1);

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
