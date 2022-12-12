<?php

/**
 * Evoke Web Services plugin functions and services definitions
 *
 * @package     local_evokews
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Evoke Web Services';

$functions = [
    'local_evokews_get_user_campaigns' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'get_user_campaigns',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:get_user_campaigns', 'local_evokews'),
        'type' => 'read'
    ],
    'local_evokews_get_user_campaign_badges' => [
        'classname' => 'local_evokews\api\badges',
        'methodname' => 'get_user_campaign_badges',
        'classpath' => 'local/evokews/classes/api/badges.php',
        'description' => get_string('function:get_user_campaign_badges', 'local_evokews'),
        'type' => 'read'
    ],
];

$services = [
    'Evoke Web Services' => [
        'functions' => [
            'local_evokews_get_user_campaigns',
            'local_evokews_get_user_campaign_badges'
        ],
        'restrictedusers' => 0,
        'enabled' => 1
    ]
];
