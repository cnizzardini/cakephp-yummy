<?php

namespace Yummy\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Yummy\Controller\Component\YummySearchComponent;

/**
 * Yummy\Controller\Component\YummySearchComponent Test Case
 */
class YummySearchComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Yummy\Controller\Component\YummySearchComponent
     */
    public $YummySearchComponent;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->YummySearchComponent = new YummySearchComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->YummySearchComponent);

        parent::tearDown();
    }

    /**
     * Test beforeRender method
     *
     * @return void
     */
    public function testBeforeRender()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test search method
     *
     * @return void
     */
    public function testSearch()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
