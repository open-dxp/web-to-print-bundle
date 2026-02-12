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

namespace OpenDxp\Bundle\WebToPrintBundle\Tests\Model\Document;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printpage;
use OpenDxp\Model\Document;
use OpenDxp\Tests\Support\Test\ModelTestCase;

/**
 * Class DocumentTest
 *
 * @package OpenDxp\Tests\Model\Document
 *
 * @group model.document.document
 */
class DocumentTest extends ModelTestCase
{
    protected ?Printcontainer $testPrintContainer = null;

    protected ?Printpage $testprintPage = null;

    public function testPrintContainer(): void
    {
        // create
        $document = new Printcontainer();
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setKey(uniqid('', true) . rand(10, 99));
        $document->save();

        $document = Document::getById($document->getId());
        $this->assertInstanceOf(Printcontainer::class, $document);
    }

    public function testPrintPage(): void
    {
        // create
        $document = new Printpage();
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setKey(uniqid('', true) . rand(10, 99));
        $document->save();

        $document = Document::getById($document->getId());
        $this->assertInstanceOf(Printpage::class, $document);
    }
}
