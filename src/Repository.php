<?php

namespace EasyMVC\Repository;

use Exception;
use RudyMas\PDOExt\DBconnect;

/**
 * Class Repository
 *
 * @author      Rudy Mas <rudy.mas@rmsoft.be>
 * @copyright   2017-2018, rmsoft.be. (http://www.rmsoft.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     2.0.1
 * @package     EasyMVC\Repository
 */
class Repository
{
    private $data = [];
    private $indexMarker = 0;
    protected $db;

    /**
     * Repository constructor.
     * @param DBconnect|null $db
     * @param $object
     */
    public function __construct(DBconnect $db = null, $object = null)
    {
        if ($object !== null) {
            $this->data[] = $object;
        }
        if ($db !== null) {
            $this->db = $db;
        }
    }

    /**
     * @param $object
     */
    public function add($object): void
    {
        $this->data[] = $object;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * @deprecated
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function getAtIndex(int $id)
    {
        trigger_error('Use getByIndex instead.', E_USER_DEPRECATED);
        return $this->getByIndex($id);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function getByIndex(int $id)
    {
        foreach ($this->data as $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new Exception('<b>ERROR:</b> Call to an unknown repository Index!');
    }

    /**
     * @param string $field
     * @param string $search
     * @return array
     */
    public function getBy(string $field, string $search): array
    {
        $output = [];
        foreach ($this->data as $value) {
            if ($value[$field] == $search) {
                $output[] = $value;
            }
        }
        return $output;
    }

    /**
     * @return bool
     */
    public function hasNext(): bool
    {
        if (isset($this->data[$this->indexMarker + 1])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function hasPrevious(): bool
    {
        if (isset($this->data[$this->indexMarker - 1])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->data[$this->indexMarker];
    }

    /**
     * @return mixed
     */
    public function next()
    {
        $this->indexMarker++;
        return $this->current();
    }

    /**
     * @return mixed
     */
    public function previous()
    {
        $this->indexMarker--;
        return $this->current();
    }

    public function reset()
    {
        $this->indexMarker = 0;
    }

    /**
     * @param string $table
     * @param string $model
     */
    public function loadAllFromTable(string $table, string $model): void
    {
        $newModel = '\\Models\\' . $model;
        $query = "SELECT * FROM {$table}";
        $this->db->query($query);
        $this->db->fetchAll();
        foreach ($this->db->data as $data) {
            $this->data[] = $newModel::new($data);
        }
    }

    /**
     * @param string $model
     * @param string $preparedStatement
     * @param array $keyBindings
     */
    public function loadAllFromTableByQuery(string $model, string $preparedStatement, array $keyBindings): void
    {
        $newModel = '\\Models\\' . $model;
        $this->db->prepare($preparedStatement);
        foreach ($keyBindings as $key => $value) {
            if (is_integer($value)) {
                $this->db->bindValue($key, $value, \PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $this->db->bindValue($key, $value, \PDO::PARAM_BOOL);
            } elseif (is_null($value)) {
                $this->db->bindValue($key, $value, \PDO::PARAM_NULL);
            } elseif (is_string($value)) {
                $this->db->bindValue($key, $value, \PDO::PARAM_STR);
            }
        }
        $this->db->execute();
        $this->db->fetchAll();
        foreach ($this->db->data as $data) {
            $this->data[] = $newModel::new($data);
        }
    }
}

/** End of File: Repository.php **/