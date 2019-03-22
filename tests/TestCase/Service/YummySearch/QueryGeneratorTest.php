<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\Association;
use Yummy\Service\YummySearch\QueryGenerator;

class QueryGeneratorTest extends TestCase
{
    public function testGetWhereSingle()
    {
        $teamsTable = TableRegistry::get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ];

        $connection = ConnectionManager::get('test');
        $association = new Association();
        $models = $association->getModels($connection, $config);

        $queryGenerator = new QueryGenerator($config, $query);
        $query = $queryGenerator->getWhere(
            $query,
            'Teams.name',
            'eq',
            'NY Giants'
        );
    }
}