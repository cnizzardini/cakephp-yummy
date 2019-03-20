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
            ]
        ]);

        $this->assertFalse($rule->isColumnAllowed('Bar'));
        $this->assertTrue($rule->isColumnAllowed('Foo'));
    }

    public function testIsColumnAllowed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}