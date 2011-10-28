<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'portfolio_boxnet', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   plagiarism_turnitin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adminlogin'] = 'Login to Turnitin as Admin';
$string['comparestudents'] = 'Compare submitted files with other students files';
$string['comparestudents_help'] = 'This option allows submissions to be compared with other students files';
$string['comparejournals'] = 'Compare submitted files with Journals, periodicals, publications';
$string['comparejournals_help'] = 'This option allows submissions to be compared with Journals, periodicals, and publications that Turnitin currently indexes';
$string['compareinternet'] = 'Compare submitted files with Internet';
$string['compareinternet_help'] = 'This option allows submissions to be compared with Internet content that  Turnitin currently indexes';
$string['compareinstitution'] = 'Compare submitted files with papers submitted within this institution';
$string['compareinstitution_help'] = 'This option is only available if you have setup/purchased a custom node. It should be set to "No" if unsure.';
$string['configdefault'] = 'This is a default setting for the assignment creation page. Only users with the capability moodle/plagiarism_turnitin:enableturnitin can change this setting for an individual assignment';
$string['usetii'] = 'Enable Turnitin submission.';
$string['defaultupdated'] = 'Turnitin defaults updated';
$string['defaultsdesc'] = 'The following settings are the defaults set when enabling Turnitin within an Activity Module';
$string['draftsubmit'] = 'When should the file be submitted to Turnitin';
$string['excludebiblio'] = 'Exclude bibliography';
$string['excludebiblio_help'] = 'Bibliographic materials can also be included and excluded when viewing the Originality Report. This setting cannot be modified after the first file has been submitted..';
$string['excludematches'] = 'Exclude small matches';
$string['excludematches_help'] = 'You can exclude small matches by percentage or by word count - select the type you wish to use and enter the percentage or word count in the box below.';
$string['excludequoted'] = 'Exclude quoted material';
$string['excludequoted_help'] = 'Quoted materials can also be included and excluded when viewing the Originality Report. This setting cannot be modified after the first file has been submitted..';$string['percentage'] = 'Percentage';
$string['reportgen'] = 'When to generate the Originality Reports';
$string['reportgen_help'] = 'This option allows you to selct when the Originality Report should be generated';
$string['reportgenduedate'] = 'On the due date';
$string['reportgenimmediate'] = 'Immediately (first report is final)';
$string['reportgenimmediateoverwrite'] = 'Immediately (can overwrite reports)';
$string['savedconfigfailure'] = 'Unable to connect/authenticate to Turnitin - you may have an incorrect Secret Key/Account ID combination or this server cannot connect to the API';
$string['savedconfigsuccess'] = 'Turnitin Settings Saved, and Teacher account created';
$string['showwhenclosed']='When Activity closed';
$string['showstudentsscore']='Show Plagiarism score to student';
$string['showstudentsscore_help']='The Plagiarism score is the percentage of the submission that has been matched with other content - a high score is usually bad.';
$string['showstudentsreport']='Show Plagiarism report to student';
$string['showstudentsreport_help']='The Plagiarism report gives a breakdown on what parts of the submission were plagiarised and the location that Turnitin first saw this content';
$string['similarity'] = 'Similarity';
$string['studentdisclosuredefault']  ='All files uploaded will be submitted to the plagiarism detection service Turnitin.com';
$string['studentdisclosure'] = 'Student Disclosure';
$string['configstudentdisclosure'] = 'This text will be displayed to all students on the file upload page.';
$string['submitondraft'] = 'Submit file when first uploaded';
$string['submitonfinal'] = 'Submit file when student sends for marking';
$string['teacherlogin'] = 'Login to Turnitin as Teacher';
$string['turnitindefaults'] = 'Turnitin Defaults';
$string['turnitinerrors'] = 'Turnitin Errors';
$string['tii'] = 'Turnitin';
$string['tiiheading'] = 'Turnitin settings';
$string['tiiaccountid'] ='Turnitin Account ID';
$string['tiiaccountid_help'] ='This is your Account ID as provided from Turnitin.com';
$string['tiiapi'] = 'Turnitin API';
$string['tiiapi_help'] = 'This is the address of the Turnitin API - usually https://api.turnitin.com/api.asp';
$string['tiiconfigerror'] = 'A site configuration error occured when attempting to send this file to Turnitin';
$string['tiiemailprefix'] ='Student Email prefix';
$string['configtiiemailprefix'] ='You must set this if you do not want students to be able to log into the turnitin.com site and view full reports.';
$string['tiienablegrademark'] = 'Enable Grademark';
$string['tiienablegrademark_help'] = 'Grademark is an optional feature within Turnitin - you must have included this in your Turnitin subscription to use it.';
$string['tiierror'] = 'TII Error:';
$string['tiierror1007'] = 'Turnitin could not process this file as it is too large';
$string['tiierror1008'] = 'An error occured when attempting to send this file to Turnitin';
$string['tiierror1009'] = 'Turnitin could not process this file - it is an unsupported file type. Valid file types are MS Word, Acrobat PDF, Postscript, Text, HTML, WordPerfect and Rich Text Format';
$string['tiierror1010'] = 'Turnitin could not process this file - it must contain more than 100 non-whitespace characters';
$string['tiierror1011'] = 'Turnitin could not process this file - it is incorrectly formatted and there seems to be spaces between each letter.';
$string['tiierror1012'] = 'Turnitin could not process this file - it\'s length exceeds the Turnitin limits';
$string['tiierror1013'] = 'Turnitin could not process this file - it must contain more than 20 words';
$string['tiierror1020'] = 'Turnitin could not process this file - it contains characters from a character set that is not supported';
$string['tiierror1023'] = 'Turnitin could not process this pdf - make sure it is not password protected and contains selectable text rather than scanned images';
$string['tiierror1024'] = 'Turnitin could not process this file - it does not meet the Turnitin criteria for a legitamate paper';
$string['tiierrorpaperfail'] = 'Turnitin could not process this file.';
$string['tiierrorpending'] ='File pending submission to Turnitin';
$string['tiiconfigerror'] = 'A site configuration error occured when attempting to send this file to Turnitin';
$string['submitondraft'] = 'Submit file to TII when first uploaded';
$string['submitonfinal'] = 'Submit file to TII when student sends for marking';
$string['tiidraftsubmit'] = 'When should the file be submitted to Turnitin';
$string['tiiexplain'] = 'Turnitin is a commercial product and you must have a paid subscription to use this service; for more information see <a href=\"http://docs.moodle.org/en/Turnitin_administration\">http://docs.moodle.org/en/Turnitin_administration</a>';
$string['tiiexplainerrors'] = 'This page lists any files submitted to Turnitin that are currently in an error state. A list of turnitin Error codes and their description is available here:<a href=\"http://docs.moodle.org/en/Turnitin_errors\">docs.moodle.org/en/Turnitin_errors</a><br/>When files are reset, the cron will attempt to submit the file to turnitin again.<br/>NOTE: files with errors in the range 1000-1999 will not benefit from being reset, and will probably always fail.';
$string['tiisecretkey'] ='Turnitin Secret Key';
$string['tiisecretkey_help'] ='Log into Turnitin.com as your site administrator to obtain this.';
$string['tiisenduseremail'] = 'Send User E-mail';
$string['tiisenduseremail_help'] = 'Send e-mail to every student created in the TII system with a link to allow login to www.turnitin.com with a temporary password';
$string['turnitin'] = 'Turnitin';
$string['turnitin_attemptcodes'] = 'Error codes to auto-resubmit';
$string['turnitin_attempts'] = 'Number of retries';
$string['turnitin_institutionnode'] = 'Enable Institution Node';
$string['turnitin_institutionnode_help'] = 'If you have setup/purchased an institution node with your account enable this to allow the node to be selected when creating assignments. NOTE: if you do not have an institution node, enabling this setting will cause your paper submission to fail.';
$string['useturnitin'] ='Enable Turnitin';
$string['wordcount'] = 'Word count';
$string['resetall'] = 'Reset All';

