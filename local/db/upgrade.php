<?php
function xmldb_local_upgrade($oldversion) {

    global $CFG, $THEME, $db;

    $result = true;

    if ($result && $oldversion < 2008052500) { /// TII API UPGRADE
        //new TII table
        $table = new XMLDBTable('tii_files');
        //fields
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('module', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('instance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('filename', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('tii', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);
        $table->addFieldInfo('tiicode', XMLDB_TYPE_CHAR, '10', null, null, null, null, null, null);
        $table->addFieldInfo('tiiscore', XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        //keys
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        if ($result and !table_exists($table)) {
            /// Launch create table for assignment_files
            $result = $result && create_table($table);
        }

    }

    if ($result && $oldversion < 2008052501) {
        //plagiarism_config table for configuration of modules
        $table = new XMLDBTable('plagiarism_config');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('cm', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null, null);
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null, null);
        /// Adding keys to table
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        /// Launch create table
        $result = $result && create_table($table);
        
        //now get all existing settings from old assignment table
        $assignments = get_records('assignment', 'use_tii_submission', 1);
        foreach ($assignments as $assignment) {
            $cm = get_coursemodule_from_instance('assignment', $assignment->id);
            $pconfig = new stdclass();
            $pconfig->cm = $cm->id;
            $pconfig->name = 'use_turnitin';
            $pconfig->value = $assignment->use_tii_submission;
            insert_record('plagiarism_config', $pconfig);
            $pconfig->name = 'plagiarism_show_student_score';
            $pconfig->value = $assignment->tii_show_student_score;
            insert_record('plagiarism_config', $pconfig);
            $pconfig->name = 'plagiarism_show_student_report';
            $pconfig->value = $assignment->tii_show_student_report;
            insert_record('plagiarism_config', $pconfig);
        }
        $table = new XMLDBTable('assignment');
        $field = new XMLDBField('use_tii_submission');
        $result = $result && drop_field($table, $field);
        $field = new XMLDBField('tii_show_student_score');
        $result = $result && drop_field($table, $field);
        $field = new XMLDBField('tii_show_student_report');
        $result = $result && drop_field($table, $field);
    }

    if ($result && $oldversion < 2011041200) {
        $table = new XMLDBTable('tii_files');
        $field = new XMLDBField('attempt');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'tiiscore');
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        }
    }

    if ($oldversion < 2011083100) {
        // Define field apimd5 to be added to turnitin_files
        $table = new XMLDBTable('tii_files');
        $field = new XMLDBField('apimd5');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'attempt');

        // Conditionally launch add field apimd5
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        }
    }

    return $result;

}

?>