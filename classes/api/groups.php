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
use core\context\course as context_course;
use core\context\coursecat as context_coursecat;
use local_evokews\util\BackupRestore;
use invalid_parameter_exception;

class groups extends external_api {
    public static function create_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course ID'),
            'name' => new external_value(PARAM_TEXT, 'The group name'),
        ]);
    }

    public static function create($courseid, $name) {
        global $DB, $CFG;

        self::validate_parameters(self::create_parameters(), ['courseid' => $courseid, 'name' => $name]);

        require_once("$CFG->dirroot/group/lib.php");

        if ($DB->get_record('groups', array('courseid' => $courseid, 'name' => $name))) {
            throw new invalid_parameter_exception('Group with the same name already exists in the course');
        }

        $context = context_course::instance($courseid, IGNORE_MISSING);

        self::validate_context($context);

        require_capability('moodle/course:managegroups', $context);

        $group = new \stdClass();
        $group->courseid = $courseid;
        $group->name = $name;

        $id = groups_create_group($group, false);

        return ['id' => $id];
    }

    public static function create_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The new course ID'),
        ]);
    }
}
