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
            'page' => new external_value(PARAM_INT, 'The page', VALUE_OPTIONAL),
                )
        );
    }

    /**
     * Return the learning path info
     * @return array Learning path array
     */
    public static function get_all_reviews($page = 0) {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . '/blocks/vlearn_reviews/lib.php';
        $params = self::validate_parameters(self::get_all_reviews_parameters(),
                array( 'page' => $page));
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
        $companyreviews = new \block_vlearn_reviews\vlearn_reviews($sitecontext);
        $students = $companyreviews->get_all_program_students();
        $total_to_reviews = $companyreviews->get_program_reviews(0, 0);
        $reviews = $companyreviews->get_program_reviews($page, $perpage);

        $data = array();
        $i = 0;
        $total_students_program = 0;
        $url_params = array();
        if (!empty($reviews)) {
            foreach ($reviews as $key => $activity) {
                $data['activity'][$i]['course_name'] = $activity->coursename;
                $data['activity'][$i]['name'] = $activity->activityname;
                $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
                $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/vlearn_reviews/pix/assignment-blue.svg";
                $user = \core_user::get_user($activity->userid);
                $data['activity'][$i]['studentname'] = "$user->firstname $user->lastname";
                $data['activity'][$i]['grade'] = $activity->grade;
                $data['activity'][$i]['feedbackdesc'] = $activity->feedbackdesc;
                $data['activity'][$i]['feedback'] = "View Feedback";
                $grade_params = array('id' => $activity->cmid, 'action' => 'grader', 'userid' => $activity->userid);
                $data['activity'][$i]['gradeurl'] = new \moodle_url('/mod/assign/view.php', $grade_params);
                $i++;
            }

            $total_reviews = $data['to_reviews'] = count($total_to_reviews);
            $pagination = '';

            $out .= $OUTPUT->render_from_template('block_vlearn_reviews/allreviews', $data);
            $url = new moodle_url('/blocks/vlearn_reviews/allreviews.php', $url_params);
            $out .= html_writer::start_div('pagination-nav-filter');
            $out .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
            $out .= html_writer::end_div();
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

}
