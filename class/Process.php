<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/22/17
 * Time: 10:47 PM
 */

if (file_exists('inc/config.php')) {
    require_once('inc/config.php');
} else {
    require_once('../inc/config.php');
}


class Process
{

    private $lnk = null;
    public  $error = null;
    private $host  = DB_SERVER;
    private $user  = DB_USER;
    private $pass  = DB_PASS;
    private $db    = DB_NAME;
    private $port  = DB_PORT;
    public  $lastID = null;
    public $type = null;
    public  $qryCount = 0;
    public $affectedRows = 0;
    public $colCount = 0;
    public $colNames = [];
    public $connected = false;
    public $results = [];
    private $pvtResults = [];

    public function __construct()
    {
        ini_set('mysql.connect_timeout', '300');
        ini_set('default_socket_timeout', '300');
        ini_set('max_allowed_packet', '256M');
    }

    private function connect()
    {

        $this->lnk = new mysqli($this->host, $this->user, $this->pass, $this->db, $this->port);
        if ($this->lnk->connect_errno) {
            $this->error = $this->lnk->connect_error;
            $this->connected = false;
        } else {
            $this->connected = true;
        }

        return $this->lnk;
    }

    public function query($sql, $params = null)
    {
        $this->connect();
        
        $type = $this->setTypes($sql);

        if ($this->error == null && $this->connected == true && $params != null) {
            $this->complexQuery($sql, $params);
        } else if ($this->error == null && $this->connected == true && $params == null) {
            $this->results = $this->simpleQuery($sql);
        } else {
            $this->results = [$this->error = 'error'];
        }

        if ($type == "insert") {
            $this->setLastID();
        } else if ($type == "update") {
            $this->setAffectedRows();
        } else if ($type == "show") {
            $this->setColNames($this->results);
        }

        $this->setQryCount($this->results);

        $this->setResults();

        $this->lnk->close();

        return $this->results;
    }

    private function simpleQuery($sql): array
    {
        $data = [];

        $query = $this->lnk->prepare($sql);
        if(!$query) {
            $this->error = $this->lnk->error;
            return $this->error;
        }
        $query->execute();
        $data = $query->get_result();
        $results = $data->fetch_all(MYSQLI_ASSOC);

        $this->pvtResults = $results;

        return $this->pvtResults;
    }

    private function complexQuery($sql, $params): array
    {
        $data = [];

        $query = $this->lnk->prepare($sql);
        $params_ref = array();

        if(!$query) {
            $this->error = $this->lnk->error;
            return $this->error;
        }

        foreach ($params as $key => $value) $params_ref[$key] = &$params[$key];
        call_user_func_array(array($query, 'bind_param'), array_merge(array(str_repeat('s', count($params))), $params_ref));

        $query->execute();
        $data = $query->get_result();
        $results = $data->fetch_all(MYSQLI_ASSOC);

        $this->pvtResults = $results;

        return $this->pvtResults;
    }

    private function setResults(): void
    {
        $this->results = $this->pvtResults;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    private function setTypes($sql): string
    {
        $type = null;
        switch (true) {
            case preg_match('/SELECT/', $sql):
                $type = 'select';
                break;
            case preg_match('/INSERT/', $sql):
                $type = 'insert';
                break;
            case preg_match('/UPDATE/', $sql):
                $type = 'update';
                break;
            case preg_match('/SHOW/', $sql):
                $type = 'show';
                break;
        }
        $this->type = $type;

        return $this->type;
    }

    public function getTypes(): string
    {
        return $this->type;
    }

    private function setLastID(): void
    {
        $this->lastID = $this->lnk->insert_id;
    }

    public function getLastID(): int
    {
        return $this->lastID;
    }

    private function setAffectedRows(): void
    {
        $this->affectedRows = $this->lnk->affected_rows;
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    private function setColNames($results): void
    {
        foreach ($this->results as $key => $value) {
            $this->colNames[] = $key;
        }
    }

    public function getColNames(): array
    {
        return $this->colNames;
    }

    private function setQryCount($results): void
    {
        if(is_array($results)) {
            $this->qryCount = count($results);
        } else {
            $this->qryCount = 0;
        }
     }
    

    public function getQryCount(): int
    {

        if (!isset($this->qryCount)) {
            return false;
        } else {
            return $this->qryCount;
        }
    }

    public function getColCount(): int
    {
        return $this->colCount;
    }

    public function getError(): string
    {
        If($this->error != null){
            return $this->error;
        } else {
            return false;
        }
 
    }

    public function getConnectionStatus(): bool
    {
        return $this->connected;
    }

}