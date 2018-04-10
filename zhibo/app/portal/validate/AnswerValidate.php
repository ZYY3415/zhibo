<?php
namespace app\portal\validate;
use think\Validate;
/**
 * 前台用户提交ajax 提交问题的数据验证
 **/
class AnswerValidate extends Validate
{
    protected $rule = [
        'mid'         =>     'require|number',
        'wid'         =>     'require|number',
        'room'        =>     'require|number',
        'answer'    =>       'require|min:2',
        'is_true_user'=>     'require|in:0,1'
    ];

    protected $message = [
        'mid.require'                =>            '用户id不能为空',
        'mid.number'                 =>            '用户id必须是数字',
        'wid.require'                =>            '问题id不能为空',
        'wid.number'                 =>            '问题id必须是数字',
        'room.require'               =>            '房间号不能为空',
        'room.number'                =>            '房间号必须是数字',
        'answer.require'           =>             '回答内容不能为空',
        'answer.min'               =>             '回答内容长度不能低于2',
        'is_true_user.require'       =>            '字段不能为空',
        'is_true_user.in'            =>            '字段值只能在0和1之间'
    ];

    protected $field = [
        'mid'      =>     '用户id',
        'wid'      =>     '问题id',
        'room'     =>     '房间号',
        'answer' =>       '回答内容',
        'is_true_user'=>  '提交问题的用户是否是真实用户'
    ];

}



