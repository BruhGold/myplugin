<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/requests_to_dmoj.php');
require_login();

$userid = $USER->id;

$request = new GetUserDMOJId([$userid]);
$response = $request->run();
$body = json_decode($response['body'], true);
$record = $body[$userid] ?? null;

if (!empty($record['user_id'])) {
    $insert = new stdClass();
    $insert->moodle_user_id = $userid;
    $insert->dmoj_user_id = $record['user_id'];
    $DB->insert_record('myplugin_dmoj_users', $insert);

    redirect(new moodle_url('/user/profile.php', ['id' => $userid]), get_string('dmoj_link_success', 'local_myplugin'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    redirect(new moodle_url('/user/profile.php', ['id' => $userid]), get_string('dmoj_link_failed', 'local_myplugin'), null, \core\output\notification::NOTIFY_ERROR);
}

