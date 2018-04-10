<?php

namespace app\portal\controller;

use app\portal\model\SendRedbagModel;
use cmf\controller\AdminBaseController;
use app\portal\model\RebagModel;
use app\portal\model\AdminHouseModel;
use app\admin\model\TeacherModel;
use think\Db;
class RebagController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $room = $this->get_rid_list();

        $model = new RebagModel();
        $teacherList = $model->getRebagList($param);
        $data = array_map('switch_rid_ridname',$teacherList->toArray()['data']);
        $this->assign('rooms',$room);
        $this->assign('page',$teacherList->render());
        $this->assign('data',$data);

        $this->assign('rid',isset($param['rid']) ? $param['rid'] : '');
        $this->assign('room',isset($param['rid']) ? $param['rid'] : '');
        $this->assign('tid',isset($param['tid']) ? $param['tid'] : '');
        $this->assign('keyword',isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('start_time',isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time',isset($param['end_time']) ? $param['end_time'] : '');

        return $this->fetch();
    }

    /**红包详情**/
    public function redbagInfo()
    {
        $id = $this->request->param('id',0,'intval');
        $param = $this->request->param();

        if(empty($id))
        {
            $this->error('该条信息不存在！','index');
        }


        $model = new RebagModel();
        $result = $model->getRedbagInfo($id,$param);
        $data = array_map('switch_rid_ridname',$result['redbag']->toArray());
        $this->assign('redbagInfo',$result['redbagInfo']->all());
        $this->assign('redbag',$data);
        $this->assign('page',$result['redbagInfo']->render());
        $this->assign('id',$id);
        $this->assign('start_time',isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time',isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword',isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('id',$id);

        return $this->fetch();

    }


    /**删除红包记录**/

    public function deleteRedbag()
    {
        $id = $this->request->param('id',0,'intval');
        $ids = $this->request->param()['ids'];

        if(!empty($id))
        {
            Db::transaction(function($id) use ($id){
                Db::name('send_redbag')->delete($id);
                Db::name('portal_redbag_info')->where('redbag_id',$id)->delete();
            });
        }else if(!empty($ids))
        {
            Db::transaction(function($ids) use ($ids){

                Db::name('send_redbag')->delete($ids);
                Db::name('portal_redbag_info')->where('redbag_id','in',$ids)->delete();
            });
        }

        $this->error('数据删除成功！','index');
    }

    /**删除红包详细记录**/
    public function deleteRedbagInfo()
    {
        $id = $this->request->param('id',0,'intval');
        $ids = $this->request->param()['ids'];

        if(!empty($id))
        {
            $result = Db::name('portal_redbag_info')->where('redbag_id',$id)->delete();

        }else if(!empty($ids))
        {
            $result = Db::name('portal_redbag_info')->where('redbag_id','in',$ids)->delete();
        }

        if($result)
        {
            $this->error('数据删除失败！','redbagInfo');
        }
        $this->error('数据删除成功！','redbagInfo');
    }


}