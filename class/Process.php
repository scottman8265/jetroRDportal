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
        /*  $dsn = 'mysql:host=' . $this->host . ' dbname=' . $this->db . ' port=' . $this->port . ' charset=utf8';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->lnk = new PDO($dsn, $this->user, $this->pass, $options);
            $this->connected = true;
        }
        catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->connected = false;
        }*/

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
        $results = [];
        $type = setType($sql);

        if ($this->error == null && $this->connected == true && $params != null) {
            complexQuery($sql, $params, $type);
        } else if ($this->error == null && $this->connected == true && $params == null) {
            $results = simpleQuery($sql);
        } else {
            $results = [false];
        }

        if ($type = "insert") {
            setLastID();
        } else if ($type = "update") {
            setAffectedRows();
        } else if ($type = "show") {
            setColNames($results);
        }


        $this->qryCount = count($results);

        unset($lnk);

        return $results;
    }

    private function simpleQuery($sql): void
    {
        $results = [];
        $data = [];

        $query = $this->lnk->prepare($sql);
        $query->execute();
        $data = $query->get_result();
        $results = $data->fetch_all(MYSQLI_ASSOC);

        $this->pvtResults = $results;
    }

    private function complexQuery($sql, $params, $type): void
    {
        $results = [];
        $data = [];

        $query = $this->lnk->prepare($sql);
        $query->bind_param(str_repeat('s', count($params)), $params);
        $query->execute();
        $data = $query->get_result();
        $results = $data->fetch_all(MYSQLI_ASSOC);

        $this->pvtResults = $results;
    }

    private function setResults($results): void
    {
        $this->results = $this->pvtResults;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    private function setType($sql): void
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
    }

    public function getType(): string
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
        foreach ($results as $key => $value) {
            $this->colNames[] = $key;
        }
    }

    public function getColNames(): array
    {
        return $this->colNames;
    }

    private function setQueryCount($results): void
    {
        $this->qryCount = count($results);
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
        return $this->error;
    }

    public function getConnectionStatus(): bool
    {
        return $this->connected;
    }
}
