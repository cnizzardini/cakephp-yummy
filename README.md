# Yummy plugin for CakePHP

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Code Climate](https://codeclimate.com/github/cnizzardini/cakephp-yummy/badges/gpa.svg)](https://codeclimate.com/github/cnizzardini/cakephp-yummy)
[![Issue Count](https://codeclimate.com/github/cnizzardini/cakephp-yummy/badges/issue_count.svg)](https://codeclimate.com/github/cnizzardini/cakephp-yummy)

Delightfully tasty tools for your cakephp project.  This is beta code, use at own risk.

## Demo & Docs

[Live Demo available](http://cake3.cnizz.com/yummy-demo) and full documented in the [git wiki](https://github.com/cnizzardini/cakephp-yummy/wiki).

## Features

### [YummySearch](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-Search)

A component that works with PaginatorComponent to add search functionality to tables [(demo)](http://cake3.cnizz.com/yummy-demo/teams)

![yummy search screenshot](https://www.cnizz.com/media/cakephp-yummy-search-screenshot.png)

### [YummyAcl](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-ACL)

A component that works with Auth to add basic access controls to your site 
[(documentation)](https://github.com/cnizzardini/cakephp-yummy/wiki/Yummy-ACL). 

```php
	$this->loadComponent('Yummy.YummyAcl',[
	    'group' => $this->Auth->user('group'),
	]);
	$this->YummyAcl->actions([
	    'login' => '*', // allow all 
	    'view' => ['Admin','Manager'], // allow Admin + Manager
	    'edit' => ['Admin'], // allow Admin
	]);
```

### YummyTemplate

A series of bootstrap admin themes for your admin portal [(demo)](http://cake3.cnizz.com/yummy-demo/teams)

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
