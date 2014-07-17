<?php

namespace Utipd\MysqlModel\Test\Directory;


use Utipd\MysqlModel\BaseMysqlDirectory;
use \Exception;

/*
* BazDirectory
*/
class BazDirectory extends BaseMysqlDirectory
{

    protected $model_class = 'Utipd\\MysqlModel\\Test\\Model\\Special\\SpecialBazModel';

}
