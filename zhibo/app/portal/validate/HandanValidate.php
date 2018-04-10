<?php

namespace app\portal\validate;
use Think\Validate;


class HandanValidate extends Validate
{
    protected $rule = [
        ['mid', 'require|number','用户id必须存在|用户id必须是数字'],
        ['room','require|number','房间号必须存在|房间号必须为数字'],
        ['is_true_user','require|in:0,1','字段必须存在|字段值必须在0和1之间'],
        ['type','require|in:买入,卖出','类型必须存在|类型值必须是买入或者卖出'],
        ['etpt_position','require','仓位必须存在'],
        ['commodity','require','商品名必须存在'],
        ['open_etpt_value','require|float','开仓价必须存在|开仓价必须是浮点型'],
        ['loss_value','require|float','止损价必须存在|止损价必须是浮点型'],
        ['stop_profit_value','require|float','止盈价必须存在|止盈价必须是浮点型'],
        ['flat_etpt_value','require','平仓价格必须存在'],
        ['profit_count','require','盈利点数必须存在'],

    ];

    protected $field = [
        'mid'                    =>               '用户id',
        'room'                   =>               '房间号',
        'is_true_user'           =>               '是否是假人',
        'open_etpt_time'         =>               '开仓时间',
        'type'                   =>               '类型',
        'etpt_position'          =>               '仓位',
        'commodity'              =>               '商品名',
        'open_etpt_value'        =>               '开仓价',
        'loss_value'             =>               '止损价',
        'stop_profit_value'      =>               '止盈价',
        'flat_etpt_time'         =>               '平仓时间',
        'flat_etpt_value'        =>               '平仓价格',
        'profit_count'           =>               '盈利点数',
        'analyst'                =>               '分析师',
        'remarks'                =>               '备注',
    ];
}






