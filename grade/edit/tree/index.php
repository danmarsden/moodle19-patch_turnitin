<?php  // $Id$

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/lib.php'; // for preferences

$courseid = required_param('id', PARAM_INT);
$action   = optional_param('action', 0, PARAM_ALPHA);
$eid      = optional_param('eid', 0, PARAM_ALPHANUM);


/// Make sure they can even access this course

if (!$course = get_record('course', 'id', $courseid)) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/grade:manage', $context);

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'edit', 'plugin'=>'tree', 'courseid'=>$courseid));
$returnurl = $gpr->get_return_url(null);

//first make sure we have proper final grades - we need it for locking changes
grade_regrade_final_grades($courseid);

// get the grading tree object
// note: total must be first for moving to work correctly, if you want it last moving code must be rewritten!
$gtree = new grade_tree($courseid, false, false);

if (empty($eid)) {
    $element = null;
    $object  = null;

} else {
    if (!$element = $gtree->locate_element($eid)) {
        error('Incorrect element id!', $returnurl);
    }
    $object = $element['object'];
}

$switch = grade_get_setting($course->id, 'aggregationposition', $CFG->grade_aggregationposition);

$strgrades             = get_string('grades');
$strgraderreport       = get_string('graderreport', 'grades');
$strcategoriesedit     = get_string('categoriesedit', 'grades');
$strcategoriesanditems = get_string('categoriesanditems', 'grades');

$navigation = grade_build_nav(__FILE__, $strcategoriesanditems, array('courseid' => $courseid));
$moving = false;

switch ($action) {
    case 'delete':
        if ($eid) {
            if (!element_deletable($element)) {
                // no deleting of external activities - they would be recreated anyway!
                // exception is activity without grading or misconfigured activities
                break;
            }
            $confirm = optional_param('confirm', 0, PARAM_BOOL);

            if ($confirm and confirm_sesskey()) {
                $object->delete('grade/report/grader/category');
                redirect($returnurl);

            } else {
                print_header_simple($strgrades . ': ' . $strgraderreport, ': ' . $strcategoriesedit, $navigation, '', '', true, '', navmenu($course));
                $strdeletecheckfull = get_string('deletecheck', '', $object->get_name());
                $optionsyes = array('eid'=>$eid, 'confirm'=>1, 'sesskey'=>sesskey(), 'id'=>$course->id, 'action'=>'delete');
                $optionsno  = array('id'=>$course->id);
                notice_yesno($strdeletecheckfull, 'index.php', 'index.php', $optionsyes, $optionsno, 'post', 'get');
                print_footer($course);
                die;
            }
        }
        break;

    case 'autosort':
        //TODO: implement autosorting based on order of mods on course page, categories first, manual items last
        break;

    case 'synclegacy':
        grade_grab_legacy_grades($course->id);
        redirect($returnurl);

    case 'move':
        if ($eid and confirm_sesskey()) {
            $moveafter = required_param('moveafter', PARAM_ALPHANUM);
            if(!$after_el = $gtree->locate_element($moveafter)) {
                error('Incorect element id in moveafter', $returnurl);
            }
            $after = $after_el['object'];
            $parent = $after->get_parent_category();
            $sortorder = $after->get_sortorder();

            $object->set_parent($parent->id);
            $object->move_after_sortorder($sortorder);

            redirect($returnurl);
        }
        break;

    case 'moveselect':
        if ($eid and confirm_sesskey()) {
            $moving = $eid;
        }
        break;

    default:
        break;
}

print_header_simple($strgrades . ': ' . $strgraderreport, ': ' . $strcategoriesedit, $navigation, '', '', true, '', navmenu($course));

/// Print the plugin selector at the top
print_grade_plugin_selector($courseid, 'edit', 'tree');

print_heading(get_string('categoriesedit', 'grades'));


print_box_start('gradetreebox generalbox');
echo '<ul id="grade_tree">';
print_grade_tree($gtree, $gtree->top_element, $moving, $gpr, $switch);
echo '</ul>';
print_box_end();

