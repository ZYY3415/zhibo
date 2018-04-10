<?php
namespace app\portal\validate;

use think\Validate;

class AdminHouseValidate extends Validate
{
    protected $rule = [
      'rid'                =>   'require|number|between:1001,1100',                    //房间号 1001-1100
      'room'               =>   'require',                                             //房间名称必须
      'speak_intval'        =>   'number|min:0',                //游客发言间隔   最小值不能小于0
    ];

    protected $message = [
        'rid.require' => '房间号必须',
        'rid.number' => '房间号必须是数字',
        'rid.between' => '房间号1001-1100间',
        'room.require' => '房间名称必须',
        'speak_intval.number' => '游客发言间隔必须是数字',
        'speak_intval.min' => '游客发言间隔最小为0',
    ];


}