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
            $selectOptions = array_replace_recursive($selectOptions, $this->getOptions($options));
        }

        if ($this->config['selectGroups'] !== false) {
            ksort($selectOptions);
            foreach(array_keys($selectOptions) as $group) {
                ksort($selectOptions[$group]);
            }
        } else {
            ksort($selectOptions);
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

            $meta = $this->getYummyMeta($camelName, $field);

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
     * @param array $field
     * @return array
     */
    private function getYummyMeta(string $model, array $field) : array
    {
        $meta = [
            'options' => false,
            'niceName' => false,
            'default' => false,
            'operators' => false,
            'group' => false,
            'sortOrder' => false
        ];

        $config = $this->config;
        $column = $field['column'];

        if (!isset($config['allow']["$model.$column"])) {
            return $meta;
        }

        $options = $config['allow']["$model.$column"];
        $keys = array_keys($config['allow']);
        $defaultName = Inflector::singularize($model) . ' ' . ucwords($column);

        return [
            'niceName' => isset($options['name']) ? $options['name'] : $defaultName,
            'options' => isset($options['select']) ? $options['select'] : [],
            'operators' => $this->getOperators($field, $options),
            'default' => false,
            'sortOrder' => array_search("$model.$column", $keys),
            'group' => isset($options['group']) ? $options['group'] : $model
        ];
    }

    private function getOperators(array $field, array $options) : array
    {
        if (isset($options['operators'])) {
            return $options['operators'];
        }

        if (isset($options['select']) && !empty($options['select'])) {
            return ['eq','not_eq'];
        }

        switch (strtolower($field['type'])) {
            case 'string':
                return ['like','not_like','eq','not_eq'];
                break;
            case 'date':
            case 'datetime':
            case 'timestamp':
                return ['eq','not_eq','gt','lt','gt_eq','lt_eq'];
                break;
            default:
                return [];
        }
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
        $return = [];

        $keys = array_keys($options);
        $name = reset($keys);

        if (!isset($options[$name])) {
            return $return;
        }

        ksort($options[$name]);

        if ($this->config['selectGroups'] === false && isset($options[$name])) {
            $return = $options[$name];
        } else if ($this->config['selectGroups'] === 'custom') {
            $tmp = $options[$name];
            $return = [];

            $groups = $this->getSortedGroups();

            foreach ($tmp as $key => $option) {

                $groupName = $option['data-group'] ? $option['data-group'] : $name;

                $index = array_search($groupName, $groups);
                $padding = count($groups) - $index;

                $groupIndex = str_pad('', $padding, ' ') . $groupName;

                $return[$groupIndex][$key] = $option;
            }
        }

        return $return;
    }

    /**
     * Returns an array of sorted groups
     *
     * @return array
     */
    private function getSortedGroups() : array
    {
        $groups = [];

        foreach ($this->config['allow'] as $option) {
            if (!isset($option['group'])) {
                continue;
            }
            $groups[] = $option['group'];
        }

        return array_values(array_unique($groups));
    }
}