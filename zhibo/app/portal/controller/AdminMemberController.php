<?php

namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\RoomBasicModel;
use app\portal\model\MemberModel;
use think\Validate;
use think\Db;
use Kernal\Common;
class AdminMemberController extends AdminBaseController
{
    /**
     * 会员列表
     * 
     */
    public function index()
    {

        $param = $this->request->param();

        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);

        $MemberModel = new MemberModel();
        $member = $MemberModel->getMember($param,$room);

        $data = $member->toArray()['data'];

        foreach ($data as &$v) {
            $v['tuijianmid'] = Db::name('member')->field('nickname TJname')->where('id',$v['tuijianmid'])->find();
        }

        $data = array_map('switch_rid_ridname',$data);

        $obj = $member->all();

        $_SESSION['admin_member'] = serialize($obj);
        $role  = $this->getAdminUser();

        $member->appends($param);
        // dump($member);die;
        $this->assign('role', $role);
        $this->assign('rooms', $rooms);
        $this->assign('members',$data);
        $this->assign('page', $member->render());
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('roleid', isset($param['roleid']) ? $param['roleid'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return $this->fetch();
    }

    /**
     * 获取房间列表
     * @return array
     */
    private function getRooms($room)
    {
        $roomBasicModel = new RoomBasicModel();
        return $roomBasicModel->field('rid,room')->whereIn('rid',$room)->select()->toArray();
    }

    /**
     * 获取用户组
     * @return array
     */
    private function getAdminUser()
    {
        return Db::name('protal_role')->field('id,keyword,rolename')->select()->toArray();
    }

    /**
     * 新增会员
     */
    public function addMember()
    {

        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);

        $count = RoomBasicModel::count();

        $role  = $this->getAdminUser();
        $member = Db::name('member')->field('id,nickname')->select();
        // dump($member);die;
        $this->assign('member',$member);
        $this->assign('count',$count);
        $this->assign('role', $role);
        $this->assign('rooms', $rooms);
        return $this->fetch('add');
    }

