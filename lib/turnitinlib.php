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
 * turnitinlib.php - Contains Turnitin related functions called by Modules.
 * 
 * @author    Dan Marsden <dan@danmarsden.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//Turnitin fcmd types - return values.
define('TURNITIN_LOGIN', 1);
define('TURNITIN_RETURN_XML', 2);
define('TURNITIN_UPDATE_RETURN_XML', 3);
// Turnitin user types
define('TURNITIN_STUDENT', 1);
define('TURNITIN_INSTRUCTOR', 2);
define('TURNITIN_ADMIN', 3);
//Turnitin API actions.
define('TURNITIN_CREATE_USER', 1);
define('TURNITIN_CREATE_CLASS', 2);
define('TURNITIN_JOIN_CLASS', 3);
define('TURNITIN_CREATE_ASSIGNMENT', 4);
define('TURNITIN_SUBMIT_PAPER', 5);
define('TURNITIN_RETURN_REPORT', 6);
define('TURNITIN_VIEW_SUBMISSION', 7);
define('TURNITIN_DELETE_SUBMISSION', 8); //unlikely to need this.
define('TURNITIN_LIST_SUBMISSIONS', 10);
define('TURNITIN_CHECK_SUBMISSION', 11);
define('TURNITIN_ADMIN_STATS', 12);
define('TURNITIN_RETURN_GRADEMARK', 13);
define('TURNITIN_REPORT_TIME', 14);
define('TURNITIN_SUBMISSION_SCORE', 15);
define('TURNITIN_START_SESSION', 17);
define('TURNITIN_END_SESSION', 18);
//Turnitin allowed file types
define('TURNITIN_TYPE_TEXT', 1);
define('TURNITIN_TYPE_FILE', 2);

//Turnitin Response codes - there are many more of these, just not used directly.
//
define('TURNITIN_RESP_USER_CREATED', 11); // User creation successful, do not send to login
define('TURNITIN_RESP_CLASS_CREATED_LOGIN', 20); // Class created successfully, send to login
define('TURNITIN_RESP_CLASS_CREATED', 21); // Class Created successfully, do not send to login
define('TURNITIN_RESP_CLASS_UPDATED', 22); // Class updated successfully
define('TURNITIN_RESP_USER_JOINED', 31); // successful, User joined to class, do not sent to login
define('TURNITIN_RESP_ASSIGN_CREATED', 41); // Assignment Created
define('TURNITIN_RESP_ASSIGN_MODIFIED', 42); // Assignment modified
define('TURNITIN_RESP_ASSIGN_DELETED', 43); // Assignment deleted
define('TURNITIN_RESP_PAPER_SENT', 51); // paper submitted
define('TURNITIN_RESP_SCORE_RECEIVED', 61); // Originality score retrieved.
define('TURNITIN_RESP_ASSIGN_EXISTS', 419); // Assignment already exists.

function tii_get_url($tii, $returnArray=false, $pid='') {
    global $CFG;
    $tiisettings = get_records_menu('config_plugins', 'plugin', 'tii', '', 'name,value');

    //make sure all $tii values are clean.
    foreach($tii as $key => $value) {
        if (!empty($value) AND $key <> 'tem' AND $key <> 'uem' AND $key <> 'dtstart' AND $key <> 'dtdue' AND $key <> 'submit_date') {
            $value = rawurldecode($value); //decode url first. (in case has already be encoded - don't want to end up with double % replacements)
            $value = rawurlencode($value);
            $value = str_replace('%20', '_', $value);
            $tii[$key] = $value;
        }
    }
    //TODO need to check lengths of certain vars. - some cannot be under 5 or over 50.
    if (isset($tiisettings['turnitin_senduseremail']) && $tiisettings['turnitin_senduseremail']) {
        $tii['dis'] ='0'; //sets e-mail notification for users in tii system to enabled.
        //munge e-mails if prefix is set.
        if (isset($tiisettings['turnitin_emailprefix'])) { //if email prefix is set
            if ($tii['uem'] <> $tiisettings['turnitin_email']) { //if email is not the global teacher.
                $tii['uem'] = $tiisettings['turnitin_emailprefix'] . $tii['uem']; //munge e-mail to prevent user access.
            }
        }
    } else {
        $tii['dis'] ='1'; //sets e-mail notification for users in tii system to disabled.
    }
    //set vars if not set.
    if (!isset($tii['encrypt'])) {
        $tii['encrypt'] = '0';
    }
    if (!isset($tii->diagnostic)) {
        $tii['diagnostic'] = '0';
    }
    if (!isset($tii['tem'])) {
        $tii['tem'] = $tiisettings['turnitin_email'];
    }
    if (!isset($tii['upw'])) {
        $tii['upw'] = '';
    }
    if (!isset($tii['cpw'])) {
        $tii['cpw'] = '';
    }
    if (!isset($tii['ced'])) {
        $tii['ced'] = '';
    }
    if (!isset($tii['dtdue'])) {
        $tii['dtdue'] = '';
    }
    if (!isset($tii['dtstart'])) {
        $tii['dtstart'] = '';
    }
    if (!isset($tii['newassign'])) {
        $tii['newassign'] = '';
    }
    if (!isset($tii['newupw'])) {
        $tii['newupw'] = '';
    }
    if (!isset($tii['oid'])) {
        $tii['oid'] = '';
    }
    if (!isset($tii['pfn'])) {
        $tii['pfn'] = '';
    }
    if (!isset($tii['pln'])) {
        $tii['pln'] = '';
    }
    if (!isset($tii['ptl'])) {
        $tii['ptl'] = '';
    }
    if (!isset($tii['ptype'])) {
        $tii['ptype'] = '';
    }
    if (!isset($tii['said'])) {
        $tii['said'] = '';
    }
    if (!isset($tii['assignid'])) {
        $tii['assignid'] = '';
    }
    if (!isset($tii['assign'])) {
        $tii['assign'] = '';
    }
    if (!isset($tii['cid'])) {
        $tii['cid'] = '';
    }
    if (!isset($tii['ctl'])) {
        $tii['ctl'] = '';
    }

    $tii['gmtime']  = tii_get_gmtime();
    $tii['aid']     = $tiisettings['turnitin_accountid'];
    $tii['version'] = 'Moodle_19'; //maybe change this to get $CFG->version - only really used by TII for stats reasons. - we don't need this.

    //prepare $tii for md5string - need to urldecode before generating the md5.
    $tiimd5 = array();
    foreach($tii as $key => $value) {
        if (!empty($value) AND $key <> 'tem' AND $key <> 'uem') {
            $value = rawurldecode($value); //decode url for calculating MD5
            $tiimd5[$key] = $value;
        } else {
            $tiimd5[$key] = $value;
        }
    }

    $tii['md5'] = tii_get_md5string($tiimd5);
    if (!empty($pid) &&!empty($tii['md5'])) {
        //save this md5 into the record.
        $tiifile = new stdClass();
        $tiifile->id = $pid;
        $tiifile->apimd5 = $tii['md5'];
        update_record('tii_files', $tiifile);
    }
    if ($returnArray) {
        return $tii;
    } else {
        $url = $tiisettings['turnitin_api']."?";
        foreach ($tii as $key => $value) {
            $url .= $key .'='. $value. '&';
        }

        return $url;
    }
}


