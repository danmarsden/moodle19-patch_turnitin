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
    return($result);
}
?>