    /**
     * 点击保存按钮提交
     */
    public function addPost()
    {
        $param  = $this->request->param();
        $result = $this->validate($param, 'AdminMember');
        if ($result !== true) {
            $this->error($result);
        }
        $rel = portal_rid_exists($param['rid']);;

        if(!$rel)
        {
            $this->error('房间信息错误');
        }

        $MemberModel = new MemberModel();
        $res = $MemberModel->addPost($param);
        if(!$res){
            $this->error('添加失败');
        }
        $this->success('添加成功',url('AdminMember/index'));
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
        $member = Db::name('member')->field('id,nickname')->select();
        $teacher = Db::name('teacher')->alias('a')->field("a.*,r.room")->join("__ROOM_BASIC__ r",'a.rid = r.rid','left')->select();
        $members = Db::name('member')->alias('a')->field('a.*,b.keyword,b.rolename,c.room')->join('__PROTAL_ROLE__ b',"a.adminid = b.keyword")->join('__ROOM_BASIC__ c','a.rid = c.rid','left')->where('a.id',$id)->find();

        $members['tuijianmid'] = Db::name('member')->field('id,nickname TJname')->where('id',$members['tuijianmid'])->find();
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
        
        $rel = portal_rid_exists($param['rid']);
        if(!$rel)
        {
            $this->error('房间信息错误');
        }
        $result = $this->Validate($param,'AdminMember.edit');

        if($result !== true)
        {
            $this->error($result);
        }

        $MemberModel = new MemberModel();
        $res = $MemberModel->addPost($param);
        if(!$res){
            $this->error('编辑失败,内容没有发生变化');
        }
        $this->success('修改资料成功',url('AdminMember/index'));
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
            $this->success('成功',url('AdminMember/index'));
        } else {
            $this->error("请求错误");
        }
    }  

    /**
     * delete
     */
    public function delete()
    {

        $param = $this->request->param();
        $MemberModel = new MemberModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $MemberModel->where(['id' => $id])->delete();            
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $MemberModel->where(['id' => ['in', $ids]])->delete();
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');    
        }
    } 

    /**
     * activate
     */
    public function activate()
    {

        $param = $this->request->param();
        $MemberModel = new MemberModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $MemberModel->where(['id' => $id])->update(["status"=>1]);            
            if (!$result) {
                $this->error('激活失败',"");
            }
                $this->success("激活成功！", '');

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $MemberModel->where(['id' => ['in', $ids]])->update(["status"=>1]);  
            if (!$result) {
                $this->error('激活失败',"");
            }
                $this->success("激活成功！", '');    
        }
    }

    public function export()
    {



        if(!isset($_SESSION['admin_member']))
        {
            return 'error';
        }
        $obj = unserialize($_SESSION['admin_member']);

        if(!is_array($obj)){
            $this->error('参数类型错误');
        }
       
        foreach($obj as $key=>$value)
        {

            $data[$key] = $value->toArray();            //导出的数据
        }

        $table_header = array_keys($obj[0]->toArray());           //表头

        $table_name = '用户表';                          //表名

        $excel = new Common();


        /**
        $data  要导出的数据 array array  二维数组
         * $table_header  表头信息  array  一位数组
         * $table_name  string  导出的Excel表名
         * $field_width array  [列名=>宽度]  这里宽度不要px  且只能是数字  列名注意要大写
         **/

        $field_width = ['G'=>20,'D'=>20];

        $excel->Excel($data,$table_header,$table_name,$field_width);        //调用Excel  导出方法

    }

    /**选择用户**/
    public function ajax_select_mid()
    {
        $param = $this->request->param();
        $rooms = $this->get_rid_list();
        $roles = Db::name('protal_role')
            ->where('status',1)
            ->column('keyword,rolename');

        $where = '';
        if(!empty($param['rid']))
        {
            $where['m.rid'] =['like','%'.$param['rid'].'%'];
        }
        if(!empty($param['roleid']))
        {
            $where['pr.keyword'] = $param['roleid'];
        }
        if(!empty($param['keyword']))
        {
            $where['m.id|m.nickname|pr.rolename'] = ['like','%'.$param['keyword'].'%'];
        }


        $rid_list = cmf_get_current_admin_rid();
        $userInfo = model('member')
            ->alias('m')
            ->field('m.id,m.nickname,pr.rolename,m.rid')
            ->join('__PROTAL_ROLE__ pr','m.adminid = pr.keyword')
            ->join('__ROOM_BASIC__ rb','rb.rid in (m.rid)')
            ->whereIn('rb.rid',$rid_list)
            ->where($where)
            ->paginate(config('admin_page_size'))
            ->appends($param);

        $data = array_map('switch_rid_ridname',$userInfo->all());

        $this->assign('data',$data);
        $this->assign('rooms',$rooms);
        $this->assign('page',$userInfo->render());
        $this->assign('roles',$roles);
        $this->assign('rid',isset($param['rid']) ?$param['rid'] :'');
        $this->assign('roleid',isset($param['roleid']) ?$param['roleid'] :'');
        $this->assign('room',isset($param['keyword']) ?$param['keyword'] :'');

        return view('selectmid');
    }

    /**
     * 批量导入房间
     */
    public function ImportRoom()
    {
        $param = $this->request->param();
        $MemberModel = new MemberModel();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $rid    = $this->request->param('ridname', 0, 'intval');
            $result = $MemberModel->where(['id' => $id])->update(["rid"=>$rid]);            
            if (!$result) {
                $this->error('导入房间失败',"");
            }
                $this->success("导入房间成功！", '');

        }
        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $rid    = $this->request->param('ridname', 0, 'intval');
            $result = $MemberModel->where(['id' => ['in', $ids]])->update(["rid"=>$rid]);  
            if (!$result) {
                $this->error('导入房间失败',"");
            }
                $this->success("导入房间成功！", '');    
        }
    }

}