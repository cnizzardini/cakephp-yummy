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

    public function testIsColumnAllowed()
    {
        $rule = new Rule([
            'allow' => [
                'Foo' => ['id']
            ],
            'deny' => [
                'Bar'
            ]
        ]);
    }
}