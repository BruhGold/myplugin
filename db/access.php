<?php
// use Moodle's capability system to define permissions for the plugin. apply it at the start of the page
// require_capability('plugin_type/plugin_name:dosomething', context_system::instance());
// use the syntax below for button or link
// if (has_capability('plugin_type/plugin_name:dosomething', context_system::instance())) {
// Show button or link
// }
$capabilities = [
    'local/myplugin:viewstat' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ],
    ],
    'local/myplugin:addactivity' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
        ],
    ],
    'local/myplugin:participatequiz' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
        ],
    ],
];