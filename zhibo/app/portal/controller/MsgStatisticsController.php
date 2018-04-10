<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use Think\Db;
use app\portal\model\AdminRobotModel;
use think\Request;
class MsgStatisticsController extends AdminBaseController
{
    public function index(Request $request)
    {

          $param = $request->param();
        var_dump($param);
          $select = Db::name('portal_msginfo');

        if(!empty($param['rid']))
        {
            $select->where('rid',$param['rid']);
        }
        if(!empty($param['start_time']))
        {
            $select->whereTime('time',' >= ',$param['start_time']);
        }
        if(!empty($param['end_time']))
        {
            $select->whereTime('time',' <= ',$param['end_time']);
        }
        if(!empty($param['keyword']))
        {

        }

          $data = $select->paginate(config('admin_page_size'))->appends($param);

          $rooms = model('AdminHouse')->column('rid,room');

          $this->assign('rooms',$rooms);
          $this->assign('msginfo',$data->all());
          $this->assign('page',$data->render());
          $this->assign('room',isset($param['room']) ? $param['room'] : '');
          $this->assign('keyword',isset($param['keyword']) ? $param['keyword'] : '');
          $this->assign('time',isset($param['start_time']) ? $param['start_time'] : '');
          $this->assign('time',isset($param['end_time']) ? $param['end_time'] : '');
          return view();
    }



}

