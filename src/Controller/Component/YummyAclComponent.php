<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\ForbiddenException;
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
        $this->controller = $this->_registry->getController();
        $this->controllerName = $this->controller->name . 'Controller';
        $this->actionName = $this->controller->request->action;
        
        // check for required components
        $this->checkComponents();
        
        // determine the redirect url
        $this->setRedirect();
        
        // determine if we are using a flat file config
        $this->whichConfig();
        
        // check if user is authenticated
        if( $this->checkAuth() == false ){
            return $this->controller->redirect($this->redirect('redirect'));
        }
        
        // check for controller level acl
        $hasController = $this->checkControllerAccess();
        if( $hasController === false ){
            return $this->controller->redirect($this->config('redirect'));
        } else if( $hasController === true ){
            return true;
        }
        
        // check for action level acl
        $hasAction = $this->checkActionAccess();
        if( $hasAction == false ){
            return $this->controller->redirect($this->config('redirect'));
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
        
        $this->setConfig('allow', $config);
        return true;
    }
    
    /**
     * actions - set ACLs for a controllers actions
     * @param array $config
     * @return bool true on succes
     * @throws InternalErrorException
     */
    public function actions(array $config)
    {
        if( !is_array($config) || empty($config) ){
            throw new InternalErrorException('YummyAcl::actions argument must be an array. Check documentation for array structure');
        }
        $this->setConfig('actions', $config);
        return true;
    }
    
    /**
     * checkAuth - checks if the user is authenticated
     * @return boolean
     * @throws ForbiddenException
     */
    private function checkAuth()
    {   
        if( !$this->Auth->user() ){
            $this->Flash->warn(__('You are not logged in'),[
                'params'=>['title'=>'Access denied']
            ]);

            if( $this->config('redirect') == 403 ){
                throw new ForbiddenException();
            }
            return false;
        }
        return true;
    }
    
    /**
     * checkActionAccess - check if user has access to the requested action
     * @return boolean - redirect on false
     * @throws InternalErrorException
     * @throws ForbiddenException
     */
    private function checkActionAccess()
    {
        $config = $this->config();
        
        // actions are not configured? 
        if( !isset($config['actions']) ){
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config is missing "actions". To enable access to all actions set "allow" equal to wildcard (*)'));
        
        // actions must be an array at this point
        } else if ( !is_array($config['actions']) ){
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config "actions" should be an array of [action => [groups]]'));

        // $this->actionName must be a key in the actions array at this point
        } else if ( !isset($config['actions'][ $this->actionName ]) ){
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config is missing the action "' . $this->actionName . '" as a key in the "actions" array'));
            
        // check for allow all or specific group
        } else if( $config['actions'][ $this->actionName ] != '*' && !in_array($config['group'], $config['actions'][$this->actionName]) ){
            $this->Flash->warn(__('You are not authorized to visit this page'),[
                'params'=>['title'=>'Access denied']
            ]);
            if( $config['redirect'] == 403 ){
                throw new ForbiddenException();
            }
            return false;
        }
        return true;
    }
    
    /**
     * checkControllerAccess - check if user has access to the requested controller
     * @return boolean|void - exit component on true, redirect on false, do nothing on void
     * @throws InternalErrorException
     * @throws ForbiddenException
     */
    private function checkControllerAccess()
    {
        if( $this->config('allow') != null ){
            
            // allow access to all actions in this controller
            if( $this->config('allow') == '*' ){
                return true;
            
            // must be an array at this point, throw exception
            } else if( !is_array($this->config('allow')) ){
                throw new InternalErrorException(__($this->controllerName . ' YummyAcl config "allow" option must be (1) not set, (2) an array of groups, or (3) equal to wildcard (*)'));
                
            // check for group level access to this controller    
            } else if( in_array($this->config('group'), $this->config('allow')) ){
                return true;
            
            // not authorized
            } else {
                $this->Flash->warn(__('You are not authorized to view this section'),[
                    'params'=>['title'=>'Access denied']
                ]);
                
                if( $this->config('redirect') == 403 ){
                    throw new ForbiddenException();
                }
                return false;
            }            
        }
    }
    
    /**
     * checkComponents - throws exception if missing a required component
     * @throws InternalErrorException
     */
    private function checkComponents()
    {
        if( !isset($this->controller->Auth) ){
            throw new InternalErrorException(__('YummyAcl requires the AuthComponent'));
        }
        
        if( !isset($this->controller->Flash) ){
            throw new InternalErrorException(__('YummyAcl requires the FlashComponent'));
        }
    }
    
    /**
     * whichConfig - whether to use the flat file config or not
     * @throws InternalErrorException
     */
    private function whichConfig()
    {   
        if( $this->config('config') == true ){
            $config = Configure::read('YummyAcl');
            
            if( !$config ){
                throw new InternalErrorException(__('YummyAcl config file does not exist. Have you created it: YummyCake/config/acl_config.php?'));
            }
            
            if( !isset($config[ $this->controller->name ]) ){
                throw new InternalErrorException(__('The controller "' . $this->controller->name . '" is missing from the YummyAcl config file'));
            }
            
            $this->configShallow($config[ $this->controller->name ]);
        }
        
        if( $this->config('group') == null ){
            throw new InternalErrorException(__('The "group" option is required in YummyAcl config'));
        }
    }
    
    /**
     * setRedirect - sets the redirect url
     * @throws InternalErrorException
     */
    private function setRedirect()
    {
        if( $this->config('redirect') == null ){
            $authConfig = $this->Auth->config();
            
            if( $authConfig['unauthorizedRedirect'] == true ){
                $this->setConfig('redirect', $this->request->referer(true));
                
            } else if( is_string($authConfig['unauthorizedRedirect']) ){
                $this->setConfig('redirect', $authConfig['unauthorizedRedirect']);
                
            } else if( $authConfig['unauthorizedRedirect'] == false ){
                $this->setConfig('redirect', 403);
            }
            else{
                throw new InternalErrorException(__('YummyAcl requires the "redirect" option in config or Auth.loginAction or Auth.unauthorizedRedirect'));
            }
        }    
    }
}