function tii_get_gmtime() {
    return substr(gmdate('YmdHi'), 0, -1);
}

function tii_get_md5string($tii){
    global $CFG;
    $tiisettings = get_records_menu('config_plugins', 'plugin', 'tii', '', 'name,value');

    $md5string = $tiisettings['turnitin_accountid'].
                $tii['assign'].
                $tii['assignid'].
                $tii['ced'].
                $tii['cid'].
                $tii['cpw'].
                $tii['ctl'].
                $tii['diagnostic'].
                $tii['dis'].
                $tii['dtdue'].
                $tii['dtstart'].
                $tii['encrypt'].
                $tii['fcmd'].
                $tii['fid'].
                $tii['gmtime'].
                $tii['newassign'].
                $tii['newupw'].
                $tii['oid'].
                $tii['pfn'].
                $tii['pln'].
                $tii['ptl'].
                $tii['ptype'].
                $tii['said'].
                $tii['tem'].
                $tii['uem'].
                $tii['ufn'].
                $tii['uid'].
                $tii['uln'].
                $tii['upw'].
                $tii['username'].
                $tii['utp'].
                $tiisettings['turnitin_secretkey'];

    return md5($md5string);
}

//this function gets the xml from Turnitin when provided a URL
function tii_get_xml($url) {
    require_once("filelib.php");
    if (!($fp = download_file_content($url))) {
        notify("error trying to open Turnitin XML file!".$url);
        return false;
    } else {
            //now do something with the XML file to check to see if this has worked!
        $xml = new SimpleXMLElement($fp);
        return $xml;
    }
}

function tii_post_data($tii, $file='', $pid='') {
    $tiicomplete = tii_get_url($tii, 'array', $pid);

    $tiisettings = get_records_menu('config_plugins', 'plugin', 'tii', '', 'name,value');

    $ch = curl_init($tiisettings['turnitin_api']);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    if (!empty($file)) { //send file if that's passed as well!
        $tiicomplete['pdata'] = '@'.$file;
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $tiicomplete);
    ob_start();
    $result = curl_exec($ch);

    curl_close($ch);
    $tiixml = new SimpleXMLElement(ob_get_contents());
    ob_end_clean();
    return $tiixml;
}


