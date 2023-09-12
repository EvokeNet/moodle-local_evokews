<?php

/**
 * Campaign class
 *
 * @package     local_evokews
 * @copyright   2022 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_evokews\api;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_evokegame\util\badge;

class badges extends external_api {
    /**
     * @return external_function_parameters
     */
    public static function get_user_campaign_badges_parameters() {
        return new external_function_parameters([
            'campaignid' => new external_value(PARAM_INT, 'The campaign/course ID'),
            'highlight' => new external_value(PARAM_INT, 'The campaign/course ID', VALUE_DEFAULT, true),
        ]);
    }

    /**
     * @param $userid
     */
    public static function get_user_campaign_badges($campaignid, $highlight) {
        global $USER;

        self::validate_parameters(self::get_user_campaign_badges_parameters(), ['campaignid' => $campaignid, 'highlight' => $highlight]);

        if (!class_exists('\local_evokegame\util\badge')) {
            return [];
        }

        $context = \core\context\course::instance($campaignid);

        $badgeutil = new badge();
        $badges = $badgeutil->get_course_badges_with_user_award($USER->id, $campaignid, $context->id, 1, $highlight);

        if (!$badges) {
            return [];
        }

        $data = [];
        foreach ($badges as $badge) {
            $data[] = [
                'id' => $badge['badgeid'],
                'name' => $badge['name'],
                'description' => $badge['description'],
                'image' => $badge['badgeimage']->out(),
                'awarded' => $badge['awarded']
            ];
        }

        return $data;
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_user_campaign_badges_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The Moodle badge ID'),
                'name' => new external_value(PARAM_TEXT, 'The badge name'),
                'description' => new external_value(PARAM_TEXT, 'The badge description'),
                'image' => new external_value(PARAM_TEXT, 'The badge image'),
                'awarded' => new external_value(PARAM_BOOL, 'True if user already awarded the badge'),
            ])
        );
    }
}
