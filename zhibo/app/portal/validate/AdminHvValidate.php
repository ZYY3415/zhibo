<?php
namespace app\portal\validate;

use think\Validate;

class AdminHvValidate extends Validate
{
    protected $rule = [
        'rid'                =>   'require|number|between:1001,1100',                    //房间号 1001-1100
        'play_source'        =>   'require',
        'YYfcode'            =>   'require|number|length:6,13',
        'YYcode'            =>    'require|number|length:6,13',
    ];

    protected $message = [
        'rid.require'         =>  '房间号必须',
        'rid.number'          =>  '房间号必须是数字',
        'rid.between'         =>  '房间号1001-1100间',
        'play_source.require' =>  '视频来源必须',
        'YYfcode.require'     =>  'yy房间号必须',
        'YYfcode.number'      =>  'yy房间号必须为数字',
        'YYfcode.length'      =>  'yy房间号最少6位,最多13位',
        'YYcode.require'              =>  'yy号必须',
        'YYcode.number'              =>  'yy号必须为数字',
        'YYcode.length'              =>  'yy号最少6位,最多13位',
    ];


}