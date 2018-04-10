<?php

namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Url;
class PortalGiftController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();

        $result = model('PortalGift')->getGift($param);
        $data = array_map('switch_rid_ridname',$result->toArray()['data']);
        $rid_list = cmf_get_current_admin_rid();
        $rooms = $this->get_rid_list();

        $teachers = Db::name('teacher')
            ->whereIn('rid',$rid_list)
            ->column('jid,jname');

        $this->assign('data',$data);
        $this->assign('page',$result->render());
        $this->assign('rooms',$rooms);
        $this->assign('room',isset($param['room']) ? $param['room'] :'');
        $this->assign('teachers',$teachers);
        $this->assign('teacher',isset($param['teacher']) ? $param['teacher']:'');
        $this->assign('start_time',isset($param['start_time']) ? $param['start_time']:'');
        $this->assign('end_time',isset($param['end_time']) ? $param['end_time']:'');
        $this->assign('keyword',isset($param['keyword']) ? $param['keyword']:'');
        $this->assign('type',isset($param['type']) ? $param['type']:0);

        return $this->fetch();
    }

    /**删除收费统计信息**/
    public function deleteGift()
    {
        $param = $this->request->param();
        $type = isset($param['type']) ? $param['type'] : 0;
        if(isset($param['id']))
        {
            $rel = model('PortalGift')->destroy($param['id']);
        }else if(isset($param['ids']))
        {
            $rel = model('PortalGift')->destroy($param['ids']);
        }

         if(!$rel)
         {
             $this->error('删除失败！','index',['type'=>$type]);
         }

             $this->success('删除成功！','index',['type'=>$type]);
    }
}