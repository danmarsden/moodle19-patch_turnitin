<?php
require_once("../config.php");
//file used to reset link between Moodle and Turnitin assignment
$id = required_param('id', PARAM_INT);

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:config', $context);

$cm = get_record('course_modules', 'id', $id);
if (empty($cm)) {
    error("invalid cm");
}

if (delete_records('plagiarism_config', 'cm', $id)) {
    notify("assignment reset");
}