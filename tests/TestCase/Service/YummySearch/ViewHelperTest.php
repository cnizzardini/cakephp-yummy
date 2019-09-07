<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Yummy\Service\YummySearch\ViewHelper;
use Yummy\Service\YummySearch\Association;

class ViewHelperTest extends TestCase
{
    public $fixtures = ['plugin.Yummy.Teams', 'plugin.Yummy.Divisions','plugin.Yummy.Conferences'];

    public function testGetYummyHelperData()
    {
        $teamsTable = TableRegistry::getTableLocator()->get('Teams');
        $teamsTable->addAssociations([
            'belongsTo' => [
                'Divisions'
            ]
        ]);

        $divisionsTable = TableRegistry::getTableLocator()->get('Divisions');
        $divisionsTable->addAssociations([
            'belongsTo' => [
                'Conferences'
            ]
        ]);

        $query = TableRegistry::getTableLocator()->get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams.name' => ['name' => 'Team Name', 'select' => false],
                'Teams.id' => ['name' => 'Identifier', 'operators' => false, 'select' => false],
                'Conferences.name' => [
                    'name' => 'Conference',
                    'operators' => ['eq'],
                    'select' => ['1' => 'AFC','2' => 'NFC']
                ],
            ],
            'operators' => [
                'eq' => 'Equals',
                'like' => 'Containing'
            ],
            'selectGroups' => true
        ];

        $viewHelper = new ViewHelper($config);
        $association = new Association();
        $request = new ServerRequest();

        $connection = ConnectionManager::get('test');
        $models = $association->getModels($connection, $config);

        $data = $viewHelper->getYummyHelperData($models, $request);

        $this->assertArrayHasKey('Teams', $data['models']);
        $this->assertArrayHasKey('Conferences', $data['models']);
        $this->assertArrayNotHasKey('Divisions', $data['models']);
        $this->assertEquals('Identifier', $data['models']['Teams'][1]['text']);
        $this->assertEquals('Team Name', $data['models']['Teams'][0]['text']);
        $this->assertTrue(is_array($data['models']['Conferences'][2]['data-items']));
    }

    public function testGetYummyHelperDataCustomOptionGroup()
    {
        $query = TableRegistry::getTableLocator()->get('Teams')->find();

        $select = [
            'A' => 'One',
            'B' => 'Two',
        ];

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams.name' => ['name' => 'Team Name', 'select' => $select],
                'Teams.id' => ['name' => 'Identifier', 'operators' => false, 'select' => false],
            ],
            'operators' => [
                'eq' => 'Equals',
                'like' => 'Containing'
            ],
            'selectGroups' => true
        ];

        $viewHelper = new ViewHelper($config);
        $association = new Association();
        $request = new ServerRequest();

        $connection = ConnectionManager::get('test');
        $models = $association->getModels($connection, $config);

        $data = $viewHelper->getYummyHelperData($models, $request);
        $expectation = $data['models']['Teams'][0]['data-items'];

        $this->assertCount(2, $expectation);
        $this->assertEquals('Two', $expectation['B']);
        $this->assertEquals('B', array_search('Two', $expectation));
    }
}
