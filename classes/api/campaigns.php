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

class campaigns extends external_api {
    public static function get_user_campaigns_parameters() {
        return new external_function_parameters([]);
    }

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

    public static function get_user_campaigns_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The campaign/course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'The campaign/course shortname'),
                'fullname' => new external_value(PARAM_TEXT, 'The campaign/course fullname')
            ])
        );
    }

    public static function get_all_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Returns all courses/campaigns
     *
     * @param $userid
     */
    public static function get_all() {
        global $DB;

        return $DB->get_records_sql('SELECT id, shortname, fullname FROM {course} WHERE id > 1 ORDER BY fullname ASC');
    }

    public static function get_all_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The campaign/course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'The campaign/course shortname'),
                'fullname' => new external_value(PARAM_TEXT, 'The campaign/course fullname')
            ])
        );
    }

    public static function get_campaign_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The campaign/course ID'),
        ]);
    }

    /**
     * Returns all courses/campaigns
     *
     * @param $userid
     */
    public static function get_campaign($id) {
        global $DB;

        self::validate_parameters(self::get_campaign_parameters(), ['id' => $id]);

        $course = $DB->get_record('course', ['id' => $id], 'id, shortname, fullname', MUST_EXIST);

        return $course;
    }

    public static function get_campaign_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The campaign/course ID'),
            'shortname' => new external_value(PARAM_TEXT, 'The campaign/course shortname'),
            'fullname' => new external_value(PARAM_TEXT, 'The campaign/course fullname')
        ]);
    }

    public static function create_parameters() {
        return new external_function_parameters([
            'shortname' => new external_value(PARAM_TEXT, 'The campaign shortname'),
            'fullname' => new external_value(PARAM_TEXT, 'The campaign fullname', VALUE_DEFAULT, true),
        ]);
    }

    public static function create($shortname, $fullname) {
        global $CFG;

        require_once($CFG->dirroot . "/course/lib.php");

        self::validate_parameters(self::create_parameters(), ['shortname' => $shortname, 'fullname' => $fullname]);

        $context = context_coursecat::instance(1, IGNORE_MISSING);

        self::validate_context($context);

        require_capability('moodle/course:create', $context);

        $course = [
            'shortname' => trim($shortname),
            'fullname' => trim($fullname),
            'enablecompletion' => 1,
            'category' => 1,
            'numsections' => 1,
        ];

        if (class_exists(\format_dots\output\renderer::class)) {
            $course['format'] = 'dots';
        }

        $course = create_course((object) $course);

        return ['id' => $course->id];
    }

    public static function create_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The new course ID'),
        ]);
    }

    public static function duplicate_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The campaign ID to duplicate'),
            'shortname' => new external_value(PARAM_TEXT, 'The campaign shortname'),
            'fullname' => new external_value(PARAM_TEXT, 'The campaign fullname', VALUE_DEFAULT, true),
        ]);
    }

    public static function duplicate($courseid, $shortname, $fullname) {
        global $DB;

        self::validate_parameters(self::duplicate_parameters(), ['courseid' => $courseid, 'shortname' => $shortname, 'fullname' => $fullname]);

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        require_capability('moodle/course:update', context_course::instance($course->id));

        $backuprestore = new BackupRestore();

        $coursetobackup = $course->id; // Set this to one existing choice cmid in your dev site
        $userdoingthebackup   = 2; // Set this to the id of your admin account

        $backup = $backuprestore->backup($coursetobackup, $userdoingthebackup);

        $newcourse = new \stdClass();
        $newcourse->fullname = trim($fullname);
        $newcourse->shortname = trim($shortname);
        $newcourse->category = $course->category;
        $newcourse->timecreated = time();
        $newcourse->startdate = time();

        $courseid = $backuprestore->restore($newcourse, $backup, $userdoingthebackup);

        return ['id' => $courseid];
    }

    public static function duplicate_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The new course ID'),
        ]);
    }
}
