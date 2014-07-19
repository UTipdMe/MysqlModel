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
class BaseDocumentMysqlDirectory extends BaseMysqlDirectory
{




    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // create / update modifiers

    protected function onSave_pre($create_vars) {
        // modify create vars going to the database
        return $this->filterVarsForDatabase($create_vars);
    }

    protected function onUpdate_pre($update_vars, $model_or_id) {
        if ($model_or_id instanceof BaseDocumentMysqlModel) {
            $model = $model_or_id;
        } else {
            $model = $this->findByID($model_or_id);
        }

        // merge the updates with the document
        $existing_model_vars = (array)$model;
        $update_vars = array_replace_recursive($existing_model_vars, $update_vars);
        unset($update_vars['id']);

        // modify update vars going to database
        return $this->filterVarsForDatabase($update_vars);
    }

    protected function onLoadFromDB_post($model_vars) {
        // modify vars coming from the database
        $out = [];
        $doc_vars = json_decode($model_vars['document'], true);
        $out = $model_vars;
        unset($out['document']);
        $out = array_merge($out, $doc_vars);
        return $out;
    }


    protected function filterVarsForDatabase($vars) {
        $filtered_create_vars = [];
        if (isset($vars['id'])) {
            $filtered_create_vars['id'] = $vars['id'];
            unset($vars['id']);
        }

        $filtered_create_vars['document'] = json_encode($vars);

        return $filtered_create_vars;
    }


}

