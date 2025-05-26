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

namespace block_vlearn_reviews;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use moodle_url;
use context_system;
use html_writer;

class external extends external_api {

    /**
     * defines parameters to be passed in ws request
     */
    public static function get_all_reviews_parameters() {
        return new external_function_parameters(
                array(
            'program' => new external_value(PARAM_RAW, 'Program name', VALUE_OPTIONAL),
            'cid' => new external_value(PARAM_TEXT, 'Course id name', VALUE_OPTIONAL),
            'type' => new external_value(PARAM_TEXT, 'Activity type', VALUE_OPTIONAL),
            'page' => new external_value(PARAM_INT, 'The page', VALUE_OPTIONAL),
            'duesorting' => new external_value(PARAM_TEXT, 'The page', VALUE_OPTIONAL),
//            'duesort' => new external_value(PARAM_INT, 'The page', VALUE_OPTIONAL),
                )
        );
    }

    /**
     * Return the learning path info
     * @return array Learning path array
     */
    public static function get_all_reviews($program, $cid, $type, $page = 0, $duesorting) {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . '/local/iomad/lib/iomad.php';
        require_once $CFG->dirroot . '/blocks/vlearn_reviews/lib.php';
        $params = self::validate_parameters(self::get_all_reviews_parameters(),
                        array('program' => $program, 'cid' => $cid, 'type' => $type, 'page' => $page, 'duesorting' => $duesorting));
        $perpage = 10;
        $page = $params['page'];
        $sitecontex = \context_system::instance();
        $PAGE->set_context($sitecontex);
        $where = '';
        $allparams = array("sesskey" => sesskey(), "dataformat" => 'csv');
        if (!empty($params['activityname'])) {
            $where .= " AND a.name LIKE '%" . $params['activityname'] . "%'";
            $allparams['activityname'] = $params['activityname'];
        }

        if (!empty($params['program'])) {
            $where .= " AND lp.id = " . $params['program'];
        }

        if (!empty($params['cid'])) {
            $where .= " AND  c.id = " . $params['cid'];
        }

        if (!empty($params['type'])) {
            $where .= $params['type'] == 1 ? " AND gi.gradepass > 0" : " AND gi.gradepass <= 0";
        }
        $sort = " a.gradingduedate ASC ";
        if (!empty($params['duesorting']) && $params['duesorting'] == "desc") {
            $sort = " a.gradingduedate DESC";
        }

        $sitecontext = context_system::instance();
        $companyid = \iomad::get_my_companyid($sitecontext, false);
        $companyreviews = new \block_vlearn_reviews\vlearn_reviews($companyid, $sitecontext);
        $students = $companyreviews->get_all_program_students();
        $total_to_reviews = $companyreviews->get_program_reviews(0, 0, $where);
        $reviews = $companyreviews->get_program_reviews($page, $perpage, $where, $sort);

        $data = array();
        $i = 0;
        $total_students_program = 0;
        $url_params = array();
        if (!empty($reviews)) {
            foreach ($reviews as $key => $activity) {
                $total_students = $data['activity'][$i]['out_of'] = count($students[$activity->programid]);
                $data['activity'][$i]['program_name'] = $activity->programname;
                $data['activity'][$i]['course_name'] = $activity->coursename;
                $data['activity'][$i]['name'] = $activity->activityname;
                $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
                $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/vlearn_reviews/pix/assignment-blue.svg";
                $submit = $companyreviews->get_assign_submission($activity->instance, $students[$activity->programid]);
                $data['activity'][$i]['submit'] = $submit . "/" . $total_students;
                $submission = ($submit - $companyreviews->get_assign_review($activity->instance, $students[$activity->programid]));
                $data['activity'][$i]['review'] = $submission > 0 ? $submission : 0;
                $data['activity'][$i]['pendingclass'] = $submission > 0 ? '' : "disabled = true";
                //        $data['activity'][$i]['type'] = $activity->moduletype;
                $grade_params = array('id' => $activity->id, 'action' => 'grading');
                $data['activity'][$i]['gradeurl'] = new \moodle_url('/mod/assign/view.php', $grade_params);
                $completedreviews = $companyreviews->get_assign_review($activity->instance, $students[$activity->programid]);
                $data['activity'][$i]['completedreview'] = !empty($completedreviews) ? $completedreviews : 0;

                if ($activity->gradingduedate > 0) {
                    $data['activity'][$i]['gradingduedate'] = userdate($activity->gradingduedate, '%d %b %Y');
                    $data['activity'][$i]['gradingduetime'] = userdate($activity->gradingduedate, '%H:%M');
                } else
                    $data['activity'][$i]['gradingduedateempty'] = '--';
                $remaningday = (int) (($activity->gradingduedate - time()) / 86400);
                $data['activity'][$i]['remaningdays'] = $remaningday;
                $data['activity'][$i]['instance'] = $activity->instance;
                $data['activity'][$i]['programid'] = $activity->programid;
                $data['activity'][$i]['cmid'] = $activity->id;
                if ($remaningday < 7 && $remaningday > 0) {
                    $data['activity'][$i]['remaningdays'] = abs($remaningday) . " days remaining";
                    $data['activity'][$i]['remaining_text'] = "text-success";
                    $data['activity'][$i]['daystyle'] = "sample";
                } else if ($remaningday < 0) {
                    $data['activity'][$i]['remaningdays'] = abs($remaningday) . " days overdue";
                } else {
                    $data['activity'][$i]['remaningdays'] = $remaningday . " days remaining";
                    $data['activity'][$i]['daystyle'] = "style='color: #E86E15'";
                }
                if ($activity->gradepass > 0) {
                    $data['activity'][$i]['assigntype'] = 'mandatory';
                }
                $total_students_program += $total_students;
                $i++;
            }

            $total_reviews = $data['to_reviews'] = count($total_to_reviews);
//            $data['downloadurl'] = new \moodle_url('/blocks/vlearn_reviews/download/download_allreviews.php', $allparams);
            $pagination = '';
            if (!empty($params['duesorting'])) {
                $out .= $OUTPUT->render_from_template('block_vlearn_reviews/allreviews_faculty_sort', $data);
                $url = new moodle_url('/blocks/vlearn_reviews/allreviews.php', $url_params);
//            $pagination .= html_writer::start_div('pagination-nav-filter');
                $pagination .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
//             $pagination .= html_writer::end_div();
            } else {
                $out .= $OUTPUT->render_from_template('block_vlearn_reviews/allreviews_faculty', $data);
                $url = new moodle_url('/blocks/vlearn_reviews/allreviews.php', $url_params);
                $out .= html_writer::start_div('pagination-nav-filter');
                $out .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
                $out .= html_writer::end_div();
            }
        } else {
            $out = html_writer::div(get_string('nothingtodisplay', 'block_vlearn_reviews'), 'alert alert-info mt-3');
        }
        $html = array();
        if (!empty($params['program'])) {
            $html['options'] = course_filter($params['program'], $params['cid']);
        }
        $html['displayhtml'] = $out;
        $html['pagedata'] = $pagination;
        return $html;
    }

