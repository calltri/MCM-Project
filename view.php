<?php

require('../../config.php');
require_once('lib.php');
 
$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'distributed_quiz');
$distributed_quiz = $DB->get_record('distributed_quiz', array('id'=> $cm->instance), '*', MUST_EXIST);

//renders its page layout and activities