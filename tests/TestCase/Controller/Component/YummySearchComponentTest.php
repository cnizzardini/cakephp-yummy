<?php

namespace Yummy\Test\TestCase\Controller\Component;

use Cake\TestSuite\TestCase;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;
use Cake\Http\ServerRequest;
use Cake\Network\Response;
use Yummy\Controller\Component\YummySearchComponent;

/**
 * Yummy\Controller\Component\YummySearchComponent Test Case
 */
class YummySearchComponentTest extends TestCase
{
    public function testSearch()
    {
        $request = new ServerRequest();
        $request = $request->withQueryParams([
            'YummySearch' => [
                'field' => [
                    'Teams.name',
                ],
                'operator' => [
                    'eq',
                ],
                'search' => [
                    'NY Giants',
                ]
            ]
        ]);
        $response = new Response();

        $request->action = 'index';

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();

        $controller->loadComponent('Paginator');

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

        $registry = new ComponentRegistry($controller);
        $yummySearch = new YummySearchComponent($registry, [
            'query' => $query,
            'model' => 'Teams',
            'trim' => true
        ]);

        $query = $yummySearch->search($query);

        $results = $query->toArray();

        $row = reset($results);

        $this->assertEquals(21, $row->id);
    }
}
