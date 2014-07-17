<?php

use MongoModel\Util\ModelUtil;
use MongoModel\Util\SerialUtil;
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
* Requires MySQL running on localhost
* Will create a DB called mysqlmodel_test
*/
class BaseDirectoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateInMemory() {
        $directory = new FooDirectory(TestDBHelper::getMySQLDB());
        $model = $directory->create(['memoryOnly' => 'yes']);
        PHPUnit::assertObjectNotHasAttribute('id', $model);
        PHPUnit::assertEquals('yes', $model['memoryOnly']);
    }

    public function testCreateAndSave() {
        $directory = new FooDirectory(TestDBHelper::getMySQLDB());
        $model = $directory->create([]);
        $model['added1'] = 'yes';
        $model = $directory->save($model);
        PHPUnit::assertNotNull($model['id']);


        $model = $directory->reload($model);
        PHPUnit::assertEquals('yes', $model['added1']);
    }


    public function testFindModel() {
        $directory = new FooDirectory(TestDBHelper::getMySQLDB());
        $model1 = $directory->createAndSave(['added1' => 'barz']);
        PHPUnit::assertTrue($model1 instanceof FooModel);
        
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof FooModel);

        $model = $directory->findOne(['added1' => 'barz']);
        PHPUnit::assertInstanceOf('Utipd\MysqlModel\Test\Model\FooModel', $model);
        PHPUnit::assertEquals($model1['id'], $model['id']);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals($model1['id'], $model['id']);
    }


    public function testUpdate() {
        $directory = new FooDirectory(TestDBHelper::getMySQLDB());
        $model1 = $directory->createAndSave(['added1' => 'barz']);
        PHPUnit::assertTrue($model1 instanceof FooModel);
        
        $result = $directory->update($model1, ['added1' => 'updatedbaz']);
        PHPUnit::assertEquals(1, $result);
        PHPUnit::assertTrue($result === 1);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals('updatedbaz', $model['added1']);
    }


    public function testDelete() {
        $directory = new FooDirectory(TestDBHelper::getMySQLDB());
        $model1 = $directory->createAndSave(['added1' => 'barz']);
        
        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals($model1['id'], $model['id']);

        $result = $directory->delete($model1);
        PHPUnit::assertTrue($result);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertNull($model);
    }


    public function testGetSpecialModels() {
        $directory = new BarDirectory(TestDBHelper::getMySQLDB());
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof BarModel);
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof BarModel);

        $directory = new BazDirectory(TestDBHelper::getMySQLDB());
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof SpecialBazModel);
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof SpecialBazModel);
    }



    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    public function setup() {
        TestDBSetup::dropDatabase();
        TestDBSetup::updateMySQLDBs();
    }


}
