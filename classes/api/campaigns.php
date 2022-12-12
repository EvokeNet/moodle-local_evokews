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

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

class campaigns extends external_api {
    /**
     * @return external_function_parameters
     */
    public static function get_user_campaigns_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * @param $userid
     */
    public static function get_user_campaigns() {
        global $USER;

        $courses = enrol_get_users_courses($USER->id, true, '*', 'visible DESC, fullname ASC, sortorder ASC');

        if (!$courses) {
            return [];
        }

        $data = [];
        foreach ($courses as $course) {
            $data[] = [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
            ];
        }

        return $data;
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_user_campaigns_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The campaign/course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'The campaign/course shortname'),
                'fullname' => new external_value(PARAM_TEXT, 'The campaign/course fullname')
            ])
        );
    }
}
