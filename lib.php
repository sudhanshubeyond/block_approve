<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

function get_filters($programs) {

    if ($programs) {
        $i = 0;
        $programids = [];
        $programlist = [];
        foreach ($programs as $key => $name) {
            $programlist[$i]['name'] = $name;
            $programlist[$i]['value'] = $key;
            $programids[] = $key;
            $i++;
        }
        $courses = get_program_courses($programids);
        $courseslist = [];
        $i = 0;
        foreach ($courses as $key => $course) {
            $courseslist[$i]['name'] = $course;
            $courseslist[$i] ['value'] = $key;
            $i++;
        }
        return array($programlist, $courseslist);
    }
    return array();
}

/*
 * get program courses
 */
function get_program_courses($programids) {
    global $DB;
    $programslist = implode(',', $programids);
//    print_object($programslist);die;
    $companyid = 0;
    $where = " AND lp.id IN ($programslist) ";
    $sql = "Select DISTINCT(c.id), c.fullname as coursename "
            . " from {learningpaths} as lp left join {learningpath_courses} as lpc on lp.id = lpc.learningpathid "
            . " left join {course} as c "
            . " on c.id = lpc.courseid  where "
            . "  lpc.course_active = 1 and c.visible = 1 "
            . " and lp.deleted = 0  " . $where;
//    $sortsql = " ORDER by $sort";
    $courses = $DB->get_records_sql_menu($sql);
    return $courses;
}

/*
 * Create dropdown based on program
 */
function course_filter($programid, $cid) {
    $options = '<option selected>Course</option>';
    $courses = get_program_courses(array($programid));
    if (!empty($courses)) {
        foreach ($courses as $key => $course) {
            $selectd = ($key == $cid) ? 'selected' : '';
            $options .= '<option value="' . $key . '" '.$selectd.'>' . $course . '</option>';
        }
    }

    return $options;
}
