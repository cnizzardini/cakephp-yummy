<?php

namespace Yummy\Service\YummySearch;

use Cake\Database\Query;

class QueryGenerator
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Returns Query object after setting where condition
     *
     * @param Query $query
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return Query
     *
     * @throws Yummy\Exception\YummySearch\QueryException
     */
    public function getWhere(Query $query, string $column, string $operator, string $value)
    {
        $operator = $this->getOperator($operator);

        if ($operator === false) {
            throw new Yummy\Exception\YummySearch\QueryException('Unknown condition encountered');
        }

        if ($this->config['trim'] == true) {
            $value = trim($value);
        }

        $query->where(["$column $operator" => $value]);

        return $query;
    }

    /**
     * Returns the correct SQL operator, returns false if operator not found
     *
     * @param $operator
     * @return string|bool
     */
    private function getOperator($operator)
    {
        $operators = [
            'like' => 'LIKE',
            'not_like' => 'NOT LIKE',
            'gt' => '>',
            'gt_eq' => '>=',
            'lt' => '<',
            'lt_eq' => '<=',
            'eq' => '',
            'not_eq' => '!='
        ];

        if (isset($operators[$operator])) {
            return $operators[$operator];
        }

        return false;

    }
}