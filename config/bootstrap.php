<?php
use Cake\Core\Configure;
if( file_exists('acl_config.php') ){
    Configure::load('YummyCake.acl_config');
}
