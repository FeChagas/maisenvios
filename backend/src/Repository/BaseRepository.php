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

    public function findAll() {
        $return = [];
        $result = $this->mysql->query("SELECT * FROM `{$this->tablename}`");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $shop = new $this->entity();
                $shop = $shop->create($row);
                array_push($return, $shop);
            }
        }
        return $return;
    }

    public function findOneBy(Array $arr) {
        $return = [];
        $where = '1 = 1';
        foreach ($arr as $key => $value) {
            $where .= " AND {$key} = {$value}";
        }
        $result = $this->mysql->query("SELECT * FROM `{$this->tablename}` WHERE {$where} limit 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $shop = new $this->entity();
                $shop = $shop->create($row);
                array_push($return, $shop);
            }
        }
        return $return;
    }
}
