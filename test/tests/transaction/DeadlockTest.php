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
class DeadlockTest extends \PHPUnit_Framework_TestCase
{

    // don't run this by default - it is lengthly
    protected $do_deadlock_test = FALSE;

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    public function testDeadlock() {
        // this test is very time consuming, we skip it by default
        if (!$this->do_deadlock_test) { return; }

        $db = TestDBHelper::getMySQLDB();

        $manager = new Spork\ProcessManager();
        $fork = $manager->fork(function() {
            // do something in child process!
            sleep(1); // sleep for a second to let parent set things up
            $db = TestDBHelper::getMySQLDB();

            $db->prepare("SET SESSION innodb_lock_wait_timeout = 3")->execute();
            $db->beginTransaction();
            $sth = $db->prepare("SELECT * FROM foo where amount = 0");
            PHPUnit::assertTrue($sth->execute());
            $sth = $db->prepare("UPDATE foo SET amount = 0 WHERE amount <> 0");
            PHPUnit::assertTrue($sth->execute());

#            Debug::trace("child sleeping for 10 secs",__FILE__,__LINE__,$this);
            sleep(10);
#            Debug::trace("child done",__FILE__,__LINE__,$this);
            $db->commit();
#            Debug::trace("child exiting",__FILE__,__LINE__,$this);
        });




        $db = TestDBHelper::getMySQLDB();
        $foo_directory = new FooDirectory($db);
        $foo1 = $foo_directory->createAndSave(['amount' => 0]);
        $foo2 = $foo_directory->createAndSave(['amount' => 1]);


        // let child process start first
        sleep(2);

        $transaction = new MysqlTransaction($db);
        $transaction->doInTransaction(function($db) {
            $db->prepare("SET SESSION innodb_lock_wait_timeout = 3")->execute();

            $sth = $db->prepare("SELECT * FROM foo where amount = 1");
            if (!$sth->execute()) {
                $error_info = $sth->errorInfo();
#                Debug::trace("\$error_info=",$error_info,__FILE__,__LINE__,$this);
            }
            $sth = $db->prepare("UPDATE foo SET amount = 2 WHERE amount <> 1");
            if (!$sth->execute()) {
                $error_info = $sth->errorInfo();
#                Debug::trace("\$error_info=",$error_info,__FILE__,__LINE__,$this);
            }
        });

        $foo = $foo_directory->findOne(['id' => $foo1['id']]);
        PHPUnit::assertEquals(2, $foo['amount']);
    } 

    public function setup() {
        TestDBSetup::dropDatabase();
        TestDBSetup::updateMySQLDBs();
    }


}
