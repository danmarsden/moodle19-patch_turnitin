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
 
function tii_get_url($tii, $returnArray=false) {
    global $CFG;
    $tiisettings = get_records_menu('config_plugins', 'plugin', 'tii', '', 'name,value');

    //make sure all $tii values are clean.
    foreach($tii as $key => $value) {
        if (!empty($value) AND $key <> 'tem' AND $key <> 'uem') {
            $value = rawurldecode($value); //decode url first. (in case has already be encoded - don't want to end up with double % replacements)
            $value = rawurlencode($value);
            $value = str_replace('%20', '_', $value);
            $tii[$key] = $value;
        }
    }
    //TODO need to check lengths of certain vars. - some cannot be under 5 or over 50.
    if (isset($tiisettings['turnitin_senduseremail']) && $tiisettings['turnitin_senduseremail']) {
        $tii['dis'] ='0'; //sets e-mail notification for users in tii system to enabled.
    } else {
        $tii['dis'] ='1'; //sets e-mail notification for users in tii system to disabled.
    }
    //munge e-mails if prefix is set.
    if (isset($tiisettings['turnitin_emailprefix'])) { //if email prefix is set
        if ($tii['uem'] <> $tiisettings['turnitin_email']) { //if email is not the global teacher.
            $tii['uem'] = $tiisettings['turnitin_emailprefix'] . $tii['uem']; //munge e-mail to prevent user access.
        }
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
        error("error trying to open Turnitin XML file!".$url);
    } else {
            //now do something with the XML file to check to see if this has worked!
        $xml = new SimpleXMLElement($fp);
        return $xml;
    }
}

