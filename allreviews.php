<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block vlearn_program is defined here.
 *
 * @package     block_vlearn_learners
 * @copyright   2022 Deependra Kumar Singh <deependra.singh@herovired.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once 'lib.php';

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
global $CFG, $DB, $USER, $OUTPUT, $PAGE;
require_login();
// Page configurations.
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('allreviews', 'block_vlearn_reviews'));
$PAGE->set_heading(get_string('allreviews', 'block_vlearn_reviews'));
$url = new moodle_url('/blocks/vlearn_reviews/allreviews.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);

echo $OUTPUT->header();

$homeurl = new \moodle_url('/my');
echo '<div class="back_arrow_text breadcrumb-forall-pages mobile-none">
            <span class="d-flex justify-content-start align-items-center ">
                <a class="accent-blue font-13 d-block font-weight-normal" href="' . $homeurl . '" title="">Home</a>
                <i class="fa fa-angle-right mx-2 d-block font-12"></i>
                <a class="black-base font-13 d-block font-weight-normal" title="">Assignment To Review</a>
            </span>
    </div>';
echo ' <div class="back_arrow_btn breadcrumb-forall-pages desktop-none mobile-block mt-0">
                    <span class="d-flex justify-content-start align-items-center ">
                        <i class="fa fa-angle-left mr-1 black-base d-block font-18 font-w-600" style="color: #333 !important;"></i>
                        <a class="black-base font-16 font-w-600 d-block" href="' . $homeurl . '" title>
                        Back  
                        </a>
                    </span>
            </div>';
echo "<div class='all-toreview-fullview-page'>";
echo $OUTPUT->heading('Assignment To Review');

// Learning path based on tenant 
$allparams = array("sesskey" => sesskey(), "dataformat" => 'csv');
$sitecontext = context_system::instance();
$companyreviews = new \block_vlearn_reviews\vlearn_reviews($sitecontext);
$students = $companyreviews->get_all_program_students();
$total_to_reviews = $companyreviews->get_program_reviews(0, 0);
$reviews = $companyreviews->get_program_reviews(0, $noofreviews);

$data = array();
$i = 0;
$total_students_program = 0;
$url_params = array();
foreach ($reviews as $key => $activity) {
//    $data['activity'][$i]['course_name'] = $activity->coursename;
//    $data['activity'][$i]['name'] = $activity->activityname;
//    $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
//    $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/vlearn_reviews/pix/assignment-blue.svg";
//    $submit = $companyreviews->get_assign_submission($activity->instance, $students[$activity->programid]);
//    $data['activity'][$i]['submit'] = $submit . "/" . $total_students;
//    $submission = ($submit - $companyreviews->get_assign_review($activity->instance, $students[$activity->programid]));
//    $data['activity'][$i]['review'] = $submission > 0 ? $submission : 0;
//    $data['activity'][$i]['pendingclass'] = $submission > 0 ? '' : "disabled = true";
////        $data['activity'][$i]['type'] = $activity->moduletype;
//    $grade_params = array('id' => $activity->id, 'action' => 'grading');
//    $data['activity'][$i]['gradeurl'] = new \moodle_url('/mod/assign/view.php', $grade_params);
//    $completedreviews = $companyreviews->get_assign_review($activity->instance, $students[$activity->programid]);
//    $data['activity'][$i]['completedreview'] = !empty($completedreviews) ? $completedreviews : 0;
//    if ($activity->gradingduedate > 0) {
//        $data['activity'][$i]['gradingduedate'] = userdate($activity->gradingduedate, '%d %b %Y');
//        $data['activity'][$i]['gradingduetime'] = userdate($activity->gradingduedate, '%H:%M');
//    } else
//        $data['activity'][$i]['gradingduedateempty'] = '--';
//    $remaningday = (int) (($activity->gradingduedate - time()) / 86400);
//    $data['activity'][$i]['remaningdays'] = $remaningday;
//    $data['activity'][$i]['instance'] = $activity->instance;
//    $data['activity'][$i]['programid'] = $activity->programid;
//    $data['activity'][$i]['cmid'] = $activity->id;
//    if ($remaningday < 7 && $remaningday > 0) {
//        $data['activity'][$i]['remaningdays'] = abs($remaningday) . " days remaining";
//        $data['activity'][$i]['remaining_text'] = "text-success";
//        $data['activity'][$i]['daystyle'] = "sample";
//    } else if ($remaningday < 0) {
//        $data['activity'][$i]['remaningdays'] = abs($remaningday) . " days overdue";
//    } else {
//        $data['activity'][$i]['remaningdays'] = $remaningday . " days remaining";
//        $data['activity'][$i]['daystyle'] = "style='color: #E86E15'";
//    }
//    if ($activity->gradepass > 0) {
//        $data['activity'][$i]['assigntype'] = 'mandatory';
//    }
//    $total_students_program += $total_students;
//    $i++;

    $data['activity'][$i]['course_name'] = $activity->coursename;
    $data['activity'][$i]['name'] = $activity->activityname;
    $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
    $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/vlearn_reviews/pix/assignment-blue.svg";
    $user = core_user::get_user($activity->userid);
    $data['activity'][$i]['studentname'] = "$user->firstname $user->lastname";
    $data['activity'][$i]['grade'] = $activity->grade;
    $data['activity'][$i]['feedback'] = $activity->feedbackdesc;
    $data['activity'][$i]['feedbackdesc'] = strlen($activity->feedbackdesc < 10)? substr($activity->feedbackdesc, 0, 10)."..." : $activity->feedbackdesc;
    $grade_params = array('id' => $activity->cmid, 'action' => 'grader','userid'=>$activity->userid);
    $data['activity'][$i]['gradeurl'] =  new \moodle_url('/mod/assign/view.php', $grade_params);
    $i++;
}

//$total_reviews = $data['to_reviews'] = count($total_to_reviews);

$output = "";
//$programs = $companyreviews->get_trainer_programs_list();
//list($programlist, $courses) = get_filters($programs);
//$output .= $OUTPUT->render_from_template('block_vlearn_reviews/filters', array());
//$output .= html_writer::start_div('allreviews-display');
//$data['downloadurl'] = new \moodle_url('/blocks/vlearn_reviews/download/download_allreviews.php', $allparams);
//
//$output .= $OUTPUT->render_from_template('block_vlearn_reviews/allreviews_faculty', $data);
    $output .= $OUTPUT->render_from_template('block_vlearn_reviews/allreviews', $data);
//$url = new moodle_url('/blocks/vlearn_reviews/allreviews.php', $url_params);
//$output .= html_writer::start_div('pagination-nav-filter');
//$output .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
//$output .= html_writer::end_div();
//$output .= html_writer::end_div();
echo $output;
$type = 'custom-allreviews';
$PAGE->requires->js_call_amd('block_content_approval/cleavertab_data', 'init', array(SITEID, $type));
echo "</div>";
echo $OUTPUT->footer();

