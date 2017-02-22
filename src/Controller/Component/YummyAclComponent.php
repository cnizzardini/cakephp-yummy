<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\InternalErrorException;
use Cake\Core\Configure;

/**
 * This component is a rudimentary ACL system for applying group level access to controllers and methods
 */
class YummyAclComponent extends Component
{	
    public $components = ['Flash','Auth'];
    
    
    protected $_defaultConfig = [
        'redirect' => '/',
        //'allow' => '*'
    ];
    
    /**
     * beforeFilter - handles acl logic
     */
    public function startup() 
    {
        $config = $this->config();
        
        // access the userland controller
        $controller = $this->_registry->getController();
        
        // are we using the yummy config file or controller level configurations?
        if( $this->config('config') == true ){
            $tmp = Configure::read('YummyAcl');
            
            if( !$tmp ){
                throw new InternalErrorException(__('YummyAcl config file does not exist. Have you created it: YummyCake/config/acl_config.php?'));
            }
            
            if( !isset($tmp[ $controller->name ]) ){
                throw new InternalErrorException(__('The controller (' . $controller->name . ') is missing from the YummyAcl config file'));
            }
            
            $config = array_merge($config, $tmp[ $controller->name ]);
        }
        
        // do not bother with ACL checks if user is not logged in
        if( !$this->Auth->user() ){
            return false;
        }
        
        // group is required, throw exception if not set
        if( !isset($config['group']) ){
           throw new InternalErrorException(__('The "group" option is required in YummyAcl config'));
        }
        
        // get action name
        $actionName = $controller->request->action;
        // get controller name
        $controllerName = $controller->name . 'Controller';

        // check for controller level acl
        if( isset($config['allow']) ){
            
            // allow access to all actions in this controller
            if( $config['allow'] == '*' ){
                return false;
            
            // must be an array at this point, throw exception
            } else if( !is_array($config['allow']) ){
                throw new InternalErrorException(__($controllerName . ' YummyAcl config "allow" option must be (1) not set, (2) an array of groups, or (3) equal to wildcard (*)'));
                
            // check for group level access to this controller    
            } else if( !in_array($config['group'], $config['allow']) ){
                $this->Flash->warn(__('You are not authorized to view this section'),[
                    'params'=>['title'=>'Access denied']
                ]);
                return $controller->redirect($config['redirect']);
            }
        }

        // actions are not configured? 
        if( !isset($config['actions']) ){
            throw new InternalErrorException(__($controllerName . ' YummyAcl config is missing "actions". To enable access to all actions set "allow" equal to wildcard (*)'));
        
        // actions must be an array at this point
        } else if ( !is_array($config['actions']) ){
            throw new InternalErrorException(__($controllerName . ' YummyAcl config "actions" should be an array of [action => [groups]]'));

        // $actionName must be a key in the actions array at this point
        } else if ( !isset($config['actions'][ $actionName ]) ){
            throw new InternalErrorException(__($controllerName . ' YummyAcl config is missing the action (' . $actionName . ') as a key in the "actions" array'));
            
        // check for allow all or specific group
        } else if( $config['actions'][ $actionName ] != '*' && !in_array($config['group'], $config['actions'][$actionName]) ){
            $this->Flash->warn(__('You are not authorized to visit this page'),[
                'params'=>['title'=>'Access denied']
            ]);
            return $controller->redirect($config['redirect']);
        }
    }
    
    /**
     * allow - set controller level acl
     * @param mixed $config
     */
    public function allow($config)
    {
        $this->_config['allow'] = $config;
    }
    
    /**
     * actions - set action level acl
     * @param mixed $config
     */
    public function actions($config)
    {
        $this->_config['actions'] = $config;
    }
}
