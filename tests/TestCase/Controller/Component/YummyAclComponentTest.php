<?php
namespace Yummy\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;

use Yummy\Controller\Component\YummyAclComponent;
/**
 * Yummy\Controller\Component\YummyAclComponent Test Case
 */
class YummyAclComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Yummy\Controller\Component\YummyAclComponent
     */
    public $YummyAclComponent;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $request = new Request();
        $response = new Response();
        
        $request->action = 'index';
        
        $this->controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        
        $this->controller->loadComponent('Auth');
        $this->controller->loadComponent('Flash');
        
        $this->controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'user'
        ]);
        
        $registry = new ComponentRegistry($this->controller);
        $this->component = new YummyAclComponent($registry,[
            'group' => $this->controller->Auth->user('group')
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->YummyAclComponent);

        parent::tearDown();
    }

    /**
     * Test allowing everyone access to controller
     * @return void
     */
    public function testAllowAllController()
    {
        $this->component->allow('*');
        
        $event = new Event('Controller.startup', $this->controller);
        
        $this->assertEquals(true, $this->component->startup($event));
    }
    
    /**
     * Test allowing one group access to controller
     * @return void
     */
    public function testAllowGroupController()
    {
        $this->component->allow(['user']);
        
        $event = new Event('Controller.startup', $this->controller);
        
        $this->assertEquals(true, $this->component->startup($event));
    }
    
    /**
     * Test denying one group access to controller
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
    public function testAllowAllAction(){
        
        $this->component->actions(['index'=>'*']);
        
        $event = new Event('Controller.startup', $this->controller);
        
        $this->assertEquals(true, $this->component->startup($event));
    }
    
    public function testAllowGroupAction(){
        
        $this->component->actions(['index'=>['user']]);
        
        $event = new Event('Controller.startup', $this->controller);
        
        $this->assertEquals(true, $this->component->startup($event));
    }
    
    public function testDenyGroupAction(){
        
        $this->component->actions(['index'=>['admin']]);
        
        $event = new Event('Controller.startup', $this->controller);

        $this->assertEquals('Cake\Http\Response', get_class($this->component->startup($event)));
    }
    
    /**
     * Test that Flash Component is loaded
     */
    public function testFlash(){
        
        $request = new Request();
        $response = new Response();
        
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        
        $controller->loadComponent('Auth');
        
        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry,[
            'group' => 'some group'
        ]);
        $event = new Event('Controller.startup', $controller);
        
        try{
           $YummyAclComponent->startup($event);
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
    }
    
    /**
     * Test that Auth Component is loaded
     */
    public function testAuth(){
        
        $request = new Request();
        $response = new Response();
        
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        
        $controller->loadComponent('Flash');
        
        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry,[
            'group' => 'some group'
        ]);
        $event = new Event('Controller.startup', $controller);
        
        try{
           $YummyAclComponent->startup($event);
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
    }

    public function testConfigFileAllowGroupAction(){
        $request = new Request();
        $response = new Response();
        
        $request->here = 'index';
        $request->params = ['controller'=>'User','action'=>'index'];
        
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        
        $controller->name = 'User';
        $controller->loadComponent('Flash');
        $controller->loadComponent('Auth');
        
        $controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'admin'
        ]);        
        
        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry,[
            'group' =>  $controller->Auth->user('group'),
            'config' => true
        ]);
        
        \Cake\Core\Configure::write('YummyAcl', [
            'User' => [
                'actions' => [
                    'index' => ['admin']
                ]
            ]
        ]);
        
        $event = new Event('Controller.startup', $controller);
        
        $this->assertEquals(true, $YummyAclComponent->startup($event));
    }
    
    public function testConfigFileDenyGroupAction(){
        $request = new Request();
        $response = new Response();
        
        $request->here = 'index';
        $request->params = ['controller'=>'User','action'=>'index'];
        
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        
        $controller->name = 'User';
        $controller->loadComponent('Flash');
        $controller->loadComponent('Auth');
        
        $controller->Auth->setUser([
            'id' => 1,
            'username' => 'test',
            'group' => 'user'
        ]);        
        
        $registry = new ComponentRegistry($controller);
        $YummyAclComponent = new YummyAclComponent($registry,[
            'group' =>  $controller->Auth->user('group'),
            'config' => true
        ]);
        
        \Cake\Core\Configure::write('YummyAcl', [
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
     * Test allow method
     *
     * @return void
     */
    public function testMethodAllow()
    {
        // require non-empty arrays
        try{
            $this->assertNotEquals(true, $this->component->allow([]));
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
        
        // require "*" if is string
        try{
            $this->assertNotEquals(true, $this->component->allow(''));
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
        
        // test "*"
        $this->assertEquals(true, $this->component->allow('*'));
        
        // test array
        $this->assertEquals(true, $this->component->allow(['group']));
    }

    /**
     * Test actions method
     *
     * @return void
     */
    public function testMethodActions()
    {   
        // require array
        try{
            $this->assertNotEquals(true, $this->component->allow('string'));
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
        
        // require non-empty array
        try{
            $this->assertNotEquals(true, $this->component->allow([]));
        }
        catch(\Cake\Network\Exception\InternalErrorException $e){
            $this->assertEquals(500, $e->getCode());
        }
        
        // test array
        $this->assertEquals(true, $this->component->actions(['index' => ['admin','superuser','manager']]));
    }
}
