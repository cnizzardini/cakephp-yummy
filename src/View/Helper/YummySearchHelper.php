<?php

namespace Yummy\View\Helper;

use Cake\View\Helper;

class YummySearchHelper extends Helper
{
    /**
     * Renders YummySearch form
     * @param array $config
     * @return string
     */
    public function basicForm($config = [])
    {
        $element = 'Yummy.YummySearch/basic-form';

        if (isset($config['element'])) {
            $element = $config['element'];
        }

        return $this->_View->element($element, $config);
    }
}
