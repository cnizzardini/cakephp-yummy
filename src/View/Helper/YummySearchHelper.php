<?php
namespace YummyCake\View\Helper;

use Cake\View\Helper;

class YummySearchHelper extends Helper
{
    public function basicForm($config=[])
    {
        $element = 'YummyCake.YummySearch/basic-form';
        
        if( isset($config['element']) ){
            $element = $config['element'];
        }
        
        return $this->_View->element($element, $config);
    }
}