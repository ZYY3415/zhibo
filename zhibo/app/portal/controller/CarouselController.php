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

use cmf\controller\AdminBaseController;
use think\Db;

class CarouselController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $rid_list = cmf_get_current_admin_rid();
        $rooms = $this->get_rid_list();

        $select = Db::name('carousel')
            ->alias('c')
            ->field('c.*,rb.room')
            ->join('__ROOM_BASIC__ rb','rb.rid = c.rid');

        if(!empty($param['room']))
        {
            $select->where('c.rid',$param['room']);
        }else{
            $select->where('c.rid','in',$rid_list);
        }

        if(!empty($param['keyword']))
        {
            $select->where('c.rid|c.id|rb.room','like',$param['keyword']);
        }
        $data = $select->paginate(config('admin_page_size'))->appends($param);
        $this->assign('page',$data->render());
        $data = array_map(function($attr){


            $attr['child_carous'] = explode(',',$attr['carousel']);

            return $attr;
        },$data->all());

        $this->assign('room',isset($param['room']) ? $param['room'] : '');
        $this->assign('rooms',$rooms);
        $this->assign('data',$data);


        return $this->fetch();
    }


    /**轮播图片添加**/

    public function carouselAdd()
    {
        if($this->request->isGet())
        {
            $rooms = $this->get_rid_list();

            $this->assign('rooms',$rooms);
            return $this->fetch();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();

            if(empty($param['photo_urls']))
            {
                $this->error('轮播图不能为空！');
            }

            if(empty($param['rid']))
            {
                $this->error('房间不能为空！');
            }

            $data['rid'] = $param['rid'];
            $data['carousel'] = implode(',',$param['photo_urls']);
            $data['carousel_name'] = implode(',',$param['photo_names']);
            $data['create_time'] = time();

            $rel = Db::name('carousel')
                ->insert($data);

            if(!$rel)
            {
                $this->error('添加失败！');
            }

            $this->success('添加成功','index');


        }
    }

    /**轮播图片编辑**/
    public function carouseEdit()
    {
        if($this->request->isGet())
        {
            $id = $this->request->param('id',0,'intval');

            $rooms = $this->get_rid_list();

            $rid_list = cmf_get_current_admin_rid();
            $rel = Db::name('carousel')
                ->alias('c')
                ->field('c.*,rb.room')
                ->join('__ROOM_BASIC__ rb','rb.rid = c.rid')
                ->whereIn('c.rid',$rid_list)
                ->find($id);

            $rel['child_carouses'] = explode(',',$rel['carousel']);
            $rel['child_carname'] = explode(',',$rel['carousel_name']);

            if(!$rel)
            {
                $this->error('该轮播图片不存在');
            }

            $this->assign('rooms',$rooms);
            $this->assign('data',$rel);
            return $this->fetch();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();

            if(empty($param['rid']) || empty($param['id']))
            {
                $this->error('房间不能为空！');
            }

            $data['rid'] = $param['rid'];
            $data['carousel'] = implode(',',$param['photo_urls']);
            $data['carousel_name'] = implode(',',$param['photo_names']);
            $data['create_time'] = time();
            $rel = Db::name('carousel')
                ->where('id',$param['id'])
                ->update($data);

            if(!$rel)
            {
                $this->error('编辑失败');
            }

            $this->success('编辑成功！','index');
        }
    }
    /**轮播图片删除**/
    public function carouseDelete()
    {
        $id = $this->request->param('id',0,'intval');
        $ids = $this->request->param('ids/a');
        $rid_list = cmf_get_current_admin_rid();

        if(isset($id))
        {
            $rel = Db::name('carousel')                
                ->where('rid','in',$rid_list)
                ->delete($id);

        }
        if(isset($ids)){
            $rel = Db::name('carousel')
                ->where('rid','in',$rid_list)
                ->delete($ids);
        }

        if(!$rel)
        {
            $this->error('删除失败!','');
        }

        $this->success('删除成功!','');
    }

}