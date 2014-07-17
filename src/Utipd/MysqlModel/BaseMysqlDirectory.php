<?php

namespace Utipd\MysqlModel;


use Exception;
use PDO;
use Utipd\MysqlModel\BaseMysqlModel;
use Utipd\MysqlModel\MysqlLiteral;

/*
* BaseMysqlDirectory
* This is a simple CRUD handler for a Mysql data row mapped to a simple object
*/
class BaseMysqlDirectory
{


    /**
     * an optional collection name
     * This will be determined automatically by the class name if left null
     * @var string
     */
    protected $table_name = null; // like 'account'

    /**
     * An optional model namespace
     *
     * like MyClasses\Model
     * @var string
     */
    protected $model_namespace = null;

    /**
     * an optional fully qualified class name for the model
     * like MyClasses\MySpecialModels\SpecialModel
     * @var string
     */
    protected $model_class = null;


    protected $mysql_dbh = null;

    ////////////////////////////////////////////////////////////////////////

    public function __construct($mysql_dbh) {
        $this->mysql_dbh = $mysql_dbh;
    }

    /**
     * creates a new model
     * @param  array  $create_vars
     * @return BaseMysqlModel a new model
     */
    public function createAndSave($create_vars=[]) {
        // build new object
        $new_model = $this->create($create_vars);

        // save it to the database
        $model = $this->save($new_model);

        return $model;
    }

    /**
     * creates a model without saving to the database
     * @param  array $create_vars
     * @return BaseMysqlModel a new model
     */
    public function create($create_vars=[]) {
        // add
        $new_model_doc_vars = array_merge($this->newModelDefaults(), $create_vars);
        $new_model_doc_vars = $this->onCreate_pre($new_model_doc_vars);

        return $this->newModel($new_model_doc_vars);
    }

    /**
     * saves a new model to the database
     * @param  array $create_vars
     * @return BaseMysqlModel a new model
     */
    public function save(BaseMysqlModel $model) {
        $create_vars = (array)$model;

        $sql = $this->buildInsertStatement($create_vars);
        $sth = $this->mysql_dbh->prepare($sql);
        $result = $sth->execute(array_values($create_vars));

        $id = $this->mysql_dbh->lastInsertId(); 
        $model['id'] = $id;

        return $model;
    }


    /**
     * finds a model by ID
     * @param  BaseMysqlModel|MongoID|string $model_or_id
     * @return BaseMysqlModel|null
     */
    public function findByID($model_or_id, $options=null) {
        $id = $this->extractID($model_or_id);
        return $this->findOne(['id' => $id], null, $options);
    }

    /**
     * queries the database for models
     * @param  array $query
     * @param  array $sort
     * @param  array $limit
     * @return Iterator a collection of BaseMysqlModels
     */

