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
 * Block vlearn_reviews is defined here.
 *
 * @package     block_vlearn_reviews
 * @copyright   2022 Sudhanshu Gupta <sudhanshu.gupta@herovired.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_vlearn_reviews extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_vlearn_reviews');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
     global $USER, $DB, $CFG, $OUTPUT, $PAGE;
//     require_once $CFG->dirroot.'/local/custom_events/lib.php';
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $text = '';
            $noofreviews = 4;
            $sitecontext = context_system::instance();
            $companyreviews = new \block_vlearn_reviews\vlearn_reviews($sitecontext);
            $students = $companyreviews->get_all_program_students();
            $total_to_reviews = $companyreviews->get_program_reviews(0,0);
            $reviews = $companyreviews->get_program_reviews(0, $noofreviews);

            $data = array();
            $i = 0;
            $total_students_program = 0;
//            print_object($reviews);die;
            foreach ($reviews as &$activity) {
//                $total_students = $data['activity'][$i]['out_of'] = count($students[$activity->programid]);
                $data['activity'][$i]['course_name'] = $activity->coursename;
                $data['activity'][$i]['name'] = $activity->activityname;
                $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
                $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/vlearn_reviews/pix/assignment-blue.svg";
                $user = core_user::get_user($activity->userid);
                $data['activity'][$i]['studentname'] = "$user->firstname $user->lastname";
                $data['activity'][$i]['grade'] = $activity->grade;
                $data['activity'][$i]['feedbackdesc'] = $activity->feedbackdesc;
                $data['activity'][$i]['feedback'] = "View Feedback";
                $grade_params = array('id' => $activity->cmid, 'action' => 'grader','userid'=>$activity->userid);
                $data['activity'][$i]['gradeurl'] =  new \moodle_url('/mod/assign/view.php', $grade_params);
                $i++;
            }

            $data['to_reviews'] = count($total_to_reviews);
            if (count($reviews) >= 1) {
            $data['allreviewsurl'] = $CFG->wwwroot.'/blocks/vlearn_reviews/allreviews.php';
            }
//            if (is_siteadmin() || has_capability("block/vlearn_reviews:viewinstance", $this->context)){
             $text .= $OUTPUT->render_from_template('block_vlearn_reviews/reviews', $data);
//            }

            $this->content->text = $text;
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_vlearn_reviews');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'all' => true,
        );
    }
}
