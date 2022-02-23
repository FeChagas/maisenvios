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

    /* 
        $join = [
            'tablename' => [
                'parentKey' => 'id',
                'childKey' => 'id',
                'where' => [
                    'column' => 'value'
                ]
            ]
        ];

        $select = ['id', 'MAX(createdAt)', 'foo', 'bar'];
    
    */
    public function findAll(Array $args = [], int $limit = 0, Array $orderBy = [], Array $join = [], Array $select = ['*'], Array $groupBy = [], $raw = false) {
        $return = [];
        $where = ' 1 = 1';
        $select = implode(', ', $select);
        $groupBy = (count($groupBy) > 0) ? 'GROUP BY ' . implode(', ', $groupBy) : '';
        $innerJoin = '';
        $orderingBy = '';
        foreach ($args as $key => $value) {
            $where .= " AND {$this->tablename}.{$key} = '{$value}'";
        }
        foreach ($join as $column => $args) {
            $innerJoin .= " INNER JOIN {$column} ON {$this->tablename}.{$args['parentKey']} = {$column}.{$args['childKey']}";
            foreach ($args['where'] as $key => $value) {
                $where .= " AND {$column}.{$key} = '{$value}'";
            }
        }
        foreach ($orderBy as $key => $value) {
            $orderingBy = "ORDER BY {$key} {$value}";
        }
        $limit = ($limit > 0) ? "LIMIT {$limit}" : '';
        $query = "SELECT {$select} FROM `{$this->tablename}` {$innerJoin} WHERE {$where} {$groupBy} {$orderingBy} {$limit}";
        $result = $this->mysql->query($query);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if (!$raw) {
                    $obj = new $this->entity();
                    $obj = $obj->create($row);
                    array_push($return, $obj);
                } else {
                    array_push($return, $row);
                }
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
