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
 * Utility class for learning path block
 *
 * @package    block_vlearn_reviews
 * @copyright  2021 Sudhanshu Gupta (sudhanshug5@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_vlearn_reviews;

defined('MOODLE_INTERNAL') || die();

class vlearn_reviews {

    protected $companyid;
    protected $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function get_program_reviews($page = 0, $perpage = 0, $where = null, $sort = ' id ASC') {
        global $DB, $USER;
        $params = array();
//        if (is_siteadmin() || has_capability("block/calendar_month:view_todaysclasses", $context)) {
            $sql = "Select gr.*, c.fullname as coursename, a.name as activityname from {assign_graderesponse} gr left join {course} c on c.id = gr.courseid"
                    . " left join {assign} a on a.id = gr.assignmentid" . $where;
            $sortsql = " ORDER by $sort";
            $assignments = $DB->get_records_sql($sql.$sortsql, $params, ($page * $perpage), $perpage);
//        } else {
//            $params['userid'] = $USER->id;
//            $params['ttuserid'] = $USER->id;
//            $sql = "Select cm.id, lp.name as programname, lp.id as programid, c.fullname as coursename,cm.section, cm.course, cm.instance, a.name as activityname, a.gradingduedate ,"
//                    . "  a.duedate, a.id as assignid, gi.gradepass  from {learningpaths} as lp left join {learningpath_courses} as lpc on lp.id = lpc.learningpathid left join {course} as c "
//                    . "on c.id = lpc.courseid left join {course_modules} as cm on cm.course = c.id left join {trainer_tagging} tt ON tt.cmid = cm.id  "
//                    . " left join {modules} as m on m.id = cm.module "
//                    . " left join {assign} as a on a.id = cm.instance "
//                    . " left join {grade_items} gi  on gi.iteminstance = cm.instance "
//                    . " LEFT JOIN {custom_role_mapping} crm  ON crm.programid= lp.id "
//                    . " where lp.companyid = $companyid"
//                    . " and cm.visible =1 and lpc.course_active = 1 and cm.deletioninprogress = 0 and m.name='assign' and tt.userid = :ttuserid "
//                    . " and lp.deleted = 0 and gi.itemmodule='assign' and a.allowsubmissionsfromdate < $time and crm.userid=:userid " . $where . " group by cm.id";
//            $sortsql = " ORDER by $sort";
//            $assignments = $DB->get_records_sql($sql.$sortsql, $params, ($page * $perpage), $perpage);
//        }
        return $assignments;
    }

    function get_all_program_students() {
        global $DB;
        $sql = "SELECT id from {user} where deleted = 0";
        $records = $DB->get_records_sql($sql);
        foreach ($records as &$lps) {
            $programs[$lps->id] = $this->get_program_students($lps->id);
        }
        return $programs;
    }

    function get_program_students($programid) {
        global $DB;
//        $sql = "Select lpu.userid from {learningpath_users} as lpu where lpu.learningpathid = $programid";
        $sql = "SELECT id from {user} where deleted = 0";
        $records = $DB->get_records_sql($sql);
        $users = array();
        foreach ($records as &$user) {
            $users[] = $user->userid;
        }
        return $users;
    }

    function get_assign_submission($assignid,$userid) {
         global $DB;
        $users = implode(',', $userid);
        $submission = 0;
        if(!empty($users)){
        $sql = "SELECT id from {assign_submission} where assignment = $assignid and status = 'submitted' and userid IN ($users)";
        $submission = count($DB->get_records_sql($sql));
        }
        return $submission;
    }

    function get_assign_review($assignid,$userid) {
        global $DB;
        $users = implode(',', $userid);
        $reviewed = 0;
        if(!empty($users)){
        $sql = "SELECT id from {assign_grades} where assignment = $assignid and grader > 0 and userid IN ($users)";
        $reviewed = count($DB->get_records_sql($sql));
        }
        return $reviewed;
    }
    
    function get_activity_trainer($cmid, $courseid){
        global $DB;
        $faculty_name1 = "No Trainer";
        $faculty_name_others = "No Trainer";
        $sql = "SELECT concat(u.firstname, ' ' , u.lastname) as fullname FROM {trainer_tagging} as tt left join {user} as u on tt.userid = u.id WHERE tt.cmid = $cmid";
        $record = $DB->get_records_sql($sql);
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'trainer'));
        if (empty($record)) {
            $sql = "SELECT concat(u.firstname, ' ' , u.lastname) as fullname FROM {user} u INNER JOIN {role_assignments} ra ON ra.userid = u.id INNER JOIN"
                    . " {context} ct ON ct.id = ra.contextid INNER JOIN {course} c ON c.id =ct.instanceid INNER JOIN {role} r"
                    . " ON r.id = ra.roleid INNER JOIN {course_categories} cc ON cc.id = c.category WHERE r.id = $roleid and c.id = $courseid";
            $record = $DB->get_records_sql($sql);
        }

        if (!empty($record)) {
            $firstname = array_shift($record);
            $faculty_name1 = $firstname->fullname;
            $faculty_name_others = $this->get_list_trainers($record);
        }
        $trainers = array($faculty_name1, $faculty_name_others);
        return $trainers;
    }
    
    function get_activity_average_grade($assignid,$programid){
        global $DB;
        $users = $this->get_program_students($programid);
        $users_list = implode(',', $users);
        $average = 0;
        if(!empty($users_list)){
        $sql = "SELECT sum(grade) as total_score from {assign_grades} where assignment = $assignid and grader > 0 and userid IN ($users_list)";
        $record = $DB->get_record_sql($sql);
        $sum = $record->total_score;
        $average = number_format($sum/count($users),2);
        }
        return $average;
    }
    
    function get_list_trainers($records) {
    $teachers = implode(', ', array_keys($records));
    return $teachers;
    }
    
   function get_activity_similarity_score($cmid,$programid){
        global $DB;
        $users = $this->get_program_students($programid);
        $users_list = implode(',', $users);
        $average = 0;
        if(!empty($users_list)){
        $sql = "SELECT sum(similarityscore) as total_score from {plagiarism_copyleaks_files} where cm = $cmid and userid IN ($users_list)";
        $record = $DB->get_record_sql($sql);
        $sql = "SELECT count(distinct(id)) as total_users from {plagiarism_copyleaks_files} where cm = $cmid and userid IN ($users_list) and statuscode = 'success'";
        $user_record = $DB->get_record_sql($sql);
        $sum = $record->total_score;
        if($user_record->total_users > 0){
        $average = number_format($sum/($user_record->total_users),2);
        }else 
            $average = 0;
        }
        return $average;
    }
