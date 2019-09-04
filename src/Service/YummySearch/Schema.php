<?php

namespace Yummy\Service\YummySearch;

use Cake\Database\Connection;
use Cake\Database\Schema\TableSchema;
use Cake\Utility\Inflector;
use Yummy\Exception\YummySearch\SchemaException;

class Schema
{
    private $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Returns array of columns after checking allow/deny rules
     *
     * @param Connection $connection
     * @param string $modelName
     * @return array
     *
     * @throw Yummy\Exception\YummySearch\SchemaException
     */
    public function getColumns(Connection $connection, string $modelName) : array
    {
        $data = [];
        $collection = $connection->getSchemaCollection();
        $tableName = Inflector::underscore($modelName);
        $modelName = Inflector::camelize($tableName);

        try{
            $schema = $collection->describe($tableName);
            $columns = $schema->columns();
        } catch(\Cake\Database\Exception $e) {
            throw new SchemaException(
                "Unable to determine schema. Does this controller have an associated "
                . "database schema? Try manually defining the model YummySearch should use.  "
                . "\Cake\Database\Exception: " . $e->getMessage()
            );
        }

        foreach ($columns as $column) {

            if ($this->rule->hasAllowRule() && !$this->rule->isColumnAllowed($modelName, $column)) {
                continue;
            }

            $data["$modelName.$column"] = $this->buildData($schema, $modelName, $column);
        }

        return $data;
    }

    /**
     * Builds Yummy compatible schema data
     *
     * @param TableSchema $schema
     * @param string $modelName
     * @param string $column
     * @return array
     */
    private function buildData(TableSchema $schema, string $modelName, string $column) : array
    {
        $columnMeta = $schema->getColumn($column);

        return [
            'column' => $column,
            'text' => Inflector::humanize($column),
            'type' => $columnMeta['type'],
            'length' => $columnMeta['length'],
            'sort-order' => $this->rule->hasAllowRule() ? $this->rule->getSortOrder($modelName, $column) : null
        ];
    }
}