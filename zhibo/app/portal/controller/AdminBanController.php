<?php

namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\RoomBasicModel;
use app\portal\model\MemberModel;
use app\portal\model\IpBanModel;
use think\Validate;
use think\Db;
class AdminBanController extends AdminBaseController
{
	/**
	 * ip黑名单首页
	 */
	public function index()
	{
        $param = $this->request->param();
		$IpBanModel = new IpBanModel();
		$ban = $IpBanModel->getIpBan($param);

		$this->assign('ban',$ban->all());
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('page', $ban->render());
		return view('banIP');
	}

    /**
        Ip新增黑名单
     **/
    public function addIpBan()
    {
        if($this->request->isGet())
        {
            return $this->fetch();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();


            $rel = $this->validate($param,'IpBan');

            if($rel !== true)
            {
                $this->error($rel);
            }

            $param['create_uid'] = cmf_get_current_admin_id();
            $param['create_time'] = time();

            $result = IpBanModel::create($param);

            if(!$result)
            {
                $this->error('数据新增失败！','index');
            }
            $this->success('数据新增成功！','index');
        }
    }

    /**
     * delete
     */
    public function delete()
    {

        $param  = $this->request->param();
        $IpBanModel = new IpBanModel();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $result = $IpBanModel->where(['id' => $id])->delete();            

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $IpBanModel->where(['id' => ['in', $ids]])->delete();
        }

        if (!$result) {
            $this->error('删除失败',"");
        }
            $this->success("删除成功！", '');
    } 

    /**
     * 黑名单用户首页
     */
    public function banUser()
    {
        $param = $this->request->param();
        $rid_list = cmf_get_current_admin_rid();

        $where = [
            'a.ban' => 1,
        ];
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
        if (!empty($keyword)) {
            $where['a.username'] = ['like', "%$keyword%"];
        }
        if (!empty($param['room'])) {
            $where['a.rid'] = ['like','%'.$param['room'].'%'];
        }else{
            $where['a.rid'] =['in',$rid_list];
        }

        $banUser = Db::name('member')
            ->alias('a')
            ->field('a.id,a.nickname,a.rid,b.room')
            ->join('__ROOM_BASIC__ b','a.rid in (b.rid)','left')
            ->where($where)
            ->paginate(config('admin_page_size'))
            ->appends($param);

        $rooms = $this->get_rid_list();

        $data = array_map('switch_rid_ridname',$banUser->all());

        $this->assign('banUser',$data);
        $this->assign('page',$banUser->render());
        $this->assign('rooms',$rooms);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('room', isset($param['room']) ? $param['room'] : '');
        return $this->fetch('banUser');
    }

    /**添加用户黑名单**/
    public function addbanuser()
    {
        if($this->request->isGet())
        {
            $rooms = $this->get_rid_list();
            $this->assign('rooms',$rooms);
            return view();

        }else if($this->request->isPost())
        {
            $param = $this->request->param();
            $id = isset($param['mid']) ? $param['mid'] : 0;
            $rule = [
                'rid'     =>    'require',
                'mid'     =>    'require|number'
            ];

            $msg = [
                'rid.require'         =>    '房间号必须存在',
                'mid.require'         =>    '用户必须存在',
                'mid.number'          =>    '用户id必须是数字'
            ];

            $validate = new Validate($rule,$msg);
            $rel = $validate->check($param);

            if(!$rel)
            {
                $this->error($validate->getError(),'addbanuser');
            }

            $result = DB::name('member')
                ->where('id',$id)
                ->setField('ban',1);

            if(!$result)
            {
                $this->error('添加失败！','addbanuser');
            }

            $this->success('添加成功！','banUser');
        }
    }


    /**
     * 修改资料
     */
    public function edit()
    {
        $id = $this->request->param('id',0,'intval');
        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);
        $role  = $this->getAdminUser();
        $member  = Db::name('member')->field('id,nickname')->select();
        $teacher = Db::name('teachers')->alias('a')->field("a.*,r.room")->join("__ROOM_BASIC__ r",'a.rid = r.rid','left')->select();
        $members = Db::name('member')->alias('a')->field('a.*,b.id roleid,b.rolename,c.room')->join('__PROTAL_ROLE__ b',"a.adminid = b.id")->join('__ROOM_BASIC__ c','a.rid = c.rid','left')->where('a.id',$id)->find();
        // dump($members);die;
        $this->assign('members',$members);
        $this->assign('member',$member);
        $this->assign('teacher',$teacher);
        $this->assign('role', $role);
        $this->assign('rooms', $rooms);
        return $this->fetch();
    }

    /**
     * 点击保存按钮提交
     */
    public function editPost()
    {
        $param  = $this->request->param();

        $MemberModel = new MemberModel();
        $res = $MemberModel->addPost($param);
        if(!$res){
            $this->error('失败');
        }
        $this->success('成功',url('AdminBan/banUser'));
    }

    /**
     * 修改密码
     */
    public function ModifyPassword()
    {
        $id = $this->request->param('id',0,'intval');
        $member = Db::name('member')->field('id,nickname')->where('id',$id)->find();
        // dump($member);die;
        $this->assign('member',$member);
        return $this->fetch('modify');
    }

    /**
     * 修改密码提交
     */
    public function ModifyPost()
    {
        if ($this->request->isPost()) {

            $validate = new Validate([
                'password' => 'require|max:16|min:6',
                'cfpassword' => 'require',
            ]);

            $validate->message([
                'password.require' => '密码必须',
                'password.max' => '密码最大长度为16个字符',
                'password.min' => '密码最小长度为6个字符',
                'cfpassword.require' => '确认密码必须',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if($data['password'] != $data['cfpassword']){
                $this->error('两次输入的密码不一致！');
            }
            $MemberModel = new MemberModel();   
            $res = $MemberModel->addPost($data);
            if(!$res){
                $this->error('失败');
            }
            $this->success('成功',url('AdminBan/banUser'));
        } else {
            $this->error("请求错误");
        }
    }  

    /**
     * 获取房间列表
     * @return array
     */
    private function getRooms()
    {
        $roomBasicModel = new RoomBasicModel();
        return $roomBasicModel->field('rid,room')->select()->toArray();
    }

    /**
     * 获取用户组
     * @return array
     */
    private function getAdminUser()
    {
        return Db::name('protal_role')->field('id,rolename')->select()->toArray();
    }

    /**
     * delete
     */
    public function BanUserDelete()
    {

        $param  = $this->request->param();
        $MemberModel = new MemberModel();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $result = $MemberModel->where(['id' => $id])->update(['ban'=>0]);            

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $MemberModel->where(['id' => ['in', $ids]])->update(['ban'=>0]);
        }

        if (!$result) {
            $this->error('删除失败',"");
        }
            $this->success("删除成功！", '');
    } 
}