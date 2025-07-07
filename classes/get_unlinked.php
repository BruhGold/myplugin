<?php
function get_unlinked_users() {
    global $DB;

    // Fetch users who do not have a DMOJ account linked
    $sql = "SELECT u.id, u.username, u.email, u.firstname, u.lastname
            FROM {user} u
            LEFT JOIN {myplugin_dmoj_users} d ON u.id = d.moodle_user_id
            WHERE d.dmoj_user_id IS NULL";

    return $DB->get_records_sql($sql);
}