<?php

namespace Yummy\Service\YummySearch;

use Cake\Utility\Inflector;
use Cake\Network\Request;

class ViewHelper
{
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves an array used by YummySearchHelper
     *
     * @param array $models
     * @param Request $request
     * @return array
     */
    public function getYummyHelperData(array $models, Request $request) : array
    {
        $selectOptions = [];

        foreach ($models as $camelName => $model) {
            $options = $this->getYummyMetaColumns($model, $camelName, $request);
            $selectOptions = $selectOptions + $this->getOptions($options);
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
     * @param Request $request
     * @return array
     */
    private function getYummyMetaColumns(array $model, string $camelName, Request $request) : array
    {
        $selectOptions = [];

        foreach ($model['columns'] as $column => $field) {

            $humanName = $model['humanName'];

            $meta = $this->getYummyMeta($camelName, $field['column']);

            $element = [
                'text' => ($meta['niceName'] !== false) ? $meta['niceName'] : $field['text'],
                'path' => $model['path'],
                'value' => $column,
                'data-items' => !empty($meta['options']) ? implode(',', $meta['options']) : false,
                'data-type' => !empty($meta['options']) ? 'list' : $field['type'],
                'data-length' => $field['length'],
                'selected' => ($request->query('YummySearch') === null && $meta['default'] === true) ? true : false,
                'data-operators' => is_array($meta['operators']) ? implode(',', $meta['operators']) : false,
                'data-group' => $meta['group']
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
        $keys = array_keys($config['allow']);
        $defaultName = Inflector::singularize($model) . ' ' . ucwords($column);

        return [
            'niceName' => isset($options['name']) ? $options['name'] : $defaultName,
            'options' => isset($options['select']) ? $options['select'] : [],
            'operators' => isset($options['operators']) ? $options['operators'] : [],
            'default' => false,
            'sortOrder' => array_search("$model.$column", $keys),
            'group' => isset($options['group']) ? $options['group'] : false
        ];
    }

    /**
     * Gets array of html select compatible options as either options, optgroups, or custom optgroups depending
     * on configuration
     *
     * @param array $options
     * @return array
     */
    private function getOptions(array $options) : array
    {
        $keys = array_keys($options);
        $name = reset($keys);
        ksort($options[$name]);

        if ($this->config['selectGroups'] === false) {
            $options = $options[$name];
        } else if ($this->config['selectGroups'] === 'custom') {
            $tmp = $options[$name];
            $options = [];
            foreach ($tmp as $option) {
                $groupName = $option['data-group'] ? $option['data-group'] : $name;
                $options[$groupName][] = $option;
            }
        }

        return $options;
    }
}