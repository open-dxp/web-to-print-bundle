<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Factory;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printpage;
use OpenDxp\Test\Factory\AbstractPageSnippetFactory;

/**
 * @extends AbstractPageSnippetFactory<Printpage>
 */
final class PrintpageFactory extends AbstractPageSnippetFactory
{
    public static function class(): string
    {
        return Printpage::class;
    }
}
