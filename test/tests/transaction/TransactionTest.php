<?php

use Utipd\MysqlModel\MysqlTransaction;
use Utipd\MysqlModel\Test\Directory\FooDirectory;
use Utipd\MysqlModel\Test\Util\TestDBHelper;
use Utipd\MysqlModel\Test\Util\TestDBSetup;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* Requires MySQL running on localhost
* Will create a DB called mysqlmodel_test
*/
class TransactionTest extends \PHPUnit_Framework_TestCase
{

    public function testUpdateAccountTransactionRollback() {
        $connection_manager = TestDBHelper::getConnectionManager();
        $foo_directory = new FooDirectory($connection_manager);
        $foo1 = $foo_directory->createAndSave(['amount' => 1000]);
        $foo2 = $foo_directory->createAndSave(['amount' => 1000]);

        try {
            $transaction = new MysqlTransaction($connection_manager);
            $transaction->doInTransaction(function() use ($foo1, $foo2, $foo_directory) {
                $foo1 = $foo_directory->reload($foo1);
                $foo_directory->update($foo1, ['amount' => 1200]);
                throw new Exception("Test Error: Fail after half update", 1);
            });
        } catch (Exception $e) {
            // do nothing and allow test to continue
            // print "ERROR: ".$e->getMessage()."\n";
        }

        // accounts were rolled back
        $foo1 = $foo_directory->reload($foo1);
        PHPUnit::assertEquals(1000, $foo1['amount']);
        $foo2 = $foo_directory->reload($foo2);
        PHPUnit::assertEquals(1000, $foo2['amount']);

    } 



    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    public function setup() {
        TestDBSetup::dropDatabase();
        TestDBSetup::updateMySQLDBs();
    }


}
