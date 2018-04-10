<?php
namespace app\portal\validate;

use think\Validate;

class PortalTeacherValidate extends Validate
{
    protected $rule = [
      'jname' =>   'require|min:2|max:8',    //开始时间必须
      'rid'   =>   'require',    //结束时间必须
    ];

    protected $message = [
        'jname.require' => '教师名称必须',
        'jname.min'     => '教师名称最小为2个字符',
        'jname.max'     => '教师名称最大为8个字符',
        'rid.require'   => '房间名称必须',
    ];


}