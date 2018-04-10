<?php
namespace app\portal\validate;

use think\Validate;

class AdminCourseValidate extends Validate
{
    protected $rule = [
        'starttime' => 'require',    //开始时间必须
        'endtime' => 'require',    //结束时间必须
        'rid' => 'require',
        'teacher' => 'require',    //教师
        'course' => 'require',    //课程内容
        ];

    protected $message = [
        'starttime.require' => '开始时间必须',
        'endtime.require' => '结束时间必须',
        'rid.require' => '房间必须',
        'teacher.require' => '讲师必须',
        'course.require' => '课程内容必须',
    ];
}