//$tii is an array of vars needed to submit to the TII API
//$success is a integer with expected ID on success of this post.
function tii_post_to_api($tii, $success, $action='GET', $file='', $savercode=true, $returncode=false) {
    $tiixml = '';
    if (isset($tii['diagnostic']) AND $tii['diagnostic']) { // if in diagnostic mode, print the url used.
        debugging(tii_get_url($tii));
        return true;
    } else {
        switch ($action) {
            case 'GET':
                $tiixml = tii_get_xml(tii_get_url($tii, false, $file->id)); //get xml to work with
                break;
            case 'POST':
                if (!empty($file)) {
                    $tiixml = tii_post_data($tii, $file->fileinfo->filepath.$file->filename, $file->id);
                } else {
                    $tiixml = tii_post_data($tii);
                }
                break;
        }
    }
    //ERROR CHECKING TO ENSURE $tiixml response is correct
    if (empty($tiixml)) {
        error("no response recieved");
    }
    if (isset($tiixml->rcode[0]) && $savercode) { //415 = report not avail yet - don't store this status.
        $tii_file = new object();
        $tii_file->id = $file->id;
        $tii_file->tiicode = $tiixml->rcode[0];
        if (!update_record('tii_files', $tii_file)) {
            error("error updating tii_files record");
        }
    }
    if ($returncode) {
        return $tiixml->rcode[0];
    } elseif ($tiixml->rcode[0] == '51') { //this is the success code for uploading a file. - we need to return the oid and save it!
        $tii_file->tii = $tiixml->objectID;
        if ($savercode) {
            if (!update_record('tii_files', $tii_file)) {
                debugging("Error updating tii_files record");
            }
        }
        return true;
    } elseif ($tiixml->rcode[0] == '61') { //this is the report with score.
        return $tiixml->originalityscore[0];
    } elseif ($tiixml->rcode[0] == '415') { //report not ready yet
        return false;
    } elseif ($tiixml->rcode[0] <> $success) {
        debugging("Error occured in Turnitin System:" .$tiixml->rmessage[0]." tiicode:".$tiixml->rcode[0]);
        return false;
    } else {
        return true;
    }

}
function tii_get_settings() {

   $tiisettings = get_records_menu('config_plugins', 'plugin', 'tii', '', 'name,value');
   //check if tii enabled.
   if (isset($tiisettings['turnitin_use']) && $tiisettings['turnitin_use'] && isset($tiisettings['turnitin_accountid']) && $tiisettings['turnitin_accountid']) {
      //now check to make sure required settings are set!
      if (empty($tiisettings['turnitin_secretkey'])) {
        error("Turnitin Secret Key not set!");
      }
      if (empty($tiisettings['turnitin_userid'])) {
        error("Turnitin userid not set!");
      }
      if (empty($tiisettings['turnitin_email'])) {
        error("Turnitin email not set!");
      }
      if (empty($tiisettings['turnitin_firstname']) || empty($tiisettings['turnitin_lastname'])) {
        error("Turnitin firstname/lastname not set!");
      }
      return $tiisettings;
   } else {
    return false;
   }
}
//tii_send_files called by Moodle cron - it will send all files to Turnitin.
function tii_send_files() {
    global $CFG;
    $count = 0;
    $processedmodules = array();
    if ($tiisettings = tii_get_settings()) {
        mtrace("sending Turnitin files");
        //first do submission
        //get all files set to "pending"
        $files = get_records('tii_files','tiicode','pending');
        if (!empty($files)) {
            foreach($files as $file) {
               //set globals.
               if (!$user = get_record('user', 'id', $file->userid)) {
                   debugging("invalid userid! - userid:".$file->userid." Module:".$moduletype." Fileid:".$file->id);
                   continue;
               }
               if (!$course = get_record('course', 'id', $file->course)) {
                   debugging("invalid courseid! - courseid:".$file->course." Module:".$moduletype." Fileid:".$file->id);
                   continue;
               }
               if (!$moduletype = get_field('modules','name', 'id', $file->module)) {
                   debugging("invalid moduleid! - moduleid:".$file->module." Module:".$moduletype." Fileid:".$file->id);
                   continue;
               }
               if (!$module = get_record($moduletype, 'id', $file->instance)) {
                   debugging("invalid instanceid! - instance:".$file->instance." Module:".$moduletype." Fileid:".$file->id);
                   continue;
               }
               //now get details on the uploaded file!!
               $modfile = "$CFG->dirroot/mod/$moduletype/lib.php";
               $modfunc = $moduletype."_get_tii_file_info";
               if (file_exists($modfile)) {
                   include_once($modfile);
                   if (function_exists($modfunc)) {
                       $file->fileinfo = $modfunc($file);
                   }
               }
               if (empty($file->fileinfo)) {
                   debugging("no filepath found for this file! - Module:".$moduletype." Fileid:".$file->id);
                   continue;
               }
               if (!file_exists($file->fileinfo->filepath.$file->filename)) {
                   //this file has been deleted, so it should be deleted from tii_files
                   mtrace("a file from $course->shortname, assignment: $module->name, user:$user->username doesn't exist -deleting tii_files entry(it was probably deleted by the student)");
                   delete_records('tii_files', 'id', $file->id);
                   continue;
               }
               $tiisession = turnitin_start_session();
               $cm = get_coursemodule_from_instance($moduletype, $file->instance);
               $plagiarismvalues = get_records_menu('plagiarism_config', 'cm',$cm->id,'','name,value');
               if (!empty($module->timedue) && !empty($module->preventlate) && ($module->timedue+(24 * 50 * 60) < time())) {
                   mtrace("a file from course $course->shortname assignment $module->name cannot be submitted as the timedue has passed and preventlate is set");
               } else {
                   $coursecreated = true;
                   if (!isset($processedmodules[$moduletype][$module->id])) {
                       //first set up this assignment/assign the global teacher to this course.
                        $tii = array();
                        //set globals.
                        $tii['username'] = $tiisettings['turnitin_userid'];
                        $tii['uem']      = $tiisettings['turnitin_email'];
                        $tii['ufn']      = $tiisettings['turnitin_firstname'];
                        $tii['uln']      = $tiisettings['turnitin_lastname'];
                        $tii['uid']      = $tiisettings['turnitin_userid'];
                        $tii['utp']      = TURNITIN_INSTRUCTOR; //2 = this user is an instructor
                        $tii['ctl']      = (strlen($course->shortname) > 45 ? substr($course->shortname, 0, 45) : $course->shortname);
                        $tii['ctl']      = (strlen($tii['ctl']) > 5 ? $tii['ctl'] : $tii['ctl']."_____");
                        $tii['session-id'] = $tiisession;
                        //$tii['diagnostic'] = '1'; //debug only - uncomment when using in production.
                        if (get_config('plagiarism_turnitin_course', $course->id)) {
                            //course already exists - don't bother to create it.
                            $tii['cid']      = get_config('plagiarism_turnitin_course', $course->id); //course ID
                        } else {
                            $tii['cid']      = "c_".time().rand(10,5000); //some unique random id only used once.
                            $tii['fcmd'] = TURNITIN_RETURN_XML;
                            $tii['fid']  = TURNITIN_CREATE_CLASS; // create class under the given account and assign above user as instructor (fid=2)
                            $tiixml = tii_get_xml(tii_get_url($tii, false, $file->id));
                            if (!empty($tiixml->rcode[0]) && ($tiixml->rcode[0] == TURNITIN_RESP_CLASS_CREATED_LOGIN or 
                                              $tiixml->rcode[0] == TURNITIN_RESP_CLASS_CREATED or 
                                              $tiixml->rcode[0] == TURNITIN_RESP_CLASS_UPDATED)) {
                                //save external courseid for future reference.
                                if (!empty($tiixml->classid[0])) {
                                    set_config($course->id, $tiixml->classid[0], 'plagiarism_turnitin_course');
                                    $tii['cid']  = $tiixml->classid[0];
                                }
                            } else {
                                $coursecreated = false;
                            }
                            //$rcode = tii_post_to_api($tii, 21, 'GET','',false);
                        }

                        if ($coursecreated) { //these rcodes signify that this assignment exists, or has been successfully updated.
                            //now create Assignment in Class
                            if (!empty($plagiarismvalues['turnitin_assignid'])) {
                                $tii['assignid']   = $plagiarismvalues['turnitin_assignid'];
                                $tii['fcmd'] = TURNITIN_UPDATE_RETURN_XML;
                                if (empty($module->timeavailable)) {
                                    $dtstart = get_field('plagiarism_config', 'value', 'cm', $cm->id, 'name','turnitin_dtstart');
                                    if (empty($dtstart)) {
                                        $dtstart = time()+60*60;
                                        $configval = new stdclass();
                                        $configval->cm = $cm->id;
                                        $configval->name = 'turnitin_dtstart';
                                        $configval->value = $dtstart;
                                        insert_record('plagiarism_config', $configval);
                                    }
                                    $tii['dtstart'] = rawurlencode(date('Y-m-d H:i:s', $dtstart));
                                } else {
                                    $tii['dtstart']  = rawurlencode(date('Y-m-d H:i:s', $module->timeavailable));
                                    $dtstart = $module->timeavailable;
                                }
                                if (empty($module->timedue)) {
                                    $tii['dtdue'] = rawurlencode(date('Y-m-d H:i:s', time()+(365 * 24 * 60 * 60)));
                                } else {
                                    if ($dtstart > $module->timedue) {
                                        $tii['dtdue'] = rawurlencode(date('Y-m-d H:i:s', $dtstart+(30 * 24 * 60 * 60)));
                                    } else {
                                        $tii['dtdue']    = rawurlencode(date('Y-m-d H:i:s', $module->timedue));
                                    }
                                }
                            } else {
                                $tii['assignid'] = "a_".time().rand(10,5000); //some unique random id only used once.
                                $tii['fcmd'] = TURNITIN_RETURN_XML;
                                $tii['dtstart'] = rawurlencode(date('Y-m-d H:i:s', time()+60*60));
                                $tii['dtdue'] = rawurlencode(date('Y-m-d H:i:s', time()+(365 * 24 * 60 * 60)));
                            }
                            $tii['assign']   = get_assign_name($module->name, $cm->id); //assignment name stored in TII
                            $tii['fid']      = TURNITIN_CREATE_ASSIGNMENT;
                            $tii['ptl']      = $course->id.$course->shortname; //paper title? - assname?
                            $tii['ptype']    = '2'; //filetype
                            $tii['pfn']      = $tii['ufn'];
                            $tii['pln']      = $tii['uln'];
                            $tii['late_accept_flag']  = (empty($module->preventlate) ? '1' : '0');
                            if (isset($plagiarismvalues['plagiarism_show_student_report'])) {
                                $tii['s_view_report']     = (empty($plagiarismvalues['plagiarism_show_student_report']) ? '0' : '1'); //allow students to view the full report.
                            } else {
                                $tii['s_view_report']     = '1';
                            }
                            $tii['s_paper_check']     = (isset($plagiarismvalues['plagiarism_compare_student_papers']) ? $plagiarismvalues['plagiarism_compare_student_papers'] : '1');
                            $tii['internet_check']    = (isset($plagiarismvalues['plagiarism_compare_internet']) ? $plagiarismvalues['plagiarism_compare_internet'] : '1');
                            $tii['journal_check']     = (isset($plagiarismvalues['plagiarism_compare_journals']) ? $plagiarismvalues['plagiarism_compare_journals'] : '1');
                            $tii['report_gen_speed']  = (isset($plagiarismvalues['plagiarism_report_gen']) ? $plagiarismvalues['plagiarism_report_gen'] : '1');
                            $tii['exclude_biblio'] = (isset($plagiarismvalues['plagiarism_exclude_biblio']) ? $plagiarismvalues['plagiarism_exclude_biblio'] : '0');
                            $tii['exclude_quoted'] = (isset($plagiarismvalues['plagiarism_exclude_quoted']) ? $plagiarismvalues['plagiarism_exclude_quoted'] : '0');
                            $tii['exclude_type']      = (isset($plagiarismvalues['plagiarism_exclude_matches']) ? $plagiarismvalues['plagiarism_exclude_matches'] : '0');
                            $tii['exclude_value']     = (isset($plagiarismvalues['plagiarism_exclude_matches_value']) ? $plagiarismvalues['plagiarism_exclude_matches_value'] : '');
                            //$tii['diagnostic'] = '1'; //debug only - uncomment when using in production.
                            $tiixml = tii_post_data($tii);

                            if ($tiixml->rcode[0]==TURNITIN_RESP_ASSIGN_EXISTS) { //if assignment already exists then update it and set externalassignid correctly
                                $tii['fcmd'] = TURNITIN_UPDATE_RETURN_XML; //when set to 3 - it updates the course
                                $tiixml = tii_post_data($tii);
                            }
                            if ($tiixml->rcode[0]==TURNITIN_RESP_ASSIGN_CREATED) {
                                //save assid for use later.
                                if (!empty($tiixml->assignmentid[0])) {
                                    if (empty($plagiarismvalues['turnitin_assignid'])) {
                                        $configval = new stdclass();
                                        $configval->cm = $cm->id;
                                        $configval->name = 'turnitin_assignid';
                                        $configval->value = $tiixml->assignmentid[0];
                                        insert_record('plagiarism_config', $configval);
                                    } else {
                                        $configval = get_record('plagiarism_config', 'cm', $cm->id, 'name', 'turnitin_assignid');
                                        $configval->value = $tiixml->assignmentid[0];
                                        update_record('plagiarism_config', $configval);
                                    }
                                    $plagiarismvalues['turnitin_assignid'] = $tiixml->assignmentid[0];
                                }
                                if (!empty($module->timedue) or (!empty($module->timeavailable))) {
                                    //need to update time set in Turnitin.
                                    $tii['assignid'] = $tiixml->assignmentid[0];
                                    $tii['fcmd'] = TURNITIN_UPDATE_RETURN_XML;
                                    if (!empty($module->timeavailable)) {
                                        $tii['dtstart']  = rawurlencode(date('Y-m-d H:i:s', $module->timeavailable));
                                        $dtstart = $module->timeavailable;
                                    }
                                    if (!empty($module->timedue)) {
                                        $tii['dtdue']    = rawurlencode(date('Y-m-d H:i:s', $module->timedue));
                                    }
                                }
                                $tiixml = tii_post_data($tii);
                            }
                            if ($tiixml->rcode[0]==TURNITIN_RESP_ASSIGN_MODIFIED) {
                                if ($dtstart+900 > time()) { //Turnitin doesn't like receiving files too close to start time
                                    mtrace("Warning: assignment start date is too early ".date('Y-m-d H:i:s', $dtstart)." in course $course->shortname assignment $module->name will delay sending files until next cron");
                                    $processedmodules[$moduletype][$module->id] = false; //try again next cron
                                } else {
                                    $processedmodules[$moduletype][$module->id] = true; //Only do the last 2 calls once per cron.
                                    mtrace("Turnitin Success creating Class and assignment");
                                }
                            } else {
                                mtrace("Error: could not create assignment in course $course->shortname assignment $module->name TIICODE:".$tiixml->rcode[0]. 'CM:'.$cm->id. ' URL:'.tii_get_url($tii));
                                $processedmodules[$moduletype][$module->id] = false; //try again next cron
                            }
                        } else {
                            mtrace("Error: could not create class and assign global instructor in course $course->shortname assignment $module->name TIICODE:$rcode");
                            $processedmodules[$moduletype][$module->id] = false; //try again next cron
                        }
                   }
                   //now send the files.
                   //if this module and assignment have been created successfully, send the files to Turnitin!
                   if (!(isset($processedmodules[$moduletype][$module->id]) && $processedmodules[$moduletype][$module->id])) {
                       //mtrace("could not send a file, as class  and assignment could not be created. course $course->shortname assignment $module->name");
                   } else {
                       $tii2 = array();
                       $tii2['username'] = $user->username;
                       $tii2['uem']      = $user->email;
                       $tii2['ufn']      = $user->firstname;
                       $tii2['uln']      = $user->lastname;
                       $tii2['uid']      = $user->username;
                       $tii2['utp']      = TURNITIN_STUDENT; // 1= this is a student.
                       $tii2['cid']      = get_config('plagiarism_turnitin_course', $course->id);
                       $tii2['ctl']      = (strlen($course->shortname) > 45 ? substr($course->shortname, 0, 45) : $course->shortname);
                       $tii2['ctl']      = (strlen($tii['ctl']) > 5 ? $tii['ctl'] : $tii['ctl']."_____");
                       $tii2['fcmd']     = TURNITIN_RETURN_XML; //when set to 2 the tii api call returns XML
                       $tii2['session-id'] = $tiisession;

                       //$tii2['diagnostic'] = '1';
                       $tii2['fid']      = TURNITIN_CREATE_USER; //set command. - create user and login student to Turnitin (fid=1)
                       if (tii_post_to_api($tii2, 11, 'GET', $file)) {
                           //now enrol user in class under the given account (fid=3)
                           $tii2['assignid'] = $plagiarismvalues['turnitin_assignid'];
                           $tii2['assign']   = (strlen($module->name) > 90 ? substr($module->name, 0, 90) : $module->name); //assignment name stored in TII
                           $tii2['fid']      = TURNITIN_JOIN_CLASS;
                           //$tii2['diagnostic'] = '1';
                           //bug with TII API replication - sometimes doesn't replicate from their master to slave
                           //quick enough for the next call and returns a 407. - use tii_post_handler to manage this.
                           if (tii_post_handler(407, $tii2, 31, 'GET', $file)) {
                               //TODO TII expects more than 100 characters in a submitted file - should probably check this?

                               //now submit this uploaded file to Tii! (fid=5)
                               $tii2['utp']     = TURNITIN_STUDENT; //2 = instructor, 1= student.
                               $tii2['fid']     = TURNITIN_SUBMIT_PAPER;
                               $tii2['ptl']     = $file->filename; //paper title
                               $tii2['submit_date'] = rawurlencode(date('Y-m-d H:i:s', filemtime($file->fileinfo->filepath.$file->filename)));
                               $tii2['ptype']   = '2'; //filetype
                               $tii2['pfn']     = $tii['ufn'];
                               $tii2['pln']     = $tii['uln'];
                               //$tii['diagnostic'] = '1';
                               $count++;
                               if (tii_post_to_api($tii2, 51, 'POST', $file)) {
                                   debugging("success uploading assignment", DEBUG_DEVELOPER);
                               }
                           }
                       }
                   }
               } //done all that is needed for tii submission..
               turnitin_end_session($tiisession);
           }
       }
       mtrace("sent ".$count." files");
       //now check for files that need to be retried.
       require_once("$CFG->dirroot/mod/assignment/lib.php");
        //get list of files that need to be resubmitted - sanity check against cm
        if (!empty($tiisettings['turnitin_attempts']) && is_numeric($tiisettings['turnitin_attempts'])
            && !empty($tiisettings['turnitin_attemptcodes'])) {
            $acin = '';
            $acodes = explode(',',trim($tiisettings['turnitin_attemptcodes']));
            foreach ($acodes as $ac) {
                if (!empty($acin)) {
                    $acin .=',';
                }
                $acin .= "'".(int)$ac."'";
            }
            $sql = "SELECT *
                    FROM ".$CFG->prefix."tii_files
                    WHERE tiicode IN (".$acin.") AND attempt < ".$tiisettings['turnitin_attempts'];
            $items = get_records_sql($sql);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item->tiicode = 'pending';
                    $item->attempt = $item->attempt+1;
                    update_record('tii_files', $item);
                }
            }
        }
   }
}

