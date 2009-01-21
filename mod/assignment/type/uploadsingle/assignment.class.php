<?php // $Id$

/**
 * Extend the base assignment class for assignments where you upload a single file
 *
 */
class assignment_uploadsingle extends assignment_base {


    function print_student_answer($userid, $return=false){
           global $CFG, $COURSE, $USER;

        $filearea = $this->file_area_name($userid);
        $submission = $this->get_submission($userid);
        $output = '';

        if ($basedir = $this->file_area($userid)) {
            if ($files = get_directory_list($basedir)) {
                require_once($CFG->libdir.'/filelib.php');
                foreach ($files as $key => $file) {

                    $icon = mimeinfo('icon', $file);
                    $ffurl = get_file_url("$filearea/$file");

                    //died right here
                    //require_once($ffurl);
                    $output = '<img src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                            '<a href="'.$ffurl.'" >'.$file.'</a>';
                   //now get TII stuff if enabled
                       $moduleid = get_field('modules', 'id','name','assignment');
                       $assignment = get_record('assignment', 'id', $submission->assignment);

                       if (isset($assignment->use_tii_submission) && $assignment->use_tii_submission) {

                           if (has_capability('moodle/local:viewsimilarityscore', $this->context)) {
                               include_once($CFG->libdir.'/turnitinlib.php');
                               if ($tiisettings = tii_get_settings()) {
                                   $tiifile = get_record_select('tii_files', "course='".$COURSE->id.
                                                            "' AND module='".get_field('modules', 'id','name','assignment').
                                                            "' AND instance='".$submission->assignment.
                                                            "' AND userid='".$userid.
                                                            "' AND filename='".$file.
                                                            "' AND tiicode<>'pending' AND tiicode<>'51'");
                                   if (isset($tiifile->tiiscore) && $tiifile->tiicode=='success') {
                                        if (has_capability('moodle/local:viewfullreport', $this->context)) {
                                            $output .= '&nbsp;<a class="turnitinreport" href="'.tii_get_report_link($tiifile).'" target="_blank">'.get_string('similarity', 'turnitin').':</a>'.$tiifile->tiiscore.'%';
                                        } else {
                                            $output .= '&nbsp;'.get_string('similarity', 'turnitin').':'.$tiifile->tiiscore.'%';
                                        }
                                   } elseif(isset($tiifile->tiicode)) {
                                       $output .= get_tii_error($tiifile->tiicode);
                                   }
                               }
                           }
                       }
                       $output .='<br/>';

                }
            }
        }

        $output = '<div class="files">'.$output.'</div>';
        return $output;
    }

    function assignment_uploadsingle($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'uploadsingle';
    }

    function view() {

        global $USER;

        $context = get_context_instance(CONTEXT_MODULE,$this->cm->id);
        require_capability('mod/assignment:view', $context);

        add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

        $this->view_header();

        $this->view_intro();

        $this->view_dates();

        $filecount = $this->count_user_files($USER->id);

        if ($submission = $this->get_submission()) {
            if ($submission->timemarked) {
                $this->view_feedback();
            }
            if ($filecount) {
                print_simple_box($this->print_user_files($USER->id, true), 'center');
            }
        }

        if (has_capability('mod/assignment:submit', $context)  && $this->isopen() && (!$filecount || $this->assignment->resubmit || !$submission->timemarked)) {
            $this->view_upload_form();
        }

        $this->view_footer();
    }


    function view_upload_form() {
        global $CFG;
        $struploadafile = get_string("uploadafile");

        $maxbytes = $this->assignment->maxbytes == 0 ? $this->course->maxbytes : $this->assignment->maxbytes;
        $strmaxsize = get_string('maxsize', '', display_size($maxbytes));

        echo '<div style="text-align:center">';
        echo '<form enctype="multipart/form-data" method="post" '.
             "action=\"$CFG->wwwroot/mod/assignment/upload.php\">";
        echo '<fieldset class="invisiblefieldset">';
        echo "<p>$struploadafile ($strmaxsize)</p>";
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        require_once($CFG->libdir.'/uploadlib.php');
        upload_print_form_fragment(1,array('newfile'),false,null,0,$this->assignment->maxbytes,false);
        echo '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';
    }


