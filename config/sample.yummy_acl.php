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
            'allow' => ['Root'],
            'actions' => [
                'index' => ['Admin','Manager'],
                'edit' => ['Admin','Manager'],
                'add' => ['Admin','Manager'],
                'login' => '*',
                'logout' => '*',
            ]
        ]
    ]
];