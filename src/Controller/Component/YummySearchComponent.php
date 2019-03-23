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
use Yummy\Service\YummySearch\Helper;
use Yummy\Service\YummySearch\QueryGenerator;
use Yummy\Service\YummySearch\Rule;
use Yummy\Service\YummySearch\Association;
use Yummy\Service\YummySearch\Option;
use Yummy\Service\YummySearch\ViewHelper;

/**
 * This component is used to generate a query builder UI, ORM conditions, and return the subsequent query results
 *
 * @link https://github.com/cnizzardini/cakephp-yummy
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

        $option = new Option($this->_config);
        $viewHelper = new ViewHelper($this->_config, $option);

        $yummy = $viewHelper->getYummyHelperData($this->models, $this->controller->request);

        // make yummy search data available to view
        $this->controller->set('YummySearch', $yummy);
    }

    /**
     * Adds conditions to Cake\ORM\Query
     *
     * @param Query $query
     * @return Query
     */
    public function search(Query $query) : Query
    {
        $request = $this->controller->request;

        if ($request->query('YummySearch') == null || $request->query('YummySearch_clear') != null) {
            return $query;
        }

        $rule = new Rule($this->_config);

        $data = Helper::getFormattedData($request);

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
     * Returns CakePHP ORM compatible conditions
     *
     * @param string $model
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param Query $query
     * @return Query
     */
    private function getSqlCondition(string $model,  string $column, string $operator, string $value, Query $query) : Query
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
}
