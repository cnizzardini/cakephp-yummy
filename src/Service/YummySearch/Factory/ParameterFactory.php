<?php

namespace Yummy\Service\YummySearch\Factory;

use Yummy\Service\YummySearch\Parameter;

class ParameterFactory
{
    private $config,
            $models;

    public function __construct(array $config, array $models = [])
    {
        $this->config = $config;
        $this->models = $models;
    }

    public function create(string $model, string $column, array $item) : Parameter
    {
        $parameter = new Parameter($model, $column, $this->config);
        $parameter->setOperator($item['operator'])->setValue($item['search']);

        if (isset($this->models[$model]['columns']["$model.$column"]['type'])) {
            $parameter->setType($this->models[$model]['columns']["$model.$column"]['type']);
        }

        return $parameter;
    }
}