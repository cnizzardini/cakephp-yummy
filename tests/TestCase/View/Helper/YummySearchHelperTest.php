<?php
namespace Yummy\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Yummy\View\Helper\YummySearchHelper;

/**
 * Yummy\View\Helper\YummySearchHelper Test Case
 */
class YummySearchHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Yummy\View\Helper\YummySearchHelper
     */
    public $YummySearchHelper;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
        $view = new View();
        $this->YummySearchHelper = new YummySearchHelper($view);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown() : void
    {
        unset($this->YummySearchHelper);

        parent::tearDown();
    }

    /**
     * Test basicForm method
     * @todo https://book.cakephp.org/3.0/en/development/testing.html#testing-helpers
     * @return void
     */
    public function testBasicForm()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
