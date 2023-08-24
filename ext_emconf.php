<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TwentyThree',
    'description' => 'Provides an online media provider for the TwentyThree Video Marketing Platform',
    'category' => 'misc',
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '0.1.3',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'autoload' => [
        'psr-4' => [
            'B13\\TwentyThree\\' => 'Classes/',
        ]
    ]
];
