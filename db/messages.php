<?php

defined('MOODLE_INTERNAL') || die();

$messageproviders = array (
    // Notify students that a quiz has been created
    'created' => array (
        'capability'  => 'mod/distributed_quiz:emailnotifycreated'
    )
);