    /**
     * returns leaders info in json format
     */
    public static function get_all_reviews_returns() {
        return $data = new external_single_structure([
            'displayhtml' => new external_value(PARAM_RAW, 'html'),
            'options' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL),
            'pagedata' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL)
        ]);
    }

    /**
     * defines parameters to be passed in ws request
     */
    public static function get_pending_submissions_parameters() {
        return new external_function_parameters(
                array(
            'instanceid' => new external_value(PARAM_INT, 'Module Instance id', VALUE_OPTIONAL),
            'programid' => new external_value(PARAM_INT, 'Program name', VALUE_OPTIONAL),
            'cmid' => new external_value(PARAM_INT, 'Course module id', VALUE_OPTIONAL)
                )
        );
    }

    /**
     * Return the learning path info
     * @return array Learning path array
     */
    public static function get_pending_submissions($instance, $programid, $cmid) {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . '/local/iomad/lib/iomad.php';
        require_once $CFG->dirroot . '/blocks/vlearn_reviews/lib.php';
        $params = self::validate_parameters(self::get_pending_submissions_parameters(),
                        array('instanceid' => $instance, 'programid' => $programid, 'cmid' => $cmid));

        $sitecontex = \context_system::instance();
        $PAGE->set_context($sitecontex);
        $where = '';
        $companyid = 0; //iomad::get_my_companyid($sitecontex, false);
        $companyreviews = new \block_vlearn_reviews\vlearn_reviews($companyid, $sitecontext);
        $students = $companyreviews->get_all_program_students();
        $users = $companyreviews->get_pending_assignment($students[$params['programid']], $params['instanceid']);
        if (!empty($users)) {
            $data = array();
            $i = 0;
            foreach ($users as $user) {
                $data[$i]['fullname'] = $user->fullname;
                $data[$i]['email'] = $user->email;
                $condition = (userdate($user->gradingduedate, '%d %b %Y') . ' ' . userdate($user->gradingduedate, '%H:%M'));
                $gradingduecondition = ($user->gradingduedate == 0 ) ? '--' : $condition;
                $data[$i]['gradingduedate'] = $gradingduecondition;
                $url = $CFG->wwwroot . "/mod/assign/view.php?id=" . $params['cmid'] . "&rownum=0&action=grader&userid=$user->userid";
                $data[$i]['gradingurl'] = $url;
                if ($user->userextensiondate) {
                    $userdate = userdate($user->extensionduedate);
                    $data[$i]['userextensiondate'] = get_string('userextensiondate', 'assign', $userdate);
                }
                $i++;
            }
            $users = array_values($users);

            $output .= $OUTPUT->render_from_template('block_vlearn_reviews/pending', array('learner' => $data));
        }
        $html = array();

        $html['displayhtml'] = $output;
        return $html;
    }

    /**
     * returns leaders info in json format
     */
    public static function get_pending_submissions_returns() {
        return $data = new external_single_structure([
            'displayhtml' => new external_value(PARAM_RAW, 'html'),
            'options' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL),
            'pagedata' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL)
        ]);
    }

}
