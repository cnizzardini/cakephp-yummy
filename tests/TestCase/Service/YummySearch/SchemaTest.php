<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\Schema;
use Yummy\Service\YummySearch\Rule;

class SchemaTest extends TestCase
{
    public $fixtures = ['plugin.Yummy.Teams', 'plugin.Yummy.Divisions','plugin.Yummy.Conferences'];

    public function testGetColumns()
    {
        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ]
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
        ];

        $connection = ConnectionManager::get('test');

        $rule = new Rule($config);
        $schema = new Schema($rule);

        $columns = $schema->getColumns($connection, 'Teams');

        $this->assertArrayHasKey('Teams.id', $columns);
        $this->assertArrayHasKey('Teams.name', $columns);
        $this->assertArrayHasKey('Teams.abbreviation', $columns);

        $this->assertEquals('id', $columns['Teams.id']['column']);
        $this->assertEquals('Id', $columns['Teams.id']['text']);
        $this->assertEquals('smallinteger', $columns['Teams.id']['type']);
        $this->assertEquals('', $columns['Teams.id']['sort-order']);
    }

    public function testGetColumnsCustomAllow()
    {
        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams.name' => ['name' => false, 'operators' => false, 'select' => false]
            ]
        ];

        $connection = ConnectionManager::get('test');

        $rule = new Rule($config);
        $schema = new Schema($rule);

        $columns = $schema->getColumns($connection, 'Teams');

        $this->assertArrayHasKey('Teams.name', $columns);
        $this->assertArrayNotHasKey('Teams.id', $columns);
    }

    public function testGetColumnsException()
    {
        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
        ];

        $connection = ConnectionManager::get('test');

        $rule = new Rule($config);
        $schema = new Schema($rule);

        $exceptionName = '';

        try {
            $schema->getColumns($connection, 'Nope');
        } catch(\Exception $e) {
            $exceptionName = (get_class($e));
        }

        $this->assertEquals('Yummy\Exception\YummySearch\SchemaException',$exceptionName);

    }
}