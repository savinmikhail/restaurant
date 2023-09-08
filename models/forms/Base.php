<?php

namespace app\models;

use yii\db\ActiveRecord;

abstract class Base extends ActiveRecord
{
    public function tblTableName()
    {
        return $this->recordTableName();
    }

    protected $changeset = [];

    protected $default_vals = [
    ];

    protected $int_vals = [
    ];

    protected $float_vals = [
    ];

    protected $date_vals = [
    ];

    abstract protected function recordTableName();

    abstract protected function prefixName();

    public function __get($name)
    {
        $generate_name = $this->prefixName().'_'.$name;

        if ($this->hasAttribute($generate_name)) {
            return parent::__get($generate_name);
        }

        return parent::__get($name);
    }

    public function setAttribute($name, $value)
    {
        if ($this->getAttribute($name) != $value) {
            $this->changeset[$name] = [
                'old' => $this->getAttribute($name),
                'new' => $value,
            ];
        }

        parent::setAttribute($name, $value);
    }

    public function __set($name, $value)
    {
        $generate_name = $this->prefixName().'_'.$name;

        if ($this->getAttribute($generate_name) != $value) {
            $old_value = null;

            try {
                $old_value = $this->$name;
            } catch (\Exception $e) {
            }

            $this->changeset[$generate_name] = [
                'old' => $old_value,
                'new' => $value,
            ];
        }

        if ($this->hasAttribute($generate_name)) {
            return parent::__set($generate_name, $value);
        }

        return parent::__set($name, $value);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->hasAttribute('created') && !$this->created) {
            // $this->created = date('Y-m-d H:i:s');
        }

        if ($this->hasAttribute('updated')) {
            //     $this->updated = date('Y-m-d H:i:s');
        }

        foreach ($this->default_vals as $name => $val) {
            if ($this->hasAttribute($name) && !$this->getAttribute($name)) {
                $this->setAttribute($name, $val);
            }
        }

        foreach ($this->int_vals as $name) {
            $this->setAttribute($name, intval($this->getAttribute($name)));
        }

        foreach ($this->float_vals as $name) {
            $this->setAttribute($name, floatval($this->getAttribute($name)));
        }

        foreach ($this->date_vals as $name) {
            $format_date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getAttribute($name));

            if (!$format_date) {
                $format_date = \DateTime::createFromFormat('Y-m-d H:i', $this->getAttribute($name));
            }

            if ($format_date && $format_date->format('Y-m-d H:i:s') > date('Y-m-d H:i:s', 0)) {
                $this->setAttribute($name, $format_date->format('Y-m-d H:i:s'));
            } else {
                $this->setAttribute($name, null);
            }
        }

        return parent::save($runValidation, $attributeNames);
    }

    public static function getCollumnsNames()
    {
        $result = [];
        $columns = self::getTableSchema();

        foreach ($columns->getColumnNames() as $column) {
            $column = $columns->getColumn($column);
            $result[] = $column->name;
        }

        return $result;
    }

    public static function getPrimaryName()
    {
        $columns = self::getTableSchema();

        foreach ($columns->getColumnNames() as $column) {
            $column = $columns->getColumn($column);
            $name = $column->name;

            if ($column->isPrimaryKey) {
                return $name;
            }
        }

        return '';
    }
}
