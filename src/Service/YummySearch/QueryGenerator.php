<?php

namespace Yummy\Service\YummySearch;

use Cake\Database\Query;

class QueryGenerator
{
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
        switch ($operator) {
            case 'eq':
                $query->where([$column => $value]);
                break;
            case 'not_eq':
                $query->where(["$column !=" => $value]);
                break;
            case 'like':
                $query->where(["$column LIKE" => "%$value%"]);
                break;
            case 'not_like':
                $query->where(["$column NOT LIKE" => "%$value%"]);
                break;
            case 'gt':
                $query->where(["$column >" => $value]);
                break;
            case 'lt':
                $query->where(["$column <" => $value]);
                break;
            case 'gt_eq':
                $query->where(["$column >=" => $value]);
                break;
            case 'lt_eq':
                $query->where(["$column <=" => $value]);
                break;
            default:
                throw new Yummy\Exception\YummySearch\QueryException('Unknown condition encountered');
        }

        return $query;
    }
}