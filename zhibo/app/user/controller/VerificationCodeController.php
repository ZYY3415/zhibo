<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use think\Validate;

class VerificationCodeController extends HomeBaseController
{
    public function send()
    {
        if ($this->request->isAjax()) {
            $validate = new Validate([
                'username' => 'require',
            ]);

            $validate->message([
                'username.require' => '请输入手机号!',
            ]);

            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if (!preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
                $this->error("请输入正确的手机或者邮箱格式!");
            }

            //TODO 限制 每个ip 的发送次数
            $code = cmf_get_verification_code($data['username']);
            if (empty($code)) {
                $this->error("验证码发送过多,请明天再试!");
            }

            $sms = new \HJ100\BBSMS\SMS();
            $sendResult = $sms->sendCommonCode($data['username'], $code);

            if ($sendResult->code != 'HJ.OK') {
                $this->error('验证码发送失败:' . $sendResult->message);
            }

            cmf_verification_code_log($data['username'], $code);

            $this->success('验证码已经发送成功!');
        }
    }

}
