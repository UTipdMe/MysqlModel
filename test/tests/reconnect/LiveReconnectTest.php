<?php

use Utipd\MysqlModel\BaseMysqlModel;
use Utipd\MysqlModel\Test\Directory\BarDirectory;
use Utipd\MysqlModel\Test\Directory\BazDirectory;
use Utipd\MysqlModel\Test\Directory\FooDirectory;
use Utipd\MysqlModel\Test\Model\FooModel;
use Utipd\MysqlModel\Test\Model\Special\BarModel;
use Utipd\MysqlModel\Test\Model\Special\SpecialBazModel;
use Utipd\MysqlModel\Test\Util\TestDBHelper;
use Utipd\MysqlModel\Test\Util\TestDBSetup;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
*/
class LiveReconnectTest extends \PHPUnit_Framework_TestCase
{

    // to run this test set the DO_LIVE_RECONNECT environment variable
    //   and restart MySQL during the "sleeping" phase
    public function testLiveReconnect() {
        if (!getenv('DO_LIVE_RECONNECT')) { $this->markTestIncomplete(); }

        $directory = new FooDirectory(TestDBHelper::getConnectionManager());
        $model = $directory->create([]);
        $model['added1'] = 'yes';
        $model = $directory->save($model);
        PHPUnit::assertNotNull($model['id']);

        // sleep for a bit to force a mysql disconnection
        $sleep = 10;
        fwrite(STDOUT, "\nsleeping for $sleep seconds");
        for ($i=0; $i < $sleep; $i++) { fwrite(STDOUT, "."); sleep(1); }
        fwrite(STDOUT, "done.\n");

        $model = $directory->reload($model);
        PHPUnit::assertEquals('yes', $model['added1']);
    }



    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    public function setup() {
        TestDBSetup::dropDatabase();
        TestDBSetup::updateMySQLDBs();
    }


}
