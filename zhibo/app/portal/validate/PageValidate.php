<?php
namespace app\portal\validate;
use think\Validate;
/**
 * 前台用户提交ajax 提交问题的数据验证
 **/
class PageValidate extends Validate
{
    protected $rule = [
        'pageNo'           =>     'require|number',
        'pageSize'         =>     'require|number',
    ];

    protected $message = [
        'pageNo.require'                  =>            '当前页数不能为空',
        'pageNo.number'                   =>            '当前页数必须是数字',
        'pageSize.require'                =>            '每页记录数不能为空',
        'pageSize.number'                 =>            '每页记录数必须是数字',
    ];

    protected $field = [
        'pageNo'        =>     '当前页数',
        'pageSize'      =>     '每页显示的记录数',
    ];

}



