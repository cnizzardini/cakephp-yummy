<?php
namespace Yummy\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\ForbiddenException;
use Cake\Core\Configure;

/**
 * This component is a rudimentary ACL system for applying group level access to controllers and methods
 * @todo this may need to operate differently for parsed extensions such as json and xml
 */
class YummyAclComponent extends Component
{
    public $components = ['Flash','Auth'];

//    protected $_defaultConfig = [
//        'redirect' => '/',
//        'allow' => '*'
//    ];

    /**
     * startup - this is a magic method that gets called by cake
     * @return bool|\Cake\Network\Response returns true if the acl passes, network response redirect if fails
     * @throws InternalErrorException
     */
    public function startup()
    {
        $this->controller = $this->_registry->getController();
        $this->controllerName = $this->controller->name . 'Controller';
        $this->actionName = $this->controller->request->action;

        // check for required components
        $this->checkComponents();

        // determine if we are using a flat file config
        $this->whichConfig();

        // perform sanity check
        $this->sanityCheck();
        
        // determine the redirect url
        $this->setRedirect();

        if ($this->checkControllerAccess() == true) {
            return true;
        }

        // check for controller level acl
        if ($this->checkActionAccess() == false) {
            return $this->denyAccess();
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
        if ((is_string($config) && $config != '*') || ( is_array($config) && empty($config) )) {
            throw new InternalErrorException('YummyAcl::allow argument must be either a string value of "*" or an '
            . 'array of groups');
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
        if (!is_array($config) || empty($config)) {
            throw new InternalErrorException('YummyAcl::actions argument must be an array. Check documentation for '
            . 'array structure');
        }

        $this->setConfig('actions', $config);
        return true;
    }

    /**
     * sanityCheck - ensures component was configured correctly
     * @return void
     * @throws InternalErrorException
     */
    private function sanityCheck()
    {
        $config = $this->config();
        
        // group required
        if ($this->Auth->user() && $config['group'] == null) {
            throw new InternalErrorException(__('The "group" option is required in YummyAcl config'));
        }
        
        // if allow is set must be "*" or (array)
        if (isset($config['allow']) && !is_string($config['allow']) && !is_array($config['allow'])) {
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config "allow" option must be '
                    . '(1) not set, (2) an array of groups, or (3) equal to wildcard (*)'));
        }
        
        // if actions is set must be (array)
        if (isset($config['actions']) && !is_array($config['actions'])) {
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config "actions" should be an array '
                    . 'of [action => [groups]]'));
        }
    }

    /**
     * denyAccess - sets flash message and if redirect is not set throws a 403 exception
     * @return boolean - on false issue deny access
     * @throws ForbiddenException
     */
    private function denyAccess()
    {
        $this->Flash->warn(__('You are not authorized to view this section'), [
            'params' => [
                'title' => 'Access denied'
            ]
        ]);

        $redirect = $this->config('redirect');

        if ($redirect == 403) {
            throw new ForbiddenException();
        }

        return $this->controller->redirect($redirect);
    }

    /**
     * checkActionAccess - check if user has access to the requested action
     * @return boolean
     * @throws InternalErrorException
     * @throws ForbiddenException
     */
    private function checkActionAccess()
    {
        $config = $this->config();

        // actions are not configured? 
        if (!isset($config['actions'])) {
            return false;
        }
        
        // actions must be an array at this point
        if (!isset($config['actions'][$this->actionName])) {
            throw new InternalErrorException(__($this->controllerName . ' YummyAcl config is missing the action '
                    . '"' . $this->actionName . '" as a key in the "actions" array'));
        }

        // check for allow all
        if ($config['actions'][$this->actionName] == '*') {
            return true;

        // check for defined group access
        } else if (in_array($config['group'], $config['actions'][$this->actionName])) {
            return true;
        }
        
        return false;
    }

    /**
     * checkControllerAccess - check if user has access to the requested controller
     * @return boolean|void - passes on true, redirect on false, do nothing on void
     * @throws InternalErrorException
     * @throws ForbiddenException
     */
    private function checkControllerAccess()
    {
        // allow all
        if ($this->config('allow') == '*') {
            return true;
        }
        
        // allow group
        if (is_array($this->config('allow')) && in_array($this->config('group'), $this->config('allow'))) {
            return true;
        }
        
        return false;
    }

    /**
     * checkComponents - throws exception if missing a required component
     * @throws InternalErrorException
     */
    private function checkComponents()
    {
        if (!isset($this->controller->Auth)) {
            throw new InternalErrorException(__('YummyAcl requires the AuthComponent'));
        }

        if (!isset($this->controller->Flash)) {
            throw new InternalErrorException(__('YummyAcl requires the FlashComponent'));
        }
    }

    /**
     * whichConfig - whether to use the flat file config or not
     * @return boolean true on success
     * @throws InternalErrorException
     */
    private function whichConfig()
    {
        if ($this->config('config') !== true) {
            return true;
        }

        $config = Configure::read('YummyAcl');

        if (!$config) {
            throw new InternalErrorException(__('YummyAcl config is missing. Please create config/yummy_acl.php'));
        }

        $this->configShallow($config[$this->controller->name]);

        return true;
    }

    /**
     * setRedirect - sets the redirect url or throws an exception if unable to determine redirect url
     * @return boolean
     * @throws InternalErrorException
     */
    private function setRedirect()
    {
        if ($this->config('redirect') != null) {
            return true;
        }

        $authConfig = $this->Auth->config();

        if ($authConfig['unauthorizedRedirect'] == true) {
            $this->setConfig('redirect', $this->request->referer(true));
            return true;
        } else if (is_string($authConfig['unauthorizedRedirect'])) {
            $this->setConfig('redirect', $authConfig['unauthorizedRedirect']);
            return true;
        } else if ($authConfig['unauthorizedRedirect'] == false) {
            $this->setConfig('redirect', 403);
            return true;
        }

        throw new InternalErrorException(__('YummyAcl requires the "redirect" option in config or Auth.loginAction or '
                . 'Auth.unauthorizedRedirect'));
    }

}
