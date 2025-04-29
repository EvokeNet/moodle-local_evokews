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

class users extends external_api {
    public static function create_parameters() {
        return new external_function_parameters([
            'username' => new external_value(PARAM_TEXT, 'O username do usuário'),
            'firstname' => new external_value(PARAM_TEXT, 'O primeiro nome do usuário'),
            'lastname' => new external_value(PARAM_TEXT, 'O último nome do usuário'),
            'email' => new external_value(PARAM_TEXT, 'O email do usuário'),
            'courseid' => new external_value(PARAM_INT, 'The Moodle course ID'),
            'groupid' => new external_value(PARAM_INT, 'The Moodle group ID'),
        ]);
    }


    public static function create($username, $firstname, $lastname, $email, $courseid, $groupid) {
        global $DB, $CFG;

        try {
            $params = self::validate_parameters(self::create_parameters(), [
                'username' => $username,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'courseid' => $courseid,
                'groupid' => $groupid,
            ]);

            if ($DB->record_exists('user', ['username' => $params['username']])) {
                throw new \invalid_parameter_exception('Username already exists: '.$params['username']);
            }

            if ($DB->record_exists('user', ['email' => $params['email']])) {
                throw new \invalid_parameter_exception('Email in use by another user: '.$params['email']);
            }

            if (empty($CFG->allowaccountssameemail)) {
                // Make a case-insensitive query for the given email address.
                $select = $DB->sql_equal('email', ':email', false) . ' AND mnethostid = :mnethostid';

                // If there are other user(s) that already have the same email, throw an error.
                if ($DB->record_exists_select('user', $select, ['email' => $params['email'], 'mnethostid' => $CFG->mnet_localhost_id])) {
                    throw new \invalid_parameter_exception('Email address already exists: '.$params['email']);
                }
            }

            $user = new \stdClass();
            $user->username = $params['username'];
            $user->firstname = $params['firstname'];
            $user->lastname = $params['lastname'];
            $user->email = $params['email'];
            $user->confirmed = true;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->auth = 'oauth2';

            require_once("{$CFG->dirroot}/user/lib.php");

            $transaction = $DB->start_delegated_transaction();

            $user->id = user_create_user($user, false);

            // Matricula aluno no curso.
            $courseenrol = self::get_manual_enrol_method_by_course($courseid);
            if (!$courseenrol) {
                throw new \coding_exception('Course enrolment method not found');
            }

            require_once($CFG->libdir . "/enrollib.php");
            if (!$enrolmanual = enrol_get_plugin('manual')) {
                throw new \coding_exception('Can not instantiate enrol_manual');
            }

            $enrolmanual->enrol_user($courseenrol, $user->id, 5, time());

            // Adiciona aluno no grupo.
            require_once("{$CFG->dirroot}/group/lib.php");
            groups_add_member($groupid, $user->id);

            \core\event\user_created::create_from_userid($user->id)->trigger();

            $transaction->allow_commit();

            return [
                'data' => $user->id,
                'status' => 'ok'
            ];
        } catch (\Exception $exception) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                throw $exception;
            }

            return [
                'data' => $exception->getMessage(),
                'status' => 'error'
            ];
        }

    }

    public static function create_returns()
    {
        return new external_single_structure([
            'data' => new external_value(PARAM_TEXT, 'Id do usuário criado'),
            'status' => new external_value(PARAM_TEXT, 'Status da operação')
        ]);
    }

    /**
     * @param $courseid
     *
     * @return \stdClass|bool
     *
     * @throws \dml_exception
     */
    public static function get_manual_enrol_method_by_course($courseid) {
        global $DB, $CFG;

        try {
            return $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', MUST_EXIST);
        } catch (\Exception $e) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                throw $e;
            }

            return false;
        }
    }
}
