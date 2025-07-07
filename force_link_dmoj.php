<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Readme file for local customisations
 *
 * @package    local_myplugin
 * @copyright  Dinh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/user_data_form.php');
require_once(__DIR__ . '/classes/requests_to_dmoj.php');
global $DB;
// Require login and admin privileges
require_login();
require_admin();

// Set up the page
$PAGE->set_url(new moodle_url('/local/myplugin/force_link_dmoj.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('dmoj_admin_force_link', 'local_myplugin'));
$PAGE->set_pagelayout('standard');

// Output starts here
echo $OUTPUT->header();

$context = (object)[
    'user' => $USER,
];

echo $OUTPUT->render_from_template('local_myplugin/index', $context);

// Form section
$mform = new UserForceLinkForm();
$mform->display();

if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.
    $selected_ids = $fromform->unlinked_users ?? [];

    $payload = [];
    list($in_sql, $params) = $DB->get_in_or_equal($selected_ids, SQL_PARAMS_NAMED);
    $users = $DB->get_records_select('user', "id $in_sql", $params);

    foreach ($users as $id => $user) {
        $payload[$id] = [
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
        ];
    }

    // send request to force create DMOJ account
    $request = new ForceCreateDMOJAccount($payload);
    $response = $request->run();

    // get the response and save to db
    $data = json_decode($response['body'], true);
    
    // Handle successful user links
    if (!empty($data['success'])) {
        foreach ($data['success'] as $moodleid => $userinfo) {
            $insertdata = new stdClass();
            $insertdata->moodle_user_id = (int)$moodleid;
            $insertdata->dmoj_user_id = $userinfo['dmoj_uid'];

            // Save to database
            $DB->insert_record('myplugin_dmoj_users', $insertdata);
            echo $OUTPUT->notification(
                get_string('dmoj_user_linked', 'local_myplugin', [
                    'moodleid' => $moodleid,
                    'dmojuid' => $userinfo['dmoj_uid'],
                ]),
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    }

    // Handle errors
    if (!empty($data['errors'])) {
        foreach ($data['errors'] as $moodleid => $errorinfo) {
            debugging("Failed to link user ID $moodleid: " . json_encode($errorinfo), DEBUG_DEVELOPER);
        }
    }
} else {
    // this came from moodledoc but i found that it is not necessary, since the form will definitely be displayed
    // even if the validated data is incorrect, the form is still there and you can just submit again
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.
    // Display the form.
    // $mform->display();
}
echo $OUTPUT->footer();
?>