<?php
 //allows the admin to configure turnitin stuff

    require_once(dirname(dirname(__FILE__)) . '/config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/turnitinlib.php');

    require_login();
    admin_externalpage_setup('turnitin_errors');

    $context = get_context_instance(CONTEXT_SYSTEM);

    $fileid = optional_param('fileid',0,PARAM_INT);
    $resetuser = optional_param('reset',0,PARAM_INT);
    $page = optional_param('page', 0, PARAM_INT);

    admin_externalpage_print_header();
    print_heading(get_string('turnitinerrors', 'turnitin'));
    //run check to tidy up bad 31 codes with deleted files.
    $files = get_records('tii_files','tiicode','31');
    if (!empty($files)) {
        foreach($files as $file) {
           //set globals.
           $user = get_record('user', 'id', $file->userid);
           $course = get_record('course', 'id', $file->course);
           $moduletype = get_field('modules','name', 'id', $file->module);
           $module = get_record($moduletype, 'id', $file->instance);

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
               notify("a file from $course->shortname, assignment: $module->name, user:$user->username doesn't exist -deleting tii_files entry(it was probably deleted by the student)");
               delete_records('tii_files', 'id', $file->id);
           }
        }
    }
    print_box(get_string('tiiexplainerrors', 'turnitin'));

    if ($resetuser==1 && $fileid) {
        $tfile = get_record('tii_files', 'id', $fileid);
        $tfile->tiicode = 'pending';
        if (update_record('tii_files', $tfile)) {
            notify("File reset");
        }
    } elseif ($resetuser==2) {
        $sql = "tiicode <>'success' AND tiicode<>'pending' AND tiicode<>'51'";
        $tiifiles = get_records_select('tii_files', $sql);
        foreach($tiifiles as $tiifile) {
            $tiifile->tiicode = 'pending';
            if (update_record('tii_files', $tiifile)) {
                notify("File reset");
            }
        }
    }

        $tablecolumns = array('name', 'course', 'file', 'status');
        $tableheaders = array(get_string('name'),
                              get_string('course'),
                              get_string('file'),
                              get_string('status'));

        require_once($CFG->libdir.'/tablelib.php');
        $table = new flexible_table('turnitin-errors');

        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($CFG->wwwroot.'/admin/turnitin_errors.php');

        $table->sortable(false);
        $table->collapsible(true);
        $table->initialbars(false);

        $table->column_suppress('fullname');

        $table->column_class('name', 'name');
        $table->column_class('course', 'course');
        $table->column_class('file', 'file');
        $table->column_class('status', 'status');

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'attempts');
        $table->set_attribute('class', 'submissions');
        $table->set_attribute('width', '100%');
        //$table->set_attribute('align', 'center');

        $table->no_sorting('name');
        $table->no_sorting('course');
        $table->no_sorting('file');
        $table->no_sorting('status');

        $table->setup();

        $sql = "tiicode <>'success' AND tiicode<>'pending' AND tiicode<>'51'";
        $tiifiles = get_records_select('tii_files', $sql);
        $pagesize = 15;
        $table->pagesize($pagesize, count($tiifiles));
        $start = $page * $pagesize;
        $pagtiifiles = array_slice($tiifiles, $start, $pagesize);
        if (!empty($pagtiifiles)) {
            foreach($pagtiifiles as $tiifile) {
                //should tidy these up - shouldn't need to call so often
                $reset = $tiifile->tiicode.'&nbsp;<a href="turnitin_errors.php?reset=1&fileid='.$tiifile->id.'">reset</a>';
                $user = get_record('user', 'id', $tiifile->userid);
                $course = get_record('course', 'id', $tiifile->course);
                $row = array(fullname($user), $course->shortname, $tiifile->filename, $reset);

                $table->add_data($row);
            }
        }

        $table->print_html();
        if (!empty($tiifiles)) {
            echo '<br/><br/><div align="center">';
            $options["reset"] = "2";
            print_single_button("turnitin_errors.php", $options, get_string("resetall", "turnitin"));
            echo '</div>';
        }
        admin_externalpage_print_footer();
?>