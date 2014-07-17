<?php

namespace Utipd\MysqlModel\Test\Util;

use Exception;
use UTApp\Debug\Debug;

/*
* TestDBSetup
*/
class TestDBSetup
{

    ////////////////////////////////////////////////////////////////////////


    public static function updateMySQLDBs() {
        // update SQL tables
        $sql = file_get_contents(TEST_PATH.'/etc/sql/test-tables.mysql');
        $sql = str_replace('{{db}}', TestDBHelper::buildMySQLDB(), $sql);
        TestDBHelper::getMySQLClient()->exec($sql);
    }

    public static function dropDatabase() {
        $db_name = TestDBHelper::buildMySQLDB();
        TestDBHelper::getMySQLClient()->exec("DROP DATABASE IF EXISTS `$db_name`");
    }

    ////////////////////////////////////////////////////////////////////////

}

