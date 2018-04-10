<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;
use think\Loader;

/**
前台首页附加应用的控制器接口
 **/
class AlySetController extends AdminBaseController
{

    public function setCrossDomain()
    {
        Vendor('alyvideo.NewStreamController');
        $app = new \NewStreamController();                        //实例化类

        $domain = $this->request->server('SERVER_NAME');

        dump($app->gethttpheader());
    }
}
