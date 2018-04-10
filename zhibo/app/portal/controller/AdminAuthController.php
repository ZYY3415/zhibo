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
use app\portal\model\AdminHouseModel;
use think\Db;
use think\Request;

/**
 * Class AdminTagController 门户角色管理控制器
 * @package app\portal\controller
 */
class AdminAuthController extends AdminBaseController
{
    /**用户权限添加**/
    function addauth(Request $request){

       if($request->isGet()){

           return view();

       }else if($request->isPost()){

           $data['auth_name'] = $request->post('name');
           $data['remark'] = $request->post('remark');
           $data['status'] = $request->post('status');

           if(empty($data['auth_name'])){
               $this->error(lang('AUTHNAME_REQUIRED'));
           }
           if($data['status'] !=0 && $data['status'] !=1){
               $this->error(lang('STATUS_REQUIRED'));
           }

           $rel = DB::name('protal_auth')->insert($data);

           if(!$rel){
              $this->error(lang("ADDAUTH_ERROR"));
           }
           $this->success('添加成功','index');
       }

    }

    /**用户权限显示**/
    function index()
    {
        $keyword = $this->request->param('keyword');

        $select = DB::name('protal_auth');

        if(!empty($keyword))
        {
            $select->where('id|auth_name|remark|status','like',"$keyword");
        }

        $auth = $select->paginate(config('admin_page_size'))->appends($keyword);

        $this->assign('auth',$auth->all());
        $this->assign('page',$auth->render());
        $this->assign('keyword',isset($keyword) ? $keyword : '');
        return $this->fetch();
    }


    /**用户权限编辑**/
    function editauth(Request $request){
        if($request->isGet()){

            $auth_id = $request->param('id');
            $auth = DB::name('protal_auth')->where('id',$auth_id)->find();

            if(!$auth){
                $this->error(lang("DATA_ERROR"));
            }

            $this->assign('auth',$auth);
            return $this->fetch();
        }else if($request->isPost())
        {
            $data['auth_name'] = $request->post('name');
            $data['remark'] = $request->post('remark');
            $data['status'] = $request->post('status');
            $data['id'] = $request->post('id');

            if(empty($data['auth_name'])){
                $this->error(lang('AUTHNAME_REQUIRED'));
            }

            if($data['status'] !=0 && $data['status'] !=1){
                $this->error(lang('STATUS_REQUIRED'));
            }

            $rel = DB::name('protal_auth')->update($data);

            if(!$rel){
                $this->error('权限更新失败!');
            }

            $this->success('权限更新成功!','index');

        }
    }


    /**权限删除**/
    function deleteauth(Request $request)
    {
        if($request->isGet())
        {
            $auth_id = $request->param('id');
            $auth_ids = $request->param('ids');

            if(!empty($auth_id))
            {
                $rel = DB::name('protal_auth')->delete($auth_id);
            }else if(!empty($auth_ids))
            {
                $rel = DB::name('protal_auth')->delete($auth_ids);
            }

            if(!$rel){
                $this->error('删除出错！');
            }
            
            $this->success('删除成功!','index');
        }
    }

    /**权限分配**/
    function authorize(Request $request)
    {
        if($request->isGet())
        {
            $role_id = $request->param('id');
            $id = DB::name('protal_roleauth')
                ->where('role_id',$role_id)
                ->value('id');

            $data = DB::name('protal_roleauth')
                ->where('role_id',$role_id)
                ->value('auth_id');
            //$role_auth =explode(',',$data);

            //获取所有权限的名称及id
            $auths = DB::name('protal_auth')
                ->where('status',1)
                ->field('id,auth_name')
                ->select();

            return $this->fetch('',['data'=>$data,'auths'=>$auths,'id'=>$id,'role_id'=>$role_id]);

        }else if($request->isPost())
        {
          $data = $request->post('');
          $id= $request->post('id');
          $role_id= $request->post('role_id');
          $str = '';
          foreach($data['auth'] as $key=>$value){
                if($value)
                {
                    $str .=$key.',';
                }
            }
           $auth_id = substr($str,0,-1);

          if(empty($id))
          {
              //新增角色权限
              $isdata = [
                  'role_id'=>$role_id,
                  'auth_id'=>$auth_id
              ];
              $rel =DB::name('protal_roleauth')
                  ->insert($isdata);

          }else{
              //更新角色权限
              $rel = DB::name('protal_roleauth')
                  ->where('id',$id)
                  ->setField('auth_id',$auth_id);
          }
            if(!$rel)
            {
                $this->error('权限分配出错！');
            }

            $this->success('权限分配成功！','adminRole/index');
        }
    }
}