    public function find($vars, $sort=null, $limit=null, $options=null) {
        $sql = $this->buildSelectStatement(array_keys($vars), $sort, $limit, $options);
        $sth = $this->mysql_dbh->prepare($sql);
        $result = $sth->execute(array_values($vars));
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            yield $this->newModel($row);
        }
    }

    /**
     * queries the database for a single object
     * @param  array $query
     * @return BaseMysqlModel or null
     */
    public function findOne($vars, $order_by_keys=null, $options=null) {
        foreach ($this->find($vars, $order_by_keys, 1, $options) as $row) {
            return $this->newModel($row);
        }
        return null;
    }

    /**
     * Finds all models for this collection
     * @return Iterator a collection of BaseMysqlModels
     */
    public function findAll() {
        return $this->find([]);
    }


    /**
     * reloads the model from the database
     * @param  BaseMysqlModel|MongoID|string $model_or_id
     * @return BaseMysqlModel
     */
    public function reload($model_or_id) {
        return $this->findByID($model_or_id);
    }


    /**
     * reloads the model from the database
     * @param  BaseMysqlModel|MongoID|string $model_or_id
     * @return BaseMysqlModel
     */
    public function reloadForUpdate($model_or_id) {
        return $this->findByID($model_or_id, ['forUpdate' => true]);
    }



    /**
     * updates a model in the database
     * @param  BaseMysqlModel|MongoID|string $model_or_id
     * @param  array $update_vars
     * @param  array $mongo_options
     * @return BaseMysqlModel
     */
    public function update($model_or_id, $update_vars, $mongo_options=[]) {
        $id = $this->extractID($model_or_id);

        $update_vars = $this->onUpdate_pre($update_vars);

        $sql = $this->buildUpdateStatement($update_vars, ['id']);

        $sth = $this->mysql_dbh->prepare($sql);
        $vars = $this->removeMysqlLiterals(array_values($update_vars));
        $vars[] = $id;
        $result = $sth->execute($vars);
        return $sth->rowCount();
    }

    public function delete(BaseMysqlModel $model) {
        
        $sql = $this->buildDeleteStatement(['id']);
        $sth = $this->mysql_dbh->prepare($sql);

        $vars = [$model['id']];
        $result = $sth->execute($vars);
        return $result;
    }

    // /**
    //  * deletes all items in this collection
    //  */
    // public function deleteAll() {
    //     $this->getCollection()->remove([]);
    // }


    /**
     * builds DB indexes specific to this directory
     */
    public function bringUpToDate() {
        // parent::bringUpToDate();

        // build indexes, etc
        // $this->getCollection()->ensureIndex(['primaryUser' => 1], ['unique' => true]);

        return;
    }


    ////////////////////////////////////////////////////////////////////////

    protected function getDefaultCreateVars($data) {
        // abstract
        return $data;
    }


    ////////////////////////////////////////////////////////////////////////

    protected function buildInsertStatement($data) {
        $fields = '';
        $values = '';
        $first = true;
        foreach($data as $data_key => $data_val) {
            $fields .= ($first?'':',')."`$data_key`";
            $values .= ($first?'':',')."?";
            $first = false;
        }
        $table_name = $this->getTableName();
        return "INSERT INTO `{$table_name}` ({$fields}) VALUES ({$values})";
    }

    protected function buildUpdateStatement($data, $where_keys) {
        $set_expresssion = '';
        $first = true;
        foreach($data as $data_key => $data_val) {
            $val_expression = '?';
            if ($data_val instanceof MysqlLiteral) { $val_expression = $data_val->getText(); }

            $set_expresssion .= ($first?'':',')."`$data_key` = {$val_expression}";

            $first = false;
        }

        $where_expresssion = $this->buildWhereExpression($where_keys);
        $table_name = $this->getTableName();
        return "UPDATE `{$table_name}` SET {$set_expresssion} WHERE {$where_expresssion}";
    }

    protected function buildDeleteStatement($where_keys) {
        $where_expresssion = $this->buildWhereExpression($where_keys);
        $table_name = $this->getTableName();
        return "DELETE FROM `{$table_name}` WHERE {$where_expresssion}";
    }

    protected function buildSelectStatement($where_keys, $order_by_keys=null, $limit=null, $options=null) {
        $where_expresssion = $this->buildWhereExpression($where_keys);
        $table_name = $this->getTableName();
        $sql = "SELECT * FROM `{$table_name}`".(strlen($where_expresssion) ? " WHERE {$where_expresssion}" : '');
        if ($order_by_keys !== null) {
            $order_by_expression = '';
            $first = true;
            foreach($order_by_keys as $order_by_field => $sort_direction) {
                $order_by_expression .= ($first?'':', ')."`$order_by_field` ".($sort_direction == -1 ? 'DESC' : 'ASC');
                $first = false;
            }
            $sql .= " ORDER BY {$order_by_expression}";
        }

        if ($options AND isset($options['forUpdate']) AND $options['forUpdate']) {
            $sql .= " FOR UPDATE";
        }

        return $sql;
    }


    protected function buildWhereExpression($where_keys) {
        $where_expresssion = '';
        $first = true;
        foreach($where_keys as $where_key) {
            $where_expresssion .= ($first?'':' AND ')."`{$where_key}` = ?";
            $first = false;
        }
        return $where_expresssion;
    }



    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////

    protected function newModel($data) {
        if (isset($this->model_class)) {
            $class = $this->model_class;

        } else {
            $namespace_tokens = explode('\\', get_class($this));

            // transforms FooDirectory to FooModel
            $directory_class = implode('', array_slice($namespace_tokens, -1));
            $model_class_name = substr($directory_class, 0, -9)."Model";

            if (!$this->model_namespace) {
                // transforms ACME\MyStuff\Directory to ACME\MyStuff\Model
                $this->model_namespace = implode('\\', array_slice($namespace_tokens, 0, -2)).'\\Model';
            }

            $this->model_class = "{$this->model_namespace}\\{$model_class_name}";
            $class = $this->model_class;
        }

        return new $class($this, $data);
    }

    protected function newModelDefaults() {
        $out = [];
        return $out;
    }



    protected function extractID($item_or_id) {
        if (is_object($item_or_id)) { return $item_or_id['id']; }
        $id = intval($item_or_id);
        if ($id <= 0) { throw new Exception("Unknown ID: ".json_encode($item_or_id)."", 1); }
        return $id;
    }

    protected function getTableName() {
        if ($this->table_name === null) {
            $directory_class = implode('', array_slice(explode('\\', get_class($this)), -1));
            $this->table_name = strtolower(substr($directory_class, 0, -9))."";
        }

        return $this->table_name;
    }

    protected function removeMysqlLiterals($array) {
        $out = [];
        foreach($array as $array_val) {
            if ($array_val instanceof MysqlLiteral) { continue; }
            $out[] = $array_val;
        }
        return $out;
    }


    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // create / update modifiers

    protected function onCreate_pre($create_vars) {
        // modify all create operations
        return $this->onCreateOrUpdate_pre($create_vars);
    }

    protected function onUpdate_pre($update_vars) {
        // modify all updates
        return $this->onCreateOrUpdate_pre($update_vars);
    }
    

    protected function onCreateOrUpdate_pre($vars) {
        return $vars;
    }    



}

