<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * This component is a should be used in conjunction with the YummySearchHelper for building rudimentary search filters
 */
class YummySearchComponent extends Component
{

    public function startup(){
        $this->controller = $this->_registry->getController();
    }
    
    /**
     * beforeRender - sets fields for use by YummySearchHelper
     */
    public function beforeRender()
    {
        $database = ConnectionManager::get('default');

        // check components
        $this->checkComponents();
        
        // Create a schema collection.
        $this->collection = $database->schemaCollection();

        // merge configurations
        $this->mergeConfig();

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
            throw new InternalErrorException(__('YummySearch requires the Paginator Component'));
        }
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
            ],
            'singular_models' => false,
            'max_recursion' => 3
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
        
        if( $this->config('singular_names') == false ){
            $modelName = Inflector::camelize(Inflector::pluralize($tableName));
        } else {
            $modelName = Inflector::classify($tableName);
        }
        
        $schema = $this->collection->describe($tableName);
        $columns = $schema->columns();
        
        foreach($columns as $column){
            if( $this->isColumnAllowed($modelName, $column) == true ){
                $data["$modelName.$column"] = Inflector::singularize($modelName) . ' ' . Inflector::humanize($column);
            }
        }
                
        return $data;
    }
    
    /**
     * getModels - returns an array of models and their columns
     * @param object $object (Default: empty)
     * @param integer $currentDepth (Default: 0)
     * @return array
     * @example [ModelName => [ModelName.column_name => Column Name]]
     */
    private function getModels($object='', $currentDepth = 0)
    {
        $currentDepth++;
        
        if (empty($object)) {
            $thisModel = $this->config('model');
            $object = $this->controller->{$thisModel};
        } else {
            $thisModel = $object->getName();
        }
                
        // only supporting HasOne and BelongsTo for now
        $allowedAssociations = ['Cake\ORM\Association\HasOne', 'Cake\ORM\Association\BelongsTo'];
        
        // gets array of Cake\ORM\Association objects
        $associations = $object->associations();

        // build an array of models and their associations
        $models = [
            "$thisModel" => $this->getColumns($thisModel)
        ];
        
        // return if no associtions are found or $currentDepth is greater than $maxDepth
        if (empty($associations) || $currentDepth > $this->config('max_recursion') ) {
            return $models;
        }
        
        // get associations
        foreach($associations as $object){
            
            // get proper form of models name
            $name = Inflector::humanize(Inflector::tableize($object->getName()));

            // get the table objects name
            $table = $object->getTable();
            
            // add to $models if does not exist in $models and is an $allowedAssociation
            if( !isset($models[ $name ]) && in_array(get_class($object), $allowedAssociations) ){
                $models[ $name ] = $this->getColumns($table);
                $models = array_merge($models, $this->getModels($object, $currentDepth));
            }
        }
        
        return $models;
    }

    /**
     * isColumnAllowed - checks allow/deny rules to see if column is allowed
     * @param string $model
     * @param string $column
     * @return boolean
     */
    private function isColumnAllowed($model, $column){
        
        $config = $this->config();
        
        // check deny all
        if( isset($config['deny'][$model]) && $config['deny'][$model] == '*' ){
            return false;
            
        // check deny column
        } else if( isset($config['deny'][$model]) && in_array($column, $config['deny'][$model]) ){
            return false;
            
        // check if in allow columns (if allow isset)
        } else if( isset($config['allow'][$model]) && !in_array($column, $config['allow'][$model]) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * getSqlCondition - returns cakephp orm compatible condition after checking allow/deny rules
     * @param string $model
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return array|bool: array on success, false if operator is not found
     */
    private function getSqlCondition($model, $column, $operator, $value)
    {
        if ($this->isColumnAllowed($model, $column) == false) {
            return false;
        }
        
        switch ($operator) {
            case 'matching':
                return ["$model.$column" => $value];
            case 'not_matching';
                return ["$model.$column != " => $value];
            case 'containing';
                return ["$model.$column LIKE " => "%$value%"];
            case 'not_containing';
                return ["$model.$column NOT LIKE " => "%$value%"];
            case 'greater_than';
                return ["$model.$column > " => "%$value%"];
            case 'less_than';
                return ["$model.$column < " => "%$value%"];
        }
        return false;
    }

    /**
     * search - appends cakephp orm conditions to PaginatorComponent
     * @return bool: true if search query was requested, false if not
     */
    public function search()
    {
        // exit if no search was performed or user cleared search paramaters
        $this->controller = $this->_registry->getController();
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
            
            $conditions = $this->getSqlCondition($model, $column, $operator, $search);

            if( is_array($conditions) ){
                $this->controller->paginate['conditions'] = array_merge(
                    $this->controller->paginate['conditions'], 
                    $conditions
                );
            }
        }
        
        return true;
    }
}