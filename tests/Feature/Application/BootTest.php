<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Feature\Application;

use OpenDxp\TestFoundation\Container;

it('boots the application the bundle is tested in', function () {
    expect(Container::environment())->toBe('test');
});
