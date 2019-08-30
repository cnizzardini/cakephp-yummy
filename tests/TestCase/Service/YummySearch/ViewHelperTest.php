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
    public function testGetYummyHelperData()
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
        $this->assertEquals('AFC,NFC', $data['models']['Conferences'][2]['data-items']);
    }

    public function testGetYummyHelperDataStandardOptionGroup()
    {
        $this->markTestIncomplete('@todo');
    }

    public function testGetYummyHelperDataCustomOptionGroup()
    {
        $this->markTestIncomplete('@todo');
    }
}
