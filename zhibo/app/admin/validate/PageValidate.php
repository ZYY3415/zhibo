<?php
namespace app\admin\validate;

use think\Validate;

class PageValidate extends Validate
{
    protected $rule = [
        'rid' => 'require',
        'title'  => 'require',
        'icon'  => 'require',
        'open_type'  => 'require',
        'width'  => 'require',
        'height'  => 'require',
        'is_start'  => 'require',
    ];

    protected $message = [
        'rid.require' => '房间不能为空',
        'title.require'  => '标题不能为空',
        'icon.require'  => '图标不能为空',
        'open_type.require'  => '打开类型不能为空',
        'width.require'  => '宽度不能为空',
        'height.require'  => '高度不能为空',
        'is_start.require'  => '是否开启不能为空',
    ];
}