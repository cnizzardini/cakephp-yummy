<?php

namespace Yummy\Service\YummySearch;

use Cake\Utility\Inflector;

class Association
{
    /**
     * Defines models associations
     *
     * @param \Cake\Database\Connection $connection
     * @param array $config
     * @return array
     */
    public function getModels(\Cake\Database\Connection $connection, array $config)
    {
        $baseModel = $config['model'];
        $allowedModels = isset($config['allow']) ? $config['allow'] : [];
        $baseHumanName = $this->getHumanName($allowedModels, $baseModel);
        $baseModelName = Inflector::camelize($baseModel);

        $rule = new Rule($config);
        $schema = new Schema($rule);

        $models = [
            $baseModelName => [
                'humanName' => $baseHumanName,
                'path' => false,
                'columns' => $schema->getColumns($connection, $baseModel),
            ]
        ];

        $paths = $this->getPaths($config);

        foreach ($paths as $path) {

            $pieces = explode('.', $path);
            $modelName = end($pieces);

            if ($modelName === 'queryBuilder') {
                continue;
            }

            $columns = $schema->getColumns($connection, $modelName);

            if (empty($columns)) {
                continue;
            }

            $models[$modelName] = [
                'humanName' => $this->getHumanName($allowedModels, $modelName),
                'path' => $path,
                'columns' => $columns,
            ];
        }

        return $models;
    }

    /**
     * Returns paths to model associations in dot notation
     *
     * @param array $config
     * @return array
     */
    private function getPaths(array $config)
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
     *
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

    /**
     * Returns human readable name of the supplied model
     *
     * @param array $allowedModels
     * @param string $baseModel
     * @return string
     */
    private function getHumanName(array $allowedModels, string $baseModel)
    {
        if (isset($allowedModels[$baseModel]['_niceName'])) {
            return $allowedModels[$baseModel]['_niceName'];
        }

        return Inflector::humanize(Inflector::underscore($baseModel));
    }
}