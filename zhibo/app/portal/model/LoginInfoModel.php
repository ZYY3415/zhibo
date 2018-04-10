<?php
namespace app\portal\model;

use think\Model;

class LoginInfoModel extends Model
{
    const DEVICE_PC = 1;//
    const DEVICE_MOBILE = 2;//

    /**
     * 写入登录日志
     */
    public function writeLoginInfo()
    {
        $member = session('member');
        $date = date('Y-m-d');
        $loginInfo = $this->where('mid', $member['id'])->where('date', $date)->find();
        $clientIp = get_client_ip(0, true);
        if ($loginInfo) {
            //当天已经登录
            $this->where('mid', $member['id'])
                ->where('date', $date)
                ->update(array(
                    'login_time' => time(),
                    'login_ip' => $clientIp,
                ));
        } else {
            //当天第一次登录
            $this->save(array(
                'mid' => $member['id'],
                'username' => $member['nickname'],
                'login_time' => time(),
                'login_ip' => $clientIp,
                'rid' => session('default_Rid'),
                'date' => $date,
                'device' => cmf_is_mobile() ? self::DEVICE_MOBILE : self::DEVICE_PC,
            ));
        }
    }

    /**
     * 退出登录日志记录
     */
    public function writeLogoutInfo()
    {
        $member = session('member');
        $date = date('Y-m-d');
        $loginInfo = $this->where('mid', $member['id'])->where('date', $date)->find();
        if ($loginInfo) {
            $onlineTime = time() - intval($loginInfo['login_time']);
            $this->where('mid', $member['id'])->where('date', $date)
                ->update(array(
                    'last_time' => time(),
                    'online_time' => $onlineTime,
                ));
        }
    }

    /**
     * 获取设备标签
     * @param null $key
     * @return array|mixed
     */
    public static function getDeviceLabel($key = null)
    {
        $data = array(
            self::DEVICE_PC => '电脑',
            self::DEVICE_MOBILE => '手机',
        );
        if ($key !== null && !empty($data[$key])) {
            return $data[$key];
        } else {
            return $data;
        }
    }
}