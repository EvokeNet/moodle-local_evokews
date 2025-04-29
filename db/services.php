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
    'local_evokews_get_user_campaign_badges' => [
        'classname' => 'local_evokews\api\badges',
        'methodname' => 'get_user_campaign_badges',
        'classpath' => 'local/evokews/classes/api/badges.php',
        'description' => get_string('function:get_user_campaign_badges', 'local_evokews'),
        'type' => 'read'
    ],
    'local_evokews_get_user_campaigns' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'get_user_campaigns',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:get_user_campaigns', 'local_evokews'),
        'type' => 'read'
    ],
    'local_evokews_get_all_campaigns' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'get_all',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:get_all_campaigns', 'local_evokews'),
        'type' => 'read'
    ],
    'local_evokews_get_campaign' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'get_campaign',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:get_campaign', 'local_evokews'),
        'type' => 'read'
    ],
    'local_evokews_create_campaign' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'create',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:create_campaign', 'local_evokews'),
        'type' => 'write'
    ],
    'local_evokews_duplicate_campaign' => [
        'classname' => 'local_evokews\api\campaigns',
        'methodname' => 'duplicate',
        'classpath' => 'local/evokews/classes/api/campaigns.php',
        'description' => get_string('function:duplicate_campaign', 'local_evokews'),
        'type' => 'write'
    ],
    'local_evokews_create_group' => [
        'classname' => 'local_evokews\api\groups',
        'methodname' => 'create',
        'classpath' => 'local/evokews/classes/api/groups.php',
        'description' => get_string('function:create_group', 'local_evokews'),
        'type' => 'write'
    ],
    'local_evokews_create_user' => [
        'classname' => 'local_evokews\api\users',
        'methodname' => 'create',
        'classpath' => 'local/evokews/classes/api/users.php',
        'description' => get_string('function:create_user', 'local_evokews'),
        'type' => 'write'
    ],
];

$services = [
    'Evoke Web Services' => [
        'functions' => [
            'local_evokews_get_user_campaign_badges',
            'local_evokews_get_user_campaigns',
            'local_evokews_get_all_campaigns',
            'local_evokews_get_campaign',
            'local_evokews_create_campaign',
            'local_evokews_duplicate_campaign',
            'local_evokews_create_group',
            'local_evokews_create_user',
        ],
        'restrictedusers' => 1,
        'enabled' => 1
    ]
];
