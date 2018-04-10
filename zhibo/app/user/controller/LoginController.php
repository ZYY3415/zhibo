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

use app\portal\model\LoginInfoModel;
use app\user\model\MemberModel;
use think\Validate;
use cmf\controller\HomeBaseController;
use app\user\model\UserModel;

class LoginController extends HomeBaseController
{

    /**
     * 登录
     */
    public function index()
    {

            $rid = $this->request->param('rid');
            //$redirect = $this->request->post("redirect",$this->request->root() . '/');
            $redirect = $this->request->post("redirect");
            if (empty($redirect)) {
                $redirect = $this->request->server('HTTP_REFERER');
            } else {
                $redirect = base64_decode($redirect);
            }
            session('login_http_referer', $redirect);

            $memberModel = new MemberModel();
            if ($memberModel->is_member_login()) { //已经登录时直接跳到首页
                return redirect($redirect);
            } else{
                if(cmf_is_mobile())
                {
                    return $this->fetch("portal@mobile/login",['url'=>url('doLogin'),'rid'=>$rid]);
                }else{
                    return $this->fetch(":login",['rid'=>$rid]);
                }
            }
    }

    /**
     * 登录验证提交
     */
    public function doLogin()
    {
        if ($this->request->isPost()) {

            if(cmf_is_mobile())
            {
                $validate = new Validate([
                    'rid'      => 'require',
                    'username' => 'require',
                    'password' => 'require|min:6|max:32',
                ]);
                $validate->message([
                    'rid.require'      => '房间号必须',
                    'username.require' => '用户名不能为空',
                    'password.require' => '密码不能为空',
                    'password.max' => '密码不能超过32个字符',
                    'password.min' => '密码不能小于6个字符',
                ]);
            }else{
                $validate = new Validate([
                    'rid'      => 'require',
                    'captcha' => 'require',
                    'username' => 'require',
                    'password' => 'require|min:6|max:32',
                ]);
                $validate->message([
                    'rid.require'      => '房间号必须',
                    'username.require' => '用户名不能为空',
                    'password.require' => '密码不能为空',
                    'password.max' => '密码不能超过32个字符',
                    'password.min' => '密码不能小于6个字符',
                    'captcha.require' => '验证码不能为空',
                ]);
            }

            $data = $this->request->post();
            $default = cmf_get_default_rid();
            $data['rid'] = isset($data['rid']) ? $data['rid'] : $default;
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            //验证 验证码
            if(!cmf_is_mobile())
            {
                if (!cmf_captcha_check($data['captcha'])) {
                    $this->error(lang('CAPTCHA_NOT_RIGHT'));
                }
            }

            $memberModel = new MemberModel();
            if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
                $log = $memberModel->doMobile($data);
            } else {
                $log = $memberModel->doName($data);
            }

            $session_login_http_referer = session('login_http_referer');
            $redirect = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;

            switch ($log) {
                case 0:
                    $loginInfoModel = new LoginInfoModel();
                    $loginInfoModel->writeLoginInfo();
                    $member = session('member');
                    $tuijianMember = MemberModel::get($member['tuijianmid']);
                    if (empty($tuijianMember)) {
                        $member['tuijianusername'] = $tuijianMember['username'];
                        $member['tuijiannickname'] = $tuijianMember['nickname'];
                        $member['tuijianadminid'] = $tuijianMember['adminid'];
                    } else {
                        $member['tuijianusername'] = '';
                        $member['tuijiannickname'] = '';
                        $member['tuijianadminid'] = '';
                    }
                    $cacheName = session_id() .'_'.$data['rid'].'_userInfo';
                    cache($cacheName, $member, 3600 * 24);

                    $this->success(lang('LOGIN_SUCCESS'), $redirect);
                    break;
                case 1:
                    $this->error(lang('PASSWORD_NOT_RIGHT'));
                    break;
                case 2:
                   return $this->error('账户不存在');
                    break;
                case 3:
                    $this->error('账号未激活，禁止访问系统');
                    break;
                case 4:
                    $this->error('您不能访问当前房间');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

    /**
     * 找回密码
     */
    public function findPassword()
    {
        return $this->fetch('/find_password');
    }

    /**
     * 用户密码重置
     */
    public function passwordReset()
    {

        if ($this->request->isPost()) {
            $validate = new Validate([
                'captcha' => 'require',
                'verification_code' => 'require',
                'password' => 'require|min:6|max:32',
            ]);
            $validate->message([
                'verification_code.require' => '验证码不能为空',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过32个字符',
                'password.min' => '密码不能小于6个字符',
                'captcha.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $captchaId = empty($data['_captcha_id']) ? '' : $data['_captcha_id'];
            if (!cmf_captcha_check($data['captcha'], $captchaId)) {
                $this->error('验证码错误');
            }

            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $userModel = new UserModel();
            if ($validate::is($data['username'], 'email')) {

                $log = $userModel->emailPasswordReset($data['username'], $data['password']);

            } else if (preg_match('/(^(13\d|15[^4\D]|17[013678]|18\d)\d{8})$/', $data['username'])) {
                $user['mobile'] = $data['username'];
                $log = $userModel->mobilePasswordReset($data['username'], $data['password']);
            } else {
                $log = 2;
            }
            switch ($log) {
                case 0:
                    $this->success('密码重置成功', $this->request->root());
                    break;
                case 1:
                    $this->error("您的账户尚未注册");
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