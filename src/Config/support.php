<?php
return [
    "support" => [
        'default' => 'redis',
        'redis' => [
            'client' => 'predis',
            'default' => [
                'host' => '127.0.0.1',
                'password' => null,
                'port' => '6379',
                'database' => 0,
            ],
            'options' => [
                'timeout' => '30'
            ]
        ],
        'queue' => [
            'redis' => [
                'driver' => 'redis',
                'queue' => 'default'
            ],
        ]
    ]
];