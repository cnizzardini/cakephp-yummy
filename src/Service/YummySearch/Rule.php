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
     * Checks if any allow column rules exist
     *
     * @return bool
     */
    public function hasAllowRule() : bool
    {
        if (!isset($this->config['allow']) || empty($this->config['allow'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks allow/deny rules to see if column is allowed.
     *
     * @param string $model
     * @param string $column
     * @return boolean
     */
    public function isColumnAllowed(string $model, string $column) : bool
    {
        if (!isset($this->config['allow']) || isset($this->config['allow']["$model.$column"])) {
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
     * @return int
     */
    public function getSortOrder(string $model, string $column) : int
    {
        return array_search(
            "$model.$column",
            array_keys($this->config['allow'])
        );
    }
}