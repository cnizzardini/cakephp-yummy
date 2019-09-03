<?php

namespace Yummy\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\Http\Exception\InternalErrorException;
use Cake\TestSuite\TestCase;
use Yummy\Controller\Component\YummyAclComponent;

/**
 * Yummy\Controller\Component\YummyAclComponent Test Case
 */
class YummyAclComponentTest extends TestCase
{

    /**
     * Test YummyAclComponent
     * @var \Yummy\Controller\Component\YummyAclComponent
     */
    public $YummyAclComponent;

    /**
     * setUp method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new ServerRequest(['params' => ['action' => 'index']]);
        $request->withMethod('index');
        $response = new Response();

        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setConstructorArgs([$request, $response])
                ->setMethods(['index'])
                ->getMock();

        $this->controller->loadComponent('Auth');
        $this->controller->loadComponent('Flash');

        $this->controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'user'
        ]);

        $registry = new ComponentRegistry($this->controller);
        $this->component = new YummyAclComponent($registry, [
            'group' => $this->controller->Auth->user('group')
        ]);
    }

    /**
     * tearDown method
     * @return void
     */
    public function tearDown()
    {
        unset($this->YummyAclComponent);

        parent::tearDown();
    }

    /**
     * testAllowAllController - all users can access the controllers actions
     * @return void
     */
    public function testAllowAllController()
    {
        $this->component->allow('*');

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals(true, $this->component->startup($event));
    }

    /**
     * testAllowGroupController - specific user group can access controller
     * @return void
     */
    public function testAllowGroupController()
    {
        $this->component->allow(['user']);

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals(true, $this->component->startup($event));
    }

    /**
     * testDenyGroupController - specific user group is denied access to controller
     * @return void
     */
    public function testDenyGroupController()
    {
        $this->component->allow(['admin']);

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals('Cake\Http\Response', get_class($this->component->startup($event)));
    }

    /**
     * testAllowAllAction - all users can access the requested action
     * @return void
     */
    public function testAllowAllAction()
    {
        $this->component->actions(['index' => '*']);

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals(true, $this->component->startup($event));
    }

    /**
     * testAllowGroupAction - specific user group can access the requested action
     * @return void
     */
    public function testAllowGroupAction()
    {
        $this->component->actions(['index' => ['user']]);

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals(true, $this->component->startup($event));
    }

    /**
     * testDenyGroupAction - specific user group is denied from the requested action
     * @return void
     */
    public function testDenyGroupAction()
    {
        $this->component->actions(['index' => ['admin']]);

        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals('Cake\Http\Response', get_class($this->component->startup($event)));
    }

    /**
     * testFlash - throws exception if Flash component is not loaded
     * @return void
     */
    public function testFlash()
    {
        $request = new ServerRequest();
        $response = new Response();

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setConstructorArgs([$request, $response])
                ->setMethods(null)
                ->getMock();

        $controller->loadComponent('Auth');

        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry, [
            'group' => 'some group'
        ]);
        $event = new Event('Controller.startup', $controller);

        try {
            $YummyAclComponent->startup($event);
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }
    }

    /**
     * testAuth - throws exception if Auth Component is not loaded
     * @return void
     */
    public function testAuth()
    {
        $request = new ServerRequest();
        $response = new Response();

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setConstructorArgs([$request, $response])
                ->setMethods(null)
                ->getMock();

        $controller->loadComponent('Flash');

        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry, [
            'group' => 'some group'
        ]);
        $event = new Event('Controller.startup', $controller);

        try {
            $YummyAclComponent->startup($event);
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }
    }

    /**
     * testConfigFileAllowGroupAction - specific user group can access requested action using file config
     * @return void
     */
    public function testConfigFileAllowGroupAction()
    {
        $request = new ServerRequest(['params' => ['controller' => 'User', 'action' => 'index']]);
        $request->withMethod('index');
        $response = new Response();

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setConstructorArgs([$request, $response])
                ->setMethods(null)
                ->getMock();

        $controller->setName('User');
        $controller->loadComponent('Flash');
        $controller->loadComponent('Auth');

        $controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'admin'
        ]);

        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry, [
            'group' => $controller->Auth->user('group'),
            'use_config_file' => true
        ]);

        Configure::write('YummyAcl', [
            'User' => [
                'actions' => [
                    'index' => ['admin']
                ]
            ]
        ]);

        $event = new Event('Controller.startup', $controller);

        $this->assertEquals(true, $YummyAclComponent->startup($event));
    }

    /**
     * testConfigFileDenyGroupAction - specific user group is denied from the requested action using config file
     * @return void
     */
    public function testConfigFileDenyGroupAction()
    {
        $request = new ServerRequest(['params' => ['controller' => 'User', 'action' => 'index']]);
        $response = new Response();

        $controller = $this->getMockBuilder('Cake\Controller\Controller')
                ->setConstructorArgs([$request, $response])
                ->setMethods(null)
                ->getMock();

        $controller->setName('User');
        $controller->loadComponent('Flash');
        $controller->loadComponent('Auth');

        $controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'user'
        ]);

        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry, [
            'group' => $controller->Auth->user('group'),
            'config' => true
        ]);

        Configure::write('YummyAcl', [
            'User' => [
                'actions' => [
                    'index' => ['admin']
                ]
            ]
        ]);

        $event = new Event('Controller.startup', $controller);

        $this->assertEquals('Cake\Http\Response', get_class($YummyAclComponent->startup($event)));
    }

    /**
     * testMethodAllow - throws exceptions on bad arguments
     * @return void
     */
    public function testMethodAllow()
    {
        // require non-empty arrays
        try {
            $this->assertNotEquals(true, $this->component->allow([]));
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }

        // require "*" if is string
        try {
            $this->assertNotEquals(true, $this->component->allow(''));
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }

        // test "*"
        $this->assertEquals(true, $this->component->allow('*'));

        // test array
        $this->assertEquals(true, $this->component->allow(['group']));
    }

    /**
     * testMethodActions - throws exceptions on bad arguments
     * @return void
     */
    public function testMethodActions()
    {
        // require array
        try {
            $this->assertNotEquals(true, $this->component->allow('string'));
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }

        // require non-empty array
        try {
            $this->assertNotEquals(true, $this->component->allow([]));
        } catch (InternalErrorException $e) {
            $this->assertEquals(500, $e->getCode());
        }

        // test array
        $this->assertEquals(true, $this->component->actions(['index' => ['admin', 'superuser', 'manager']]));
    }
}
