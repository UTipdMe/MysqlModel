<?php

namespace Utipd\MysqlModel;

use Exception;
use PDOException;

/*
* MysqlTransaction
*/
class MysqlTransaction
{

    ////////////////////////////////////////////////////////////////////////

    protected $connection_manager = null;

    protected $MAX_ATTEMPTS_ALLOWED = 10;

    ////////////////////////////////////////////////////////////////////////

    public function __construct($connection_manager) {
        $this->connection_manager = $connection_manager;
    }

    public function doInTransaction($function) {
        $attempts = 0;
        while ($attempts++ < $this->MAX_ATTEMPTS_ALLOWED) {
            $pdo = $this->connection_manager->getConnection();
            $pdo->beginTransaction();

            $deadlock_detected = false;
            try {
                $result = $function($this->connection_manager);
                $pdo->commit();
                return $result;

            } catch(PDOException $e) {
                if ($e->errorInfo[1] == 1213 OR $e->errorInfo[1] == 1205) {
                    $deadlock_detected = true;
                }
                if ($e->errorInfo[1] == 2006) {
                    if ($pdo->getTransactionLevel() > 1) {
                        // we can't handle an error in nested transaction
                        throw $e;
                    }

                    // MySQL has gone away error
                    $this->connection_manager->reconnect();
                    continue;
                } 
            } catch (Exception $e) {
                // catch the deadlock so we can rollback
            }

            // always rollback
            $pdo->rollback();

            if (!$deadlock_detected) {
                throw $e;
            }

            // attempt deadlocked query again
            if ($attempts > $this->MAX_ATTEMPTS_ALLOWED) {
                throw $e;
            }

            usleep(250000); // sleep 250 ms before trying again
        }
    }

    ////////////////////////////////////////////////////////////////////////

}

