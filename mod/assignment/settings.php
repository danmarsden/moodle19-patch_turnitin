<?php  //$Id$

require_once($CFG->dirroot.'/mod/assignment/lib.php');

$settings->add(new admin_setting_configselect('assignment_maxbytes', get_string('maximumsize', 'assignment'),
                   get_string('configmaxbytes', 'assignment'), 1048576, get_max_upload_sizes($CFG->maxbytes)));

$options = array(ASSIGNMENT_COUNT_WORDS   => trim(get_string('numwords', '')),
                 ASSIGNMENT_COUNT_LETTERS => trim(get_string('numletters', '')));
$settings->add(new admin_setting_configselect('assignment_itemstocount', get_string('itemstocount', 'assignment'),
                   get_string('configitemstocount', 'assignment'), ASSIGNMENT_COUNT_WORDS, $options));

$settings->add(new admin_setting_configcheckbox('assignment_showrecentsubmissions', get_string('showrecentsubmissions', 'assignment'),
                   get_string('configshowrecentsubmissions', 'assignment'), 1));
                   
$tii = get_field('config_plugins', 'value', 'name', 'turnitin_use');
if (isset($tii) && $tii) {
    $settings->add(new admin_setting_configcheckbox('assignment_use_tii_submission', get_string('usetii', 'turnitin'), 
                   get_string('configusetiimodule', 'turnitin'), 0));
    $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
    $settings->add(new admin_setting_configselect('assignment_turnitin_default_use', get_string('defaultuse', 'turnitin'),
                   get_string('configdefault', 'turnitin'), 0, $ynoptions));

    $tiioptions = array();
    $tiioptions[0] = get_string("never");
    $tiioptions[1] = get_string("always");
    $tiioptions[2] = get_string("showwhenclosed", "turnitin");
         
    $settings->add(new admin_setting_configselect('assignment_turnitin_default_showscore', get_string('defaultshowscore', 'turnitin'),
                   get_string('configdefault', 'turnitin'), 1, $tiioptions));
                   
    $settings->add(new admin_setting_configselect('assignment_turnitin_default_showreport', get_string('defaultshowreport', 'turnitin'),
                   get_string('configdefault', 'turnitin'), 1, $tiioptions));

}
?>