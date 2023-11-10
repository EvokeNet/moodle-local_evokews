<?php

namespace local_evokews\util;

use backup_controller;
use backup_setting;
use backup;

use restore_dbops;
use restore_controller;
use output_indented_logger;
use moodle_exception;

class BackupRestore {
    protected $options = [
        'activities' => 1,
        'blocks' => 1,
        'filters' => 1,
        'users' => 0,
        'role_assignments' => 0,
        'comments' => 0,
        'logs' => 0,
        'groups' => 0,
        'badges' => 1
    ];

    public function backup($courseid, $userid) {
        global $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

        $bc = new backup_controller(
            backup::TYPE_1COURSE, $courseid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userid);

        // Can set custom settings here.
        foreach ($this->options as $name => $value) {
            $setting = $bc->get_plan()->get_setting($name);
            $setting->set_status(backup_setting::NOT_LOCKED);
            $setting->set_value($value);
        }
        $backupid       = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $backupfile = $results['backup_destination'];
        $bc->destroy();

        return [
            'id' => $backupid,
            'basepath' => $backupbasepath,
            'file' => $backupfile
        ];
    }

    public function restore($course, $backup, $userid, $removebackupfile=true, $log=false): int {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $backupfile = $backup['file'];
        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backup['basepath'] . "/moodle_backup.xml")) {
            $backupfile->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backup['basepath']);
        }

        $newcourseid = restore_dbops::create_new_course($course->fullname, $course->shortname, $course->category);

        $rc = new restore_controller(
            $backup['id'], $newcourseid, backup::INTERACTIVE_NO,
            backup::MODE_SAMESITE, $userid, backup::TARGET_NEW_COURSE
        );

        if ($log) {
            $rc->get_logger()->set_next(new output_indented_logger(backup::LOG_DEBUG, false, true));
        }

        foreach ($this->options as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            $setting->set_status(backup_setting::NOT_LOCKED);
            $setting->set_value($value);
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backup['basepath']);
                }
                $errorinfo = '';
                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= ' ' . $error;
                }
                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= ' ' . $warning;
                    }
                }
                $rc->destroy();
                throw new moodle_exception('errorrestoreprecheck', 'local_courseduplication', '', $errorinfo);
            }
        }

        // Executing restoration will copy content (sections, activities, ...)
        // But it will override the data defined in the duplication_form.
        // It also uses the default course format options.
        $rc->execute_plan();
        $rc->destroy();

        // So, update the record afterward with our custom values.
        $course->id = $newcourseid;
        $DB->update_record('course', $course);

        // Also update course format option (weeks -> automaticenddate).
        $cfo = $DB->get_record('course_format_options', array(
            'courseid' => $course->id,
            'name' => 'automaticenddate'
        ));
        if ($cfo) {
            $DB->update_record('course_format_options', (object)array(
                'id' => $cfo->id,
                'value' => $course->automaticenddate
            ));
        }

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backup['basepath']);
        }

        if ($removebackupfile) {
            $backupfile->delete();
        }

        return $newcourseid;
    }
}