function tii_get_scores() {
    if ($tiisettings = tii_get_settings()) {
        $count = 0;
        mtrace("getting Turnitin scores");
        //first do submission
        //get all files set to "51" - success code for uploading.
        $files = get_records('tii_files','tiicode','51');
        //print_object($files);
        if (!empty($files)) {
            foreach($files as $file) {
               //set globals.
               $user = get_record('user', 'id', $file->userid);
               $course = get_record('course', 'id', $file->course);
               if (empty($user) or empty($course)) {
                   //this course/user doesn't exist anymore so delete it.
                   delete_records('tii_files', 'id', $file->id);
               }

               $tii['username'] = $tiisettings['turnitin_userid'];
               $tii['uem']      = $tiisettings['turnitin_email'];
               $tii['ufn']      = $tiisettings['turnitin_firstname'];
               $tii['uln']      = $tiisettings['turnitin_lastname'];
               $tii['uid']      = $tiisettings['turnitin_userid'];
               $tii['utp']      = TURNITIN_INSTRUCTOR; //2 = this user is an instructor
               $tii['cid'] = get_config('plagiarism_turnitin_course', $course->id); //course ID
               $tii['ctl']      = (strlen($course->shortname) > 45 ? substr($course->shortname, 0, 45) : $course->shortname);
               $tii['ctl']      = (strlen($tii['ctl']) > 5 ? $tii['ctl'] : $tii['ctl']."_____");
               $tii['fcmd']     = TURNITIN_RETURN_XML; //when set to 2 the tii api call returns XML
               $tii['fid']      = TURNITIN_RETURN_REPORT;
               $tii['oid']      = $file->tii;

               $tiiscore = tii_post_to_api($tii, 61, 'GET', $file, false);
               if (isset($tiiscore) && $tiiscore) {
                   $file = get_record('tii_files','id',$file->id); //get latest record as it may have changed with debug info.
                   $file->tiiscore = $tiiscore;
                   $file->tiicode = 'success';
                   $count++;
                   if (!update_record('tii_files', $file)) {
                       debugging("update tii score failed");
                   }
               }
            }
        }
        mtrace("received ".$count." new scores");
    }
}

