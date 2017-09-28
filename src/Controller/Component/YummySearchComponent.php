<?php

namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use Cake\Network\Exception\InternalErrorException;

/**
 * This component is a should be used in conjunction with the YummySearchHelper for building rudimentary search filters
 */
class YummySearchComponent extends Component
{

    private $models = false;
    protected $_defaultConfig = [
        'operators' => [
            'like' => 'Containing',
            'not_like' => 'Not Containing',
            'gt' => 'Greater than',
            'gt_eq' => 'Greater than or equal',
            'lt' => 'Less than',
            'lt_eq' => 'Less than or equal',
            'eq' => 'Exact Match',
            'not_eq' => 'Not Exact Match',
        ],
        'dataSource' => 'default',
        'selectGroups' => true,
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);

        if (isset($config['operators'])) {
            $this->setConfig('operators', $config['operators']);
        }

        $this->controller = $this->_registry->getController();

        if (!isset($config['model'])) {
            $this->setConfig('model', $this->controller->name);
        }

        if (isset($config['dataSource'])) {
            $this->setConfig('dataSource', 'default');
        }

        if (isset($config['selectGroups'])) {
            $this->setConfig('selectGroups', $config['selectGroups']);
        }

        // Create a schema collection.
        $database = ConnectionManager::get($this->getConfig('dataSource'));
        $this->collection = $database->schemaCollection();

