<?php

namespace Utipd\MysqlModel\Test\Util;

use Exception;

/*
* TestDBSetup
*/
class TestDBSetup
{

    ////////////////////////////////////////////////////////////////////////


    public static function updateMySQLDBs() {
        // update SQL tables
        $sql = file_get_contents(TEST_PATH.'/etc/sql/test-tables.mysql');
        $sql = str_replace('{{db}}', TestDBHelper::getMySQLDBName(), $sql);
        TestDBHelper::getMySQLClient()->exec($sql);
    }

    public static function dropDatabase() {
        $db_name = TestDBHelper::getMySQLDBName();
        TestDBHelper::getMySQLClient()->exec("DROP DATABASE IF EXISTS `$db_name`");
    }

    ////////////////////////////////////////////////////////////////////////

}

