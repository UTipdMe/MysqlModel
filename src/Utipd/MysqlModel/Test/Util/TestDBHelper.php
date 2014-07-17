<?php

namespace Utipd\MysqlModel\Test\Util;


use Exception;
use UTApp\Debug\Debug;

/*
* TestDBHelper
*/
class TestDBHelper
{

    ////////////////////////////////////////////////////////////////////////


    public static function getMySQLDB() {
        $connection_string = self::buildMySQLConnectionString();
        $db = self::buildMySQLDB();
        return self::buildPDO($connection_string.';dbname='.$db);
    }

    public static function getMySQLClient() {
        $connection_string = self::buildMySQLConnectionString();
        return self::buildPDO($connection_string);
    }

    public static function buildPDO($connection_string) {
        $mysql_user = self::envVarOrDefault('MYSQL_USER', "root");
        $mysql_password = self::envVarOrDefault('MYSQL_PASSWORD', "");
         
        $pdo = new \PDO($connection_string, $mysql_user, $mysql_password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function buildMySQLConnectionString() {
        return self::envVarOrDefault('MYSQL_CONNECTION_STRING', "mysql:host=localhost;port=3306");
    }
    public static function buildMySQLDB() {
        return self::envVarOrDefault('MYSQL_DB', "mysqlmodel_test");
    }

    ////////////////////////////////////////////////////////////////////////

    public static function envVarOrDefault($var_name, $default) {
        return (($env_val = getenv($var_name)) === false) ? $default : $env_val;
    }
}

