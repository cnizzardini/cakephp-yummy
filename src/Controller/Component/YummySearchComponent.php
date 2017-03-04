<?php

namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * This component is a should be used in conjunction with the YummySearchHelper for building rudimentary search filters
 * @todo deep relations are not implemented
 */
class YummySearchComponent extends Component
{

//    protected $_defaultConfig = [
//        'operators' => [],
//    ];
    
    /**
     * beforeRender - sets fields for use by YummySearchHelper
     */
    public function beforeRender()
    {
        $db = ConnectionManager::get('default');

        // Create a schema collection.
        $this->collection = $db->schemaCollection();
        
        $this->controller = $this->_registry->getController();

        // merge configurations
        $this->mergeConfig();

        // set array for use by YummySearchHelper
        $yummy = $this->getYummyHelperData();

        // make yummy search data available to view
        $this->controller->set('YummySearch', $yummy);
    }

    /**
     * mergeConfig - merges user supplied configuration with defaults
     * @return void
     */
    private function mergeConfig()
    {

        if ($this->config('operators') != null) {
            return;
        }

        $config = [
            'operators' => [
                'containing' => 'Containing',
                'not_containing' => 'Not Containing',
                'greater_than' => 'Greater than',
                'less_than' => 'Less than',
                'matching' => 'Exact Match',
                'not_matching' => 'Not Exact Match',
            ]
        ];

        $this->configShallow($config);
    }

    /**
     * getYummyHelperData - retrieves an array used by YummySearchHelper
     * @return array
     */
    private function getYummyHelperData()
    {
        $yummy = [
            'base_url' => $this->controller->request->here,
            'rows' => $this->controller->request->query('YummySearch'),
            'operators' => $this->config('operators'),
            'models' => $this->getModels()
        ];
        
        return $yummy;
    }
    
    private function getColumns($name)
    {
        $data = [];
        $tableName = Inflector::underscore($name);
        $modelName = Inflector::classify($tableName);
        $schema = $this->collection->describe($tableName);
        $columns = $schema->columns();
        
        foreach($columns as $column){
            
            if( $this->isColumnAllowed($modelName, $column) == true ){
                $data["$name.$column"] = Inflector::humanize($column);
            }
        }
        
        return $data;
    }
    
    private function getModels()
    {
        // gets array of Cake\ORM\Association objects
        
        $thisModel = $this->config('model');
        
        $associations = $this->controller->{$thisModel}->associations();

        $models = ["$thisModel" => $this->getColumns($thisModel)];
        
        $allowedAssociations = ['Cake\ORM\Association\HasOne', 'Cake\ORM\Association\BelongsTo'];
        
        foreach($associations as $object){
            
            $name = Inflector::humanize(Inflector::tableize($object->getName()));
            $table = $object->getTable();

            if( !isset($models[ $name ]) && in_array(get_class($object), $allowedAssociations) ){
                $columns = $this->getColumns($table);
                if( !empty($columns) ){
                    $models[ $name ] = $columns;
                }
            }
        }
        
        return $models;
    }


    private function isColumnAllowed($model, $column){
        
        $config = $this->config();
        
        if( isset($config['deny'][$model][$column]) ){
            return false;
        } else if( isset($config['deny'][$model]) && $config['deny'][$model] == '*' ){
            return false;
        } else if( isset($config['allow'][$model]) && !in_array($column, $config['allow'][$model]) ) {
            return false;
        }
        return true;
    }
    
    /**
     * getSqlCondition - returns cakephp orm compatible condition based on $operator type
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return array|bool: array on success, false if operator is not found
     */
    private function getSqlCondition($field, $operator, $value)
    {
        switch ($operator) {
            case 'matching':
                return [$field => $value];
            case 'not_matching';
                return ["$field != " => $value];
            case 'containing';
                return ["$field LIKE " => "%$value%"];
            case 'not_containing';
                return ["$field NOT LIKE " => "%$value%"];
            case 'greater_than';
                return ["$field > " => "%$value%"];
            case 'less_than';
                return ["$field < " => "%$value%"];
        }
        return false;
    }

    /**
     * search - appends cakephp orm conditions to PaginatorComponent
     * @return bool: true if search query was requested, false if not
     */
    public function search()
    {
        $this->controller = $this->_registry->getController();

        // exit if no search was performed or user cleared search paramaters
        $request = $this->controller->request;
        if ($request->query('YummySearch') == null || $request->query('YummySearch_clear') != null) {
            return false;
        }

        $data = $request->query('YummySearch');     // get query parameters
        $length = count($data['field']);            // get array length

        if( !isset($this->controller->paginate['conditions']) ){
            $this->controller->paginate['conditions'] = [];
        }

        // loop through available fields and set conditions
        for ($i = 0; $i < $length; $i++) {
            $field = $data['field'][$i];            // get field name
            $operator = $data['operator'][$i];      // get operator type
            $search = $data['search'][$i];          // get search paramter
            
            list($model, $column) = explode('.', $field);
            
            if( $this->isColumnAllowed($model, $column) == true ){
                $this->controller->paginate['conditions'] = array_merge(
                    $this->controller->paginate['conditions'], $this->getSqlCondition($field, $operator, $search)
                );
            }
        }
        return true;
    }

}
