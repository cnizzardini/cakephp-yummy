<?php

namespace Yummy\Controller;

use App\Controller\AppController as BaseController;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;

class AppController extends BaseController
{
    public function beforeFilter(\Cake\Event\Event $event){
        parent::beforeFilter($event);
        if( Configure::read('debug') == false ){
            throw new ForbiddenException('Forbidden');
        }
    }
}
