<?php

namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Query;
use Yummy\Service\YummySearch\QueryGenerator;
use Yummy\Service\YummySearch\Rule;
use Yummy\Service\YummySearch\Association;
use Yummy\Service\YummySearch\Option;
use Yummy\Service\YummySearch\Helper;

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
            'eq' => 'Matching',
            'not_eq' => 'Not Matching',
        ],
        'dataSource' => 'default',
        'selectGroups' => true,
        'trim' => true
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

        $database = ConnectionManager::get($this->getConfig('dataSource'));

        $association = new Association();

        $this->models = $association->getModels($database, $this->_config);
    }

    /**
     * beforeRender - sets fields for use by YummySearchHelper
     */
    public function beforeRender()
    {
        // check components
        $this->checkComponents();

        $option = new Option($this->_config);
        $helper = new Helper($this->_config, $option);

        $yummy = $helper->getYummyHelperData($this->models, $this->controller->request);

        // make yummy search data available to view
        $this->controller->set('YummySearch', $yummy);
    }

    /**
     * Adds conditions to Cake\ORM\Query
     * @param Query $query
     * @return Query
     */
    public function search(Query $query)
    {
        $request = $this->controller->request;

        if ($request->query('YummySearch') == null || $request->query('YummySearch_clear') != null) {
            return $query;
        }

        $rule = new Rule($this->_config);

        $data = $this->getFormattedData($request->query('YummySearch'));

        foreach ($data as $item) {

            $pieces = explode('.', $item['field']);
            $column = array_pop($pieces);
            $model = implode('.', $pieces);

            if ($rule->isColumnAllowed($model, $column) === false) {
                continue;
            }

            $path = isset($this->models[$model]['path']) ? $this->models[$model]['path'] : '';

            $query = $this->getSqlCondition(
                $path,
                "$model.$column",
                $item['operator'],
                $item['search'],
                $query
            );
        }

        return $query;
    }

    /**
     * checkComponents - throws exception if missing a required component
     * @throws InternalErrorException
     */
    private function checkComponents()
    {
        if (!isset($this->controller->Paginator)) {
            throw new \Cake\Network\Exception\InternalErrorException(
                __('YummySearch requires Paginator Component')
            );
        }
    }

    /**
     * Returns cakephp orm compatible condition
     * @param string $model
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param Query $query
     * @return Query
     */
    private function getSqlCondition(string $model,  string $column, string $operator, string $value, Query $query)
    {
        $queryGenerator = new QueryGenerator($this->_config);

        // for base model searches
        if (empty($model)) {
            return $queryGenerator->getWhere($query, $column, $operator, $value);
        }

        return $query->matching($model, function ($q) use ($column, $operator, $value, $queryGenerator) {
            return $queryGenerator->getWhere($q, $column, $operator, $value);
        });
    }

    /**
     * @param array $data
     * @return array
     */
    private function getFormattedData(array $data)
    {
        $array = [];

        $length = count($data['field']);

        // loop through available fields and set conditions
        for ($i = 0; $i < $length; $i++) {

            if (!isset($data['field'][$i]) || !isset($data['operator'][$i]) || !isset($data['search'][$i])) {
                continue;
            }

            $array[] = [
                'field' => $data['field'][$i],
                'operator' => $data['operator'][$i],
                'search' => $data['search'][$i],
            ];
        }

        return $array;
    }
}
