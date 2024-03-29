<?php

namespace EasyMVC;

use Exception;
use PDO;
use RudyMas\DBconnect;

/**
 * Class Repository (PHP version 8.1)
 *
 * @author      Rudy Mas <rudy.mas@rmsoft.be>
 * @copyright   2017-2022, rmsoft.be. (http://www.rmsoft.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     8.1.0.0
 * @package     EasyMVC
 */
class Repository
{
    private $data = [];
    private $indexMarker = 0;
    protected $db;

    /**
     * Repository constructor.
     * @param null|DBconnect $db
     * @param null $object
     */
    public function __construct(?DBconnect $db, $object = null)
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
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function getByIndex(int $id)
    {
        foreach ($this->data as $value) {
            if ($value->getData('id') == $id) {
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
            if ($value->getData($field) == $search) {
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
     * @return bool|mixed
     */
    public function current()
    {
        return (isset($this->data[$this->indexMarker])) ? $this->data[$this->indexMarker] : false;
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
     * Clearing Data
     */
    public function clearData(): void
    {
        $this->data = [];
    }

    /**
     * @param string $model
     * @param string $table
     */
    public function loadAllFromTable(string $model, string $table): void
    {
        $newModel = '\\Models\\' . $model;
        $query = "SELECT * FROM {$table}";
        $this->db->queryDB($query);
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
    public function loadAllFromTableByQuery(string $model, string $preparedStatement, array $keyBindings = []): void
    {
        $newModel = '\\Models\\' . $model;
        $statement = $this->db->prepare($preparedStatement);
        foreach ($keyBindings as $key => $value) {
            $statement->bindValue($key, $value, $this->PDOparameter($value));
        }
        $statement->execute();
        $this->db->rows = $statement->rowCount();
        $tableData = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tableData as $data) {
            $this->data[] = $newModel::new($data);
        }
    }

    /**
     * @param string $preparedStatement
     * @param array $keyBindings
     * @return bool
     */
    public function executeQuery(string $preparedStatement, array $keyBindings = []): bool
    {
        $statement = $this->db->prepare($preparedStatement);
        foreach ($keyBindings as $key => $value) {
            $statement->bindValue($key, $value, $this->PDOparameter($value));
        }
        $status = $statement->execute();
        $this->db->rows = $statement->rowCount();
        return $status;
    }

    /**
     * @return int
     */
    public function getRows(): int
    {
        return $this->db->rows;
    }

    /**
     * @param string $sql
     * @param array $keyBindings
     * @return int
     */
    public function getRowsByQuery(string $sql, array $keyBindings): int
    {
        $this->executeQuery($sql, $keyBindings);
        return $this->getRows();
    }

    /**
     * @param $value
     * @return int
     */
    public function PDOparameter($value): int
    {
        if (is_integer($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } elseif (is_string($value)) {
            return PDO::PARAM_STR;
        }
    }
}
