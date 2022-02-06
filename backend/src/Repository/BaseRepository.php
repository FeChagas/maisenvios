<?php 
namespace Maisenvios\Middleware\Repository;

class BaseRepository {
    private $mysql;
    private $tablename;
    private $entity;

    public function __construct($tablename, $entity)
    {
        $this->tablename = $tablename;
        $this->entity = $entity;
        $this->mysql = mysqli_connect("ns1010.hostgator.com.br:3306", "maisen51_painel", "QazWsx12", "maisen51_painel");
        // $this->mysql = mysqli_connect("localhost:3306", "maisen51_painel", "QazWsx12", "maisen51_painel");
        if (!$this->mysql) {
            throw new \Exception("Mysql connection failed", 1);
        }
    }

    public function findAll(Array $args = []) {
        $return = [];
        $where = ' 1 = 1';
        foreach ($args as $key => $value) {
            $where .= " AND {$key} = '{$value}'";
        }
        $query = "SELECT * FROM `{$this->tablename}` WHERE {$where}";
        $result = $this->mysql->query($query);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $obj = new $this->entity();
                $obj = $obj->create($row);
                array_push($return, $obj);
            }
        }
        return $return;
    }

    public function findOneBy(Array $args) {
        $return = [];
        $where = '1 = 1';
        foreach ($args as $key => $value) {
            $where .= " AND {$key} = '{$value}'";
        }
        $result = $this->mysql->query("SELECT * FROM `{$this->tablename}` WHERE {$where} limit 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $obj = new $this->entity();
                $obj = $obj->create($row);
                array_push($return, $obj);
            }
        }
        return $return;
    }

    public function insert(Array $args) {
        $columns = '';
        $values = '';
        $first_run = true;
        foreach ($args as $key => $value) {
            if (!$first_run) {
                $columns .= ', ';
                $values .= ', ';
            }
            $columns .= "`{$key}`";
            $values .= "'{$value}'";
            $first_run = false;
        }
        $query = "INSERT INTO `{$this->tablename}` ({$columns}) VALUES ({$values})";
        return ($this->mysql->query($query) === TRUE) ? true : false;
    }
}
