<?php

namespace Yummy\Service\YummySearch;

use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Inflector;

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
     * @param ConnectionInterface $database
     * @param string $name
     * @return array
     *
     * @throw Yummy\Exception\YummySearch\SchemaException
     */
    public function getColumns(ConnectionInterface $database, string $name)
    {
        $collection = $database->schemaCollection();

        $data = [];
        $tableName = Inflector::underscore($name);

        $modelName = Inflector::camelize($tableName);

        try{
            $schema = $collection->describe($tableName);
            $columns = $schema->columns();
        } catch(\Cake\Database\Exception $e) {
            throw new Yummy\Exception\YummySearch(
                "Unable to determine schema. Does this controller have an associated "
                . "database schema? Try manually defining the model YummySearch should use.  "
                . "\Cake\Database\Exception: " . $e->getMessage()
            );
        }

        foreach ($columns as $column) {

            $allowed = $this->rule->isColumnAllowed($modelName, $column);

            if ($allowed !== false) {
                continue;
            }

            $columnMeta = $schema->column($column);

            $data["$modelName.$column"] = [
                'column' => $column,
                'text' => Inflector::humanize($column),
                'type' => $columnMeta['type'],
                'length' => $columnMeta['length'],
                'sort-order' => $allowed === true ? null : $allowed
            ];
        }

        return $data;
    }
}