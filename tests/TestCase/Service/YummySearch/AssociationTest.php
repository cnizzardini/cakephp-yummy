<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Datasource\ConnectionManager;
use Yummy\Service\YummySearch\Association;


class AssociationTest extends TestCase
{
    public $fixtures = ['plugin.Yummy.Teams', 'plugin.Yummy.Divisions','plugin.Yummy.Conferences'];

    public function testGetModels()
    {
        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ]
        ]);

        $connection = ConnectionManager::get('test');
        $association = new Association();
        $models = $association->getModels($connection, [
            'query' => $query,
            'model' => 'Teams',
        ]);

        $this->assertArrayHasKey('Teams', $models);
        $this->assertArrayHasKey('Conferences', $models);
        $this->assertArrayHasKey('Divisions', $models);
    }

    public function testGetModelsWithDeny()
    {
        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ]
        ]);

        $connection = ConnectionManager::get('test');
        $association = new Association();
        $models = $association->getModels($connection, [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams.id',
                'Teams.name',
                'Divisions.id',
            ],
            'selectGroups' => false
        ]);

        $this->assertArrayNotHasKey('Conferences', $models);
    }
}