function tii_get_report_link($file, $userid='') {
    $return = '';
    if ($tiisettings = tii_get_settings()) { //get tii settings.
        $tii = array();
        $courseshortname = get_field('course', 'shortname', 'id', $file->course); //TODO: Dan - I don't like calling these 2 queries each time for performance
        $cmid = get_field('course_modules', 'id', 'course', $file->course, 'module', $file->module, 'instance', $file->instance);
        if (!has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cmid))) {
            $user = get_record('user', 'id', $file->userid);

            $tii['username'] = $user->username;
            $tii['uem']      = $user->email;
            $tii['ufn']      = $user->firstname;
            $tii['uln']      = $user->lastname;
            $tii['uid']      = $user->username;
            $tii['utp'] = TURNITIN_STUDENT; //1 = this user is an student
        } else {
            $tii['username'] = $tiisettings['turnitin_userid'];
            $tii['uem']      = $tiisettings['turnitin_email'];
            $tii['ufn']      = $tiisettings['turnitin_firstname'];
            $tii['uln']      = $tiisettings['turnitin_lastname'];
            $tii['uid']      = $tiisettings['turnitin_userid'];
            $tii['utp']      = TURNITIN_INSTRUCTOR; //2 = this user is an instructor
        }
        $tii['cid'] = get_config('plagiarism_turnitin_course', $file->course); //course ID
        if (empty($tii['cid'])) { //TODO: - do we need to keep this for backwards compatibility?
           $tii['cid'] = $tiisettings['turnitin_courseprefix'].$file->course.$courseshortname;
        }
        $tii['ctl']    = (strlen($courseshortname) > 70 ? substr($courseshortname, 0, 70) : $courseshortname);
        $tii['ctl']      = (strlen($tii['ctl']) > 5 ? $tii['ctl'] : $tii['ctl']."_____");
        $tii['fcmd'] = TURNITIN_LOGIN; //when set to 2 the tii api call returns XML
        $tii['fid'] = TURNITIN_RETURN_REPORT;
        $tii['oid'] = $file->tii;

    return tii_get_url($tii);
    }
}
/**
 * given an error code, returns the description for this error
 * @param string $tiicode The Error code.
 * @param boolean $notify if true, returns a notify call - otherwise just returns the text of the error.
 */
