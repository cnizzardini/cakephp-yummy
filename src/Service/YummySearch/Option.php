<?php

namespace Yummy\Service\YummySearch;

class Option
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getColumnNiceName($element)
    {
        if (is_string($element)) {
            return $element;
        }

        if (isset($element['_niceName'])) {
            return $element['_niceName'];
        }

        return false;
    }

    public function getColumnOptions($element)
    {
        if (isset($element['_options'])) {
            return $element['_options'];
        }

        return false;
    }

    public function getColumnDefault($element)
    {
        if (isset($element['_default'])) {
            return $element['_default'];
        }

        return false;
    }
}