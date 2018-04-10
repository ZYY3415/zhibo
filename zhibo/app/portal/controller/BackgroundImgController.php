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
use think\Exception;
use think\Validate;

/**
 * 房间背景图片类
 **/
class BackgroundImgController extends AdminBaseController
{
    /**房间背景图片添加类**/
    public function add()
    {
      if($this->request->isGet())
      {
          return $this->fetch();
      }else if($this->request->isPost())
      {
          $param = $this->request->param();

          $rule = [
              'rid'  =>    'require|number',
              'photo_urls'  =>    'require',
              'photo_names'  =>    'require',
          ];

          $msg = [
              'rid.require'  =>   "房间号必须存在",
              'rid.number'  =>   "房间号必须存在",
              'photo_urls.require'  =>   "背景图片必须存在",
              'photo_names.require'  =>   "背景图片名必须存在",
          ];

          $validate = new Validate($rule,$msg);
          $rel = $validate->check($param);

          if(!$rel)
          {
              $this->error($validate->getError());
          }

          $img_attr = $param['photo_urls'];
          $data['imgs'] = implode(',',$img_attr);
          $data['imgnames'] = implode(',',$param['photo_names']);
          $data['rid']  =$param['rid'];
          $data['create_time']  =time();

          $select = Db::name('bgimg')->where('rid',$data['rid'])->find();
          if(!$select)
          {
              $rel = Db::name('bgimg')
                  ->insert($data);
          }else{
              $rel = Db::name('bgimg')->where('rid',$data['rid'])->update(['imgs'=>$data['imgs'],'imgnames'=>$data['imgnames'],'create_time'=>time()]);
          }

          if(!$rel)
          {
              $this->error('添加失败！');
          }
          $this->success('添加成功！','index');
      }
    }

    public function index()
    {
        $param = $this->request->param();
        $select = Db::name('bgimg');
        $ridlist = cmf_get_current_admin_rid();

        $ridAttr = $this->get_rid_list();
        if(!empty($param['room']))
        {
            $where['m.rid'] = $param['room'];
        }else{
            $where['m.rid'] = ['in',$ridlist];
        }

        if(!empty($param['keyword']))
        {
            $where['m.rid|m.id|m.imgs'] = ['like',"%{$param['keyword']}%"];
        }

        $obj = $select
            ->alias('m')
            ->field('m.*,rb.room')
            ->join('__ROOM_BASIC__ rb','rb.rid=m.rid')
            ->where($where)
            ->paginate(config('admin_page_size'))
            ->appends($param);

        $data = array_map(function($attr){
            $attr['child_imgs'] = explode(',',$attr['imgs']);
            return $attr;
        },$obj->all());


        $this->assign('page',$obj->render());
        $this->assign('data',$data);
        $this->assign('room',isset($param['room']) ? $param['room'] : '');
        $this->assign('keyword',isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('rooms',$ridAttr);

        return view();
    }
    public function edit()
    {
        if($this->request->isGet())
        {
            $default = cmf_get_default_rid();
            $id = $this->request->param('id',0,'intval');
            $ridlist = cmf_get_current_admin_rid();
            $rel = Db::name('bgimg')
                ->alias('b')
                ->field('b.*,rb.room')
                ->join('__ROOM_BASIC__ rb','rb.rid = b.rid')
                ->where('b.id',$id)
                ->where('b.rid','in',$ridlist)
                ->find();

            $rel['child_imgs'] = explode(',',$rel['imgs']);
            $rel['child_imgnames'] = explode(',',$rel['imgnames']);

            if(!$rel)
            {
                $this->error('数据不存在！');
            }

            $this->assign('data',$rel);

            return view();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();
            $id = isset($param['id']) ?$param['id'] : 0;
            $ridlist = cmf_get_current_admin_rid();
            $rule = [
                'rid'  =>    'require|number',
                'id'  =>    'require|number',
                'photo_urls'  =>    'require',
                'photo_names'  =>    'require',
            ];

            $msg = [
                'rid.require'  =>   "房间号必须存在",
                'rid.number'  =>    "房间号必须存在",
                'photo_urls.require'  =>   "背景图片必须存在",
                'photo_names.require'  =>   "背景名必须存在",
            ];

            $validate = new Validate($rule,$msg);
            $rel = $validate->check($param);

            if(!$rel)
            {
                $this->error($validate->getError());
            }

            $select = Db::name('bgimg')
                ->where('id',$id)
                ->where('rid','in',$ridlist)
                ->find();

            if(!$select)
            {
                $this->error('编辑出错！');
            }

            $data['rid'] = $param['rid'];
            $data['imgnames'] = implode(',',$param['photo_names']);
            $data['imgs'] = implode(',',$param['photo_urls']);
            $data['create_time'] = time();

            $result = Db::name('bgimg')
                ->where('id',$id)
                ->update($data);

            if(!$result)
            {
                $this->error('编辑出错！');
            }

            $this->success('编辑成功！','index');
        }
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = isset($param['id']) ? $param['id'] : 0;
        $ridlist = cmf_get_current_admin_rid();
        if(isset($param['id']))
        {
            $rel = Db::name('bgimg')
                ->where('id',$id)
                ->where('rid','in',$ridlist)
                ->find();

            if(!$rel)
            {
                 $this->error('删除错误1！');
            }

            $result = Db::name('bgimg')->delete($id);

        }else if(isset($param['ids']))
        {
            $rel = Db::name('bgimg')
                ->where('id','in',$param['ids'])
                ->where('rid','in',$ridlist)
                ->find();

            if(!$rel)
            {
                $this->error('删除错误1！');
            }

            $result = Db::name('bgimg')->delete($param['ids']);
        }

        if(!$result)
        {
            $this->error('删除错误2！');
        }

        $this->success('删除成功！','index');
    }
}