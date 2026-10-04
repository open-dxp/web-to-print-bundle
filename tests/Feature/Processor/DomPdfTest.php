<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Processor;

use OpenDxp\Bundle\WebToPrintBundle\Processor\DomPdf;
use OpenDxp\Document\Adapter\Ghostscript;

it('renders the html into a pdf in the requested orientation', function (string $orientation, bool $wider) {
    $pdf = (new DomPdf())->getPdfFromString((string) file_get_contents(dirname(__DIR__, 2) . '/Fixtures/print.html'), ['orientation' => $orientation]);

    preg_match('#/MediaBox \[\s*[\d.]+ [\d.]+ ([\d.]+) ([\d.]+)\s*\]#', $pdf, $mediaBox);

    $file = tempnam(sys_get_temp_dir(), 'web2print');
    file_put_contents($file, $pdf);

    expect($pdf)->toStartWith('%PDF-')
        ->and((float) $mediaBox[1] > (float) $mediaBox[2])->toBe($wider)
        ->and((new Ghostscript())->getText(null, null, $file))->toContain('Pellentesque habitant morbi tristique');

    unlink($file);
})->with([
    'portrait' => ['portrait', false],
    'landscape' => ['landscape', true],
])->skip(fn () => !(new Ghostscript())->isAvailable(), 'Ghostscript reads the text of the pdf.');
