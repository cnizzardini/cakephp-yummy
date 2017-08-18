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
    private $models = false;
    
    protected $_defaultConfig = [
        'singular_names' => false,
        'max_recursion' => 3,
        'associations' => [
            'HasOne','BelongsTo'
        ],
        'operators' => [
            'containing' => 'Containing',
            'not_containing' => 'Not Containing',
            'greater_than' => 'Greater than',
            'less_than' => 'Less than',
            'matching' => 'Exact Match',
            'not_matching' => 'Not Exact Match',
        ]
    ];
    
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        if (isset($config['operators'])) {
            $this->setConfig('operators',$config['operators']);
        }
        
        if (isset($config['associations'])) {
            $this->setConfig('associations',$config['associations']);
        }
        
        if (isset($config['max_recursion'])) {
            $this->setConfig('max_recursion',$config['max_recursion']);
        }
        
        if (isset($config['singular_names'])) {
            $this->setConfig('singular_names',$config['singular_names']);
        }
        
        $this->controller = $this->_registry->getController();
        
        // Create a schema collection.
        $database = ConnectionManager::get('default');
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
        
        foreach($this->models as $modelName => $model){
            foreach($model['columns'] as $column => $field){

                $element = [
                    'text' => $field['text'], 
                    'path' => $model['path'],
                    'value' => $column, 
                    'data-type'=> $field['type'], 
                    'data-length' => $field['length']
                ];

                if ($field['sort-order'] !== false) {
                    $key = $field['sort-order'];
                    if ($key !== null) {
                        $selectOptions[ $modelName ][ $key ] = $element;
                    } else {
                        $selectOptions[ $modelName ][] = $element;
                    }
                    
                } else {
                    $selectOptions[ $modelName ][] = $element;
                }
            }
            if (isset($selectOptions[ $modelName ])) {
                ksort($selectOptions[ $modelName ]);
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
    private function defineModels($object='', $currentDepth = 0, $parent = false)
    {
        $currentDepth++;
        
        if (empty($object)) {
            $thisModel = $this->getConfig('model');
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
        $camelName = Inflector::camelize(strtolower($thisModel));
        
        if ($this->models === false) {
            $this->models = [
                "$theName" => [
                    'path' => false,
                    'columns' => $this->getColumns($thisModel),
                ]
            ];
        }
        
        // return if no associtions are found or $currentDepth is greater than $maxDepth
        if (empty($associations)) {
            return false;
        }
        
        //echo "$theName from '$parent' \r\n";
        
        // get associations
        foreach($associations as $object){
        
            // get proper form of models name
            $name = Inflector::humanize(strtolower($object->getTable()));
            //echo "$name\r\n";
            
            // get the table objects name
            $table = $object->getTable();
            
            // add to $models if does not exist in $models and is an $allowedAssociation
            if( !isset($this->models[ $name ]) && in_array(get_class($object), $allowedAssociations) ){
                                               
                $parent = $this->getParent($parent, $camelName);
                    
                //echo $this->getConfig('max_recursion') . '- ' .count(explode('.', $parent)) . ") $name:: $parent \r\n";
                if (!isset($this->models[ $name ]['path'])) {
                    $this->models[ $name ]['path'] = $parent; 
                } else {
                    $this->models[ $name ]['path'].= $parent;
                }
                
                //echo "$name: $parent \r\n";
                
                $this->models[ $name ]['columns'] = $this->getColumns($table);

                if ($currentDepth <= $this->getConfig('max_recursion')){
                    $this->defineModels($object, $currentDepth, $parent);
                }
            }
        }        
    }
    
    /**
     * return parent models in dot notation
     * @param string $parent
     * @param string $camelName
     * @return string
     */
    private function getParent($parent, $camelName)
    {
        if ($parent !== false) {
            $pieces = explode('.', $parent);
            $hasParent = false;

            foreach($pieces as $piece){
                if ($piece == $camelName) {
                    $hasParent = true;
                    break;
                }
            }
            if ($hasParent === false) {
                $parent.= "$camelName.";
            }
        } else {
            $parent = $camelName . '.';
        }
        return $parent;
    }
    
    /**
     * checks allow/deny rules to see if model is allowed
     * @param string $model
     * @return boolean
     */
    private function isModelAllowed($model)
    {    
        $config = $this->config();
        
        if( isset($config['allow'][$model]) ) {
            return true;
        }
        else if( isset($config['deny'][$model]) && $config['deny'][$model] == '*' ){
            return false;
        }
        else if( isset($config['deny']) && $config['deny'] == '*' ){
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
        if( $this->isModelAllowed($model) == false ){
            return false;
        }
        
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
     * Returns cakephp orm compatible condition
     * @param string $model
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return Cake\Database\Query
     */
    private function getSqlCondition($model, $column, $operator, $value, $query)
    {   
        // remove first item from the model path
        $pieces = explode('.', $model);
        $slices = array_slice($pieces, 1);
        $path = implode('.', $slices);
        
        if ($model == $this->getConfig('model')) {
            return $this->getWhere($query, $model . '.' . $column, $operator, $value);
        } else {
            return $query->matching($path, function($q) use($column, $operator, $value) {
                return $this->getWhere($q, $column, $operator, $value);
            });
        }
        
        return $query;
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
        switch($operator){
            case 'matching':
                return $query->where([$column => $value]);
            case 'not_matching';
                return $query->where(["$column !=" => $value]);
            case 'containing';
                return $query->where(["$column LIKE" => $value]);
            case 'not_containing';
                return $query->where(["$column NOT LIKE" => $value]);
            case 'greater_than';
                return $query->where(["$column >" => $value]);
            case 'less_than';
                return $query->where(["$column <" => $value]);
            default:
                throw new InternalErrorException('Unknown condition encountered');
        }
    }
    
    /**
     * Adds conditions to Cake\Database\Query
     * @param Cake\Database\Query $query
     * @return Cake\Database\Query
     */
    public function search($query=false)
    {
        // exit if no search was performed or user cleared search paramaters
        $request = $this->controller->request;
        if ($request->query('YummySearch') == null || $request->query('YummySearch_clear') != null) {
            return $query;
        }
        
        if( !isset($this->controller->paginate['conditions']) ){
            //$this->controller->paginate['conditions'] = [];
        }
        
        $data = $request->query('YummySearch');     // get query parameters
        $length = count($data['field']);            // get array length
        
        // loop through available fields and set conditions
        for ($i = 0; $i < $length; $i++) {
            $field = $data['field'][$i];            // get field name
            $operator = $data['operator'][$i];      // get operator type
            $search = $data['search'][$i];          // get search paramter
            
            list($camelModel, $column) = explode('.', $field);
            $humanModel = Inflector::humanize(Inflector::underscore($camelModel));
            
            $modelData = $this->models[$humanModel];
            
            $model = $modelData['path'] . $camelModel;
            
            if ($this->isColumnAllowed($camelModel, $column) !== false) {
                $query = $this->getSqlCondition($model, $column, $operator, $search, $query);
            }
        }
        return $query;
    }
}