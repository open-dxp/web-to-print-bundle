<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace OpenDxp\Bundle\WebToPrintBundle;

use OpenDxp\Bundle\WebToPrintBundle\DependencyInjection\OpenDxpWebToPrintExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpWebToPrintBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new OpenDxpWebToPrintExtension();
        }

        return $this->extension;
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpwebtoprint/css/icons.css',
        ];
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxpwebtoprint/js/startup.js',
            '/bundles/opendxpwebtoprint/js/settings.js',
            '/bundles/opendxpwebtoprint/js/document/printabstract.js',
            '/bundles/opendxpwebtoprint/js/document/printcontainer.js',
            '/bundles/opendxpwebtoprint/js/document/printpage.js',
            '/bundles/opendxpwebtoprint/js/document/printpages/pdf_preview.js',
        ];
    }

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
