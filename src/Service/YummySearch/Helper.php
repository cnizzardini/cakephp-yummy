<?php

namespace Yummy\Service\YummySearch;

class Helper
{
    /**
     * Reformat HTTP POST data in more usable array
     *
     * @param \Cake\Network\Request
     * @return array
     */
    public static function getFormattedData(\Cake\Network\Request $request) : array
    {
        $array = [];

        $data = $request->query('YummySearch');

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
     * @param \Cake\Controller\Controller $controller
     * @return bool
     * @throws InternalErrorException
     */
    public static function checkComponents(\Cake\Controller\Controller $controller) : bool
    {
        if (!isset($controller->Paginator)) {
            throw new \Cake\Network\Exception\InternalErrorException(
                __('YummySearch requires Paginator Component')
            );
        }

        return true;
    }
}