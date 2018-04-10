<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
use think\Exception;

class MoblieIndexController extends HomeBaseController
{
    //移动端主页
    public function Index()
    {
        echo 11;exit;
        return $this->fetch('mobile/index');
    }

    //移动端登录
    public function Login()
    {
        return $this->fetch('mobile/login');
    }

}