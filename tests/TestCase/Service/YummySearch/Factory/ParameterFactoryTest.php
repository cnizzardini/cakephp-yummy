<?php

namespace Yummy\Test\TestCase\Service\YummySearch\Factory;

use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Yummy\Service\YummySearch\Factory\ParameterFactory;
use Yummy\Service\YummySearch\Parameter;

class ParameterFactoryTest extends TestCase
{
    public $fixtures = ['plugin.Yummy.Teams'];

    public function testCreate()
    {
        $config = [
            'query' => TableRegistry::getTableLocator()->get('Teams')->find(),
            'model' => 'Teams',
            'trim' => true
        ];

        $item = [
            'operator' => 'eq',
            'search' => 'NY Giants'
        ];

        $parameterFactory = new ParameterFactory($config);
        $parameter = $parameterFactory->create('Teams','name', $item);

        $this->assertInstanceOf(Parameter::class, $parameter);
    }
}