$string['configtiiapi'] = 'This is the address of the Turnitin API - usually https://api.turnitin.com/api.asp';
$string['configusetii'] = 'NOTE: you must enable TII in each respective module as well.';
$string['configusetiimodule'] = 'Enable Turnitin submission.';
$string['configtiiaccountid'] ='This is your Account ID as provided from Turnitin.com';
$string['configtiisecretkey'] ='This is normally e-mailed to you on request from your Turnitin.com Account representative';
$string['configtiiuserid'] ='Username of the person you want all new classes/assignments in Moodle to be assigned to in the turnitin System - this user should not already exist in the Turnitin system, and should not be changed once the turnitin system is being used.';
$string['configtiiemail'] ='Email address of the person you want all new classes/assignments in Moodle to be assigned to in the turnitin System ';
$string['configtiifirstname'] ='First name of the person you want all new classes/assignments in Moodle to be assigned to in the turnitin System';
$string['configtiilastname'] ='Surname of the person you want all new classes/assignments in Moodle to be assigned to in the turnitin System';
$string['config_tiisenduseremail'] = 'Send e-mail to every student created in the TII system with a link to allow login to www.turnitin.com with a temporary password';
$string['defaultupdated'] ='default settings updated';
$string['updatesimilarityscores'] = 'Update similarity scores';
$string['similarityscoresupdated'] = 'Similarity scores updated';
