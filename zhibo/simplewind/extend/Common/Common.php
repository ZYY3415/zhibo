<?php
namespace Common;
use think\Db;
use tree\Tree;
/**
 * 公共类
 * 获取角色所属的房间列表及角色信息
 **/
trait Common
{
    /**
     * 获取当前后台管理员所属的房间列表
    **/
    public function get_rid_list()
    {
        $rid_attr = cmf_get_current_admin_rid();
        $data = Db::name('room_basic')
            ->where('rid','in',$rid_attr)
            ->order('rid')
            ->column('rid,room');

        return $data;
    }

    /**
     * 获取角色列表
     **/
    public function get_role_list($status=null)
    {
        if($status==null)
        {
            $role_array = Db::name('role')->select()->toArray();
            $tree = new Tree();
            $level = cmf_get_current_admin_level();
            $roleid = cmf_get_current_admin_role();
            if($tree->init($role_array,$roleid))
            {
                $child_role= $tree->getTreeTdArray($level,0,true);

                $data = Db::name('role')
                    ->where('id','in',$child_role)
                    ->column('id,name');

                return $data;
            }
        }else{
            $role_array = Db::name('role')->where('status',$status)->select()->toArray();
            $tree = new Tree();
            $level = cmf_get_current_admin_level();
            $roleid = cmf_get_current_admin_role(1);
            if($tree->init($role_array,$roleid))
            {
                $child_role= $tree->getTreeTdArray($level,0,true);

                $data = Db::name('role')
                    ->where('id','in',$child_role)
                    ->where('status',$status)
                    ->column('id,name');

                return $data;
            }
        }

    }
}