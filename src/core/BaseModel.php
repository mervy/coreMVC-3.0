<?php

namespace Core;

use PDO;

abstract class BaseModel
{

    private $pdo;
    protected $table;
    private $data = array();
    private $join = array();
    private $where = array();
    private $returning = array();
    private $groupby = array();
    private $limit = false;
    private $offset = false;
    private $order = array();
    private $nick = '';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function data($data)
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            array_push($this->data, $data);
        }
        return $this;
    }

    /**
     * Utilizado após o método select para realizar join's
     *
     * @name join
     * @param String $table Nome da tabela
     * @param String $condition Condição do JOIN
     * @param String $method  INNER, LEFT...
     * @example $obj->join("t1","t1.id = t2.id","INNER");
     */
    public function join($table, $condition, $type = 'INNER')
    {
        array_push($this->join, array($table, $condition, $type));
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição
     * @name where
     * @param String $condition
     * @example $obj->where("id = 1");
     * @example $obj->where("username = 'foo' ");
     */
    public function where($conditions)
    {
        if (is_array($conditions)) {
            $this->where = array_merge($this->where, $conditions);
        } else {
            array_push($this->where, $conditions);
        }
        return $this;
    }

    public function returning($fields)
    {
        if (is_array($fields)) {
            $this->returning = array_merge($this->returning, $fields);
        } else {
            array_push($this->returning, $fields);
        }

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = is_int($limit) ? $limit : false;

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = is_int($offset) ? $offset : false;

        return $this;
    }

    /**
     * Metodo Sql order
     *
     * @name orderby
     * @param Strin $order campo ordem
     * @example $obj->order("nome asc");
     * @example $obj->order("id desc");
     */
    public function order($order)
    {
        if (is_array($order)) {
            $this->order = array_merge($this->order, $order);
        } else {
            array_push($this->order, $order);
        }
        return $this;
    }

    public function groupby($fields)
    {
        if (is_array($fields)) {
            $this->groupby = array_merge($this->groupby, $fields);
        } else {
            array_push($this->groupby, $fields);
        }

        return $this;
    }

    private function formatData($method)
    {
        switch ($method) {
            case 'select':
                if (count($this->data)) {
                    $return = implode(', ', $this->data);
                } else {
                    $return = '*';
                }
                break;
            //Não usado ainda
            case 'update':
                $set = array();
                foreach ($this->data AS $field => $value) {
                    if (is_int($field)) {
                        $set[] = $value;
                    } else {
                        if ($value === NULL) {
                            $value = 'NULL';
                        } else {
                            $value = "'$value'";
                        }
                        $set[] = "$field = $value";
                    }
                }
                $return = implode(',', $set);
                break;
            case 'insert':
                $columns = implode(',', array_keys($this->data));
                $values = array();
                foreach ($this->data AS $v) {
                    if ($v === NULL) {
                        $value = 'NULL';
                    } else {
                        $value = "'$v'";
                    }
                    array_push($values, $value);
                }
                $values = implode(',', $values);
                $return = "($columns) VALUES ($values)";
                break;
        }
        return ' ' . $return;
    }

    private function formatJoin()
    {
        $join = array();
        foreach ($this->join as $j) {
            $join_table = $j[0];
            $join_cond = $j[1];
            $join_type = $j[2];
            $join_type = $join_type . ' JOIN ';
            $join_cond = ' ON ' . $join_cond;
            $join_string = $join_type . $join_table . $join_cond;
            array_push($join, $join_string);
        }
        return ' ' . implode(' ', $join);
    }

    private function formatWhere()
    {
        $where = array();
        foreach ($this->where AS $field => $value) {
            if (is_int($field)) {
                $where[] = $value;
            } else {
                $where[] = "$field = '$value'";
            }
        }
        $return = '';
        if (count($where)) {
            $return = ' WHERE ' . implode(' AND ', $where);
        }
        return $return;
    }

    private function formatOrder()
    {
        $order = array();
        foreach ($this->order AS $field => $_order) {
            if (is_int($field)) {
                $order[] = $_order;
            } else {
                $order[] = "$field $order";
            }
        }
        $return = '';
        if (count($order)) {
            $return = ' ORDER BY ' . implode(', ', $order);
        }
        return $return;
    }

    private function formatLimit()
    {
        $return = '';
        if (is_int($this->limit)) {
            $offset = is_int($this->offset) ? $this->offset : 0;
            $return = ' LIMIT ' . $this->limit . ' OFFSET ' . $offset;
        }
        return $return;
    }

    private function formatReturning()
    {
        if (count($this->returning)) {
            return ' RETURNING ' . implode(',', $this->returning);
        }
    }

    private function formatGroupBy()
    {
        if (count($this->groupby)) {
            return ' GROUP BY ' . implode(',', $this->groupby);
        }
    }

    public function all()
    {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();
        return $result;
    }

    public function allOne()
    {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function allWithJoin($nick)
    {
        $query = 'SELECT ';
        $query .= $this->formatData('select');
        $query .= " FROM {$this->table}";
        $query .= ($nick != null) ? " AS {$nick}" : '';
        $query .= $this->formatJoin();
        $query .= $this->formatWhere();
        $query .= $this->formatGroupBy();
        $query .= $this->formatOrder();
        $query .= $this->formatLimit();
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $stmt->closeCursor();
        return $result;
    }

    public function oneWithJoin($nick = null)
    {
        $query = 'SELECT ';
        $query .= $this->formatData('select');
        $query .= " FROM {$this->table}";
        $query .= ($nick != null) ? " AS {$nick}" : '';
        $query .= $this->formatJoin();
        $query .= $this->formatWhere();
        $query .= $this->formatGroupBy();
        $query .= $this->formatOrder();
        $query .= $this->formatLimit();

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function find($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function create(array $data)
    {
        $data = $this->prepareDataInsert($data);
        $query = "INSERT INTO {$this->table} ({$data[0]}) VALUES ({$data[1]})";
        $stmt = $this->pdo->prepare($query);
        for ($i = 0; $i < count($data[2]); $i++) {
            $stmt->bindValue("{$data[2][$i]}", $data[3][$i]);
        }
        $result = $stmt->execute();
        $stmt->closeCursor();
        return $result;
    }
    
       public function createTwoTable(array $data, $id)
    {
        $data = $this->prepareDataInsert($data);
        $query = "INSERT INTO {$this->table} ({$data[0]}) VALUES ({$data[1]})";
        $stmt = $this->pdo->prepare($query);
        for ($i = 0; $i < count($data[2]); $i++) {
            $stmt->bindValue("{$data[2][$i]}", $data[3][$i]);
        }
        $result = $stmt->execute();
        
        //Começa a inserção na outra tabela
        $id = mysql_insert_id();
        
        $stmt->closeCursor();
        return $result;
    }

    private function prepareDataInsert(array $data)
    {
        $strKeys = "";
        $strBinds = "";
        $binds = [];
        $values = [];

        foreach ($data as $key => $value) {
            $strKeys = "{$strKeys}, {$key}";
            $strBinds = "{$strBinds}, :{$key}";
            $binds[] = ":{$key}";
            $values[] = $value;
        }
        $strKeys = substr($strKeys, 1);
        $strBinds = substr($strBinds, 1);

        return[$strKeys, $strBinds, $binds, $values];
    }

    public function update(array $data, $id)
    {
        $data = $this->prepareDataUpdate($data);
        $query = "UPDATE {$this->table} SET {$data[0]} WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id", $id);
        for ($j = 0; $j < count($data[1]); $j++) {
            $stmt->bindValue("{$data[1][$j]}", "{$data[2][$j]}");
        }
        $result = $stmt->execute();
        $stmt->closeCursor();
        return $result;
    }

    private function prepareDataUpdate(array $data)
    {
        $strKeysBinds = "";
        $binds = [];
        $values = [];

        foreach ($data as $key => $value) {
            $strKeysBinds = "{$strKeysBinds}, {$key}=:{$key}";
            $binds[] = ":{$key}";
            $values[] = $value;
        }
        $strKeysBinds = substr($strKeysBinds, 1);

        return[$strKeysBinds, $binds, $values];
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id", $id);
        $result = $stmt->execute();
        $stmt->closeCursor();
        return $result;
    }

}
