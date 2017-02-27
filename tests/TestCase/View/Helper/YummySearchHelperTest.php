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
    public function setUp()
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
    public function tearDown()
    {
        unset($this->YummySearchHelper);

        parent::tearDown();
    }

    /**
     * Test basicForm method
     *
     * @return void
     */
    public function testBasicForm()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
