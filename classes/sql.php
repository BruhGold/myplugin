<?php
function get_all_users() {
    global $DB;

    $sql = "SELECT u.id, u.username, u.email, u.firstname, u.lastname
            FROM {user} u
            WHERE u.deleted = 0 AND u.username <> 'guest'";

    return $DB->get_records_sql($sql);
}