<?php

namespace Yummy\Service\YummySearch;

use Cake\Utility\Inflector;
use Schema;
use Rule;

class Association
{
    /**
     * Defines models associations
     * @return void
     */
    public function getModels($database, array $config)
    {
        $baseModel = $config['model'];

        $allowedModels = $config['allow'];;

        if (isset($allowedModels[$baseModel]['_niceName'])) {
            $baseHumanName = $allowedModels[$baseModel]['_niceName'];
        } else {
            $baseHumanName = Inflector::humanize(Inflector::underscore($baseModel));
        }

        $rule = new Rule($this->_config);
        $schema = new Schema($rule);

        $models = [
            $baseHumanName => [
                'humanName' => $baseHumanName,
                'path' => false,
                'columns' => $schema->getColumns($database, $baseModel),
            ]
        ];

        $paths = $this->getPaths();

        foreach ($paths as $path) {

            $pieces = explode('.', $path);
            $theName = end($pieces);

            if ($theName === 'queryBuilder') {
                continue;
            }

            if (isset($allowedModels[$theName]['_niceName'])) {
                $humanName = $allowedModels[$theName]['_niceName'];
            } else {
                $humanName = Inflector::humanize(Inflector::underscore($theName));
            }

            $columns = $schema->getColumns($database, $theName);

            if (!empty($columns)) {
                $models[$theName] = [
                    'humanName' => $humanName,
                    'path' => $path,
                    'columns' => $schema->getColumns($database, $theName),
                ];
            }
        }

        return $models;
    }

    /**
     * Returns paths to model associations in dot notation
     * @return array
     */
    private function getPaths($config)
    {
        $query = $config['query'];

        if (method_exists($query, 'contain') === false) {
            return [];
        }

        $contains = $query->contain();
        $dots = array_keys($this->dot($contains));

        $add = [];

        $dotNotations = array_filter($dots, function($dot){
            $pieces = explode('.', $dot);
            $length = count($pieces);
            if ($length >= 1) {
                return $dot;
            }
        });

        foreach ($dotNotations as $dot) {
            $pieces = explode('.', $dot);
            $length = count($pieces);
            for ($i = 1; $i < $length; $i++) {
                $tmp = $pieces;
                $path = implode('.', array_slice($tmp, 0, $i));
                $add[] = $path;
            }
        }
        return array_merge($dots, array_unique($add));
    }

    /**
     * Flatten multi-dimensional array with key names in dotted notation
     * @param array $array
     * @param string $prepend
     * @return array
     */
    private function dot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, $this->dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }
}