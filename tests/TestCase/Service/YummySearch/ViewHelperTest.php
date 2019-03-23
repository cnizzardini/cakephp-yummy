<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Yummy\Service\YummySearch\ViewHelper;
use Yummy\Service\YummySearch\Option;
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
                'Teams' => ['name'],
            ],
            'operators' => [
                'eq' => 'Equals'
            ],
            'selectGroups' => true
        ];

        $option = new Option($config);
        $viewHelper = new ViewHelper($config, $option);
        $association = new Association();
        $request = new ServerRequest();

        $connection = ConnectionManager::get('test');
        $models = $association->getModels($connection, $config);

        $data = $viewHelper->getYummyHelperData($models, $request);

        $this->assertArrayHasKey('Teams', $data['models']);
        $this->assertArrayHasKey('Conferences', $data['models']);
        $this->assertArrayHasKey('Divisions', $data['models']);
    }
}