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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\WebToPrintBundle;

use OpenDxp\Bundle\WebToPrintBundle\Event\DocumentEvents;
use OpenDxp\Bundle\WebToPrintBundle\Exception\CancelException;
use OpenDxp\Bundle\WebToPrintBundle\Exception\NotPreparedException;
use OpenDxp\Bundle\WebToPrintBundle\Messenger\GenerateWeb2PrintPdfMessage;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Processor\Gotenberg;
use OpenDxp\Bundle\WebToPrintBundle\Processor\PdfReactor;
use OpenDxp\Bundle\WebToPrintBundle\Processor\DomPdf;
use OpenDxp\Event\Model\DocumentEvent;
use OpenDxp\Helper\Mail;
use OpenDxp\Logger;
use OpenDxp\Model;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Twig\Environment;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityError;

abstract class Processor
{
    private static ?LockInterface $lock = null;

    public static function getInstance(): PdfReactor|Gotenberg|Processor
    {
        $config = Config::getWeb2PrintConfig();

        if ($config['generalTool'] === 'pdfreactor') {
            return new PdfReactor();
        } elseif ($config['generalTool'] === 'gotenberg') {
            return new Gotenberg();
        } elseif ($config['generalTool'] === 'dompdf') {
            return new DomPdf();
        } else {
            if (class_exists($config['generalTool'])) {
                $generalToolClass = new $config['generalTool']();
                if ($generalToolClass instanceof Processor) {
                    return $generalToolClass;
                }
            }
        }

        throw new \Exception('Invalid Configuration - ' . $config['generalTool']);
    }

    /**
     *
     *
     * @throws \Exception
     */
    public function preparePdfGeneration(int $documentId, array $config): bool
    {
        $document = $this->getPrintDocument($documentId);
        if (Model\Tool\TmpStore::get($document->getLockKey())) {
            throw new \Exception('Process with given document already running.');
        }
        Model\Tool\TmpStore::add($document->getLockKey(), true);

        $jobConfig = new \stdClass();
        $jobConfig->documentId = $documentId;
        $jobConfig->config = $config;

        $this->saveJobConfigObjectFile($jobConfig);
        $this->updateStatus($documentId, 0, 'prepare_pdf_generation');

        $disableBackgroundExecution = $config['disableBackgroundExecution'] ?? false;

        if (!$disableBackgroundExecution) {
            \OpenDxp::getContainer()->get('messenger.bus.opendxp-core')->dispatch(
                new GenerateWeb2PrintPdfMessage($jobConfig->documentId)
            );

            return true;
        }

        return (bool) self::getInstance()->startPdfGeneration($jobConfig->documentId);
    }

    /**
     *
     *
     * @throws Model\Element\ValidationException
     * @throws NotPreparedException
     */
    public function startPdfGeneration(int $documentId): ?string
    {
        $jobConfigFile = $this->loadJobConfigObject($documentId);
        if (!$jobConfigFile) {
            throw new NotPreparedException('PDF Generation for document ' . $documentId . ' is not prepared.');
        }

        $document = $this->getPrintDocument($documentId);

        $lock = $this->getLock($document);
        // check if there is already a generating process running, wait if so ...
        $lock->acquire(true);

        $pdf = null;

        try {
            $preEvent = new DocumentEvent($document, [
                'processor' => $this,
                'jobConfig' => $jobConfigFile->config,
            ]);
            \OpenDxp::getEventDispatcher()->dispatch($preEvent, DocumentEvents::PRINT_PRE_PDF_GENERATION);

            $pdf = $this->buildPdf($document, $jobConfigFile->config);
            file_put_contents($document->getPdfFileName(), $pdf);

            $postEvent = new DocumentEvent($document, [
                'filename' => $document->getPdfFileName(),
                'pdf' => $pdf,
            ]);
            \OpenDxp::getEventDispatcher()->dispatch($postEvent, DocumentEvents::PRINT_POST_PDF_GENERATION);

            $document->setLastGenerated((time() + 1));
            $document->setLastGenerateMessage('');
            $document->save();
        } catch (CancelException $e) {
            Logger::debug($e->getMessage());
        } catch (\Exception $e) {
            Logger::err((string) $e);
            $document->setLastGenerateMessage($e->getMessage());
            $document->save();
        }

        $lock->release();
        Model\Tool\TmpStore::delete($document->getLockKey());

        @unlink(static::getJobConfigFile($documentId));

        return $pdf;
    }

