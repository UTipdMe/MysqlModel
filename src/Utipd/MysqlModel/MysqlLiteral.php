<?php

namespace Utipd\MysqlModel;

use Exception;
use PDOException;

/*
* MysqlLiteral
* protect update and insert vars for advanced queries
*/
class MysqlLiteral
{

    ////////////////////////////////////////////////////////////////////////

    protected $text = '';

    ////////////////////////////////////////////////////////////////////////

    public function __construct($text) {
        $this->text = $text;
    }

    public function getText() {
        return $this->text;
    }

}

