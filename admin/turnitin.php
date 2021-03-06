<?php
 //allows the admin to configure turnitin stuff

    require_once(dirname(dirname(__FILE__)) . '/config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/turnitinlib.php');
    include_once($CFG->libdir.'/environmentlib.php'); //for normalize_version function

    require_login();
    admin_externalpage_setup('turnitin');

    $context = get_context_instance(CONTEXT_SYSTEM);

    require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");
    //check current PHP version - if it is earlier than PHP 5, throw an error as the integration won't work.
    if (normalize_version(phpversion()) < 5) {
        admin_externalpage_print_header();
        notify("The Turnitin Integration requires PHP 5 or higher, you are running an earlier version of PHP and it will not work!");
        admin_externalpage_print_footer();
        die;
    }
    require_once('turnitin_form.php');
    $tiiform = new turnitin_form(null);

    if ($tiiform->is_cancelled()) {
        redirect('');

    }

        if (($data = $tiiform->get_data()) && confirm_sesskey()) {
            if (!isset($data->turnitin_use)) {
                $data->turnitin_use = 0;
            }
            foreach ($data as $field=>$value) {
                if (strpos($field, 'turnitin')===0) {
                    if ($tiiconfigfield = get_record('config_plugins', 'name', $field, 'plugin', 'tii')) {
                        $tiiconfigfield->value = $value;
                        if (!update_record('config_plugins', $tiiconfigfield)) {
                            error("errorupdating");
                        }
                    } else {
                        $tiiconfigfield = new stdClass();
                        $tiiconfigfield->value = $value;
                        $tiiconfigfield->plugin = 'tii';
                        $tiiconfigfield->name = $field;
                        if (!insert_record('config_plugins', $tiiconfigfield)) {
                            error("errorinserting");
                        }
                    }
                }
            }
            //now call TII settings to set up teacher account as set on this page.
                if ($tiisettings = tii_get_settings()) { //get tii settings.
                    $tii = array();
                    //set globals.
                    $tii['username'] = $tiisettings['turnitin_userid'];
                    $tii['uem']      = $tiisettings['turnitin_email'];
                    $tii['ufn']      = $tiisettings['turnitin_firstname'];
                    $tii['uln']      = $tiisettings['turnitin_lastname'];
                    $tii['uid']      = $tiisettings['turnitin_userid'];
                    $tii['utp']      = '2'; //2 = this user is an instructor
                    $tii['cid']      = $tiisettings['turnitin_courseprefix']; //course ID
                    $tii['ctl']      = $tiisettings['turnitin_courseprefix']; //Course title.  -this uses Course->id and shortname to ensure uniqueness.
                    //$tii['diagnostic'] = '1'; //debug only

                    $tii['fcmd'] = '2'; //when set to 2 the TII API should return XML
                    $tii['fid'] = '1'; //set command. - create user and login to Turnitin (fid=1)
                    if (tii_post_to_api($tii, 11, 'GET','',false)) {
                        notify(get_string('savedconfigsuccess', 'turnitin'), 'notifysuccess');
                    } else {
                        notify(get_string('savedconfigfailure', 'turnitin'));
                    }
                }
        }
     $tiisettings = tii_get_settings();
     $tiiform->set_data($tiisettings);

    admin_externalpage_print_header();

    if ($tiisettings) {
        //Now show link to ADMIN tii interface - NOTE: this logs in the ADMIN user, should be hidden from normal teachers.
        $tii['uid']      = $tiisettings['turnitin_userid'];
        $tii['username'] = $tiisettings['turnitin_userid'];
        $tii['uem']      = $tiisettings['turnitin_email'];
        $tii['ufn']      = $tiisettings['turnitin_firstname'];
        $tii['uln']      = $tiisettings['turnitin_lastname'];
        $tii['utp']      = '2'; //2 = this user is an instructor
        $tii['utp'] = '3';
        $tii['fcmd'] = '1'; //when set to 2 this returns XML
        $tii['fid'] = '12'; //set commands - Administrator login/statistics.
        echo '<div align="right">';
        echo '<a href="'.tii_get_url($tii).'" target="_blank">'.get_string("adminlogin","turnitin").'</a><br/>';
        $tii['utp'] = '2';
        $tii['fid'] = '1'; //set commands - Administrator login/statistics.
        echo '<a href="'.tii_get_url($tii).'" target="_blank">'.get_string('teacherlogin', 'turnitin').'</a>';
        echo '</div>';
    }

    print_heading(get_string('tiiheading', 'turnitin'));
    $currenttab='turnitinsettings';
    require_once('turnitin_tabs.php');
    print_box(get_string('tiiexplain', 'turnitin'));

    print_simple_box_start('center','90%','','20');

    $tiiform->display();

    print_simple_box_end();

    admin_externalpage_print_footer();
?>