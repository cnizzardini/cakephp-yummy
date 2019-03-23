<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Http\ServerRequest;
use Cake\Controller\Controller;
use Cake\Controller\Component\PaginatorComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\Response;
use Yummy\Service\YummySearch\Helper;


class HelperTest extends TestCase
{
    public function testGetFormattedData()
    {
        $request = new ServerRequest();
        $request = $request->withQueryParams([
            'YummySearch' => [
               'field' => [
                   'Conferences.name',
                   //'Divisions.name',
               ],
               'operator' => [
                   'eq',
                   //'like'
               ],
               'search' => [
                   'NFC',
                   //'East'
               ]
            ]
        ]);

        $data = Helper::getFormattedData($request);

        $this->assertArrayHasKey('field', $data[0]);
        $this->assertArrayHasKey('operator', $data[0]);
        $this->assertArrayHasKey('search', $data[0]);

        $this->assertEquals('Conferences.name',$data[0]['field']);
        $this->assertEquals('eq',$data[0]['operator']);
        $this->assertEquals('NFC',$data[0]['search']);
    }

    public function testCheckComponentsTrue()
    {
        $this->markTestIncomplete('Not implemented yet.');

/*        $request = new ServerRequest();
        $response = new Response();
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        $registry = new ComponentRegistry($this->controller);
        $this->component = new PaginatorComponent($registry);
        $event = new Event('Controller.startup', $this->controller);
        $this->component->startup($event);

        $this->assertTrue(Helper::checkComponents($this->controller));*/
    }
}