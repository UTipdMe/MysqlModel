<?php

use Utipd\MysqlModel\Test\Directory\FooDirectory;
use Utipd\MysqlModel\Test\Model\FooModel;
use Utipd\MysqlModel\Test\Util\TestDBHelper;
use Utipd\MysqlModel\Test\Util\TestDBSetup;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class BaseModelTest extends \PHPUnit_Framework_TestCase
{


    public function testDesc() {
        $dir = new FooDirectory(TestDBHelper::getMySQLDB());
        $model = $dir->create();
        PHPUnit::assertEquals("{Anonymous FooModel}", $model->desc());
    }

    public function testGetDirectory() {
        $dir = new FooDirectory(TestDBHelper::getMySQLDB());
        $model = $dir->create();
        $directory = $model->getDirectory();
        PHPUnit::assertTrue($directory instanceof FooDirectory);
    }

    public function testSerializeJSON() {
        $dir = new FooDirectory(TestDBHelper::getMySQLDB());
        $model = $dir->create(['baz' => 'one']);
        PHPUnit::assertContains('"baz":"one"', json_encode($model));
    }



    public function setup() {
        TestDBSetup::updateMySQLDBs();
    }

}
