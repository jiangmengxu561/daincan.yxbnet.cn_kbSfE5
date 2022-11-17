<?php

return [
    'autoload' => false,
    'hooks' => [
        'sms_send' => [
            'alisms',
        ],
        'sms_notice' => [
            'alisms',
        ],
        'sms_check' => [
            'alisms',
        ],
        'user_sidenav_after' => [
            'signin',
        ],
        'app_init' => [
            'unidrink',
            'uniprint',
            'voicenotice',
        ],
        'run' => [
            'voicenotice',
        ],
        'action_begin' => [
            'voicenotice',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
