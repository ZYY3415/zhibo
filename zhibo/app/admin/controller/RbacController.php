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
use app\admin\model\AdminMenuModel;
use app\admin\model\RoleModel;
use app\portal\model\RoomBasicModel;
use tree\Tree;

class RbacController extends AdminBaseController
{

    /**
     * 角色管理列表
     * @adminMenu(
     *     'name'   => '角色管理',
     *     'parent' => 'admin/User/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $param = $this->request->param();

        //取得本角色id和该角色的子角色id  数组
        $role_attr = array_keys($this->get_role_list());

        $select = Db::name('role')
            ->where('id', 'in', $role_attr)
            ->order(["id" => "ESC"]);


        if (!empty($param['room'])){
            $select->where('rid', 'like', '%' . $param['room'] . '%');
        }
        if (!empty($param['role'])){
            $select->where('id', $param['role']);
        }
        if (!empty($param['keyword'])){
            $select->where('id|rid|name|remark', 'like', "%" . $param['keyword'] . "%");
        }


        $data = $select->paginate(config('admin_page_size'))
            ->appends($param);
        $this->assign("page", $data->render());                   //分页


        $data = $data->all();

      //将数据中的房间号字符串转换为中文房间名

        $data = array_map('switch_rid_ridname',$data);


        //获取包含用户级别的数据，并将新数据的层级赋值给老数据
        $level_data = $this->tree->getTreeTdArray($this->level);

        foreach($level_data as $key=>$value)
        {
            foreach($data as $k=>$v)
            {
                if($v['id'] == $value['id'])
                {
                    $data[$k]['level'] = $value['level'];
                    $data[$k]['parent_name'] = $value['parent_name'];
                }
            }
        }

        $this->assign("roleas", $data);


        $rooms = $this->get_rid_list();
        $this->assign("rooms", $rooms);
        $roles = $this->get_role_list();

        $this->assign("roles", $roles);

        $this->assign('room', isset($param['room']) ? $param['room'] : '');
        $this->assign('role', isset($param['role']) ? $param['role'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return $this->fetch();
    }

    /**
     * 添加角色
     * @adminMenu(
     *     'name'   => '添加角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色',
     *     'param'  => ''
     * )
     */
    public function roleAdd()
    {

        $data = model('app\portal\model\AdminHouseModel')
            ->field('rid,room')
            ->order('rid')
            ->select();
        $count = $data->count();

        /**获取管理员所属角色的子角色**/
        $role =$this->get_role_list(1);

        $child_rattr = $this->tree->getTreeTdArray($this->level);

        $this->assign('tree_role', $child_rattr);


        $this->assign('data', $data);
        $this->assign('count', $count);

        return $this->fetch();
    }

    /**
     * 添加角色提交
     * @adminMenu(
     *     'name'   => '添加角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色提交',
     *     'param'  => ''
     * )
     */
    public function roleAddPost()
    {
        if ($this->request->isPost()) {

            $data = $this->request->param();

            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {

                $child_roleid = $this->tree->getTreeTdArray($this->level, 0, true);

                if (!is_array($child_roleid)) {
                    $this->error("添加角色失败");
                }

                if (!in_array($data['parent_id'], $child_roleid)) {
                    $this->error("父角色不存在");
                }
                $data['create_time'] = time();
                $result = Db::name('role')->insert($data);
                if ($result) {
                    $this->success("添加角色成功", url("rbac/index"));
                } else {
                    $this->error("添加角色失败");
                }

            }
        }
    }

    /**
     * 编辑角色
     * @adminMenu(
     *     'name'   => '编辑角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色',
     *     'param'  => ''
     * )
     */
    public function roleedit()
    {

        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }

        $data = RoleModel::where('id', $id)->find()->toArray();
        if (!$data) {
            $this->error("该角色不存在！");
        }

        if ($data['isall'] == 1) {
            $data['room'] = '所有房间';
        }else{
            $rooms = DB::name('room_basic')
                ->where('rid', 'in', $data['rid'])
                ->column('room');

            $room = implode(',', $rooms);
            $data['room'] = $room;
        }

        $count = DB::name('room_basic')
            ->count();

        $tree_role = $this->tree->getTreeTdArray($this->level);


