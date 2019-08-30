<?php

namespace Yummy\Service\YummySearch;

class Parameter
{
    private $model,
            $column,
            $field,
            $allow = [],
            $operator,
            $value,
            $type,
            $doCastToDate = false;

    public function __construct(string $model, string $column, array $config)
    {
        $this->model = $model;
        $this->column = $column;
        $this->field = $model  . '.' . $column;
        $this->allow = isset($config['allow']) ? $config['allow']: [];
        $this->defineAttributes();
    }

    public function getModel() : string
    {
        return $this->model;
    }

    public function getColumn() : string
    {
        return $this->column;
    }

    public function getOperator() : string
    {
        return $this->operator;
    }

    public function setOperator(string $operator) : self
    {
        $this->operator = $operator;
        return $this;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValue(string $value) : self
    {
        $this->value = $value;
        return $this;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function getDoCastToDate() : bool
    {
        return $this->doCastToDate;
    }

    private function defineAttributes() : void
    {
        if (isset($this->allow[$this->field]['castToDate'])) {
            $this->doCastToDate = $this->allow[$this->field]['castToDate'];
        }
    }
}