    function upload() {

        global $CFG, $USER;

        require_capability('mod/assignment:submit', get_context_instance(CONTEXT_MODULE, $this->cm->id));

        $this->view_header(get_string('upload'));

        $filecount = $this->count_user_files($USER->id);
        $submission = $this->get_submission($USER->id);
        if ($this->isopen() && (!$filecount || $this->assignment->resubmit || !$submission->timemarked)) {
            if ($submission = $this->get_submission($USER->id)) {
                //TODO: change later to ">= 0", to prevent resubmission when graded 0
                if (($submission->grade > 0) and !$this->assignment->resubmit) {
                    notify(get_string('alreadygraded', 'assignment'));
                }
            }

            $dir = $this->file_area_name($USER->id);

            require_once($CFG->dirroot.'/lib/uploadlib.php');
            $um = new upload_manager('newfile',true,false,$this->course,false,$this->assignment->maxbytes);
            if ($um->process_file_uploads($dir)) {
                $newfile_name = $um->get_new_filename();
                if ($submission) {
                    $submission->timemodified = time();
                    $submission->numfiles     = 1;
                    $submission->submissioncomment = addslashes($submission->submissioncomment);
                    unset($submission->data1);  // Don't need to update this.
                    unset($submission->data2);  // Don't need to update this.
                    if (update_record("assignment_submissions", $submission)) {
                        add_to_log($this->course->id, 'assignment', 'upload',
                                'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                        $submission = $this->get_submission($USER->id);
                        $this->update_grade($submission);
                        $this->email_teachers($submission);
                        print_heading(get_string('uploadedfile'));

                        $this->save_tii_file($um->get_new_filename());

                    } else {
                        notify(get_string("uploadfailnoupdate", "assignment"));
                    }
                } else {
                    $newsubmission = $this->prepare_new_submission($USER->id);
                    $newsubmission->timemodified = time();
                    $newsubmission->numfiles = 1;
                    if (insert_record('assignment_submissions', $newsubmission)) {
                        add_to_log($this->course->id, 'assignment', 'upload',
                                'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                        $submission = $this->get_submission($USER->id);
                        $this->update_grade($submission);
                        $this->email_teachers($newsubmission);
                        print_heading(get_string('uploadedfile'));
                        $this->save_tii_file($um->get_new_filename());
                    } else {
                        notify(get_string("uploadnotregistered", "assignment", $newfile_name) );
                    }
                }
            }
        } else {
            notify(get_string("uploaderror", "assignment")); //submitting not allowed!
        }

        print_continue('view.php?id='.$this->cm->id);

        $this->view_footer();
    }

    function setup_elements(&$mform) {
        global $CFG, $COURSE;

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'resubmit', get_string("allowresubmit", "assignment"), $ynoptions);
        $mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));
        $mform->setDefault('resubmit', 0);

        $mform->addElement('select', 'emailteachers', get_string("emailteachers", "assignment"), $ynoptions);
        $mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
        $mform->setDefault('emailteachers', 0);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit') . ' ('.display_size($COURSE->maxbytes).')';
        $mform->addElement('select', 'maxbytes', get_string('maximumsize', 'assignment'), $choices);
        $mform->setDefault('maxbytes', $CFG->assignment_maxbytes);

        $course_context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $tii = get_field('config_plugins', 'value', 'name', 'turnitin_use');
        if (isset($tii) && $tii && isset($CFG->assignment_use_tii_submission) && $CFG->assignment_use_tii_submission) { //if tii enabled, allow teachers to elect to use it.
            if (has_capability('moodle/local:enableturnitin', $course_context)) {
                $mform->addElement('select', 'use_tii_submission', get_string("usetii", "turnitin"), $ynoptions);
                $mform->setDefault('use_tii_submission', $CFG->assignment_turnitin_default_use);

                $tiioptions = array();
                $tiioptions[0] = get_string("never");
                $tiioptions[1] = get_string("always");
                $tiioptions[2] = get_string("showwhenclosed", "turnitin");

                $mform->addElement('select', 'tii_show_student_score', get_string("showstudentsscore", "turnitin"), $tiioptions);
                $mform->setDefault('tii_show_student_score', $CFG->assignment_turnitin_default_showscore);

                $mform->addElement('select', 'tii_show_student_report', get_string("showstudentsreport", "turnitin"), $tiioptions);
                $mform->setDefault('tii_show_student_report', $CFG->assignment_turnitin_default_showreport);

            } else {
                //add some hidden vars here.
                $mform->addElement('hidden', 'use_tii_submission', get_string("usetii", "turnitin"));
                $mform->setDefault('use_tii_submission', $CFG->assignment_turnitin_default_use);
                $mform->addElement('hidden', 'tii_show_student_score', get_string("showstudentsscore", "turnitin"));
                $mform->setDefault('tii_show_student_score', $CFG->assignment_turnitin_default_showscore);
                $mform->addElement('hidden', 'tii_show_student_report', get_string("showstudentsreport", "turnitin"));
                $mform->setDefault('tii_show_student_report', $CFG->assignment_turnitin_default_showreport);
            }
        }
    }

    function save_tii_file($filename) {
        global $USER;
        if (isset($this->assignment->use_tii_submission) && $this->assignment->use_tii_submission) {
            //now update or insert record into tii_files
            if ($tii_file = get_record_select('tii_files', "course='".$this->course->id.
                            "' AND module='".$this->cm->module.
                            "' AND instance='".$this->assignment->id.
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
                $tii_file->course = $this->course->id;
                $tii_file->module = $this->cm->module;
                $tii_file->instance = $this->assignment->id;
                $tii_file->userid = $USER->id;
                $tii_file->filename = $filename;
                $tii_file->tiicode = 'pending';
                if (!insert_record('tii_files', $tii_file)) {
                    debugging("insert into tii_files failed");
                }
           }
       }
    }
}

?>