function tii_error_text($tiicode, $notify=true) {
   $return = '';
   $tiicode = (int) $tiicode;
   if (!empty($tiicode)) {
       if ($tiicode < 100) { //don't return an error state for codes 0-99
          return '';
       } elseif (($tiicode > 1006 && $tiicode < 1014) or ($tiicode > 1022 && $tiicode < 1025) or $tiicode == 1020) { //these are general errors that a could be useful to students.
           $return = get_string('tiierror'.$tiicode, 'turnitin');
       } elseif ($tiicode > 1024 && $tiicode < 2000) { //don't have documentation on the other 1000 series errors, so just display a general one.
           $return = get_string('tiierrorpaperfail', 'turnitin');
       } elseif ($tiicode < 1025 || $tiicode > 2000) { //these are not errors that a student can make any sense out of. 
           $return = get_string('tiiconfigerror', 'turnitin');
       }
       if (!empty($return) && $notify) {
           $return = notify($return, 'notifyproblem', 'left', true);
       }
   }
    
   return $return;
}
function get_tii_error($tiicode) {
    global $CFG;
    $tiierrordesc = get_string('tiierror'.$tiicode, 'turnitin');
    if ($tiierrordesc == '[[tiierror'.$tiicode.']]') { //display link to moodledocs for this error. 
        $lang = str_replace('_utf8', '', current_language()); //get short version of lang for docs.moodle.org
        $errordoc = $CFG->docroot. '/' .$lang. '/Turnitin_errors';
         return '<span class="error">&nbsp;'.get_string('tiierror', 'turnitin').'<a href="'.$errordoc.'" target="_blank">'.$tiicode.'</a>&nbsp;</span>';
    } else {
        return '<span class="error">&nbsp;'.$tiierrordesc.'&nbsp;</span>';
    }
}
function update_tii_files($filename, $courseid, $moduleid, $instanceid) {
    global $USER;
    //now update or insert record into tii_files
    if ($tii_file = get_record_select('tii_files', "course='".$courseid.
                    "' AND module='".$moduleid.
                    "' AND instance='".$instanceid.
                    "' AND userid = '".$USER->id.
                    "' AND filename = '".$filename."'")) {
        //update record.
        $tii_file->tiicode = 'pending';
        $tii_file->tiiscore ='0';
        if (!update_record('tii_files', $tii_file)) {
            debugging("update tii_files failed!");
        }
    } else {
        $tii_file = new object();
        $tii_file->course = $courseid;
        $tii_file->module = $moduleid;
        $tii_file->instance = $instanceid;
        $tii_file->userid = $USER->id;
        $tii_file->filename = $filename;
        $tii_file->tiicode = 'pending';
        if (!insert_record('tii_files', $tii_file)) {
            debugging("insert into tii_files failed");
        }
    }    
}
/**
 * Turnitin use a frontend/backend configuration, an inital call to the frontend cannot always
 * be relied on for a subsequent call to the frontend. - This function works as a handler for
 * tii_post_to_api - it takes an initial possible Error code, and if that code is returned from
 * the call to tii_post_to_api it will try the API call X times and wait increasing amounts of time
 * for the API to catch up.
 * @param string $tiierror if this error is returned, try again.
 */
function tii_post_handler($tiierror, $tii, $success, $action='GET', $file='', $savercode='true') {
    $retcode = $tiierror;
    $tries = 6;
    $i = 0;
    while ($tries > $i) {
        sleep($i*2);
        $retcode = tii_post_to_api($tii, $success, $action, $file, $savercode, true); //forces the code to be returned
        if ($retcode == $success) {
            return true;
        }
        if ($retcode <> $tiierror) { // a different error has occured - this handler only handles the error code in $tiierror
            return false;
        }
        $i++;
    }
    return false;
}
/**
 * Function that returns the name of the css class to use for a given Tii score
 * @param integer $score the score returned from Turnitin
 */
