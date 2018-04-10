<?php
namespace app\portal\validate;
use think\Validate;
/**
 * 前台用户提交ajax 提交问题的数据验证
 **/
class QuestionValidate extends Validate
{
    protected $rule = [
        'mid'         =>     'require|number',
        'room'        =>     'require|number',
        'question'    =>     'require|min:4',
        'is_true_user'=>     'require|in:0,1'
    ];

    protected $message = [
        'mid.require'                =>            '用户id不能为空',
        'mid.number'                 =>            '用户id必须是数字',
        'room.require'               =>            '房间号不能为空',
        'room.number'                =>            '房间号必须是数字',
        'question.require'           =>            '问题不能为空',
        'question.min'               =>            '问题长度不能低于4',
        'is_true_user.require'       =>            '字段不能为空',
        'is_true_user.in'            =>            '字段值只能在0和1之间'
    ];

    protected $field = [
        'mid'      =>     '用户id',
        'room'     =>     '房间号',
        'question' =>     '问题内容',
        'is_true_user'=>  '提交问题的用户是否是真实用户'
    ];

}



