<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/22/17
 * Time: 10:47 PM
 */

if(file_exists('inc/config.php')) {
    require_once('inc/config.php');
} else {
    require_once('../inc/config.php');
}


class Process
{

    /**
     * @var PDO
     */
    private $lnk = null;
    /**
     * @var PDOException
     */
    public  $error = null;
    private $host  = DB_SERVER;
    private $user  = DB_USER;
    private $pass  = DB_PASS;
    private $db    = DB_NAME;
    public  $lastID = null;
    public  $qryCount = 0;
    public $colCount = 0;
    public $colNames = null;
    public $connected = false;

    public function __construct() {
        ini_set('mysql.connect_timeout', '300');
        ini_set('default_socket_timeout', '300');
        ini_set('max_allowed_packet', '256M');
    }

    private function connect() {
        $dsn = 'mysql:host=' . $this->host . 'port=3306;dbname=' . $this->db;
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
        }

        return $this->lnk;

    }

    public function query($sql, $params = null) {

        $this->connect();

        $select = preg_match('/SELECT/', $sql) ? true : false;
        $insert = preg_match('/INSERT/', $sql) ? true : false;
        $update = preg_match('/UPDATE/', $sql) ? true : false;
        $show = preg_match('/SHOW/', $sql) ? true : false;

        if ($this->error == null) {
            $query = $this->lnk->prepare($sql);
            $query->execute($params);

            $this->qryCount = $query->rowCount();

            $query ? (!$select && !$show ? $results = true : $results = $query->fetchAll(PDO::FETCH_ASSOC))
                : $results = null;
        } else {
            $results = $this->error;
        }


        unset($lnk);

        return $results;
    }

    public function colNames($sql) {

        $this->connect();

        $query = $this->lnk->prepare($sql);
        $query->execute();

        $this->colNames = $query->fetchAll(PDO::FETCH_ASSOC);

        $this->colCount = $query->rowCount();

        return $this->colNames;


    }

    public function getLastID() {

        if (!isset($this->lastID)) {
            return false;
        } else {
            return $this->lastID;
        }

    }

    public function getQryCount() {

        if (!isset($this->qryCount)) {
            return false;
        } else {
            return $this->qryCount;
        }
    }

    public function getColNames() {
        return $this->colNames;
    }

    public function getColCount() {
        return $this->colCount;
    }

    public function getError() {
        return $this->error;
    }

    public function getConnectionStatus()
     {
        return $this->connected;
    }


}