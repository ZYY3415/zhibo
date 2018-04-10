<?php
namespace app\portal\validate;
use think\Validate;

/**
 * 机器人新增验证
**/
class AdminRobotValidate extends Validate
{
    protected $rule = [
        'rid'        =>     'require|number',                //验证房间号
        'aid'        =>     'require|number',                //角色id验证
        'stime'      =>     'require|number|length:10',      //上线时间验证
        'etime'      =>     'require|number|length:10',      //下线时间验证
        'week'       =>     'require',
        'username'   =>     'require'
    ];

    protected $message = [
        'rid.require'               =>               '房间必须选择',
        'rid.number'                =>               '房间号为数字',
        'aid.require'               =>               '角色必须选择',
        'aid.number'                =>               '角色id必须是数字',
        'stime.require'             =>               '上线时间必须选择',
        'stime.number'              =>               '上线时间时间戳必须是数字',
        'stime.length'              =>               '上线时间错误',
        'etime.require'             =>               '下线时间必须选择',
        'etime.number'              =>               '下线时间时间戳必须是数字',
        'etime.length'              =>               '下线时间错误',
        'week.require'              =>               '日期必须',
        'username.require'          =>               '用户名必须'

    ];
}