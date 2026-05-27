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

namespace OpenDxp\Bundle\WebToPrintBundle\Event;

final class DocumentEvents
{
    /**
     * Processor contains the processor object used to generate the PDF
     *
     * Arguments:
     *  - processor | instance of the PDF processor OpenDxp\Bundle\WebToPrintBundle\Processor\{ProcessorName}
     *
     * @Event("OpenDxp\Event\Model\DocumentEvent")
     */
    const string PRINT_PRE_PDF_GENERATION = 'opendxp.document.print.prePdfGeneration';

    /**
     * Filename contains the filename of the generated pdf on filesystem, pdf contains generated pdf as string
     *
     * Arguments:
     *  - filename | contains the path of the generated pdf on filesystem
     *  - pdf | contains generated pdf as string
     *
     * @Event("OpenDxp\Event\Model\DocumentEvent")
     */
    const string PRINT_POST_PDF_GENERATION = 'opendxp.document.print.postPdfGeneration';

    /**
     * Modify the processing options (displayed in the OpenDxp admin interface)
     *
     * Arguments:
     *  - options | array for configuration settings
     *
     * @Event("OpenDxp\Bundle\WebToPrintBundle\Event\Model\PrintConfigEvent")
     */
    const string PRINT_MODIFY_PROCESSING_OPTIONS = 'opendxp.document.print.processor.modifyProcessingOptions';

    /**
     * Modify the configuration for the processor (when the pdf gets created)
     *
     * Arguments:
     *
     * PDFReactor:
     *  - config | configuration which is passed from the opendxp admin interface
     *  - reactorConfig | configuration which is passed to PDFReactor
     *  - document | OpenDxp document that is converted
     *
     *
     * @Event("OpenDxp\Bundle\WebToPrintBundle\Event\Model\PrintConfigEvent")
     */
    const string PRINT_MODIFY_PROCESSING_CONFIG = 'opendxp.document.print.processor.modifyConfig';
}
