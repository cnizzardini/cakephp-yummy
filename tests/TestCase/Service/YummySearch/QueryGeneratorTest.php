<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\QueryGenerator;

class QueryGeneratorTest extends TestCase
{
    public function testGetWhere()
    {
        $this->markTestIncomplete('Not implemented yet.');
        /*
        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams' => ['name'],
            ]
        ];

        $connection = ConnectionManager::get('test');
        $association = new Association();
        $models = $association->getModels($connection, $config);

        $queryGenerator = new QueryGenerator($config);
        */
    }
}