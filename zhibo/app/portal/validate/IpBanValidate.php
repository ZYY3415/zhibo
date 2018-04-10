<?php
namespace app\portal\validate;
use think\Validate;
/**
 * 前台用户提交ajax 提交问题的数据验证
 **/
class IpBanValidate extends Validate
{
        protected $rule = [
            'ip'     =>    'require|ip|validate_ip',
            'mid'    =>    'number'
            ];

        protected $msg = [
            'mid.number'      =>  '用户id必须是数字'
            ];

       protected function validate_ip($value, $rule, $data)
       {
           $ip = get_client_ip(0,true);

           return $value == $ip  ? '不能封自己的Ip' : true;
       }
}