function plagiarism_get_css_rank ($score) {
    $rank = "none";
    if($score >  90) { $rank = "1"; }
    elseif($score >  80) { $rank = "2"; }
    elseif($score >  70) { $rank = "3"; }
    elseif($score >  60) { $rank = "4"; }
    elseif($score >  50) { $rank = "5"; }
    elseif($score >  40) { $rank = "6"; }
    elseif($score >  30) { $rank = "7"; }
    elseif($score >  20) { $rank = "8"; }
    elseif($score >  10) { $rank = "9"; }
    elseif($score >=  0) { $rank = "10"; }

    return "rank$rank";
}

    function plagiarism_save_form_elements($data) {
        if (!get_settings()) {
            return;
        }
        if (isset($data->use_turnitin)) {
            //array of posible plagiarism config options.
            $plagiarismelements = config_options();
            //first get existing values
            $existingelements = get_records_menu('plagiarism_config', 'cm',$data->coursemodule,'','name,id');
            foreach($plagiarismelements as $element) {
                $newelement = new object();
                $newelement->cm = $data->coursemodule;
                $newelement->name = $element;
                $newelement->value = (isset($data->$element) ? $data->$element : 0);
                if (isset($existingelements[$element])) { //update
                    $newelement->id = $existingelements[$element];
                    update_record('plagiarism_config', $newelement);
                } else { //insert
                    insert_record('plagiarism_config', $newelement);
                }

            }
        }
    }

    function plagiarism_get_form_elements_module($mform, $context) {
        global $CFG;
        if (!get_settings()) {
            return;
        }
        $cmid = optional_param('update', 0, PARAM_INT); //there doesn't seem to be a way to obtain the current cm a better way - $this->_cm is not available here.
        if (!empty($cmid)) {
            $plagiarismvalues = get_records_menu('plagiarism_config', 'cm',$cmid,'','name,value');
        }
        $plagiarismdefaults = get_records_menu('plagiarism_config', 'cm',0,'','name,value'); //cmid(0) is the default list.
        $plagiarismelements = config_options();
        if (has_capability('moodle/local:enableturnitin', $context)) {
            turnitin_get_form_elements($mform);
            if ($mform->elementExists('plagiarism_draft_submit')) {
                $mform->disabledIf('plagiarism_draft_submit', 'var4', 'eq', 0);
            }
            //disable all plagiarism elements if use_plagiarism eg 0
            foreach ($plagiarismelements as $element) {
                if ($element <> 'use_turnitin') { //ignore this var
                    $mform->disabledIf($element, 'use_turnitin', 'eq', 0);
                }
            }
            //TODO: convert use of course/instance to cm so that we can use exclude biblio/quoted.
            if (!empty($cmid)) {
                //get full cm
                $cm = get_record('course_modules', 'id', $cmid);
                //check if files have already been submitted and disable exclude biblio and quoted if turnitin is enabled.
                if (record_exists('tii_files', 'course', $cm->course, 'module', $cm->module, 'instance', $cm->instance)) {
                    $mform->disabledIf('plagiarism_exclude_biblio','use_turnitin');
                    $mform->disabledIf('plagiarism_exclude_quoted','use_turnitin');
                }
            }
        } else { //add plagiarism settings as hidden vars.
            foreach ($plagiarismelements as $element) {
                $mform->addElement('hidden', $element);
            }
        }
        //now set defaults.
        foreach ($plagiarismelements as $element) {
            if (isset($plagiarismvalues[$element])) {
                $mform->setDefault($element, $plagiarismvalues[$element]);
            } else if (isset($plagiarismdefaults[$element])) {
                $mform->setDefault($element, $plagiarismdefaults[$element]);
            }
        }
    }
    function config_options() {
        return array('use_turnitin','plagiarism_show_student_score','plagiarism_show_student_report',
                     'plagiarism_draft_submit','plagiarism_compare_student_papers','plagiarism_compare_internet',
                     'plagiarism_compare_journals','plagiarism_compare_institution','plagiarism_report_gen',
                     'plagiarism_exclude_biblio','plagiarism_exclude_quoted','plagiarism_exclude_matches',
                     'plagiarism_exclude_matches_value');
    }
    function get_settings() {
        $plagiarismsettings = (array)get_config('tii');
        //check if tii enabled.
        if (isset($plagiarismsettings['turnitin_use']) && $plagiarismsettings['turnitin_use'] && isset($plagiarismsettings['turnitin_accountid']) && $plagiarismsettings['turnitin_accountid']) {
            //now check to make sure required settings are set!
            if (empty($plagiarismsettings['turnitin_secretkey'])) {
                error("Turnitin Secret Key not set!");
            }
            return $plagiarismsettings;
        } else {
            return false;
        }
    }
    /**
     * adds the list of plagiarism settings to a form
     *
     * @param object $mform - Moodle form object
     */
    function turnitin_get_form_elements($mform) {
        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $tiioptions = array(0 => get_string("never"), 1 => get_string("always"), 2 => get_string("showwhenclosed", "turnitin"));
        $tiidraftoptions = array(0 => get_string("submitondraft","turnitin"), 1 => get_string("submitonfinal","turnitin"));
        $reportgenoptions = array( 0 => get_string('reportgenimmediate', 'turnitin'), 1 => get_string('reportgenimmediateoverwrite', 'turnitin'), 2 => get_string('reportgenduedate', 'turnitin'));
        $excludetype = array( 0 => get_string('no'), 1 => get_string('wordcount', 'turnitin'), 2 => get_string('percentage', 'turnitin'));

        $mform->addElement('header', 'plagiarismdesc');
        $mform->addElement('select', 'use_turnitin', get_string("useturnitin", "turnitin"), $ynoptions);
        $mform->addElement('select', 'plagiarism_show_student_score', get_string("showstudentsscore", "turnitin"), $tiioptions);
        $mform->setHelpButton('plagiarism_show_student_score', array('showstudentsscore', get_string('showstudentsscore', 'turnitin'),'turnitin'));
        $mform->addElement('select', 'plagiarism_show_student_report', get_string("showstudentsreport", "turnitin"), $tiioptions);
        $mform->setHelpButton('plagiarism_show_student_report', array('showstudentsreport', get_string('showstudentsreport', 'turnitin'),'turnitin'));
        if ($mform->elementExists('var4')) {
            $mform->addElement('select', 'plagiarism_draft_submit', get_string("draftsubmit", "turnitin"), $tiidraftoptions);
        }
        $mform->addElement('select', 'plagiarism_compare_student_papers', get_string("comparestudents", "turnitin"), $ynoptions);
        $mform->setHelpButton('plagiarism_compare_student_papers', array('comparestudents', get_string('comparestudents', 'turnitin'),'turnitin'));
        $mform->addElement('select', 'plagiarism_compare_internet', get_string("compareinternet", "turnitin"), $ynoptions);
        $mform->setHelpButton('plagiarism_compare_internet', array('compareinternet', get_string('compareinternet', 'turnitin'),'turnitin'));
        $mform->addElement('select', 'plagiarism_compare_journals', get_string("comparejournals", "turnitin"), $ynoptions);
        $mform->setHelpButton('plagiarism_compare_journals', array('comparejournals', get_string('comparejournals', 'turnitin'),'turnitin'));
        if (get_config('tii', 'turnitin_institutionnode')) {
            $mform->addElement('select', 'plagiarism_compare_institution', get_string("compareinstitution", "turnitin"), $ynoptions);
            $mform->setHelpButton('plagiarism_compare_institution', array('compareinstitution', get_string('compareinstitution', 'turnitin'),'turnitin'));
        }
        $mform->addElement('select', 'plagiarism_report_gen', get_string("reportgen", "turnitin"), $reportgenoptions);
        $mform->setHelpButton('plagiarism_report_gen', array('reportgen', get_string('reportgen', 'turnitin'),'turnitin'));

        $mform->addElement('select', 'plagiarism_exclude_biblio', get_string("excludebiblio", "turnitin"), $ynoptions);
        $mform->setHelpButton('plagiarism_exclude_biblio', array('excludebiblio', get_string('excludebiblio', 'turnitin'),'turnitin'));
        $mform->addElement('select', 'plagiarism_exclude_quoted', get_string("excludequoted", "turnitin"), $ynoptions);
        $mform->setHelpButton('plagiarism_exclude_quoted', array('excludequoted', get_string('excludequoted', 'turnitin'),'turnitin'));

        $mform->addElement('select', 'plagiarism_exclude_matches', get_string("excludematches", "turnitin"), $excludetype);
        $mform->setHelpButton('plagiarism_exclude_matches', array('excludematches', get_string('excludematches', 'turnitin'),'turnitin'));
        $mform->addElement('text', 'plagiarism_exclude_matches_value', '');
        $mform->addRule('plagiarism_exclude_matches_value', null, 'numeric', null, 'client');
        $mform->disabledIf('plagiarism_exclude_matches_value', 'plagiarism_exclude_matches', 'eq', 0);
    }
    /**
* generates a url to allow access to a similarity report.
*
* @param integer $userid - userid of user who owns the file.
* @param object $file - single record from turnitin_files table
* @param object $course - usually global $COURSE value
* @param integer $cmid - course module id
* @param object $module - full module recor (eg assignment table)
* @param boolean $ignoreuserchecks - ignores Global User/capability checks.(BE VERY CAREFUL!) - uses assignment settings to decide what to display.

* @return string - url to allow login/viewing of a similarity report
*/
    function plagiarism_get_links($userid, $file, $cmid, $course, $module, $ignoreuserchecks=false) {
        global $CFG, $USER;

        $plagiarismvalues = get_records_menu('plagiarism_config', 'cm',$cmid,'','name,value');
        if (empty($plagiarismvalues['use_turnitin'])) {
            //nothing to do here... move along!
           return '';
        }
        $modulecontext = get_context_instance(CONTEXT_MODULE, $cmid);
        $output = '';

        //check if this is a user trying to look at their details, or a teacher with viewsimilarityscore rights.
        if (($USER->id == $userid) || has_capability('moodle/local:viewsimilarityscore', $modulecontext) || $ignoreuserchecks) {
            if ($plagiarismsettings = get_settings()) {
                $plagiarismfile = get_record_select('tii_files', "course='".$course->id.
                                                    "' AND module='".get_field('modules', 'id','name','assignment').
                                                    "' AND instance='".$module->id.
                                                    "' AND userid='".$userid.
                                                    "' AND filename='".$file."'");
                if (isset($plagiarismfile->tiiscore) && $plagiarismfile->tiicode=='success') { //if TII has returned a succesful score.
                    //check for open mod.
                    $assignclosed = false;
                    $time = time();
                    if (!empty($module->timedue)) {
                        $assignclosed = ($time >= $module->timedue);
                    } elseif (!empty($module->timeavailable)) {
                        $assignclosed = ($module->timeavailable <= $time);
                    }
                    $rank = plagiarism_get_css_rank($plagiarismfile->tiiscore);
                    if ($USER->id <> $userid && !$ignoreuserchecks) { //this is a teacher with moodle/plagiarism_turnitin:viewsimilarityscore
                        if (has_capability('moodle/local:viewfullreport', $modulecontext)) {
                            $output .= '<span class="plagiarismreport"><a href="'.tii_get_report_link($plagiarismfile).'" target="_blank">'.get_string('similarity', 'turnitin').':</a><span class="'.$rank.'">'.$plagiarismfile->tiiscore.'%</span></span>';
                        } else {
                            $output .= '<span class="plagiarismreport">'.get_string('similarity', 'turnitin').':<span class="'.$rank.'">'.$plagiarismfile->tiiscore.'%</span></span>';
                        }
                    } elseif (isset($plagiarismvalues['plagiarism_show_student_report']) && isset($plagiarismvalues['plagiarism_show_student_score']) and //if report and score fields are set.
                             ($plagiarismvalues['plagiarism_show_student_report']== 1 or $plagiarismvalues['plagiarism_show_student_score'] ==1 or //if show always is set
                             ($plagiarismvalues['plagiarism_show_student_score']==2 && $assignclosed) or //if student score to be show when assignment closed
                             ($plagiarismvalues['plagiarism_show_student_report']==2 && $assignclosed))) { //if student report to be shown when assignment closed
                        if (($plagiarismvalues['plagiarism_show_student_report']==2 && $assignclosed) or $plagiarismvalues['plagiarism_show_student_report']==1) {
                            $output .= '<span class="plagiarismreport"><a href="'.tii_get_report_link($plagiarismfile).'" target="_blank">'.get_string('similarity', 'turnitin').'</a>';
                            if ($plagiarismvalues['plagiarism_show_student_score']==1 or ($plagiarismvalues['plagiarism_show_student_score']==2 && $assignclosed)) {
                                $output .= ':<span class="'.$rank.'">'.$plagiarismfile->tiiscore.'%</span>';
                            }
                            $output .= '</span>';
                        } else {
                            $output .= '<span class="plagiarismreport">'.get_string('similarity', 'turnitin').':<span class="'.$rank.'">'.$plagiarismfile->tiiscore.'%</span>';
                        }
                    }
                } else if(isset($plagiarismfile->tiicode)) { //always display errors - even if the student isn't able to see report/score.
                    $output .= tii_error_text($plagiarismfile->tiicode);
                }
            }
        }
        return $output.'<br/>';
    }
    //TODO: implement a way of running this function properly.
    function rebuild_assignment_files($aid, $courseid, $moduleid) {
        $submissions = get_records('assignment_submissions','assignment', $aid);
        if (empty($submissions)) {
            return '';
        }
        foreach ($submissions as $submission) {
        $filearea = $CFG->dataroot.'/'.$courseid.'/'.$CFG->moddata.'/assignment/'.$aid.'/'.$submission->userid;
            if ($files = get_directory_list($filearea)) {
                foreach ($files as $key => $file) {
                    if (!record_exists('tii_files', 'instance', $aid, 'filename', $file, 'userid',$submission->userid )) {
                        $tii_file = new object();
                        $tii_file->course = $courseid;
                        $tii_file->module = $moduleid;
                        $tii_file->instance = $aid;
                        $tii_file->userid = $submission->userid;
                        $tii_file->filename = $file;
                        $tii_file->tiicode = 'pending';
                        insert_record('tii_files', $tii_file);
                    }
                }
            }
        }
    }

