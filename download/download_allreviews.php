<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Popup download
 */

require(__DIR__ . '../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/dataformatlib.php');
$format = optional_param('dataformat', '', PARAM_TEXT);
$programid = optional_param('pid', 0, PARAM_INT);
$activityname = optional_param('activityname', '', PARAM_TEXT);
require_login();
global $DB;
if ($format) {
    // Define the headers and columns.
    $headers = [];
    $headers[] = get_string('activityname', 'block_vlearn_reviews');
    $headers[] = get_string('programname', 'block_vlearn_reviews');
    $headers[] = get_string('coursename', 'block_vlearn_reviews');
    $headers[] = get_string('sectionname', 'block_vlearn_reviews');
    $headers[] = get_string('batch_name', 'block_vlearn_reviews');
    $headers[] = get_string('faculty_name', 'block_vlearn_reviews');
    $headers[] = get_string('faculty_name_others', 'block_vlearn_reviews');
    $headers[] = get_string('due_date', 'block_vlearn_reviews');
    $headers[] = get_string('average_grade', 'block_vlearn_reviews');
    $headers[] = get_string('average_similarity_score', 'block_vlearn_reviews');
    $headers[] = get_string('submission', 'block_vlearn_reviews');
    $headers[] = get_string('review', 'block_vlearn_reviews');

    $sitecontext = context_system::instance();
    $companyid = iomad::get_my_companyid($sitecontext, false);
    $companyreviews = new \block_vlearn_reviews\vlearn_reviews($companyid, $sitecontext);
    $students = $companyreviews->get_all_program_students();
    $where = '';
    if (!empty($activityname)) {
        $where .= " AND a.name LIKE '%" . $activityname . "%'";
    }
    $reviews = $companyreviews->get_program_reviews(0, 0, $where);

    $data = array();
    $i = 0;
    $total_reviews = 0;
    $total_students_program = 0;
    $url_params = array();
    if (!empty($reviews)) {
        foreach ($reviews as $key => $activity) {
            $tmpdata = new stdClass();
            $total_students =  count($students[$activity->programid]);
            $tmpdata->activityname = htmlspecialchars_decode($activity->activityname);
            $tmpdata->programname = htmlspecialchars_decode($activity->programname);
            $tmpdata->coursename = htmlspecialchars_decode($activity->coursename);
            $tmpdata->sectionname = htmlspecialchars_decode(vlearn_reviews_get_sectionname($activity->course, $activity->section));
            $tmpdata->batch_name = "";
            list($tmpdata->faculty_name,$tmpdata->faculty_name_others) = $companyreviews->get_activity_trainer($activity->id,$activity->course);
            $tmpdata->duedate = date('d-m-Y',$activity->duedate);
            $tmpdata->average_grade = $companyreviews->get_activity_average_grade($activity->assignid,$activity->programid);
            $tmpdata->average_similarity_score = $companyreviews->get_activity_similarity_score($activity->id,$activity->programid);
            $submit = $companyreviews->get_assign_submission($activity->instance, $students[$activity->programid]);
            $tmpdata->submission = $submit . "|" . $total_students;
            $tmpdata->review = $submit - $companyreviews->get_assign_review($activity->instance, $students[$activity->programid]) . "|" . $submit;
            $allreviews[] = $tmpdata;
        }
    }

        $today_date = date("d-m-Y");
        $name = "Reviewlist_$today_date";
        $allreviews = (object) $allreviews;
        $filename = clean_filename($name);
        $activity = new ArrayObject($allreviews);
        $iterator = $activity->getIterator();

        $countrecord = 0;
        download_as_dataformat($filename, $format, $headers, $iterator, function ($activity) {
            global $DB;
            $data = array();
            $data[] = $activity->activityname;
            $data[] = $activity->programname;
            $data[] = $activity->coursename;
            $data[] = $activity->sectionname;
            $data[] = $activity->batch_name;
            $data[] = $activity->faculty_name;
            $data[] = $activity->faculty_name_others;
            $data[] = $activity->duedate;
            $data[] = $activity->average_grade;
            $data[] = $activity->average_similarity_score;
            $data[] = $activity->submission;
            $data[] = $activity->review;
            return $data;
        });
        exit;
    }
    
function vlearn_reviews_get_sectionname($courseid,$sectionid){
    global $DB;
    $sql = "SELECT CASE WHEN name IS NOT NULL THEN name ELSE CONCAT('Topic ', section) END AS name "
                . "FROM {course_sections} cs WHERE id= $sectionid AND course = $courseid";
    $sectionname = $DB->get_field_sql($sql);
    return $sectionname;
}
    