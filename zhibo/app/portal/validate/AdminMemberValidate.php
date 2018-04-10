<?php
namespace app\portal\validate;

use think\Validate;

class AdminMemberValidate extends Validate
{
    protected $rule = [
      'username' => 'require|unique:member|length:3,10',
      'password' => 'require|length:6,13',      
      'nickname' => 'require|length:2,10',
      'phone' => 'require|number|max:11|/^1[3-8]{1}[0-9]{9}$/',          
      'rid'     => 'require',
    ];

    protected $message = [
        'username.require'  => '用户名必须',
        'username.unique'  => '用户名已存在',
        'username.length'  => '用户名长度在3到10中文或者字母之间',
        'password.require'  => '密码必须',
        'password.length'  => '密码长度必须在6-13位之间',
        'nickname.require'  => '昵称必须',
        'nickname.length'  => '昵称长度在2到10中文或者字母之间',
        'phone.require'  => '手机号必须',
        'phone.number'  => '手机号必须为数字',
        'phone.max'  => '手机号不超过11位数',
        'phone./^1[3-8]{1}[0-9]{9}$/'  => '手机号格式不正确',
        'rid.require'       => '房间必须',
    ];

    protected $scene = [
        'edit'    =>    'nickname,phone,rid'
    ];
}