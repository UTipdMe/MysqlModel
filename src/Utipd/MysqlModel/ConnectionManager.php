<?php

namespace Utipd\MysqlModel;
use Exception;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Utipd\MysqlModel\NestedPDO;


/*
* ConnectionManager
* For MySQL connections that automatically reconnect on 2006 MySQL server has gone away
*/
class ConnectionManager {

    public $max_allowed_attempts = 10;

    protected $pdo      = null;
    protected $dsn      = null;
    protected $username = null;
    protected $password = null;
    protected $options  = [];

    protected $logger   = null;

    public function __construct($dsn, $username, $password, $options=null, LoggerInterface $logger=null) {
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;

        if ($options !== null) { $this->options = $options; }
        if ($logger !== null) { $this->logger = $logger; }
    }

    public function getConnection() {
        if (!isset($this->pdo)) { $this->pdo = $this->reconnect(); }
        return $this->pdo;
    }

    public function reconnect() {
        $this->pdo = new NestedPDO($this->dsn, $this->username, $this->password, $this->options);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->pdo;
    }


    /*
    // Example:
    $manager = new \Utipd\MysqlModel\ConnectionManager($dsn, $username, $password);
    $sth = $manager->executeWithReconnect(function($mysql_dbh) {
        $sth = $mysql_dbh->prepare("SELECT * FROM mytable WHERE foo=?");
        $result = $sth->execute(['bar']);
        return $sth;
    });
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        echo "\$row:\n".json_encode($row, 192)."\n";
    }
    */
    public function executeWithReconnect($callback) {
        $attempt_count = 0;
        while (++$attempt_count < $this->max_allowed_attempts) {
            try {
                if ($this->pdo === null) {
                    $pdo = $this->reconnect();
                } else {
                    $pdo = $this->pdo;
                }

                return $callback($pdo);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 2006) {
                    // mysql has gone away
                    if (isset($this->logger)) { $this->logger->info("WARNING: ".$e->getMessage()."  Attempting to reconnect (attempt $attempt_count)"); }

                    if ($pdo->isInTransaction()) {
                        // this is inside a transaction, we must bail and let the transaction do the reconnection
                        throw $e;
                    }

                    // sleep with slight backoff
                    if ($attempt_count > 1) { usleep(100000 + $attempt_count * 50000); }
                    
                    // reconnect
                    $pdo = $this->reconnect();

                    // try again
                    continue;
                }

                // some other error occurred
                throw $e;            
            }
        }

        throw new Exception("ERROR: failed to connect to database after $attempt_count attempts", 1);
    }

}

