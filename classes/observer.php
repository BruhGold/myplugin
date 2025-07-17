<?php
require_once(__DIR__ . '/../link_dmoj.php');

class local_myplugin_observer {
    public static function link_dmoj(\core\event\base $event) {
        $userid = $event->userid;
        $newvalue = get_config('local_myplugin', 'dmoj_domain');

        debugging("DMOJ domain was updated to: {$newvalue} by user ID {$userid}");
        debugging("Linking DMOJ for user ID: {$event->objectid}");

        link_dmoj($event->objectid);
    }
}
