<?php

namespace Utipd\MysqlModel;

use ArrayObject;
use Exception;

/*
* BaseMysqlModel
*/
class BaseMysqlModel extends ArrayObject
{

    function __construct(BaseMysqlDirectory $directory, $create_vars=array()) {
        $this->setDirectory($directory);

        parent::__construct($create_vars);
    }


    /**
     * the directory class
     * @var BaseMysqlDirectory
     */
    protected $directory = null;


    /**
     * gets the ID
     * @return ID
     */
    public function getID() {
        return isset($this['id']) ? $this['id'] : null;
    }

    /**
     * gets the BaseMysqlDirectory
     * @return BaseMysqlDirectory
     */
    public function getDirectory() {
        return $this->directory;
    }

    /**
     * reloads this model from the database
     * @return BaseMysqlModel
     */
    public function reload() {
        return $this->getDirectory()->reload($this);
    }




    /**
     * describes this item as a string
     * @return string
     */
    public function __toString() {
        return $this->desc();
    }

    /**
     * describes this item as a string
     * @return string
     */
    public function desc() {
        $class = implode('', array_slice(explode('\\', get_class($this)), -1));
        if (!isset($this['id'])) {
            return "{Anonymous {$class}}";
        }
        return "{{$class} {$this['id']}}";
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function setDirectory(BaseMysqlDirectory $directory) {
        $this->directory = $directory;
    }

}