echo '<div class="buttons">';
if ($moving) {
    print_single_button('index.php', array('id'=>$course->id), get_string('cancel'), 'get');
} else {
    print_single_button('category.php', array('courseid'=>$course->id), get_string('addcategory', 'grades'), 'get');
    print_single_button('item.php', array('courseid'=>$course->id), get_string('additem', 'grades'), 'get');
    if (!empty($CFG->enableoutcomes)) {
        print_single_button('outcomeitem.php', array('courseid'=>$course->id), get_string('addoutcomeitem', 'grades'), 'get');
    }
    //print_single_button('index.php', array('id'=>$course->id, 'action'=>'autosort'), get_string('autosort', 'grades'), 'get');
    echo "<br /><br />";
    print_single_button('index.php', array('id'=>$course->id, 'action'=>'synclegacy'), get_string('synclegacygrades', 'grades'), 'get');
    helpbutton('synclegacygrades', get_string('synclegacygrades', 'grades'), 'grade');
}
echo '</div>';
print_footer($course);
die;

/**
 * TODO document
 */
function print_grade_tree(&$gtree, $element, $moving, &$gpr, $switch, $switchedlast=false) {
    global $CFG, $COURSE;

/// fetch needed strings
    $strmove     = get_string('move');
    $strmovehere = get_string('movehere');
    $strdelete   = get_string('delete');

    $object = $element['object'];
    $eid    = $element['eid'];

    $header = $gtree->get_element_header($element, true, true, true);

    if ($object->is_hidden()) {
        $header = '<span class="dimmed_text">'.$header.'</span>';
    }

/// prepare actions
    $actions = $gtree->get_edit_icon($element, $gpr);
    $actions .= $gtree->get_calculation_icon($element, $gpr);

    if ($element['type'] == 'item' or ($element['type'] == 'category' and $element['depth'] > 1)) {
        if (element_deletable($element)) {
            $actions .= '<a href="index.php?id='.$COURSE->id.'&amp;action=delete&amp;eid='
                     . $eid.'&amp;sesskey='.sesskey().'"><img src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="'
                     . $strdelete.'" title="'.$strdelete.'"/></a>';
        }
        $actions .= '<a href="index.php?id='.$COURSE->id.'&amp;action=moveselect&amp;eid='
                 . $eid.'&amp;sesskey='.sesskey().'"><img src="'.$CFG->pixpath.'/t/move.gif" class="iconsmall" alt="'
                 . $strmove.'" title="'.$strmove.'"/></a>';
    }

    $actions .= $gtree->get_hiding_icon($element, $gpr);
    $actions .= $gtree->get_locking_icon($element, $gpr);

/// prepare move target if needed
    $last = '';
    $catcourseitem = ($element['type'] == 'courseitem' or $element['type'] == 'categoryitem');
    $moveto = '';
    if ($moving) {
        $actions = ''; // no action icons when moving
        $moveto = '<li><a href="index.php?id='.$COURSE->id.'&amp;action=move&amp;eid='.$moving.'&amp;moveafter='
                . $eid.'&amp;sesskey='.sesskey().'"><img class="movetarget" src="'.$CFG->wwwroot.'/pix/movehere.gif" alt="'
                . $strmovehere.'" title="'.$strmovehere.'" /></a></li>';
    }

/// print the list items now
    if ($moving == $eid) {
        // do not diplay children
        echo '<li class="'.$element['type'].' moving">'.$header.'('.get_string('move').')</li>';

    } else if ($element['type'] != 'category') {
        if ($catcourseitem and $switch) {
            if ($switchedlast) {
                echo '<li class="'.$element['type'].'">'.$header.$actions.'</li>';
            } else {
                echo $moveto;
            }
        } else {
            echo '<li class="'.$element['type'].'">'.$header.$actions.'</li>'.$moveto;
        }

    } else {
        echo '<li class="'.$element['type'].'">'.$header.$actions;
        echo '<ul class="catlevel'.$element['depth'].'">';
        $last = null;
        foreach($element['children'] as $child_el) {
            if ($switch and empty($last)) {
                $last = $child_el;
            }
            print_grade_tree($gtree, $child_el, $moving, $gpr, $switch);
        }
        if ($last) {
            print_grade_tree($gtree, $last, $moving, $gpr, $switch, true);
        }
        echo '</ul></li>';
        if ($element['depth'] > 1) {
            echo $moveto; // can not move after the top category
        }
    }
}

function element_deletable($element) {
    global $COURSE;

    if ($element['type'] != 'item') {
        return true;
    }

    $grade_item = $element['object'];

    if ($grade_item->itemtype != 'mod' or $grade_item->is_outcome_item() or $grade_item->gradetype == GRADE_TYPE_NONE) {
        return true;
    }

    $modinfo = get_fast_modinfo($COURSE);
    if (!isset($modinfo->instances[$grade_item->itemmodule][$grade_item->iteminstance])) {
        // module does not exist
        return true;
    }

    return false;
}

?>
