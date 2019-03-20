<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Yummy\Service\YummySearch\Rule;


class RuleTest extends TestCase
{
    public function testIsModelAllowedTrue()
    {
        $rule = new Rule([
            'allow' => [
                'Foo'
            ],
            'deny' => [
                'Bar'
            ]
        ]);

        $this->assertTrue($rule->isModelAllowed('Foo'));
        $this->assertFalse($rule->isModelAllowed('Bar'));

        $rule = new Rule([
            'allow' => [
                'Foo' => '*',
            ],
            'deny' => [
                'Bar' => '*'
            ]
        ]);

        $this->assertTrue($rule->isModelAllowed('Foo'));
        $this->assertFalse($rule->isModelAllowed('Bar'));
    }

    public function testIsModelAllowedFalse()
    {
        $rule = new Rule([
            'deny' => [
                'Bar'
            ]
        ]);

        $this->assertFalse($rule->isModelAllowed('Bar'));

        $rule = new Rule([
            'deny' => [
                'Bar' => '*'
            ]
        ]);

        $this->assertFalse($rule->isModelAllowed('Bar'));
    }

    public function testIsColumnAllowedTrue()
    {
        $rule = new Rule([
            'allow' => [
                'Foo' => ['id'],
                'Bar' => [
                    '_columns' => [
                        'id'
                    ]
                ],
                'Foobar' => [
                    '_columns' => [
                        'id' => ['_niceName' => 'ID']
                    ]
                ]
            ]
        ]);

        $this->assertNotFalse($rule->isColumnAllowed('Foo','id'));
        $this->assertNotFalse($rule->isColumnAllowed('Bar','id'));
        $this->assertNotFalse($rule->isColumnAllowed('Foobar','id'));
    }

    public function testIsColumnAllowedFalse()
    {
        $rule = new Rule([
            'deny' => [
                'Foo' => ['id'],
                'Bar' => '*'
            ]
        ]);

        $this->assertFalse($rule->isColumnAllowed('Foo','id'));
        $this->assertFalse($rule->isColumnAllowed('Bar','id'));
    }
}