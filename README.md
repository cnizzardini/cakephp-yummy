# Yummy plugin for CakePHP

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Code Climate](https://codeclimate.com/github/cnizzardini/cakephp-yummy/badges/gpa.svg)](https://codeclimate.com/github/cnizzardini/cakephp-yummy)
[![Issue Count](https://codeclimate.com/github/cnizzardini/cakephp-yummy/badges/issue_count.svg)](https://codeclimate.com/github/cnizzardini/cakephp-yummy)

A delightfully tasty plugin for your CakePHP 3 project including a group-based 
Access Control List (ACL), a search component with an intuitive user-interface, 
and boostrap 3 Bake Themes. Each of these components may be used separately.

## Demo & Docs

[Live Demo available](https://cnizz.com/yummy-demo) and fully documented in the [git wiki](https://github.com/cnizzardini/cakephp-yummy/wiki).

## Features

### [YummySearch](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-Search)

A search component designed to work with CakePHPs Paginator Component and Cake\ORM\Query. It supports searching the 
base model and other associations such as HasOne, BelongsTo, and HasMany through an intuitive UI in just three easy steps.

![yummy search screenshot](yummy-search.png)

```php
// Step 1. Basic Controller Usage:
$query = $this->ModelName->find();

$this->loadComponent('Yummy.YummySearch',[
    'query' => $query,
    'allow' => [
        'Table.column' => ['name' => 'Column Name'],
    ]
]);

$results = $this->paginate($this->YummySearch->search($query));

// Step 2. Basic View Usage:
$this->helpers()->load('Yummy.YummySearch');
echo $this->YummySearch->basicForm();

// Step 3. Include agnostic JavaScript
<script src="/yummy/js/yummy-search.js"></script>
 
```

Check out the [documentation](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-Search) for advanced 
usage and customization. 

### [YummyAcl](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-ACL)

A component that works with Auth to add group-based access controls to your admin portal.

![yummy acl screenshot](yummy-acl.png)

```php
// Basic Usage
$this->loadComponent('Yummy.YummyAcl',[
    'group' => $this->Auth->user('group'),
]);

// Define ACLs (you can also define ACLs in a single config file)
$this->YummyAcl->actions([
    'login' => '*', // allow all 
    'view' => ['Admin','Manager'], // allow Admin + Manager
    'edit' => ['Admin'], // allow Admin
]);
```

Check out the [documentation](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-ACL) for advanced 
usage and customization. 


### YummyBake

A series of bootstrap admin themes for your admin portal [(demo)](https://cnizz.com/yummy-demo/teams)

```
bin/cake bake template <ControllerName> -t Yummy
```

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require cnizzardini/cakephp-yummy
```

Load the plugin in: `config/bootstrap.php`

```
Plugin::load('Yummy', ['bootstrap' => false, 'routes' => true]);
```

## Documentation

Checkout the [Wiki](https://github.com/cnizzardini/cakephp-yummy/wiki/).

You can also view the source code for demo project on github:

[https://github.com/cnizzardini/cakephp-yummy-demo](https://github.com/cnizzardini/cakephp-yummy-demo)

## Test Cases
```
vendor/bin/phpunit 
```
