<?php

namespace Yummy\Service\YummySearch;

use Cake\Utility\Inflector;

class ViewHelper
{
    private $option;
    private $config;

    /**
     * @param array $config
     * @param Option $option
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves an array used by YummySearchHelper
     *
     * @param array $models
     * @param \Cake\Network\Request $request
     * @return array
     */
    public function getYummyHelperData(array $models, \Cake\Network\Request $request)
    {
        $selectOptions = [];

        foreach ($models as $camelName => $model) {
            $selectOptions = array_merge(
                $selectOptions,
                $this->getYummyMetaColumns($model, $camelName, $request)
            );
        }

        if ($this->config['selectGroups'] === false) {
            $select = [];
            foreach ($selectOptions as $options) {
                $select = array_merge($select, $options);
            }

            // apply A-Z sort when select groups are not used
            usort($select, function($a, $b) {
                return strcmp($a['text'], $b['text']);
            });
        }

        $yummy = [
            'base_url' => $request->here,
            'rows' => $request->query('YummySearch'),
            'operators' => $this->config['operators'],
            'models' => isset($select) ? $select : $selectOptions
        ];

        return $yummy;
    }

    /**
     * Retrieves columns to build dropdowns
     *
     * @param array $model
     * @param string $camelName
     * @param \Cake\Network\Request $request
     * @return array
     */
    private function getYummyMetaColumns(array $model, string $camelName, \Cake\Network\Request $request)
    {
        $selectOptions = [];

        foreach ($model['columns'] as $column => $field) {

            $humanName = $model['humanName'];

            $meta = $this->getYummyMeta($camelName, $field['column']);

            $element = [
                'text' => ($meta['niceName'] !== false) ? $meta['niceName'] : $field['text'],
                'path' => $model['path'],
                'value' => $column,
                'data-items' => ($meta['options'] !== false) ? implode(',', $meta['options']) : false,
                'data-type' => ($meta['options'] !== false) ? 'list' : $field['type'],
                'data-length' => $field['length'],
                'selected' => ($request->query('YummySearch') === null && $meta['default'] === true) ? true : false
            ];

            $selectOptions[$humanName][$meta['sortOrder']] = $element;
        }

        return $selectOptions;
    }

    /**
     * Returns yummy meta data for a column
     *
     * @param string $model
     * @param string $column
     * @return array
     */
    private function getYummyMeta(string $model, string $column) : array
    {
        $meta = [
            'options' => false,
            'niceName' => false,
            'default' => false,
        ];

        $config = $this->config;

        if (!isset($config['allow']["$model.$column"])) {
            return $meta;
        }

        $options = $config['allow']["$model.$column"];

        return [
            'niceName' => isset($options['name']) ? $options['name'] : Inflector::singularize($model) . ' ' . ucwords($column),
            'options' => isset($options['select']) ? $options['select'] : false,
            'default' => false,
            'sortOrder' => array_search(
                "$model.$column",
                $config['allow']
            )
        ];
    }
}