<?php
/**
 * This is a sample file for setting YummyAcl configuration in a config file instead of at the controller level
 */
return [
    'YummyAcl' =>[
        'Dashboard' => [
            'allow' => '*',
        ],
        'User' => [
            'actions' => [
                'index' => ['admin','manager'],
                'edit' => ['admin','manager'],
                'add' => ['admin'],
                'login' => '*',
                'logout' => '*',
            ]
        ]
    ]
];