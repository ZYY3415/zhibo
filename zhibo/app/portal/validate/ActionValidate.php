<?php
namespace app\portal\validate;

use think\Validate;

class ActionValidate extends Validate
{

    //content:inputext,username:USERNAME,mid:MID,fid:CUR_FID,fname:Room_info.room,toid:tomid,tousername:tousername,toaid:toadminid,roleid:roleid,rolename:rolename,roleaid:roleaid

    protected $rule = [
        'content'   =>   'require',    //验证内容
        'username'  =>   'require',
        'mid'  =>   'require',
        'fid'  =>   'require',

    ];

    protected $message = [
        'content.require' => '发言内容必须存在',
        'username.require' => '发言内容必须存在',
        'mid.require' => '用户id必须存在',
        'fid.require' => '房间id必须存在',
    ];


}