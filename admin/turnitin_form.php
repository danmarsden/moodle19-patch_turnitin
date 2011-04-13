<?php //$Id$

require_once($CFG->dirroot.'/lib/formslib.php');

class turnitin_form extends moodleform {

    //var $field;

/// Define the form
    function definition () {
        global $CFG;

        //$id   = required_param('id', PARAM_INT);
//
        $mform =& $this->_form;
        $choices = array('No','Yes');
        $mform->addElement('checkbox', 'turnitin_use', get_string('usetii', 'turnitin'));
        $mform->addElement('static','turnitin_use_description', '', get_string('configusetii', 'turnitin'));  
        
        $mform->addElement('text', 'turnitin_api', get_string('tiiapi', 'turnitin'));
        $mform->addElement('static','turnitin_api_description', '', get_string('configtiiapi', 'turnitin'));
        $mform->addRule('turnitin_api', null, 'required', null, 'client');
        $mform->setDefault('turnitin_api', 'https://api.turnitin.com/api.asp');
        
        $mform->addElement('text', 'turnitin_accountid', get_string('tiiaccountid', 'turnitin'));
        $mform->addElement('static','turnitin_accountid_description', '', get_string('configtiiaccountid', 'turnitin'));
        //$mform->addRule('turnitin_accountid', null, 'required', null, 'client');
        $mform->addRule('turnitin_accountid', null, 'numeric', null, 'client');
        
        $mform->addElement('passwordunmask', 'turnitin_secretkey', get_string('tiisecretkey', 'turnitin'));
        $mform->addElement('static','turnitin_secretkey_description', '', get_string('configtiisecretkey', 'turnitin'));
        $mform->addRule('turnitin_secretkey', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'turnitin_institutionnode', get_string('turnitin_institutionnode', 'turnitin'));
        $mform->addElement('static','turnitin_institutionnode_description', '', get_string('turnitin_institutionnode_help', 'turnitin'));

        $mform->addElement('checkbox', 'turnitin_senduseremail', get_string('tiisenduseremail', 'turnitin'));
        $mform->addElement('static','turnitin_senduseremail_description', '', get_string('config_tiisenduseremail', 'turnitin'));
         
        $mform->addElement('text', 'turnitin_emailprefix', get_string('tiiemailprefix', 'turnitin'));
        $mform->addElement('static','turnitin_emailprefix_description', '', get_string('configtiiemailprefix', 'turnitin'));
        $mform->disabledIf('turnitin_emailprefix', 'turnitin_senduseremail', 'checked');
         
        $mform->addElement('text', 'turnitin_userid', get_string('username'));
        $mform->addElement('static','turnitin_userid_description', '', get_string('configtiiuserid', 'turnitin'));
        $mform->addRule('turnitin_userid', null, 'required', null, 'client');

        $mform->addElement('text', 'turnitin_email', get_string('email'));
        $mform->addElement('static','turnitin_email_description', '', get_string('configtiiemail', 'turnitin'));
        $mform->addRule('turnitin_email', null, 'email', null, 'client');
        $mform->addRule('turnitin_email', null, 'required', null, 'client');

        $mform->addElement('text', 'turnitin_firstname', get_string('firstname'));
        $mform->addElement('static','turnitin_firstname_description', '', get_string('configtiifirstname', 'turnitin'));
        $mform->addRule('turnitin_firstname', null, 'required', null, 'client');
        
        $mform->addElement('text', 'turnitin_lastname', get_string('lastname'));
        $mform->addElement('static','turnitin_lastname_description', '', get_string('configtiilastname', 'turnitin'));
        $mform->addRule('turnitin_lastname', null, 'required', null, 'client'); 

        $mform->addElement('text', 'turnitin_attemptcodes', get_string('turnitin_attemptcodes', 'turnitin'));
        $mform->setDefault('turnitin_attemptcodes', '1009,1013,1023');

        $mform->addElement('text', 'turnitin_attempts', get_string('turnitin_attempts', 'turnitin'));
        $mform->addRule('turnitin_attempts', null, 'numeric', null, 'client');
        $mform->addRule('turnitin_attempts', null, 'maxlength', 1, 'client');
        $mform->setDefault('turnitin_attempts', '1');

        $mform->addElement('textarea', 'turnitin_student_disclosure', get_string('studentdisclosure','turnitin'),'wrap="virtual" rows="6" cols="50"');
        $mform->addElement('static','turnitin_student_disclosure_description', '', get_string('configstudentdisclosure','turnitin'));
        $mform->setDefault('turnitin_student_disclosure', get_string('studentdisclosuredefault','turnitin'));
         
        $this->add_action_buttons(true);
    }
}


class turnitin_defaults_form extends moodleform {

/// Define the form
    function definition () {
        $mform =& $this->_form;
        turnitin_get_form_elements($mform);
        $this->add_action_buttons(true);
    }
}

?>