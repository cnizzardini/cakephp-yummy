<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author          Chris Nizzardini
 * @link            https://github.com/cnizzardini/cakephp-yummy
 * @license         http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Query;
use Yummy\Service\YummySearch\Factory\ParameterFactory;
use Yummy\Service\YummySearch\Helper;
use Yummy\Service\YummySearch\Parameter;
use Yummy\Service\YummySearch\QueryGenerator;
use Yummy\Service\YummySearch\Rule;
use Yummy\Service\YummySearch\Association;
use Yummy\Service\YummySearch\ViewHelper;

/**
 * This component is used to generate a query builder UI, ORM conditions, and return the subsequent query results
 *
 * @link https://github.com/cnizzardini/cakephp-yummy
 */
class YummySearchComponent extends Component
{
    private $models = false,
            $controller;
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
        'selectGroups' => false,
        'trim' => true
    ];

    /**
     * Component initialization
     *
     * @param array $config
     * @return void
     */
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

        $connection = ConnectionManager::get($this->getConfig('dataSource'));

        $association = new Association();

        $this->models = $association->getModels($connection, $this->_config);
    }

    /**
     * Sets $YummySearch array for use by YummySearchHelper in template
     *
     * @return void
     */
    public function beforeRender()
    {
        Helper::checkComponents($this->controller);

        $viewHelper = new ViewHelper($this->_config);

        $yummy = $viewHelper->getYummyHelperData($this->models, $this->controller->request);

        $this->controller->set('YummySearch', $yummy);
    }

    /**
     * Adds conditions to Cake\ORM\Query
     *
     * @param Query $query
     * @return Query
     * @throws \Exception
     */
    public function search(Query $query) : Query
    {
        $request = $this->controller->request;

        if ($request->getQuery('YummySearch') == null || $request->getQuery('YummySearch_clear') != null) {
            return $query;
        }

        $rule = new Rule($this->_config);

        $data = Helper::getFormattedData($request);

        $queryGenerator = new QueryGenerator($this->_config);

        foreach ($data as $item) {

            $pieces = explode('.', $item['field']);
            $column = array_pop($pieces);
            $model = implode('.', $pieces);

            if ($rule->isColumnAllowed($model, $column) === false) {
                continue;
            }

            $path = isset($this->models[$model]['path']) ? $this->models[$model]['path'] : '';

            $parameterFactory = new ParameterFactory($this->_config, $this->models);
            $parameter = $parameterFactory->create($model, $column, $item);

            $query = $queryGenerator->getQuery(
                $query,
                $path,
                $parameter
            );
        }

        return $query;
    }

    /**
     * Adds a column that can be searched on
     *
     * @param string $column
     * @param array $options
     * @return YummySearchComponent
     */
    public function addColumn(string $column, array $options = []) : self
    {
        $option = array_merge($options, [
            'name' => false,
            'operators' => false,
            'select' => false,
            'casteToDate' => false
        ]);

        $this->_config['allow'][$column] = $option;

        return $this;
    }
}