/**
 * Function that starts Turnitin session - some api calls require this
 *
 * @param object  $plagiarismsettings - from a call to plagiarism_get_settings
 * @return string - Turnitin sessionid
 */
function turnitin_start_session() {
    $tiisettings = tii_get_settings();
    $tii = array();
    //set globals.
    $tii['utp']      = TURNITIN_STUDENT;
    $tii['username'] = $tiisettings['turnitin_userid'];
    $tii['uem']      = $tiisettings['turnitin_email'];
    $tii['ufn']      = $tiisettings['turnitin_firstname'];
    $tii['uln']      = $tiisettings['turnitin_lastname'];
    $tii['uid']      = $tiisettings['turnitin_userid'];

    $tii['fcmd']     = TURNITIN_RETURN_XML;
    $tii['fid']      = TURNITIN_START_SESSION;
    $content = tii_get_url($tii);
    $tiixml = tii_get_xml($content);
    if (isset($tiixml->sessionid[0])) {
        return $tiixml->sessionid[0];
    } else {
        return '';
    }
}
/**
 * Function that ends a Turnitin session
 *
 * @param object  $plagiarismsettings - from a call to plagiarism_get_settings
 * @param string - Turnitin sessionid - from a call to turnitin_start_session
 */

function turnitin_end_session($tiisession) {
    if (empty($tiisession)) {
        return;
    }
    $tiisettings = tii_get_settings();
    $tii = array();
    //set globals.
    $tii['utp']      = TURNITIN_STUDENT;
    $tii['username'] = $tiisettings['turnitin_userid'];
    $tii['uem']      = $tiisettings['turnitin_email'];
    $tii['ufn']      = $tiisettings['turnitin_firstname'];
    $tii['uln']      = $tiisettings['turnitin_lastname'];
    $tii['uid']      = $tiisettings['turnitin_userid'];
    $tii['fcmd']     = TURNITIN_RETURN_XML;
    $tii['fid']      = TURNITIN_END_SESSION;
    $tii['session-id'] = $tiisession;
    $tiixml = tii_get_xml(tii_get_url($tii));
}


/**
 * Helper function that makes the name of the module and the coursemoduleid into a concatentated string.
 * This avoid naming collisions in courses where duplicate names have been used for activities.
 *
 * @param string $name the name of the activity e.g. 'End of term essay'
 * @param int $cmid The id of the moodle coursemodule for this activity
 * @return string
 */
 function get_assign_name($name, $cmid) {
    $suffix   = '-'.$cmid; // suffix first, so we can keep it 90 chars even if cmid is long
    $maxnamelength = 90 - strlen($suffix);
    $shortname = (strlen($name) > $maxnamelength) ? substr($name, 0, $maxnamelength) : $name;
    return $shortname.$suffix;
 }
