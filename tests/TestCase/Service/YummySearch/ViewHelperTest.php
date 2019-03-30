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
        $query = TableRegistry::get('Teams')->find()->contain([
            'Divisions' => [
                'Conferences'
            ],
        ]);

        $config = [
            'query' => $query,
            'model' => 'Teams',
            'allow' => [
                'Teams.id' => ['name' => 'ID', 'operators' => false, 'select' => false],
                'Teams.name' => ['name' => false, 'select' => false],
                'Conferences.name' => ['name' => false, 'operators' => ['eq'], 'select' => [
                    '1' => 'AFC',
                    '2' => 'NFC'
                ]],
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

        $this->assertEquals('ID', $data['models']['Teams'][0]['text']);
        $this->assertEquals('Team Name', $data['models']['Teams'][1]['text']);
        $this->assertEquals('AFC,NFC', $data['models']['Conferences'][2]['data-items']);
    }
}