        $this->defineModels();
    }

    /**
     * beforeRender - sets fields for use by YummySearchHelper
     */
    public function beforeRender()
    {
        // check components
        $this->checkComponents();

        // set array for use by YummySearchHelper
        $yummy = $this->getYummyHelperData();

        // make yummy search data available to view
        $this->controller->set('YummySearch', $yummy);
    }

    /**
     * checkComponents - throws exception if missing a required component
     * @throws InternalErrorException
     */
    private function checkComponents()
    {
        if (!isset($this->controller->Paginator)) {
            throw new \Cake\Network\Exception\InternalErrorException(__('YummySearch requires Paginator Component'));
        }
    }

    /**
     * getYummyHelperData - retrieves an array used by YummySearchHelper
     * @return array
     */
    private function getYummyHelperData()
    {
        $selectOptions = [];

        $request = $this->controller->request;

        foreach ($this->models as $camelName => $model) {
            foreach ($model['columns'] as $column => $field) {

                $humanName = $model['humanName'];

                $meta = $this->getYummyMeta($camelName, $field['column']);

                $element = [
                    'text' => ($meta['niceName'] !== false) ? $meta['niceName'] : $field['text'],
                    'path' => $model['path'],
                    'value' => $column,
                    'data-items' => ($meta['options'] !== false) ? implode(',', $meta['options']) : false,
                    'data-type' => ($meta['options'] !== false) ? 'list' : $field['type'],
                    'data-length' => $field['length'],
                    'selected' => ($request->query('YummySearch') === null && $meta['default'] === true) ? true : false
                ];

                if ($field['sort-order'] !== false) {
                    $key = $field['sort-order'];
                    if ($key !== null && !isset($selectOptions[$humanName][$key])) {
                        $selectOptions[$humanName][$key] = $element;
                    } else {
                        $selectOptions[$humanName][] = $element;
                    }
                } else {
                    $selectOptions[$humanName][] = $element;
                }
            }
        }

        if ($this->getConfig('selectGroups') === false) {
            $select = [];
            foreach ($selectOptions as $options) {
                $select = array_merge($select, $options);
            }
        }

        $yummy = [
            'base_url' => $this->controller->request->here,
            'rows' => $this->controller->request->query('YummySearch'),
            'operators' => $this->config('operators'),
            'models' => isset($select) ? $select : $selectOptions
        ];

        return $yummy;
    }

    /**
     * getColumns - returns array of columns after checking allow/deny rules
     * @param string $name
     * @return array
     * [ModelName.column_name => Column Name]
     */
    private function getColumns($name)
    {
        $data = [];
        $tableName = Inflector::underscore($name);

        $modelName = Inflector::camelize($tableName);

        $schema = $this->collection->describe($tableName);
        $columns = $schema->columns();

        foreach ($columns as $column) {

            $allowed = $this->isColumnAllowed($modelName, $column);

            if ($allowed !== false) {

                $columnMeta = $schema->column($column);

                $data["$modelName.$column"] = [
                    'column' => $column,
                    'text' => Inflector::humanize($column),
                    'type' => $columnMeta['type'],
                    'length' => $columnMeta['length'],
                    'sort-order' => $allowed === true ? null : $allowed
                ];
            }
        }

        return $data;
    }

    /**
     * Defines models associations
     * @return void
     */
    private function defineModels()
    {
        $baseModel = $this->getConfig('model');

        $allowedModels = $this->getConfig('allow');

        if (isset($allowedModels[$baseModel]['_niceName'])) {
            $baseHumanName = $allowedModels[$baseModel]['_niceName'];
        } else {
            $baseHumanName = Inflector::humanize(Inflector::underscore($baseModel));
        }

        $this->models = [
            $baseHumanName => [
                'humanName' => $baseHumanName,
                'path' => false,
                'columns' => $this->getColumns($baseModel),
            ]
        ];

        $paths = $this->getPaths();

        foreach ($paths as $path) {
            $pieces = explode('.', $path);
            $theName = end($pieces);

            if (isset($allowedModels[$theName]['_niceName'])) {
                $humanName = $allowedModels[$theName]['_niceName'];
            } else {
                $humanName = Inflector::humanize(Inflector::underscore($theName));
            }

            if ($theName !== 'queryBuilder') {

                $columns = $this->getColumns($theName);

                if (!empty($columns)) {
                    $this->models[$theName] = [
                        'humanName' => $humanName,
                        'path' => $path,
                        'columns' => $this->getColumns($theName),
                    ];
                }
            }
        }
    }

    /**
     * Returns paths to model associations in dot notation
     * @return array
     */
    private function getPaths()
    {
        $query = $this->getConfig('query');

        if (method_exists($query, 'contain') === false) {
            return [];
        }

        $contains = $query->contain();
        $dots = array_keys($this->dot($contains));

        $add = [];

        foreach ($dots as $dot) {
            $pieces = explode('.', $dot);
            $length = count($pieces);
            if ($length > 1) {
                for ($i = 1; $i < $length; $i++) {
                    $tmp = $pieces;
                    $path = implode('.', array_slice($tmp, 0, $i));
                    $add[] = $path;
                }
            }
        }
        return array_merge($dots, array_unique($add));
    }

    /**
     * Flatten multi-dimensional array with key names in dotted notation
     * @param array $array
     * @param string $prepend
     * @return array
     */
    private function dot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, $this->dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }

    /**
     * checks allow/deny rules to see if model is allowed
     * @param string $model
     * @return boolean
     */
    private function isModelAllowed($model)
    {
        $config = $this->config();

        if (isset($config['allow'][$model])) {
            return true;
        } elseif (isset($config['deny'][$model]) && $config['deny'][$model] == '*') {
            return false;
        } elseif (isset($config['deny']) && $config['deny'] == '*') {
            return false;
        }

        return true;
    }

    /**
     * Checks allow/deny rules to see if column is allowed
     * @param string $model
     * @param string $column
     * @return boolean|int
     */
    private function isColumnAllowed($model, $column)
    {

        if ($this->isModelAllowed($model) === false) {
            return false;
        }

        $config = $this->config();

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

    /**
     * returns yummy meta data for a column
     * @param array $element
     * @return array
     */
    private function getColumnYummyMeta($element)
    {
        if (is_array($element)) {
            if (isset($element['_niceName'])) {
                $niceName = $element['_niceName'];
            }
            if (isset($element['_options'])) {
                $options = $element['_options'];
            }
            if (isset($element['_default'])) {
                $default = $element['_default'];
            }
        } elseif (is_string($element)) {
            $niceName = $element;
        }

        return [
            'niceName' => isset($niceName) ? $niceName : false,
            'options' => isset($options) ? $options : false,
            'default' => isset($default) ? $default : false,
        ];
    }

    /**
     * returns yummy meta data for a column
     * @param string $model
     * @param string $column
     * @return array
     */
    private function getYummyMeta($model, $column)
    {
        $meta = [
            'options' => false,
            'niceName' => false,
            'default' => false,
        ];

        $config = $this->getConfig();

        if (isset($config['allow'][$model][$column])) {
            $meta = $this->getColumnYummyMeta($config['allow'][$model][$column]);
        } elseif (isset($config['allow'][$model]['_columns'][$column])) {
            $meta = $this->getColumnYummyMeta($config['allow'][$model]['_columns'][$column]);
        }

        return $meta;
    }

    /**
     * Returns cakephp orm compatible condition
     * @param string $model
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return Cake\Database\Query
     */
    private function getSqlCondition($model, $column, $operator, $value, $query)
    {
        // for base model searches
        if (empty($model)) {
            return $this->getWhere($query, $column, $operator, $value);
        }

        return $query->matching($model, function ($q) use ($column, $operator, $value) {
            return $this->getWhere($q, $column, $operator, $value);
        });
    }

    /**
     * Returns a where condition
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return Cake\Database\Query
     * @throws InternalErrorException
     */
    private function getWhere($query, $column, $operator, $value)
    {
        switch ($operator) {
            case 'eq':
                return $query->where([$column => $value]);
            case 'not_eq':
                return $query->where(["$column !=" => $value]);
            case 'like':
                return $query->where(["$column LIKE" => "%$value%"]);
            case 'not_like':
                return $query->where(["$column NOT LIKE" => "%$value%"]);
            case 'gt':
                return $query->where(["$column >" => $value]);
            case 'lt':
                return $query->where(["$column <" => $value]);
            case 'gt_eq':
                return $query->where(["$column >=" => $value]);
            case 'lt_eq':
                return $query->where(["$column <=" => $value]);
            default:
                throw new InternalErrorException('Unknown condition encountered');
        }
    }

    /**
     * Adds conditions to Cake\ORM\Query
     * @param object $query
     * @return \Cake\ORM\Query
     */
    public function search(\Cake\ORM\Query $query)
    {
        // exit if no search was performed or user cleared search paramaters
        $request = $this->controller->request;
        if ($request->query('YummySearch') == null || $request->query('YummySearch_clear') != null) {
            return $query;
        }

        $data = $request->query('YummySearch');     // get query parameters
        $length = count($data['field']);            // get array length
        // loop through available fields and set conditions
        for ($i = 0; $i < $length; $i++) {
            $field = $data['field'][$i];            // get field name
            $operator = $data['operator'][$i];      // get operator type
            $search = $data['search'][$i];          // get search paramter

            $pieces = explode('.', $field);
            $column = array_pop($pieces);
            $model = implode('.', $pieces);

            if (isset($this->models[$model])) {
                $path = $this->models[$model]['path'];
            }

            if ($this->isColumnAllowed($model, $column) !== false) {
                $query = $this->getSqlCondition($path, "$model.$column", $operator, $search, $query);
            }
        }

        return $query;
    }
}
