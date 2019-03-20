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

        if ($this->hasAccessMode('allow', $config, $model)) {
            return true;
        }

        if ($this->hasAccessMode('deny', $config, $model)) {
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
    private function hasAccessMode(string $accessMode, array $config, string $model)
    {
        if (in_array($model, $config[$accessMode])) {
            return true;
        }

        if (isset($config[$accessMode][$model]) && $config[$accessMode][$model] == '*') {
            return true;
        }

        return false;
    }


    /**
     * Checks allow/deny rules to see if column is allowed
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

        // check if in allow columns
        if (isset($config['allow'][$model])) {

            $isAllowed = false;

            // check model elements
            if (in_array($column, $config['allow'][$model])) {
                $key = array_search($column, $config['allow'][$model], true);
                $isAllowed = true;
                // check model keys
            } elseif (isset($config['allow'][$model][$column])) {
                $keys = array_keys($config['allow'][$model]);
                $key = array_search($column, $keys, true);
                $isAllowed = true;
                // look in model columns
            } elseif (isset($config['allow'][$model]['_columns'])) {
                // check model column elements
                if (in_array($column, $config['allow'][$model]['_columns'])) {
                    $key = array_search($column, $config['allow'][$model]['_columns']);
                    $isAllowed = true;
                    // check model column keys
                } elseif (isset($config['allow'][$model]['_columns'][$column])) {
                    $keys = array_keys($config['allow'][$model]['_columns']);
                    $key = array_search($column, $keys, true);
                    $isAllowed = true;
                }
            }

            if ($isAllowed === false) {
                return false;
            }

            if ($key >= 0) {
                return $key;
            }
            // check deny all models
        } elseif (isset($config['deny']) && $config['deny'] == '*') {
            return false;
            // check deny specific model
        } elseif (isset($config['deny'][$model]) && $config['deny'][$model] == '*') {
            return false;

            // check deny specific model.column
        } elseif (isset($config['deny'][$model]) && in_array($column, $config['deny'][$model])) {
            return false;
        }

        return true;
    }
}