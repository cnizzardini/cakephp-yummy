<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
//use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * This component is a should be used in conjunction with the YummySearchHelper for building rudimentary search filters in your application
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
        $this->controller = $this->_registry->getController();

        // merge configurations
        $this->mergeConfig();
        
        // set array for use by YummySearchHelper
        $yummy = $this->getYummyHelperData();
        
        $this->controller->set('YummySearch', $yummy);
    }
    
    /**
     * mergeConfig - merges user supplied configuration with defaults
     * @return void
     */
    private function mergeConfig()
    {
        
        if( $this->config('operators') == null ){
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
            'rows' => $this->controller->request->query('YummySearch')
        ];
        
        if( !isset($this->controller->paginate['fields']) ){
            return $yummy;
        }
   
        $config = $this->config();
        
        /*
        $db = ConnectionManager::get('default');

        // Create a schema collection.
        $collection = $db->schemaCollection();

        // Get a single table (instance of Schema\TableSchema)
        if( !isset($config['table']) ){
            $tableSchema = $collection->describe(Inflector::tableize($controller->name));
        } else {
            $tableSchema = $collection->describe($config['table']);
        }
        */
        
        foreach($this->controller->paginate['fields'] as $field){
            $skip = false;
            if( isset($config['deny']) && in_array($field, $config['deny'])){
                $skip = true;
            } else if( isset($config['allow']) && !in_array($field, $config['allow']) ){
                $skip = true;
            }

            if( $skip == false ){

                $opt = $field;

                if( strstr($field, '.') ){
                    $tmp = explode('.', $field);
                    $opt = end($tmp);
                }

                $yummy['fields'][$field] = Inflector::humanize($opt);
            }
        }
        
        return $yummy;
    }
    
    /**
     * getSqlCondition - returns cakephp orm compatible condition based on $operator type
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return array|bool returns an array on success, false if operator is not found
     */
    private function getSqlCondition($field, $operator, $value)
    {
        switch($operator){
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
     * @return bool
     */
    public function search()
    {
        $controller = $this->_registry->getController();
        
        // exit if no search was performed or user cleared search paramaters
        if( $controller->request->query('YummySearch') == null || $controller->request->query('YummySearch_clear') != null ){
            return false;
        }
        
        $data = $controller->request->query('YummySearch');

        $length = count($data['field']);
        
        $config = $this->config();
        
        for($i=0; $i<$length; $i++){
            $field = $data['field'][ $i ];
            $operator = $data['operator'][ $i ];
            $search = $data['search'][ $i ];
            
            $skip = false;
            if( isset($config['deny']) && in_array($field, $config['deny'])){
                $skip = true;
            } else if( isset($config['allow']) && !in_array($field, $config['allow']) ){
                $skip = true;
            }
            
            if( $skip == false && in_array($field, $controller->paginate['fields']) ){
                $controller->paginate['conditions'] = array_merge($controller->paginate['conditions'], $this->getSqlCondition($field,$operator,$search));
            }
        }
        return true;
    }
}