/*
 * Get trainer programs
 * 
 */
function get_trainer_programs_list() {
        global $DB, $USER;
        $time = time();
        $programs = '';
        $context = \context_system::instance();
        if (is_siteadmin() || has_capability("block/calendar_month:view_todaysclasses", $context)) {
            $sql = "Select lp.id, lp.name as programname  "
                    . "  from {learningpaths} as lp "
                    . " WHERE lp.deleted = 0 ";
//            $sortsql = " ORDER by $sort";
            $programs = $DB->get_records_sql_menu($sql);
        } else {
            $params['userid'] = $USER->id;
            $params['ttuserid'] = $USER->id;
            $sql = "Select DISTINCT(lp.id) as id, lp.name as programname  from {learningpaths} as lp left join {learningpath_courses} as lpc on lp.id = lpc.learningpathid left join {course} as c "
                    . "on c.id = lpc.courseid left join {course_modules} as cm on cm.course = c.id left join {trainer_tagging} tt ON tt.cmid = cm.id  "
                    . " left join {modules} as m on m.id = cm.module "
                    . " left join {assign} as a on a.id = cm.instance "
                    . " LEFT JOIN {custom_role_mapping} crm  ON crm.programid= lp.id "
                    . " where cm.visible =1 and lpc.course_active = 1 and cm.deletioninprogress = 0 and m.name='assign' and tt.userid = :ttuserid "
                    . " and lp.deleted = 0 and a.allowsubmissionsfromdate < $time and crm.userid=:userid  group by lp.id";
//        $sortsql = " ORDER by $sort";
            $programs = $DB->get_records_sql_menu($sql, $params);
        }
        if ($programs) {
            return $programs;
        }
    }
    
    /*
     * Get pending assignments
     */

    function get_pending_assignment($userid, $assignid) {
        global $DB;
        $users = implode(',', $userid);
        $reviewed = 0;
        if (!empty($users)) {
            $selectfields = "ag.userid,a.*, CONCAT(u.firstname, ' ', u.lastname) fullname, u.email,  ag.grader, uf.extensionduedate as extensionduedate " ;
            $sql = "SELECT $selectfields from {assign_grades}   ag"
                    . " LEFT JOIN {assign} as a ON a.id = ag.assignment "
                    . " LEFT JOIN {user} as u ON u.id = ag.userid "
                    . " LEFT JOIN {assign_user_flags} uf ON u.id = uf.userid AND uf.assignment = ag.assignment"
                    . " where ag.assignment = $assignid and ag.grader < 0 and ag.userid IN ($users)";
            $reviewed = $DB->get_records_sql($sql);
        }
        return $reviewed;
    }

}
