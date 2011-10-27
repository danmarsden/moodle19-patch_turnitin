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

    //change table field names to match with 2.0 plugin - keep old table name on purpose.
    if ($result && $oldversion < 2011102701) {
       $table = new XMLDBTable('tii_files');
       //add new cm field
       $field = new XMLDBField('cm');
       $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'id');
       if (!field_exists($table, $field)) {
           $result = $result && add_field($table, $field);
       }
       if (field_exists($table, $field)) { //make sure cm field exists before doing the following.
           $field = new XMLDBField('course');
           if ($result && field_exists($table, $field)) {
               //now convert old fields and remove them
               //need to update all tii_files records to use CM instead of course, module, instance.
              $rs = get_recordset('tii_files');
               while ($tf = rs_fetch_next_record($rs)) {
                   //get cm for this file.
                   $cmid = get_field('course_modules', 'id', 'course', $tf->course, 'module', $tf->module, 'instance', $tf->instance);
                   if (empty($cmid)) {
                       //this cm doesn't exist - sanity check on assignment table.
                       if (!record_exists('assignment', 'id', $tf->instance)) {
                           //this file record is for an assignment that doesn't exist - delete it.
                           delete_records('tii_files', 'id', $tf->id);
                       } else {
                           print_object($tf);
                           error('an error occurred with the update script - a file and assignment were found with no cm.');
                       }
                   } else {
                       $tf->cm = $cmid;
                       update_record('tii_files', $tf);
                   }
               }
               rs_close($rs);
               //now delete old fields
               $field = new XMLDBField('course');
               if (field_exists($table, $field)) {
                  $result = $result && drop_field($table, $field);
               }
               $field = new XMLDBField('module');
               if (field_exists($table, $field)) {
                   $result = $result && drop_field($table, $field);
               }
               $field = new XMLDBField('instance');
               if (field_exists($table, $field)) {
                  $result = $result && drop_field($table, $field);
               }
           }
       }
    }
    if ($result && $oldversion < 2011102702) {
        if (!record_exists('user_info_field', 'shortname','turnitinteachercoursecache')) {
            //first insert category
            $newcat = new stdClass();
            $newcat->name = 'plagiarism_turnitin';
            $newcat->sortorder = 999;
            $catid = insert_record('user_info_category', $newcat);
            //now insert field
            $newfield = new stdClass();
            $newfield->shortname = 'turnitinteachercoursecache';
            $newfield->name = get_string('userprofileteachercache','turnitin');
            $newfield->description = get_string('userprofileteachercache_desc','turnitin');
            $newfield->datatype = 'text';
            $newfield->descriptionformat = 1;
            $newfield->categoryid = $catid;
            $newfield->sortorder = 1;
            $newfield->required = 0;
            $newfield->locked = 1;
            $newfield->visible = 0;
            $newfield->forceunique = 0;
            $newfield->signup = 0;
            $newfield->param1 = 30;
            $newfield->param2 = 5000;

            insert_record('user_info_field', $newfield);
        }

    }

    return $result;

}

?>