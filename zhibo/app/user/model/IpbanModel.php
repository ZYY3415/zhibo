<?php
namespace app\user\model;

use think\Model;

class IpbanModel extends Model
{
    /**
     * 检查ip是否在黑名单
     * @param $clientIp
     * @return bool
     */
    public function checkIp($clientIp)
    {
        $result = $this->where('ip', $clientIp)->find();
        return empty($result) ? false : true;
    }
}