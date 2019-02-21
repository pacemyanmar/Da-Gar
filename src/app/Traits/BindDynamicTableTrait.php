<?php

namespace App\Traits;

trait BindDynamicTableTrait
{
    protected $connection = null;
    protected $table = null;

    public function bind(string $table, string $connection = '')
    {
        if(!empty($connection)) {
            $this->setConnection($connection);
        }

        $this->table = $table;
        $this->setTable($table);
        return $this;
    }

    public function newInstance($attributes = [], $exists = false)
    {
    // Overridden in order to allow for late table binding.

        $model = parent::newInstance($attributes, $exists);
        $model->setTable($this->getTable());
        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

}