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
    private $models;
    
    protected $_defaultConfig = [
        'singular_names' => false,
        'max_recursion' => 3,
        'associations'
    ];
    
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $this->controller = $this->_registry->getController();
        
        if (!$this->getConfig('operators')) {
            $this->setConfig('operators', [
                'containing' => 'Containing',
                'not_containing' => 'Not Containing',
                'greater_than' => 'Greater than',
                'less_than' => 'Less than',
                'matching' => 'Exact Match',
                'not_matching' => 'Not Exact Match',
            ]);
        }
        
        if (!$this->getConfig('associations')) {
            $this->setConfig('associations',[
                'HasOne','BelongsTo'
            ]);
        }
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
            throw new \Cake\Network\Exception\InternalErrorException(__('YummySearch requires the Paginator Component'));
        }
    }

    /**
     * getYummyHelperData - retrieves an array used by YummySearchHelper
     * @return array
     */
    private function getYummyHelperData()
    {
        $this->defineModels();
        
        foreach($this->models as $model => $columns) {
            if (empty($columns)) {
                unset($this->models[ $model ]);
            }
        }
        
        $selectOptions = [];
        
        foreach($this->models as $model => $columns){
            foreach($columns as $column => $field){

                $element = [
                    'text' => $field['text'], 
                    'value'=> $column, 
                    'data-type'=> $field['type'], 
                    'data-length' => $field['length']
                ];

                if ($field['sort-order'] !== false) {
                    $key = $field['sort-order'];
                    if ($key !== null) {
                        $selectOptions[ $model ][ $key ] = $element;
                    } else {
                        $selectOptions[ $model ][] = $element;
                    }
                    
                } else {
                    $selectOptions[ $model ][] = $element;
                }
            }
        }
        
        $yummy = [
            'base_url' => $this->controller->request->here,
            'rows' => $this->controller->request->query('YummySearch'),
            'operators' => $this->config('operators'),
            'models' => $selectOptions
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
            $modelName = Inflector::camelize($tableName);
        }
        
        $schema = $this->collection->describe($tableName);
        $columns = $schema->columns();
        
        foreach($columns as $column){
            
            $allowed = $this->isColumnAllowed($modelName, $column);
            
            if( $allowed !== false ){
                
                $columnMeta = $schema->column($column);
                
                $data["$modelName.$column"] = [
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
     * Defines models associated with with the defined yummy model
     * @param object $object (Default: empty)
     * @param integer $currentDepth (Default: 0)
     * @return array
     * @example [ModelName => [ModelName.column_name => Column Name]]
     */
    private function defineModels($object='', $currentDepth = 0)
    {
        $currentDepth++;
        
        if (empty($object)) {
            $thisModel = $this->config('model');
            $object = $this->controller->{$thisModel};
        } else {
            $thisModel = $object->getTable();
        }
        
        $allowedAssociations = [];
        $configAssociations = $this->getConfig('associations');
        foreach($configAssociations as $assoc){
            $allowedAssociations[] = 'Cake\ORM\Association\\' . $assoc;
        }
        
        // gets array of Cake\ORM\Association objects
        $associations = $object->associations();

        // build an array of models and their associations
        $theName = Inflector::humanize(strtolower($thisModel));
        $models = [
            "$theName" => $this->getColumns($thisModel)
        ];
        
        //echo "$currentDepth: $theName\r\n";
        
        // return if no associtions are found or $currentDepth is greater than $maxDepth
        if (empty($associations) || $currentDepth > $this->config('max_recursion')) {
            return false;
        }
        
        // get associations
        foreach($associations as $object){
            
            // get proper form of models name
            $name = Inflector::humanize(strtolower($object->getTable()));
            
            // get the table objects name
            $table = $object->getTable();
            
            // add to $models if does not exist in $models and is an $allowedAssociation
            if( !isset($models[ $name ]) && in_array(get_class($object), $allowedAssociations) ){
                $this->models[ $name ] = $this->getColumns($table);
                $this->defineModels($object, $currentDepth);
            }
        }
    }

    /**
     * isColumnAllowed - checks allow/deny rules to see if column is allowed
     * @param string $model
     * @param string $column
     * @return boolean|int
     */
    private function isColumnAllowed($model, $column){
        
        $config = $this->config();
        

        // check if in allow columns
        if( isset($config['allow'][$model]) ) {
            
            // not in allowed columns
            if (!in_array($column, $config['allow'][$model])) {
                return false;
            }
            
            // in allowed columns, return the key so we can apply ordering
            $key = array_search($column, $config['allow'][$model]);
            if ($key >= 0) {
                return $key;
            }
        // check deny all models
        } else if($config['deny'] == '*'){
            return false;
        }
        // check deny specific model
        else if( isset($config['deny'][$model]) && $config['deny'][$model] == '*' ){
            return false;
            
        // check deny specific model.column
        } else if( isset($config['deny'][$model]) && in_array($column, $config['deny'][$model]) ){
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
        if ($this->isColumnAllowed($model, $column) === false) {
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
                return ["$model.$column > " => "$value"];
            case 'less_than';
                return ["$model.$column < " => "$value"];
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