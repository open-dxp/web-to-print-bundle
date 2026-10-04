<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Document;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printpage;
use OpenDxp\Model\Document;

it('loads a saved print document as its own type', function (string $class) {
    $document = new $class();
    $document->setParentId(1);
    $document->setKey(uniqid('print-'));
    $document->save();

    expect(Document::getById($document->getId(), ['force' => true]))->toBeInstanceOf($class);
})->with([
    'a print container' => [Printcontainer::class],
    'a print page' => [Printpage::class],
]);
