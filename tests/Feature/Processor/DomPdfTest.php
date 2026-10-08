<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Processor;

use OpenDxp\Bundle\WebToPrintBundle\Processor\DomPdf;
use OpenDxp\Document\Adapter\Ghostscript;

function isWiderThanHigh(string $pdf): bool
{
    preg_match('#/MediaBox \[\s*[\d.]+ [\d.]+ ([\d.]+) ([\d.]+)\s*\]#', $pdf, $mediaBox);

    return (float) $mediaBox[1] > (float) $mediaBox[2];
}

function textOf(string $pdf): string
{
    $file = tempnam(sys_get_temp_dir(), 'web2print');
    file_put_contents($file, $pdf);

    try {
        return (string) (new Ghostscript())->getText(null, null, $file);
    } finally {
        unlink($file);
    }
}

it('renders the html into a pdf in the requested orientation', function (string $orientation, bool $wider) {
    $html = (string) file_get_contents(dirname(__DIR__, 2) . '/Fixtures/print.html');

    $pdf = (new DomPdf())->getPdfFromString($html, ['orientation' => $orientation]);

    expect($pdf)
        ->toStartWith('%PDF-')
        ->and(isWiderThanHigh($pdf))
        ->toBe($wider)
        ->and(textOf($pdf))
        ->toContain('Pellentesque habitant morbi tristique');
})->with([
    'portrait' => ['portrait', false],
    'landscape' => ['landscape', true],
])->skip(fn () => !(new Ghostscript())->isAvailable(), 'Ghostscript reads the text of the pdf.');
