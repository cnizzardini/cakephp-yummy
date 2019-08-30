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
     * Returns Query with where conditions
     *
     * @param Query $query
     * @param string $model
     * @param Parameter $parameter
     * @return Query
     */
    public function getQuery(Query $query, string $model, Parameter $parameter) : Query
    {
        // for base model searches
        if (empty($model)) {
            return $this->getWhere($query, $parameter);
        }

        return $query->matching($model, function ($q) use ($parameter) {
            return $this->getWhere($q, $parameter);
        });
    }

    /**
     * Returns Query object after setting where condition
     *
     * @param Query $query
     * @param Parameter $parameter
     * @return Query
     *
     * @throws Yummy\Exception\YummySearch\QueryException
     */
    public function getWhere(Query $query, Parameter $parameter) : Query
    {
        $operator = $this->getOperator($parameter->getOperator());

        if ($operator === false) {
            throw new Yummy\Exception\YummySearch\QueryException('Unknown condition encountered');
        }

        $column = $parameter->getModel() . '.' . $parameter->getColumn();
        $value = $this->config['trim'] == true ? trim($parameter->getValue()) : $parameter->getValue();

        if ($parameter->getDoCastToDate() === true) {
            $date = new \DateTime($value);
            $where = "CAST($column as DATE) $operator CAST('" . $date->format('Y-m-d') . "' as DATE)";
            $query->where([$where]);
            return $query;
        }

        if ($operator == 'LIKE' || $operator == 'NOT LIKE') {
            $value = "%$value%";
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
            'eq' => '=',
            'not_eq' => '!='
        ];

        if (isset($operators[$operator])) {
            return $operators[$operator];
        }

        return false;
    }
}
