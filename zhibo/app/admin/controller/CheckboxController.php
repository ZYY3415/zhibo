<?php
namespace app\admin\controller;

use app\admin\model\PageModel;
use app\portal\model\RoomBasicModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;

class CheckboxController extends AdminBaseController
{
    public function index()
    {
        return $this->fetch('./themes/admin_simpleboot3/public/checkbox.html');
    }
}
