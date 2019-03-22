<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Yummy\Service\YummySearch\Association;
use Cake\Datasource\ConnectionManager;

class AssociationTest extends TestCase
{
    public function testGetModels()
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
            'deny' => [
                'Conferences' => '*'
            ],
            'selectGroups' => false
        ]);

        $this->assertArrayNotHasKey('Conferences', $models);
    }
}