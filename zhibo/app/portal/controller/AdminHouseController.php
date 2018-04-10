<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:kane < chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use app\portal\model\AdminHouseModel;
use \NewStreamController;
use app\portal\validate\AdminHouseValidate;
use app\portal\model\AdminHousevModel;
use \Exception;
use think\Loader;
use think\Validate;

/**
 * Class AdminHouseController 房间管理控制器
 * @package app\portal\controller
 */
class AdminHouseController extends AdminBaseController
{
    /**新增房间**/
    function addHouse(Request $request)
    {
        Vendor('alyvideo.NewStreamController');                                              //引入阿里云视频播放类
        if (request()->isGet()) {
            $index = $this->request->param('index','hbindex');

            $app = new NewStreamController();                                                //实例化类
            $partten = '/rtmp:\/\/video-center\.alivecdn\.com\/live/is';                     //中心推流地址
            $streamName = sha1(uniqid(true));                                                //随机生成流名称
            $url = $app->getPushSteam($streamName);                                              //推流地址
            $data['pc_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'flv');        //pc端拉流地址
            $data['m_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'm3u8');         //app端拉流地址
            $url = strstr($url, 'live/');
            $data['token'] = substr($url, 5);                                                     //token
            $data['time'] = date('Y-m-d H:i:s', time() + 3600 * 24);                              //有效期
            $data['c_pull_site'] = 'rtmp://video-center.alivecdn.com/live';

            $this->assign('data', $data);
            $this->assign('index', $index);
            return $this->fetch();

        } else {                                  //如果是post 方式
            if ($request->isPost()) {
            $index = $this->request->param('index','hbindex');
                Db::startTrans();
                $viedo_model = new AdminHousevModel();
                $model = new AdminHouseModel($_POST);
                try {
                    $data = $request->post();

                    $exrid = AdminHouseModel::withTrashed()->where('rid',$data['rid'])->find();                  //查看房间是否存在
                    if($exrid)
                    {
                        AdminHouseModel::destroy(['rid'=>$data['rid']],true);
                    }

                    //模型数据验证
                    $rel = $model->validate(true)
                        ->allowField(true)
                        ->save($data);

                    if($rel === false)
                    {
                       $this->error($model->getError());
                    }

                    $rule = [
                        'YYfcode' => 'require|number|length:6,13',
                        'YYcode'  => 'require|numberlength:6,13'
                    ];
                    $message = [
                        'YYfcode.number' => 'YY房间号必须是数字',
                        'YYfcode.require' => 'YY房间号必须存在',
                        'YYfcode.length'  =>  'yy房间号最少6位,最多13位',
                        'YYcode.require'   => 'YY账号必须存在',
                        'YYcode.number'   => 'YY账号必须是数字',
                        'YYcode.length'    =>  'yy号最少6位,最多13位',
                    ];

                    $exvrid = AdminHousevModel::withTrashed()->where('rid',$data['rid'])->find();

                    if($exvrid)
                    {
                        AdminHousevModel::destroy(['rid'=>$data['rid']],true);
                    }

                    if($data['play_source'] == 1)
                    {
                       //验证数据并写入数据库
                         $viedo_model->validate($rule, $message)
                            ->allowField(true)
                            ->save($data);
                    }else{
                         $viedo_model->allowField(true)->save($data);
                    }

                    Db::commit();
                  }catch (\Exception $e) {
                    // 回滚事务
                        if(!empty($model->getError()))
                        {
                            $this->error($model->getError());
                        }else if(!empty($viedo_model->getError()))
                        {
                            $this->error($viedo_model->getError());
                        }else{
                            $this->error('房间配置错误');
                        }
                        Db::rollback();
                   }
                $this->success('房间配置成功',$index);
                }
        }
    }


    /**验证用户新增的房间号是否存在**/
    public function check_roomid(Request $request)
    {
        $rid = $request->post('rid');
        if (!$rid) {
            return json_encode(["code" => 1, "msg" => "房间号为空"]);
            exit;
        }
        $rel = model('adminHouse')
            ->where('rid', $rid)
            ->field('id')
            ->column('id,delete_time');

        if ($rel) {
            $value = array_values($rel)[0];
            if(!$value)
            {
                return json_encode(["code" => 2, "msg" => "该房间号已存在,如强制添加将覆盖原数据"]);
            }
        }

        return json_encode(["code" => 0, "msg" => ""]);
    }

    /**房间相关图片上传**/
    public function image_upload(Request $request)
    {
        $fileinfo = $request->file('file');
        //图片上传路径
        $path = UPLOAD_PATH . 'room_img';

        $info = $fileinfo->validate(['size' => 100 * 1024, 'ext' => 'jpg,png,gif,jpeg'])->move($path);

        if ($info) {
            $filename = $info->getFilename();
            $data = date('Ymd');
            echo json_encode(['code' => 0, 'msg' => '', 'data' => '/upload/room_img/' . $data . '/' . $filename]);
        } else {
            echo json_encode(['code' => 1, 'msg' => '上传错误！', 'data' => '']);
        }
    }
    /***房间视频信息管理**/
    public function hvindex()
    {
        $param = $this->request->param();
        $child_rid = array_keys($this->get_rid_list());          //当前角色下的房间

        //视频基本信息
        $selectv = model('adminHousev')
            ->alias('r')
            ->join('room_basic b','r.rid = b.rid')
            ->field('r.*,b.room');

        if(!empty($param['ridv']))
        {
            $selectv->where('r.rid',$param['ridv']);
        }else{
            $selectv->where('r.rid','in',$child_rid);
        }

        if(!empty($param['keywordv']))
        {
            $selectv->where('r.rid|b.room|r.YYfcode|r.YYcode|r.pull_video_token|r.pull_video_site','like',"%$param[keywordv]%");
        }
        //视频基本信息分页
         $data = $selectv->paginate(config('admin_page_size'))->appends($param);

         $this->assign('page',$data->render());
         $this->assign('ridv',isset($param['ridv']) ? $param['ridv'] : '' );
         $this->assign('room_video', $data->all());                    //显示房间视频信息
         $this->assign('keywordv',isset($param['keywordv']) ? $param['keywordv'] : '' );
         $this->assign('room', $this->get_rid_list());                //显示所有房间名称和id
        return $this->fetch();
    }
    /***房间基本信息管理**/
    public function hbindex()
    {
        $param = $this->request->param();

        $child_rid = array_keys($this->get_rid_list());          //当前角色下的房间

        $select = model('adminHouse');
        if(!empty($param['rid']))
        {
            $select->where('rid',$param['rid']);
        }
        if(!empty($param['keyword']))
        {
            $select->where('rid|room','like',"%$param[keyword]%");
        }

        $data = $select->where('rid','in',$child_rid)->paginate(config('admin_page_size'))->appends($param);




        $this->assign('page',$data->render());
        $this->assign('rid',isset($param['rid']) ? $param['rid'] : '' );
        $this->assign('keyword',isset($param['keyword']) ? $param['keyword'] : '' );
        $this->assign('room_basic', $data->all());                //显示房间基本信息
        $this->assign('room', $this->get_rid_list());                //显示所有房间名称和id

        return $this->fetch();
    }
    /**房间视频信息增加**/
    public function addHvinfo()
    {
        if($this->request->isGet())
        {
            Vendor('alyvideo.NewStreamController');
            $app = new \NewStreamController();                                                //实例化类
            $partten = '/rtmp:\/\/video-center\.alivecdn\.com\/live/is';                     //中心推流地址
            $streamName = sha1(uniqid(true));                                                //随机生成流名称
            $url = $app->getPushSteam($streamName);                                              //推流地址
            $data['pc_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'flv');        //pc端拉流地址
            $data['m_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'm3u8');         //app端拉流地址
            $url = strstr($url, 'live/');
            $data['token'] = substr($url, 5);                                                     //token
            $data['time'] = date('Y-m-d H:i:s', time() + 3600 * 24);                              //有效期
            $data['c_pull_site'] = 'rtmp://video-center.alivecdn.com/live';

            $this->assign('data', $data);
            return $this->fetch();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();

            if(isset($param['play_source']) && $param['play_source'] == 1 )              //如果视频源是YY则验证YY房间号及YY号
            {
                if(empty(intval($param['YYfcode'])) || empty(intval($param['YYcode'])))
                {
                    $this->error('YY号以及YY房间号错误');
                }

                $rel = $this->Validate($param,'adminHv');
                if($rel !== true)
                {
                    $this->error($rel);
                }

            }else if(isset($param['play_source']) && $param['play_source'] == 2 )      //视频源为第三方推流
            {
               if(empty($param['rid']))
               {
                   $this->error('房间号必须');
               }
            }

            //查找该房间是否存在视频信息
            $real = model('adminHousev')->withTrashed()->where('rid',$param['rid'])->find();

            if($real)
            {
                AdminHousevModel::destroy(['rid'=>$param['rid']],true);
            }

            $result = model('adminHousev')->allowField(true)->isUpdate(false)->save($param);

            if(!$result)
            {
                $this->error('新增错误');
            }
            $this->success('新增成功',url('hvindex'));
        }
    }
    /**房间基本信息修改**/
    public function hbasic_edit(Request $request)
    {
        if ($request->isGet()) {
            $id = $request->param('id');
            $data = model('adminHouse')
                ->where('id', $id)->find();

            if (!$data) {
                $this->error('该房间不存在');
            }

            return $this->fetch('', ['data' => $data]);

        } else {
            if ($request->isPost()) {

                Db::startTrans();
                $basic_model = new AdminHouseModel();
                try {
                    $data = $request->post();
                    $rel = $basic_model
                        ->validate('AdminHouse')
                        ->isUpdate(true)
                        ->allowField(true)
                        ->save($data);

                   if($rel === false)
                   {
                       throw new \think\Exception();
                   }
                    Db::commit();
                    // 提交事务
                } catch (\Exception $e) {
                    // 回滚事务
                    $this->error($basic_model->getError());
                    Db::rollback();
                }
            }
            $this->success('房间信息更新成功！','hbindex');
        }
    }

    /**房间基本信息删除**/
    public function hbasic_delete(Request $request)
    {
        $param = $request->param();

        $model = new AdminHouseModel();

        if(isset($param['id']))
        {
            $data = $model->field('rid')
                ->where('id',$param['id'])
                ->find()->toArray();
            if(empty($data))
            {
                $this->error('该房间不存在','hbindex');
            }
            $rid = intval($data['rid']);

            //查询改房间下是否有用户
            $riduser = model('Member')
                ->where('rid','like',"%".$rid."%")
                ->column('id');

            if(!empty($riduser))
            {
               $this->error('该房间下还有用户,不允许删除！','hbindex');
            }


            //启动事务
            DB::transaction(function() use ($param,$rid){
                 AdminHouseModel::destroy($param['id']);
                 AdminHousevModel::destroy(['rid'=>$rid]);
            });
        }else if(isset($param['ids']))
        {

            $date =$model->where('id','in',$param['ids'])
                ->column('rid');

            if(empty($date))
            {
                $this->error('要删除的房间不存在','hbindex');
            }

            //查询要删除的房间下是否有用户
            $userrid = model('Member')
                ->column('rid');

            $rid_int = array_intersect($userrid,$date);            //所有用户房间和要删除的房间是否有交集，如果有则说明被删的房间下有用户

            if(!empty($rid_int))
            {
                $this->error('要删除房间下还有用户,不允许删除！','hbindex');
            }



            //开启事务
             DB::transaction(function() use ($param,$date){

                     $data = AdminHouseModel::destroy(function($query) use ($param){
                         $query->where('id','in',$param['ids']);
                     });

                 AdminHousevModel::destroy(function($query) use ($date){
                     $query->where('rid','in',$date);
                 });
             });
        }

        $this->success('删除成功！','hbindex');
    }


    /**视频信息编辑**/
    public function hv_edit(Request $request)
    {
        if ($request->isGet()) {
            Vendor('alyvideo.NewStreamController');
            $id = $request->param('id');

            $data = DB::name('room_video_set')
                ->alias('a')
                ->join('room_basic b', 'a.rid=b.rid', 'LEFT')
                ->field('a.*,b.room')
                ->where('a.id', $id)
                ->find();

            if (!$data) {
                $this->error('房间不存在!');
            }

            $pull_time = strtotime($data['pull_video_indate']);                               //推送过期

            //当过期时间小于当前时间，或者推送地址为空的时候
            if ($pull_time <= time() || $data['pull_video_site'] == "") {
                $app = new NewStreamController();
                $partten = '/rtmp:\/\/video-center\.alivecdn\.com\/live/is';                   //中心推流地址
                $streamName = sha1(uniqid(true));                                             //随机生成流名称

                $url = $app->getPushSteam($streamName);                                        //生成鉴权推流地址
                $data['pc_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'flv');        //鉴权pc端拉流地址
                $data['m_play_site'] = $app->getPullSteam($streamName, 3600 * 24, 'm3u8');       //鉴权app端拉流地址
                $url = strstr($url, 'live/');
                $data['pull_video_token'] = substr($url, 5);                                      //token
                $data['pull_video_site'] = 'rtmp://video-center.alivecdn.com/live';

            }

            $data['pull_video_indate'] = date('Y-m-d H:i:s', time() + 3600 * 24);
            $data['pc_play_indate'] = date('Y-m-d H:i:s', time() + 3600 * 24);
            $data['m_play_indate'] = date('Y-m-d H:i:s', time() + 3600 * 24);

            $this->assign('data', $data);
            return $this->fetch();

        } else {
            if ($request->isPost()) {
                $data = $request->post();

                $play_type = $request->post('play_source',0,'intval');
                $model = new AdminHousevModel();
                if($play_type == 1)
                {
                    $rule = [
                        'YYfcode' => 'require|number|length:6,13',
                        'YYcode'  => 'require|number|length:6,13'
                    ];

                    $message = [
                        'YYfcode.require' => 'YY房间号必须',
                        'YYfcode.number'  => 'YY房间号必须是数字',
                        'YYfcode.length' => 'YY房间号长度必须在6-15位之间',
                        'YYcode.require'  => 'YY号必须',
                        'YYcode.number'   => 'YY号必须是数字',
                        'YYcode.length'  => 'YY号长度必须在6-15位之间'
                    ];

                    $rel = $model
                        ->validate($rule, $message)
                        ->isUpdate(true)
                        ->allowField(true)
                        ->save($_POST, ['id' => $data['id']]);
                }else{
                    $rel = $model
                        ->isUpdate(true)
                        ->allowField(true)
                        ->save($_POST, ['id' => $data['id']]);
                }

                if (!$rel) {
                    $this->error($model->getError());
                }

                $this->success('房间视频信息更新成功！',url('hvindex'));
            }
        }
    }

    /**房间视频播放信息删除**/
    public function hv_delete(Request $request)
    {
        $param = $request->param();
        if(isset($param['id']))
        {
            $model =AdminHousevModel::get($param['id']);
            $rel = $model->delete();
        }else if(isset($param['ids'])){
            $rel = AdminHousevModel::destroy(function($query) use ($param){
                $query->where('id','in',$param['ids']);
            });
        }

        if (!$rel) {
            $this->error('删除失败！',url('hvindex',['action'=>1]));
        }

        $this->success('删除成功！',url('hvindex',['action'=>1]));
    }


    /**
     * 房间下拉框多选功能
    **/
    public function select(Request $request)
    {
        $ids = $request->param('ids');
        $isall = $request->param('isall',0,'intval');
        $rid_attr = cmf_get_current_admin_rid();
        $data = AdminHouseModel::where('rid','in',$rid_attr)->select();
        return $this->fetch('',['data'=>$data,'ids'=>$ids,'isall'=>$isall]);
    }
    /**
     * 房间下拉框单选功能
     **/
    public function selectOne(Request $request)
    {
        $ids = $request->param('ids',0,'intval');
        $rid_attr = cmf_get_current_admin_rid();
        $data = AdminHouseModel::where('rid','in',$rid_attr)->order('rid')->select();
        return $this->fetch('',['data'=>$data,'ids'=>$ids]);
    }

    /**
       copy房间配置，一键生成房间配置
     **/
    public function hv_copyHouse()
    {
        $id = $this->request->param('id', 0, 'intval');

        $model = new AdminHouseModel();

        $result = $model->copyHouse($id);

        if (!$result) {
            $this->error('复制房间信息错误！');
        }

        $this->error('成功！', 'hbindex');
    }

}


