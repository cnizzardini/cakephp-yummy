# Yummy plugin for CakePHP

Delightfully tasty tools for your cakephp project. 

YummySearch - A component that works with PaginatorComponent to add search functionality to tables.

[YummyAcl](https://github.com/cnizzardini/cakephp-yummy/wiki/YummyACL) - A component that works with Auth to add basic access controls to your site. 

YummyTemplate - A series of bootstrap admin themes for your admin portal.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require cnizzardini/cakephp-yummy
```

## Configuration

Edit File: config/bootstrap.php

```
Plugin::load('Yummy', ['bootstrap' => false, 'routes' => true]);
```

## Documentation

Checkout the [Wiki](https://github.com/cnizzardini/cakephp-yummy/wiki/).