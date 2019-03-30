<?php

namespace Yummy\Test\TestCase\Service\YummySearch;

use Cake\TestSuite\TestCase;
use Yummy\Service\YummySearch\Rule;


class RuleTest extends TestCase
{
    public function testIsColumnAllowedTrue()
    {
        $rule = new Rule([
            'allow' => [
                'Foo.id' => ['name' => false, 'operators' => false, 'select' => false],
                'Bar.id' => ['name' => false, 'operators' => false, 'select' => false],
            ]
        ]);

        $this->assertNotFalse($rule->isColumnAllowed('Foo','id'));
        $this->assertNotFalse($rule->isColumnAllowed('Bar','id'));
    }
}