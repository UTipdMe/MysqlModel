<?php

namespace Utipd\MysqlModel;

use Exception;
use PDO;


/*
* NestedPDO
* For nestable MySQL transactions
*/
class NestedPDO extends PDO {

    // The current transaction level.
    protected $transaction_level = 0;
 
    public function beginTransaction() {
        if ($this->transaction_level == 0) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->transaction_level}");
        }
 
        $this->transaction_level++;
    }
 
    public function commit() {
        $this->transaction_level--;
 
        if ($this->transaction_level == 0) {
            parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transaction_level}");
        }
    }
 
    public function rollBack() {
        $this->transaction_level--;
 
        if ($this->transaction_level == 0) {
            parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transaction_level}");
        }
    }

}

