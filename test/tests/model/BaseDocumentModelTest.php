<?php

use Utipd\MysqlModel\Test\Directory\FoodocDirectory;
use Utipd\MysqlModel\Test\Model\FoodocModel;
use Utipd\MysqlModel\Test\Util\TestDBHelper;
use Utipd\MysqlModel\Test\Util\TestDBSetup;
use \Exception;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class BaseDocumentModelTest extends \PHPUnit_Framework_TestCase
{


    public function testDocCreateAndSave() {
        $dbh = TestDBHelper::getMySQLDB();
        $directory = new FoodocDirectory(TestDBHelper::getConnectionManager());
        $model = $directory->create([]);
        $model['random_propery'] = 'yes';
        $model['withKey'] = 'bar1';
        $model = $directory->save($model);
        PHPUnit::assertNotNull($model['id']);


        $model = $directory->reload($model);
        PHPUnit::assertEquals('yes', $model['random_propery']);
        PHPUnit::assertEquals('bar1', $model['withKey']);

        // make sure that withKey was inserted into the DB column
        $sth = $dbh->prepare("SELECT * FROM foodoc WHERE id = ?");
        $sth->execute([$model['id']]);
        $raw_row = $sth->fetch();
        PHPUnit::assertEquals('bar1', $raw_row['withKey']);
    }


    public function testDocFindModel() {
        $directory = new FoodocDirectory(TestDBHelper::getConnectionManager());
        $model1 = $directory->createAndSave(['docadded1' => 'barz']);
        PHPUnit::assertTrue($model1 instanceof FoodocModel);
        
        $model = $directory->create([]);
        PHPUnit::assertTrue($model instanceof FoodocModel);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals($model1['id'], $model['id']);
        PHPUnit::assertEquals('barz', $model['docadded1']);
    }


    public function testDocUpdate() {
        $dbh = TestDBHelper::getMySQLDB();
        $directory = new FoodocDirectory(TestDBHelper::getConnectionManager());
        $model1 = $directory->createAndSave(['docadded1' => 'barz']);
        PHPUnit::assertTrue($model1 instanceof FoodocModel);
        
        $result = $directory->update($model1, ['docadded1' => 'updatedbaz', 'withKey' => 'baz1']);
        PHPUnit::assertEquals(1, $result);
        PHPUnit::assertTrue($result === 1);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals('updatedbaz', $model['docadded1']);
        PHPUnit::assertEquals('baz1', $model['withKey']);

        // make sure that withKey was updated into the DB column
        $sth = $dbh->prepare("SELECT * FROM foodoc WHERE id = ?");
        $sth->execute([$model['id']]);
        $raw_row = $sth->fetch();
        PHPUnit::assertEquals('baz1', $raw_row['withKey']);

    }

    public function testDocAdvancedUpdate() {
        $directory = new FoodocDirectory(TestDBHelper::getConnectionManager());
        $model1 = $directory->createAndSave(['docadded1' => ['p1' => 'child1', 'p2' => 'child2']]);
        PHPUnit::assertTrue($model1 instanceof FoodocModel);
        
        $result = $directory->update($model1, ['docadded1' => ['p1' => 'child3']]);
        PHPUnit::assertEquals(1, $result);
        PHPUnit::assertTrue($result === 1);

        $model = $directory->findById($model1['id']);
        // we don't support deep updates
        PHPUnit::assertArrayNotHasKey('p2', $model['docadded1']);
        PHPUnit::assertEquals('child3', $model['docadded1']['p1']);
    }


    public function testDocDelete() {
        $directory = new FoodocDirectory(TestDBHelper::getConnectionManager());
        $model1 = $directory->createAndSave(['docadded1' => 'barz']);
        
        $model = $directory->findById($model1['id']);
        PHPUnit::assertEquals($model1['id'], $model['id']);

        $result = $directory->delete($model1);
        PHPUnit::assertTrue($result);

        $model = $directory->findById($model1['id']);
        PHPUnit::assertNull($model);
    }






    public function setup() {
        TestDBSetup::dropDatabase();
        TestDBSetup::updateMySQLDBs();
    }

}
