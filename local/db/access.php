<?php

$local_capabilities = array(

    'moodle/local:enableturnitin' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
         'legacy' => array(
         'editingteacher' => CAP_ALLOW,
         'admin' => CAP_ALLOW
        )
    ),

    'moodle/local:viewsimilarityscore' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
         'legacy' => array(
         'editingteacher' => CAP_ALLOW,
         'admin' => CAP_ALLOW
        )
    ),

    'moodle/local:viewfullreport' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
         'legacy' => array(
         'editingteacher' => CAP_ALLOW,
         'admin' => CAP_ALLOW
        )
    ) 
);

?>