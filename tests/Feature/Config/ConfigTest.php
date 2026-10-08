<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Config;

use OpenDxp\Bundle\WebToPrintBundle\Config;

it('saves the configuration and reads it back', function () {
    $configuration = [
        ...Config::get(),
        'pdfreactorServer' => 'cloud.pdfreactor.com',
        'pdfreactorServerPort' => '443',
    ];

    Config::save($configuration);

    expect(Config::get())->toMatchArray([
        'pdfreactorServer' => 'cloud.pdfreactor.com',
        'pdfreactorServerPort' => '443',
    ]);
});
