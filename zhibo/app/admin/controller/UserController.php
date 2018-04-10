<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;
/**
 * Class UserController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   => '管理组',
 *     'action' => 'default',
 *     'parent' => 'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {

       // $where = ["user_type" => 1];
        /**搜索条件**/

        $where = '';
        $userLogin = $this->request->param('user_login');
        $userEmail = trim($this->request->param('user_email'));

        if ($userLogin) {
            $where['u.username'] = ['like', "%$userLogin%"];
        }

        if ($userEmail) {
            $where['u.email'] = ['like', "%$userEmail%"];;
        }
        $child_role = array_keys($this->get_role_list());

        if(cmf_get_current_admin_id() === 1)
        {
            $users = DB::name('user')
                ->alias('u')
                ->field('u.*,r.id,r.name,r.rid,r.isall')
                ->join('__ROLE_USER__ ru','ru.user_id = u.mid')
                ->join('__ROLE__ r','r.id = ru.role_id')
                ->where('r.status',1)
                ->where($where);
        }else{
            $users = DB::name('user')
                ->alias('u')
                ->field('u.*,r.id,r.name,r.rid,r.isall')
                ->join('__ROLE_USER__ ru','ru.user_id = u.mid')
                ->join('__ROLE__ r','r.id = ru.role_id')
                ->where('r.status',1)
                ->where('r.id','in',$child_role)
                ->where($where);
        }

        $data2 = $users->paginate(config('admin_page_size'))->appends(['username' => $userLogin, 'email' => $userEmail]);
        // 获取分页显示

        $page = $data2->render();
           foreach($data2 as $key=>&$value) {
                /**用户所属的角色，角色对应的房间号**/
                if(strpos($value['rid'],',') === false)
                {
                    $subquery[] = $value['rid'];
                }else{
                    $subquery = explode(',',$value['rid']);
                }

                $role_rid_attr = $subquery;           //用户所属的角色，角色所属的房间数组
                unset($subquery);
                    $fid_attr = [];
                    foreach($role_rid_attr as $ke => $ve){
                        $da = DB::name('room_basic')
                            ->alias('rb')
                            ->where('rid',$ve)
                            ->value('room');

                        $fid_attr[] =$da;
                    }
                    $fidname = implode(',',$fid_attr);             //用户所属的角色，所属的房间名

                /**用户所属的角色，角色对应的房间号**/

            $new_users[$key] = $value;
            $new_users[$key]['fidname'] = $fidname;
        }


        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }
        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $new_users);
        return $this->fetch();
    }

    /**
     * 管理员添加
     * @adminMenu(
     *     'name'   => '管理员添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $roles = $this->get_role_list();
        $this->assign("roles", $roles);
        return $this->fetch();
    }

    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {

        if ($this->request->isPost()) {
            if (!empty($_POST['role_id'])) {
                $role_ids = $_POST['role_id'];
                unset($_POST['role_id']);

                $result = $this->validate($this->request->only('username,password,email'), 'User');

                if ($result !== true) {
                    $this->error($result);
                } else {
                    $_POST['password'] = cmf_password($_POST['password']);

                    $result             = DB::name('user')->insertGetId($_POST);
                    if ($result !== false) {
                        //$role_user_model=M("RoleUser");
                            if (cmf_get_current_admin_id() != 1 && $role_ids == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            Db::name('RoleUser')->insert(["role_id" => $role_ids, "user_id" => $result]);

                        $this->success("添加成功！", url("user/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员编辑
     * @adminMenu(
     *     'name'   => '管理员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $roles = $this->get_role_list();
        $this->assign("roles", $roles);

        $role_id = DB::name('RoleUser')->where(["user_id" => $id])->value("role_id");
        $this->assign("role_ids", $role_id);

        $user = DB::name('user')
            ->alias('u')
            ->field('u.*,ru.role_id')
            ->join('__ROLE_USER__ ru','ru.user_id = u.mid')
            ->where(["mid" => $id])
            ->find();
        $this->assign('data',$user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id'])) {
                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                }


                $role_id = $this->request->param('role_id');
                unset($_POST['role_id']);

                $result = Validate::is($this->request->param('username'), 'require');
                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {

                    $result = DB::name('user')->update($_POST);

                    if ($result !== false) {
                        $uid = $this->request->param('mid', 0, 'intval');
                        DB::name("RoleUser")->where(["user_id" => $uid])->delete();

                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }

                            DB::name("RoleUser")->insert(["role_id" => $role_id, "user_id" => $uid]);

                        $this->success("保存成功！",'index');
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where(["mid" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['mid']       = cmf_get_current_admin_id();
            $create_result    = Db::name('user')->update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！", 'admin/user/userinfo');
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        $ids = isset($this->request->param()['ids']) ? $this->request->param() : 0;


        if(!empty($id))
        {
            if ($id == 1) {
                $this->error("最高管理员不能删除！");
            }

            if (Db::name('user')->delete($id) !== false) {
                Db::name("RoleUser")->where(["user_id" => $id])->delete();
                $this->success("删除成功！",'');
            } else {
                $this->error("删除失败！");
            }
        }else if(!empty($ids)){
            if (in_array(1,$ids)) {
                $this->error("最高管理员不能删除！");
            }

            if (Db::name('user')->delete($ids) !== false) {
                Db::name("RoleUser")->where("user_id",'in',$ids)->delete();
                $this->success("删除成功！",'');
            } else {
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["mid" => $id])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("管理员停用成功！", url("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        $type = $this->request->param('type');
        if (!empty($id)) {
                $result = Db::name('user')->where(["mid" => $id])->setField('user_status', 1);

                if ($result !== false) {
                    $this->success("管理员启用成功！", url("user/index"));
                } else {
                    $this->error('管理员启用失败！');
                }

        } else {
            $this->error('数据传入失败！');
        }
    }

}