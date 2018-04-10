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

use app\portal\model\PortalTagModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Validate;

/**
 * Class AdminTagController 门户角色管理控制器
 * @package app\portal\controller
 */
class AdminRoleController extends AdminBaseController
{
    /**显示角色列表**/
    function index()
    {

        $param = $this->request->param();

        $select = DB::name('protal_role')
            ->alias('r')
            ->join('__PORTAL_ROLEAUTH__ ra', 'r.id = ra.role_id', $type = 'LEFT')
            ->join('')
            ->order('r.sort desc')
            ->field('r.*,ra.auth_id');

        if (!empty($param['id'])) {
            $select->where('r.id', $param['id']);
        }

        if (!empty($param['keyword'])) {
            $select->where('r.id|r.rolename|r.remark', 'like', "%$param[keyword]%");
        }

        $role = $select->paginate(config('admin_page_size'))->appends($param);



        /*foreach ($role as $key => $value) {
            foreach ($value as $k => $v) {
                $new_data[$key] = $role[$key];
                if ($k == 'auth_id' and $v != null) {

                    $auth_name = DB::name('protal_auth')
                        ->where('id', 'in', $v)
                        ->column('auth_name');
                    $new_data[$key][$k] = implode(',', $auth_name);
                }
            }
        }*/

        $this->assign('role', $new_data);
        $this->assign('page', $role->render());
        $this->assign('roles', $select->select());
        $this->assign('id', isset($param['id']) ? $param['id'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return view();
    }

    /**角色添加管理**/
    function roleadd(Request $request)
    {
        if ($request->isGet()) {

            return $this->fetch();

        } else if ($request->isPost()) {

            $data['rolename'] = $request->post('name');
            $data['roleicon'] = $request->post('icon');
            $data['remark'] = $request->post('remark');
            $data['status'] = $request->post('status');
            $data['keyword'] = $request->post('keyword');

            $rule = [
                'rolename' => 'require',
                'keyword' => 'require|number|between:1,255',
                'roleicon' => 'require',
                'status' => 'require',
            ];

            $msg = [
                'rolename.require' => '角色名称不能为空！',
                'keyword.require' => '关键字不能为空！',
                'roleicon.require' => '角色头像不能为空！',
                'status.require' => '角色状态不能为空！',
                'keyword.number' => '关键字必须是数字！',
                'keyword.between' => '关键字必须在1-255之间！',
            ];
            $validate = new Validate($rule,$msg);
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }
//            if (empty($data['rolename'])) {
//                $this->error('角色名称不能为空！');
//            }
//
//            if (empty($data['roleicon'])) {
//                $this->error('角色头像不能为空！');
//            }
//
//            if (empty($data['status'])) {
//                $this->error('角色状态不能为空！');
//            }
//
//            if (empty($data['keyword'])) {
//                $this->error('关键字不能为空！');
//            }

            $rel = DB::name('protal_role')
                ->where('keyword', $data['keyword'])
                ->find();

            if ($rel != null) {
                $this->error('关键字已存在！');
            }

            $rel = DB::name('protal_role')->insert($data);
            if (!$rel) {
                $this->error('角色添加失败！');
            }
            $this->success('角色添加成功！', 'index');
        }
    }


    /**角色头像上传**/
    function icon_upload()
    {
        $file = request()->file('file');
        //文件上传
        $path = UPLOAD_PATH . 'role_icon';

        $info = $file->validate(['size' => 1024 * 100, 'ext' => 'jpg,jpeg,png,gif'])->move($path);

        if ($info) {
            $filename = $info->getFilename();
            $data = date('Ymd');
            echo json_encode(['code' => 0, 'msg' => '', 'data' => '/upload/role_icon/' . $data . '/' . $filename]);
        } else {
            echo json_encode(['code' => 1, 'msg' => '上传错误！', 'data' => '']);
        }

    }

    /**角色编辑**/
    function roleedit(Request $request)
    {
        if ($request->isGet()) {
            $role_id = $request->param('id');
            $role = DB::name('protal_role')->where('id', $role_id)->find();

            if (!$role) {
                $this->error('角色编辑出错');
            }

            $this->assign('role', $role);
            return view();
        } else if ($request->isPost()) {
            $data['id'] = $request->post('id');
            $data['rolename'] = $request->post('name');
            $data['keyword'] = $request->post('keyword');
            $data['roleicon'] = $request->post('icon');
            $data['remark'] = $request->post('remark');
            $data['status'] = $request->post('status');

            $rule = [
                'rolename' => 'require',
                'keyword' => 'require|number|between:1,255',
                'roleicon' => 'require',
                'status' => 'require',
            ];

            $msg = [
                'rolename.require' => '角色名称不能为空！',
                'keyword.require' => '关键字不能为空！',
                'roleicon.require' => '角色头像不能为空！',
                'status.require' => '角色状态不能为空！',
                'keyword.number' => '关键字必须是数字！',
                'keyword.between' => '关键字必须在1-255之间！',
            ];
            $validate = new Validate($rule,$msg);
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

//            if (empty($data['rolename'])) {
//                $this->error('角色名称不能为空！');
//            }
//
//            if (empty($data['roleicon'])) {
//                $this->error('角色头像不能为空！');
//            }
//
//            if (empty($data['status'])) {
//                $this->error('角色状态不能为空！');
//            }
//            if (empty($data['keyword'])) {
//                $this->error('关键字不能为空！');
//            }

            $rel = DB::name('protal_role')
                ->where('id', '<>', $data['id'])
                ->where('keyword', $data['keyword'])
                ->find();

            if (!empty($rel)) {
                $this->error('该关键字已存在！');
            }

            DB::name('protal_role')->update($data);

            $this->success('角色编辑成功', 'index');
        }
    }

    /**角色删除**/
    function roledelete(Request $request)
    {
        $role_id = $request->param('id');

        $rel = DB::name('protal_role')->delete($role_id);
        if (!$rel) {
            $this->error('角色删除失败');
        }

        $this->success('角色删除成功！', 'index');
    }

    /**角色列表排序**/
    function listorder(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            if (count($data['list_orders']) > 0) {
                foreach ($data['list_orders'] as $key => $value) {
                    DB::name('protal_role')
                        ->where('id', $key)
                        ->setField('sort', $value);
                }

                $this->redirect('index');
            }
        }
    }

}