<?php

namespace Yummy\Service\YummySearch;

class Rule
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Checks allow/deny rules to see if model is allowed
     *
     * @param string $model
     * @return boolean
     */
    public function isModelAllowed(string $model)
    {
        $config = $this->config;

        if ($this->hasAccessModel('allow', $config, $model)) {
            return true;
        }

        if ($this->hasAccessModel('deny', $config, $model)) {
            return false;
        }

        return true;
    }

    /**
     * Checks access mode for given config and model.
     *
     * @param string $accessMode
     * @param array $config
     * @param string $model
     * @return bool
     */
    private function hasAccessModel(string $accessMode, array $config, string $model)
    {
        if (!isset($config[$accessMode])) {
            return false;
        }

        if (in_array($model, $config[$accessMode])) {
            return true;
        }

        if (isset($config[$accessMode][$model]) && $config[$accessMode][$model] == '*') {
            return true;
        }

        return false;
    }


    /**
     * Checks allow/deny rules to see if column is allowed. Will return true or int (for sort order) if the column
     * is allowed. False otherwise.
     *
     * @param string $model
     * @param string $column
     * @return boolean|int
     */
    public function isColumnAllowed(string $model, string $column)
    {
        if ($this->isModelAllowed($model) === false) {
            return false;
        }

        $config = $this->config;

        if (isset($config['deny'][$model]) && in_array($column, $config['deny'][$model])) {
            return false;
        }

        // check if in allow columns
        if (!isset($config['allow'][$model])) {
            return true;
        }

        $key = false;

        // check model elements
        if (in_array($column, $config['allow'][$model])) {
            $key = array_search($column, $config['allow'][$model], true);
        // check model keys
        } else if (isset($config['allow'][$model][$column])) {
            $keys = array_keys($config['allow'][$model]);
            $key = array_search($column, $keys, true);
        // look in model columns
        } else if (isset($config['allow'][$model]['_columns'])) {
            // check model column elements
            if (in_array($column, $config['allow'][$model]['_columns'])) {
                $key = array_search($column, $config['allow'][$model]['_columns']);
            // check model column keys
            } else if (isset($config['allow'][$model]['_columns'][$column])) {
                $keys = array_keys($config['allow'][$model]['_columns']);
                $key = array_search($column, $keys, true);
            }
        }

        if ($key >= 0) {
            return $key;
        }

        return true;
    }

}