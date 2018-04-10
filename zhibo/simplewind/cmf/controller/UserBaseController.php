<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

class UserBaseController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
        $default = cmf_get_default_rid();
        $rid = $this->request->param('rid',$default,'intval');
        $this->checkUserLogin($rid);
    }


}