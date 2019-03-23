<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Http\ServerRequest;
use Cake\Controller\Controller;
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
        $controller = new Controller();
        $controller->loadComponent('Paginator');
        $this->assertTrue(Helper::checkComponents($controller));
    }

    public function testCheckComponentsException()
    {
        $className = '';
        $controller = new Controller();
        try{
            Helper::checkComponents($controller);
        } catch(\Exception $e) {
            $className = get_class($e);
        }
        $this->assertEquals('Yummy\Exception\YummySearch\ConfigurationException', $className);
    }
}