function tii_post_data($tii, $file='') {
    $tiicomplete = tii_get_url($tii, 'array');

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
function tii_post_to_api($tii, $success, $action='GET', $file='', $savercode='true', $returncode=false) {
    $tiixml = '';
    if (isset($tii['diagnostic']) AND $tii['diagnostic']) { // if in diagnostic mode, print the url used.
        debugging(tii_get_url($tii));
        return true;
    } else {
        switch ($action) {
            case 'GET':
                $tiixml = tii_get_xml(tii_get_url($tii)); //get xml to work with
                break;
            case 'POST':
                if (!empty($file)) {
                    $tiixml = tii_post_data($tii, $file->fileinfo->filepath.$file->filename);
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
         $file->tiiscore = $tiixml->originalityscore[0];
         $file->tiicode = 'success';
         $count++;
         if (!update_record('tii_files', $file)) {
             debugging("update tii score failed");
         }
        return $tiixml->rcode[0];
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

               if (!empty($module->timedue) && !empty($module->preventlate) && ($module->timedue+(24 * 50 * 60) < time())) {
                   mtrace("a file from course $course->shortname assignment $module->name cannot be submitted as the timedue has passed and preventlate is set");
               } else {
                   if (!(isset($processedmodules[$moduletype][$module->id]) && $processedmodules[$moduletype][$module->id])) {
                       //first set up this assignment/assign the global teacher to this course.
                        $tii = array();
                        //set globals.
                        $tii['username'] = $tiisettings['turnitin_userid'];
                        $tii['uem']      = $tiisettings['turnitin_email'];
                        $tii['ufn']      = $tiisettings['turnitin_firstname'];
                        $tii['uln']      = $tiisettings['turnitin_lastname'];
                        $tii['uid']      = $tiisettings['turnitin_userid'];
                        $tii['utp']      = '2'; //2 = this user is an instructor
                        $tii['cid']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname; //course ID
                        $tii['ctl']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname; //Course title.  -this uses Course->id and shortname to ensure uniqueness.
                        //$tii['diagnostic'] = '1'; //debug only - uncomment when using in production.

                        $tii['fcmd'] = '2'; //when set to 2 the TII API should return XML
                        $tii['fid'] = '2'; // create class under the given account and assign above user as instructor (fid=2)
                        //$tii['diagnostic'] = '1';
                        $rcode = tii_post_to_api($tii, 21, 'GET','',false);
                        if ($rcode=='21' or $rcode=='20' or $rcode=='22') { //these rcodes signify that this assignment exists, or has been successfully updated.
                            //now create Assignment in Class
                            $tii['assignid'] = $tiisettings['turnitin_courseprefix']. '_'.$module->name.'_'.$module->id; //assignment ID - uses $returnid to ensure uniqueness
                            $tii['assign']   = $tiisettings['turnitin_courseprefix']. '_'.$module->name.'_'.$module->id; //assignment name stored in TII
                            $tii['fid']      = '4';
                            $tii['ptl']      = $course->id.$course->shortname; //paper title? - assname?
                            $tii['ptype']    = '2'; //filetype
                            $tii['pfn']      = $tii['ufn'];
                            $tii['pln']      = $tii['uln'];
                            $tii['dtstart']  = gmdate('Ymd', time()-86400); //need to fix this to use the assignment date.
                            if (!empty($module->timedue) && !empty($module->preventlate)) {
                                $tii['dtdue']    = gmdate('Ymd', $module->timedue+(24 * 60 * 60)); //set to 1 day in future from date due.
                            } else {
                                $tii['dtdue']    = gmdate('Ymd', time()+ (30 * 24 * 60 * 60)); //set to 30 days in future if not set by the module.
                            }
                            $tii['s_view_report'] = '1'; //allow students to view the full report.
                            //$tii['diagnostic'] = '1'; //debug only - uncomment when using in production.
                            $rcode = tii_post_to_api($tii, 41, 'POST','',false, true);
                            if ($rcode=='419') { //if assignment already exists then update it.
                                $tii['fcmd'] = '3'; //when set to 3 - it updates the course
                                $rcode = tii_post_to_api($tii, 42, 'POST','',false, true);
                            }    
                            if ($rcode=='41' or $rcode=='42') { //if assignment created/modified or already exists.
                                $processedmodules[$moduletype][$module->id] = true; //Only do the last 2 calls once per cron.
                                mtrace("Success getting user, Class and assignment");
                            } else {
                                mtrace("Error: could not create assignment in class TIICODE:".$rcode);
                            }
                        } else {
                            mtrace("Error: could not create class and assign global instructor TIICODE:".$rcode);
                        }
                   }
                   //now send the files.
                   //if this module and assignment have been created successfully, send the files to Turnitin!
                   if (!(isset($processedmodules[$moduletype][$module->id]) && $processedmodules[$moduletype][$module->id])) {
                       mtrace("could not send a file, as class and assignment could not be created");
                   } else {
                       $tii2 = array();
                       $tii2['username'] = $user->username;
                       $tii2['uem']      = $user->email;
                       $tii2['ufn']      = $user->firstname;
                       $tii2['uln']      = $user->lastname;
                       $tii2['uid']      = $user->username;
                       $tii2['utp']      = '1'; // 1= this is a student.
                       $tii2['cid']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname;
                       $tii2['ctl']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname;
                       $tii2['fcmd']     = '2'; //when set to 2 the tii api call returns XML
                       //$tii2['diagnostic'] = '1';
                       $tii2['fid']      = '1'; //set command. - create user and login student to Turnitin (fid=1)
                       if (tii_post_to_api($tii2, 11, 'GET', $file)) {
                           //now enrol user in class under the given account (fid=3)
                           $tii2['assignid'] = $tiisettings['turnitin_courseprefix']. '_'.$module->name.'_'.$module->id;
                           $tii2['assign']   = $tiisettings['turnitin_courseprefix']. '_'.$module->name.'_'.$module->id;
                           $tii2['fid']      = '3';
                           //$tii2['diagnostic'] = '1';
                           //bug with TII API replication - sometimes doesn't replicate from their master to slave
                           //quick enough for the next call and returns a 407. - use tii_post_handler to manage this.
                           if (tii_post_handler(407, $tii2, 31, 'GET', $file)) {
                               //TODO TII expects more than 100 characters in a submitted file - should probably check this?

                               //now submit this uploaded file to Tii! (fid=5)
                               $tii2['utp']     = '1'; //2 = instructor, 1= student.
                               $tii2['fid']     = '5';
                               $tii2['ptl']     = $file->filename; //paper title
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
           }
       }
       mtrace("sent ".$count." files");
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

               $tii['username'] = $user->username;
               $tii['uem']      = $user->email;
               $tii['ufn']      = $user->firstname;
               $tii['uln']      = $user->lastname;
               $tii['uid']      = $user->username;
               $tii['utp']      = '1'; // 1= this is a student.
               $tii['cid']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname;
               $tii['ctl']      = $tiisettings['turnitin_courseprefix'].$course->id.$course->shortname;
               $tii['fcmd']     = '2'; //when set to 2 the tii api call returns XML
               $tii['fid']      = '6';
               $tii['oid']      = $file->tii;

               $tiiscore = tii_post_to_api($tii, 61, 'GET', $file, false);
               if ($tiiscore<>'61') {
                   if ($tiiscore=='415') {
                       mtrace('similarity report not available yet for fileid:'.$file->id);
                   } else {
                       mtrace('getting similarity failed for fileid: '.$file->id. ' Error:'.$tiiscore);
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
            $tii['utp'] = '1'; //1 = this user is an student
        } else {
            $tii['username'] = $tiisettings['turnitin_userid'];
            $tii['uem']      = $tiisettings['turnitin_email'];
            $tii['ufn']      = $tiisettings['turnitin_firstname'];
            $tii['uln']      = $tiisettings['turnitin_lastname'];
            $tii['uid']      = $tiisettings['turnitin_userid'];
            $tii['utp']      = '2'; //2 = this user is an instructor
        }
        $tii['cid']      = $tiisettings['turnitin_courseprefix'].$file->course.$courseshortname; //course ID //may need to include sitename in this to allow more than 1 moodle site with the same TII account to use TII API
        $tii['ctl']      = $tiisettings['turnitin_courseprefix'].$file->course.$courseshortname; //Course title.  -this uses Course->id and shortname to ensure uniqueness.
        $tii['fcmd'] = '1'; //when set to 2 the tii api call returns XML
        $tii['fid'] = '6';
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
function tii_get_css_rank ($score) {
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
?>
