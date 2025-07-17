<?php
$observers = [
    [
        'eventname' => '\local_myplugin\event\setting_updated',
        'callback'  => 'local_myplugin_observer::link_dmoj',
    ],
    [
        'eventname' => 'core\event\user_created',
        'callback'  => 'local_myplugin_observer::link_dmoj',
    ]
];
