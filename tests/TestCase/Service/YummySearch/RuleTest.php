<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Yummy\Service\YummySearch\Rule;


class RuleTest extends TestCase
{
    public function testIsModelAllowed()
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

    public function testIsColumnAllowed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}