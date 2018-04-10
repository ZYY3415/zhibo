<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use app\user\model\IpbanModel;
use app\user\model\MemberModel;
use cmf\controller\HomeBaseController;
use think\Validate;

class RegisterController extends HomeBaseController
{

    /**
     * 前台用户注册
     */
    public function index()
    {
        $redirect = $this->request->post("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);
        $memberModel = new MemberModel();
        if ($memberModel->is_member_login()) {
            return redirect($this->request->root() . '/');
        } else {
            return $this->fetch(":register");
        }
    }

    /**
     * 前台用户注册提交
     */
    public function doRegister()
    {
        if ($this->request->isPost()) {
            $rules = [
                'username' => 'require',
                'phone' => 'require',
                'code' => 'require',
                'password' => 'require|min:6|max:32',
            ];

            $validate = new Validate($rules);
            $validate->message([
                'username.require' => '账号不能为空',
                'phone.require' => '手机号码不能为空',
                'code.require' => '验证码不能为空',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过32个字符',
                'password.min' => '密码不能小于6个字符',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $clientIp = get_client_ip(0, true);
            $ipbanModel = new IpbanModel();
            if($ipbanModel->checkIp($clientIp)) {
                $this->error('您的ip已经被屏蔽,请联系管理员');
            }

//            if (!cmf_captcha_check($data['captcha'])) {
//                $this->error('验证码错误');
//            }

            $errMsg = cmf_check_verification_code($data['phone'], $data['code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $memberModel = new MemberModel();
            if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['phone'])) {
                $log = $memberModel->registerMobile($data);
            } else {
                $log = 2;
            }
            $sessionLoginHttpReferer = session('login_http_referer');
            $redirect = empty($sessionLoginHttpReferer) ? cmf_get_root() . '/' : $sessionLoginHttpReferer;
            switch ($log) {
                case 0:
                    $this->success('注册成功', $redirect);
                    break;
                case 1:
                    $this->error("您的账户已注册过");
                    break;
                case 2:
                    $this->error("您输入的账号格式错误");
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }

    }
}