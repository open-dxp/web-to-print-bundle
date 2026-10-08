<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Factory;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;
use OpenDxp\Test\Factory\AbstractPageSnippetFactory;

/**
 * @extends AbstractPageSnippetFactory<Printcontainer>
 */
final class PrintcontainerFactory extends AbstractPageSnippetFactory
{
    public static function class(): string
    {
        return Printcontainer::class;
    }
}
