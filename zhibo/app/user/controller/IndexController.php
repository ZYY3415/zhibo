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
use app\user\model\UserModel;
use cmf\controller\HomeBaseController;
use Common\WebSocketClient;
use think\Log;

class IndexController extends HomeBaseController
{

    /**
     * 前台用户首页(公开)
     */
    public function index()
    {
        $id        = $this->request->param("id", 0, "intval");
        $userModel = new UserModel();
        $user      = $userModel->where('id', $id)->find();
        if (empty($user)) {
            $this->error("查无此人！");
        }
        $this->assign($user->toArray());
        $this->assign('user',$user);
        return $this->fetch(":index");
    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (!empty(session('member'))) {
            $this->success("用户已登录", null, ['member' => cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    /**
     * 退出登录
    */
    public function logout()
    {
        $mid = cmf_get_current_user_id();
        $default = cmf_get_default_rid();
        $rid = $this->request->param('rid',$default,'intval');


        if ($mid) {
            //退出登录记录日志
            $loginInfoModel = new LoginInfoModel();
            $loginInfoModel->writeLogoutInfo();

            //退出登录时发送消息通知服务端
            $webSocketClient = new WebSocketClient();
            $webSocketClient->connect(config('web_socket_host'), config('web_socket_port'), '/');

            $token = '20180112091612';

            $data = json_encode(array(
                'token' => $token,
                'type' => 'logout',
                'mid' => cmf_get_current_user_id(),
            ));

            $result = $webSocketClient->sendData($data);
            if ($result !== true) {
                Log::error('退出登录消息发送失败');
            }
        }

        session("member", null);//只有前台用户退出
        session(session_id()."_{$rid}_userInfo", null);

        $login_http_referer = session('?login_http_referer') ? session('login_http_referer') : $this->request->root() . "/";

        return redirect($login_http_referer);
    }
}
