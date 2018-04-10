<?php
namespace app\portal\controller;

use app\portal\model\AdminHouseModel;
use app\portal\model\AdminRobotModel;
use cmf\controller\AdminBaseController;
use think\Request;
use think\Db;
/***
 * 房间机器人控制器
**/
class AdminRobotController  extends AdminBaseController
{
    /**机器人添加**/
    public function addrobot(Request $request)
    {
        if($request->isGet())
        {
            $aid = Db::name('protal_role')
                ->field('id,rolename')
                ->where('status',1)
                ->order('sort desc,id asc')
                ->select();

            $room =$this->get_rid_list();

            $this->assign('aid',$aid);
            $this->assign('room',$room);

           return $this->fetch();
        }
        else if($request->isPost())
        {
            $data = $request->post();
            $date = date('Y-m-d');
            $data['stime'] = strtotime($date.' '.$data['stime_str']);
            $data['etime'] = strtotime($date.' '.$data['etime_str']);


            if(!empty($data['week']))
            {
                $weeks  = array_keys($data['week']);
                $data['week'] = implode(',',$weeks);
            }


            $rel = $this->validate($data,'AdminRobot');

            if($rel !== true)
            {
                $this->error($rel);
                return false;
            }
            $child_rid = array_keys($this->get_rid_list());
            if(!in_array($data['rid'],$child_rid))
            {
                $this->error("房间号出错");
            }

            $real = DB::name('portal_robot')->insert($data);

            if(!$real)
            {
                $this->error('机器人新增失败');
            }
            $this->success('机器人新增成功','index');
        }

    }

    /**机器人批量添加**/
    function fkaddrobot()
    {
         $data = $this->request->post();

         if(empty($data['number'])){
             $this->error('数量不能为空！');
         }

        if($data['number'] <= 0 || $data['number'] >= 5000)
        {
            $this->error('数量只能在0-5000之间');
        }
        if(empty($data['week']))
        {
            $this->error('日期不能为空！');
        }
        if(empty($data['stime_str']))
        {
            $this->error('开始时间不能为空！');
        }
        if(empty($data['etime_str']))
        {
            $this->error('结束时间不能为空！');
        }

        $model =new AdminRobotModel();

        $child_rid = array_keys($this->get_rid_list());

        $model->faker_create($data,$child_rid);

        $this->success('批量生成成功！','index');
    }

    /**机器人显示**/
    public function index()
    {
        $search = $this->request->param();
        $child_rid = array_keys($this->get_rid_list());


        $select = AdminRobotModel::alias('r')
            ->join('cmf_room_basic b','r.rid = b.rid','LEFT')
            ->join('cmf_protal_role l','r.aid = l.id','LEFT')
            ->field('r.*,b.room,b.rid,l.rolename,l.id')
            ->where('r.rid','in',$child_rid);
        if(!empty($search['room']))
        {
            $select->where('b.rid',$search['room']);
        }
        if(!empty($search['role']))
        {
            $select->where('l.id',$search['role']);
        }
        if(!empty($search['keyword']))
        {
            $select->where('b.room|l.rolename|r.status|r.username','like',"%".$search['keyword']."%");
        }


        $data = $select->paginate(config('admin_page_size'))->appends($search);;
        $this->assign('data',$data->all());

        //查询所有房间名信息
        $room = $this->get_rid_list();

        $this->assign('rooms',$room);

        //查询所有角色信息
        $roles= Db::name('protal_role')->column('id,rolename');
        $this->assign('roles',$roles);

        //分页
        $page = $data->render();
        $this->assign('page',$page);

        //查询信息初始化
        $this->assign('room',isset($search['room']) ? $search['room'] : '');
        $this->assign('role',isset($search['role']) ? $search['role'] : '');
        $this->assign('start_time',isset($search['start_time']) ? $search['start_time'] : '' );
        $this->assign('end_time',isset($search['end_time']) ? $search['end_time'] : '');
        $this->assign('keyword',isset($search['keyword']) ? $search['keyword'] : '');

         return $this->fetch();
    }

    /**机器人编辑**/
    public function editrobot()
    {
       if($this->request->isGet())
       {
           $id = $this->request->route('id');
           $data = AdminRobotModel::get($id);
           $this->assign('data',$data);

           $room =$this->get_rid_list();
           $this->assign('room',$room);


           $aid = DB::name('protal_role')
           ->field('id,rolename')
               ->select();

           $this->assign('aid',$aid);
          return $this->fetch();

       }else if($this->request->isPost())
       {
           $insert = request()->post();

           if(empty($insert['username']))
           {
               $this->error('用户名必须存在');
           }
           if(empty($insert['week']))
           {
               $this->error('日期不能为空！');
           }
           if(empty($insert['stime_str']))
           {
               $this->error('开始时间不能为空！');
           }
           if(empty($insert['etime_str']))
           {
               $this->error('结束时间不能为空！');
           }

           $date = date('Y-m-d');
           $insert['week'] = implode(',',array_keys($insert['week']));
           $insert['stime'] = strtotime($date.' '.$insert['stime_str']);
           $insert['etime'] = strtotime($date.' '.$insert['etime_str']);
           if($insert['etime'] <= $insert['stime'])
           {
               $this->error('开始时间必须小于结束时间');
           }

           $model = new AdminRobotModel();

           $rel = $model->allowField(true)->save($insert,$insert['mid']);

           if(!$rel)
           {
               $this->error('假人编辑失败');
           }
           $this->success('机器人编辑成功','index');
       }

    }

    /***假人编辑**/
    public function deleterobot()
    {
        $param = $this->request->param();

        if(isset($param['id']))
        {
            $rel = AdminRobotModel::destroy($param['id']);
        }
        if(isset($param['ids']))
        {
            $rel = AdminRobotModel::destroy(function($query) use ($param){
            $query->where('mid','in',$param['ids']);
            });
        }

        if(!$rel)
        {
            $this->error('删除失败！');
        }

        $this->success('删除成功！','index');
    }
}