    /**
     *
     *
     * @throws \Exception
     */
    abstract protected function buildPdf(PrintAbstract $document, object $config): string;

    protected function saveJobConfigObjectFile(\stdClass $jobConfig): bool
    {
        file_put_contents(static::getJobConfigFile($jobConfig->documentId), json_encode($jobConfig));

        return true;
    }

    protected function loadJobConfigObject(int $documentId): ?\stdClass
    {
        $file = static::getJobConfigFile($documentId);
        if (file_exists($file)) {
            return json_decode(file_get_contents($file));
        }

        return null;
    }

    /**
     *
     *
     * @throws \Exception
     */
    protected function getPrintDocument(int $documentId): PrintAbstract
    {
        $document = PrintAbstract::getById($documentId);
        if (empty($document)) {
            throw new \Exception('PrintDocument with ' . $documentId . ' not found.');
        }

        return $document;
    }

    public static function getJobConfigFile(int $processId): string
    {
        return OPENDXP_SYSTEM_TEMP_DIRECTORY . DIRECTORY_SEPARATOR . 'pdf-creation-job-' . $processId . '.json';
    }

    abstract public function getProcessingOptions(): array;

    /**
     *
     * @throws CancelException
     */
    protected function updateStatus(int $documentId, int $status, string $statusUpdate): void
    {
        $jobConfig = $this->loadJobConfigObject($documentId);
        if (!$jobConfig) {
            throw new CancelException('PDF Generation for document ' . $documentId . ' is canceled.');
        }
        $jobConfig->status = $status;
        $jobConfig->statusUpdate = $statusUpdate;
        $this->saveJobConfigObjectFile($jobConfig);
    }

    public function getStatusUpdate(int $documentId): ?array
    {
        $jobConfig = $this->loadJobConfigObject($documentId);
        if ($jobConfig) {
            return [
                'status' => $jobConfig->status,
                'statusUpdate' => $jobConfig->statusUpdate,
            ];
        }

        return null;
    }

    /**
     *
     * @throws \Exception
     */
    public function cancelGeneration(int $documentId): void
    {
        $document = PrintAbstract::getById($documentId);
        if (empty($document)) {
            throw new \Exception('Document with id ' . $documentId . ' not found.');
        }

        $this->getLock($document)->release();
        Model\Tool\TmpStore::delete($document->getLockKey());
        @unlink(static::getJobConfigFile($documentId));
    }

    /**
     *
     *
     * @throws \Exception
     */
    protected function processHtml(string $html, array $params): string
    {
        $document = $params['document'] ?? null;
        $hostUrl = $params['hostUrl'] ?? null;
        /** @var Environment $twig */
        $twig = \OpenDxp::getContainer()->get('opendxp.templating');
        $twig->getExtension(SandboxExtension::class)->enableSandbox();

        try {
            $template = $twig->createTemplate($html);

            $html = $twig->render($template, $params);
        } catch (SecurityError $e) {
            Logger::err((string) $e);

            throw new \Exception(sprintf('Failed rendering the print template: %s. Please check your twig sandbox security policy or contact the administrator.', $e->getMessage()));
        } finally {
            $twig->getExtension(SandboxExtension::class)->disableSandbox();
        }

        return Mail::setAbsolutePaths($html, $document, $hostUrl);
    }

    protected function getLock(PrintAbstract $document): LockInterface
    {
        if (!self::$lock) {
            self::$lock = \OpenDxp::getContainer()->get(LockFactory::class)->createLock($document->getLockKey());
        }

        return self::$lock;
    }

    /**
     * Returns the generated pdf file. Its path or data depending supplied parameter
     *
     * @param bool $returnFilePath return the path to the pdf file or the content
     *
     */
    abstract public function getPdfFromString(string $html, array $params = [], bool $returnFilePath = false): string;
}
