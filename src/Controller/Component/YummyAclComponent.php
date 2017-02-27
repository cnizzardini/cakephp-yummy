<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Http\ServerRequest;
use Cake\Core\Configure;

/**
 * This component is a rudimentary ACL system for applying group level access to controllers and methods
 */
class YummyAclComponent extends Component
{	
    public $components = ['Flash','Auth'];
    
    
    protected $_defaultConfig = [
        //'redirect' => '/',
        //'allow' => '*'
    ];
    
    /**
     * startup - this is a magic method that gets called by cake
     * @todo this may need to operate differently for parsed extensions such as json and xml
     * @return bool|\Cake\Network\Response| returns true if the acl passes, network response redirect if fails
     * @throws InternalErrorException
     */
    public function startup() 
    {
        $config = $this->config();
        
        // access the userland controller
        $controller = $this->_registry->getController();

        if( !isset($controller->Auth) ){
            throw new InternalErrorException(__('YummyAcl requires the AuthComponent'));
        }
        
        if( !isset($controller->Flash) ){
            throw new InternalErrorException(__('YummyAcl requires the FlashComponent'));
        }
        
        // if redirect is not defined then use settings from AuthComponent
        if( !isset($config['redirect']) ){
            $authConfig = $this->Auth->config();
            
            if( $authConfig['unauthorizedRedirect'] == true ){
                $config['redirect'] = $this->request->referer(true);
                
            } else if( is_string($authConfig['unauthorizedRedirect']) ){
                $config['redirect'] = $authConfig['unauthorizedRedirect'];
                
            } else if( $authConfig['unauthorizedRedirect'] == false ){
                $config['redirect'] = 403;
            }
            else{
                throw new InternalErrorException(__('YummyAcl requires the "redirect" option in config or Auth.loginAction or Auth.unauthorizedRedirect'));
            }
        }
        
        // are we using the yummy config file or controller level configurations?
        if( $this->config('config') == true ){
            $tmp = Configure::read('YummyAcl');
            
            if( !$tmp ){
                throw new InternalErrorException(__('YummyAcl config file does not exist. Have you created it: YummyCake/config/acl_config.php?'));
            }
            
            if( !isset($tmp[ $controller->name ]) ){
                throw new InternalErrorException(__('The controller "' . $controller->name . '" is missing from the YummyAcl config file'));
            }
            
            $config = array_merge($config, $tmp[ $controller->name ]);
        }
        
        // do not bother with ACL checks if user is not logged in
        if( !$this->Auth->user() ){
            $this->Flash->warn(__('You are not logged in'),[
                'params'=>['title'=>'Access denied']
            ]);

            if( $config['redirect'] == 403 ){
                throw new ForbiddenException();
            } else {
                return $controller->redirect($config['redirect']);
            }
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
            if( $config['allow'] == '*' ){ die('123');
                return true;
            
            // must be an array at this point, throw exception
            } else if( !is_array($config['allow']) ){
                throw new InternalErrorException(__($controllerName . ' YummyAcl config "allow" option must be (1) not set, (2) an array of groups, or (3) equal to wildcard (*)'));
                
            // check for group level access to this controller    
            } else if( in_array($config['group'], $config['allow']) ){
                return true;
            
            // not authorized
            } else {
                $this->Flash->warn(__('You are not authorized to view this section'),[
                    'params'=>['title'=>'Access denied']
                ]);
                
                if( $config['redirect'] == 403 ){
                    throw new ForbiddenException();
                } else {
                    return $controller->redirect($config['redirect']);
                }
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
            throw new InternalErrorException(__($controllerName . ' YummyAcl config is missing the action "' . $actionName . '" as a key in the "actions" array'));
            
        // check for allow all or specific group
        } else if( $config['actions'][ $actionName ] != '*' && !in_array($config['group'], $config['actions'][$actionName]) ){
            $this->Flash->warn(__('You are not authorized to visit this page'),[
                'params'=>['title'=>'Access denied']
            ]);
            if( $config['redirect'] == 403 ){
                throw new ForbiddenException();
            } else {
                return $controller->redirect($config['redirect']);
            }
        }
        return true;
    }
    
    /**
     * allow - set allowed groups for a controller
     * @param string|array $config
     * @return bool true on succes
     * @throws InternalErrorException
     */
    public function allow($config)
    {
        if( (is_string($config) && $config != '*') || ( is_array($config) && empty($config) ) ){
            throw new InternalErrorException('YummyAcl::allow argument must be either a string value of "*" or an array of groups');
        }
        
        $this->_config['allow'] = $config;
        return true;
    }
    
    /**
     * actions - set ACLs for a controllers actions
     * @param string|array $config
     * @return bool true on succes
     * @throws InternalErrorException
     */
    public function actions($config)
    {
        if( !is_array($config) || empty($config) ){
            throw new InternalErrorException('YummyAcl::actions argument must be an array. Check documentation for array structure');
        }
        $this->_config['actions'] = $config;
        return true;
    }
}
