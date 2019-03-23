<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Cake\Http\ServerRequest;
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

    public function testCheckComponents()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}