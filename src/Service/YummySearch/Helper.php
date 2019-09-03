<?php

namespace Yummy\Service\YummySearch;

use Cake\Controller\Controller;
use Cake\Network\Request;
use Yummy\Exception\YummySearch\ConfigurationException;

class Helper
{
    /**
     * Reformat HTTP POST data in more usable array
     *
     * @param Request $request
     * @return array
     */
    public static function getFormattedData(Request $request) : array
    {
        $array = [];

        $data = $request->getQuery('YummySearch');

        $length = count($data['field']);

        // loop through available fields and set conditions
        for ($i = 0; $i < $length; $i++) {

            if (!isset($data['field'][$i]) || !isset($data['operator'][$i]) || !isset($data['search'][$i])) {
                continue;
            }

            $array[] = [
                'field' => $data['field'][$i],
                'operator' => $data['operator'][$i],
                'search' => $data['search'][$i],
            ];
        }

        return $array;
    }

    /**
     * Throws exception if missing a required component
     *
     * @param Controller $controller
     * @return bool
     * @throws InternalErrorException
     */
    public static function checkComponents(Controller $controller) : bool
    {
        if (!isset($controller->Paginator)) {
            throw new ConfigurationException(
                __('YummySearch requires Paginator Component')
            );
        }

        return true;
    }
}