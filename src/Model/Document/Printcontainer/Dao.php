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

namespace OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;

use OpenDxp\Bundle\WebToPrintBundle\Model\Document\PrintAbstract;
use OpenDxp\Bundle\WebToPrintBundle\Model\Document\Printcontainer;
use OpenDxp\Db\Helper;

/**
 * @internal
 *
 * @property Printcontainer $model
 */
class Dao extends PrintAbstract\Dao
{
    public function getLastedChildModificationDate(): string
    {
        $path = $this->model->getFullPath();

        return $this->db->fetchOne('SELECT modificationDate FROM documents WHERE `path` like ? ORDER BY modificationDate DESC LIMIT 0,1', [Helper::escapeLike($path) . '%']);
    }
}
