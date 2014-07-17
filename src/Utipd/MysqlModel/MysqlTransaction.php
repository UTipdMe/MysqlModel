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

    protected $mysql_dbh = null;

    protected $MAX_ATTEMPTS_ALLOWED = 10;

    ////////////////////////////////////////////////////////////////////////

    public function __construct($mysql_dbh) {
        $this->mysql_dbh = $mysql_dbh;
    }

    public function doInTransaction($function) {
        $attempts = 0;
        while ($attempts++ < $this->MAX_ATTEMPTS_ALLOWED) {
            $this->mysql_dbh->beginTransaction();

            $deadlock_detected = false;
            try {
                $result = $function($this->mysql_dbh);
                $this->mysql_dbh->commit();
                return $result;

            } catch(PDOException $e) {
                if ($e->errorInfo[1] == 1213 OR $e->errorInfo[1] == 1205) {
                    $deadlock_detected = true;
                }
            } catch (Exception $e) {
                // catch the deadlock so we can rollback
            }

            // always rollback
            $this->mysql_dbh->rollback();

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

