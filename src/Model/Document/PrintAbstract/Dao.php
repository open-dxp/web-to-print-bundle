<?php

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

namespace OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;

use Exception;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Db\Helper;
use OpenDxp\Model\Document;
use OpenDxp\Model\Exception\NotFoundException;

/**
 * @internal
 *
 * @property PrintAbstract $model
 */
class Dao extends Document\PageSnippet\Dao
{
    /**
     * Contains the valid database columns
     */
    protected array $validColumnsPage = [];

    /**
     * Get the valid columns from the database
     */
    public function init(): void
    {
        // page
        $this->validColumnsPage = $this->getValidTableColumns('documents_printpage');
    }

    /**
     * Get the data for the object by the given id, or by the id which is set in the object
     *
     *
     * @throws Exception
     */
    #[\Override]
    public function getById(?int $id = null): void
    {
        if ($id !== null) {
            $this->model->setId($id);
        }

        $data = $this->db->fetchAssociative("SELECT documents.*, documents_printpage.*, tree_locks.locked FROM documents
            LEFT JOIN documents_printpage ON documents.id = documents_printpage.id
            LEFT JOIN tree_locks ON documents.id = tree_locks.id AND tree_locks.type = 'document'
                WHERE documents.id = ?", [$this->model->getId()]);

        if (!empty($data['id'])) {
            $this->assignVariablesToModel($data);
        } else {
            throw new NotFoundException('Print Document with the ID ' . $this->model->getId() . " doesn't exists");
        }
    }

    #[\Override]
    public function create(): void
    {
        parent::create();

        $this->db->insert('documents_printpage', [
            'id' => $this->model->getId(),
        ]);
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function update(): void
    {
        $this->model->setModificationDate(time());
        $document = $this->model->getObjectVars();
        $dataDocument = [];
        $dataPage = [];

        foreach ($document as $key => $value) {
            // check if the getter exists
            $getter = 'get' . ucfirst((string) $key);
            if (!method_exists($this->model, $getter)) {
                continue;
            }

            // get the value from the getter
            if (in_array($key, $this->getValidTableColumns('documents')) || in_array($key, $this->validColumnsPage)) {
                $value = $this->model->$getter();
            } else {
                continue;
            }

            if (is_bool($value)) {
                $value = (int)$value;
            }
            if (in_array($key, $this->getValidTableColumns('documents'))) {
                $dataDocument[$key] = $value;
            }
            if (in_array($key, $this->validColumnsPage)) {
                $dataPage[$key] = $value;
            }
        }

        Helper::upsert($this->db, 'documents', $dataDocument, $this->getPrimaryKey('documents'));
        Helper::upsert($this->db, 'documents_printpage', $dataPage, $this->getPrimaryKey('documents_printpage'));

        $this->updateLocks();
    }

    /**
     * @throws Exception
     */
    #[\Override]
    public function delete(): void
    {
        $this->deleteAllProperties();

        $this->db->delete('documents_page', ['id' => $this->model->getId()]);
        $this->db->delete('documents_printpage', ['id' => $this->model->getId()]);
        parent::delete();
    }
}