        $this->assign('tree_role', $tree_role);
        $this->assign("data", $data);
        $this->assign("count", $count);
        return $this->fetch();
    }

    /**
     * 编辑角色提交
     * @adminMenu(
     *     'name'   => '编辑角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色提交',
     *     'param'  => ''
     * )
     */
    public function roleEditPost()
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        if ($this->request->isPost()) {
            $data = $this->request->param();

            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $child_role = Db::query("select count(b.id) num  from cmf_role a left join cmf_role b on a.id = b.parent_id where a.status = 1 and a.id=$id");
                if($child_role[0]['num'])
                {
                    $this->error("修改失败,该角色下还有角色，不允许修改。");
                }
                if (Db::name('role')->update($data) !== false) {
                    $this->success("保存成功！", url('rbac/index'));
                } else {
                    $this->error("保存失败！");
                }
            }
        }
    }

    /**
     * 删除角色
     * @adminMenu(
     *     'name'   => '删除角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除角色',
     *     'param'  => ''
     * )
     */
    public function roleDelete()
    {
        $id = $this->request->param("id", 0, 'intval');
        $ids = isset($this->request->param()['ids']) ? $this->request->param()['ids'] : 0;

        if (!empty($id)) {
            if ($id == 1) {
                $this->error("超级管理员角色不能被删除！");
            }

            $count = Db::name('RoleUser')->where(['role_id' => $id])->count();
            if ($count > 0) {
                $this->error("该角色已经有用户！", 'index');
            } else {

                $child_role = Db::query("select count(b.id) num  from cmf_role a left join cmf_role b on a.id = b.parent_id where a.id=$id");
                if($child_role[0]['num'])
                {
                    $this->error("修改失败,该角色下还有角色，不允许修改。");
                }

                $status = Db::name('role')->delete($id);
                if (!empty($status)) {
                    $this->success("删除成功！", url('rbac/index'));
                } else {
                    $this->error("删除失败！", 'index');
                }
            }
        } else {
            if (!empty($ids))              //批量删除
            {
                if (in_array(1, $ids)) {
                    $this->error("超级管理员角色不能被删除！", 'index');
                }
                $count = Db::name('RoleUser')->where('role_id', 'in', $ids)->count();
                if ($count > 0) {
                    $this->error("要删除的角色中已经有用户,不允许删除！");
                } else {
                    $idsStr = implode(',',$ids);
                    $child_role = Db::query("select count(b.id) num  from cmf_role a left join cmf_role b on a.id = b.parent_id where  a.id in ($idsStr)");
                    if($child_role[0]['num'])
                    {
                        $this->error("修改失败,所选角色下还有角色，不允许修改。");
                    }

                    $status = Db::name('role')->delete($ids);
                    if (!empty($status)) {
                        $this->success("删除成功！", url('rbac/index'));
                    } else {
                        $this->error("删除失败！", 'index');
                    }
                }
            }
        }
    }

    /**
     * 设置角色权限
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     */
    public function authorize()
    {
        $AuthAccess = Db::name("AuthAccess");
        $adminMenuModel = new AdminMenuModel();
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $tree = new Tree();
        $tree->icon = ['│ ', '├─ ', '└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $adminMenuModel->menuCache();

        $newMenus = [];
        $privilegeData = $AuthAccess->where(["role_id" => $roleId])->column("rule_name");//获取权限表数据

        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }

        foreach ($result as $n => $t) {
            $result[$n]['checked'] = ($this->_isChecked($t, $privilegeData)) ? ' checked' : '';
            $result[$n]['level'] = $this->_getLevel($t['id'], $newMenus);
            $result[$n]['style'] = empty($t['parent_id']) ? '' : 'display:none;';
            $result[$n]['parentIdNode'] = ($t['parent_id']) ? ' class="child-of-node-' . $t['parent_id'] . '"' : '';
        }

        $str = "<tr id='node-\$id'\$parentIdNode  style='\$style'>
                   <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuId[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
    			</tr>";
        $tree->init($result);

        $category = $tree->getTree(0, $str);

        $this->assign("category", $category);
        $this->assign("roleId", $roleId);
        return $this->fetch();
    }

    /**
     * 角色授权提交
     * @adminMenu(
     *     'name'   => '角色授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色授权提交',
     *     'param'  => ''
     * )
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("roleId", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            }
            if (is_array($this->request->param('menuId/a')) && count($this->request->param('menuId/a')) > 0) {

                Db::name("authAccess")->where(["role_id" => $roleId, 'type' => 'admin_url'])->delete();
                foreach ($_POST['menuId'] as $menuId) {
                    $menu = Db::name("adminMenu")->where(["id" => $menuId])->field("app,controller,action")->find();
                    if ($menu) {
                        $app = $menu['app'];
                        $model = $menu['controller'];
                        $action = $menu['action'];
                        $name = strtolower("$app/$model/$action");
                        Db::name("authAccess")->insert([
                            "role_id"   => $roleId,
                            "rule_name" => $name,
                            'type'      => 'admin_url'
                        ]);
                    }
                }

                cache(null, 'admin_menus');// 删除后台菜单缓存

                $this->success("授权成功！", 'index');
            } else {
                //当没有数据时，清除当前角色授权
                Db::name("authAccess")->where(["role_id" => $roleId])->delete();
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        }
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param $privData
     * @return bool
     */
    private function _isChecked($menu, $privData)
    {
        $app = $menu['app'];
        $model = $menu['controller'];
        $action = $menu['action'];
        $name = strtolower("$app/$model/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 获取菜单深度
     * @param $id
     * @param array $array
     * @param int $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }

    //角色成员管理
    public function member()
    {
        //TODO 添加角色成员管理

    }


    /**
     * ajax 获取房间列表
     **/
    public function ajax_room()
    {
        if ($this->request->isAjax()) {
            return $this->return_success($this->get_rid_list());
        } else {
            return $this->return_error();
        }

    }


}

