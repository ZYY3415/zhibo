<?php
namespace app\user\model;

use think\Model;

class PayBankModel extends Model
{
    const STATUS_DISABLE = 0;//禁用
    const STATUS_ENABLE = 1;//启用

    /**
     * 获取银行列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBankList()
    {
        return $this->field('id,code,position_y')
            ->where('status', self::STATUS_ENABLE)
            ->order('list_order')->order('id')->select();
    }

    /**
     * 返回银行名称
     * @param $code
     * @return mixed
     */
    public function getBankName($code)
    {
        return $this->where('code', $code)->value('name');
    }
}