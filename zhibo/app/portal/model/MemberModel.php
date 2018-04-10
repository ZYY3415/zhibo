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
namespace app\portal\model;

use think\Model;
use think\Db;
use app\admin\model\RoleModel;
class MemberModel extends Model
{
    protected $table = 'cmf_member';

    public function getStatusAttr($value)
    {
        $status = [0=>'未激活',1=>'已激活'];
        return $status[$value];
    }
    public function getTuijianmidAttr($value)
    {
        if(empty($value))
        {
            return '未上线';
        }else{
            return $value;
        }
    }

    public function getRegTimeAttr($value)
    {
        return Date('Y-m-d H:i:s',$value);
    }
	/**
	 * 获取会员列表
	 * @param  
	 * @return array
	 */
	public function getMember($param = '',$room)
	{
		$role  = empty($param['role']) ? '' : $param['role'];
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
		$rid = empty($param['rid']) ? '' : $param['rid'];

        $where = array();
        if (!empty($keyword)) {
            $where['a.nickname'] = ['like', "%$keyword%"];
        }
        if(!empty($role)){
        	$where['c.id'] = $role;
        }
        if (!empty($param['rid'])) {
            $where['a.rid'] = ['like',"%$rid%"];
        }else{
            $where['a.rid'] = ['in',$room];
        }

        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.reg_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.reg_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.reg_time'] = ['<= time', $endTime];
            }
        }
        if (!empty($param['roleid'])) {
            $where['a.adminid'] = $param['roleid'];
        }
        $join = [
        	["__ROOM_BASIC__ b",'b.rid in (a.rid)','left'],
        	["__PROTAL_ROLE__ c",'a.adminid = c.keyword','left'],
        ];
		$field = "a.id,a.nickname,b.room,a.phone,a.qq,a.tuijianmid,a.reg_time,c.rolename,a.ip,a.rid,a.login_count,a.money,a.remark,a.status";
        return $this->alias('a')->field($field)->join($join)->where($where)->order('a.reg_time','desc')->paginate(10);

	}

	/**
	 * addPost 添加会员信息
	 * @return  boolean
	 */
	public function addPost($param)
	{
		if(!empty($param['password'])){
			$param['password'] = cmf_password($param['password']);
		}

        if (empty($param['id'])) {
        	$param['reg_time'] = time();
            return $this->allowField(true)->data($param, true)->isUpdate(false)->save();
        } else {
            return $this->allowField(true)->data($param, true)->isUpdate(true)->save();
        }
	}

    /**
     * 获取会员列表
     * @param  
     * @return array
     */
    public function getNoSpeech($param = '',$room)
    {
        $role  = empty($param['role']) ? '' : $param['role'];

        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
        $rid = empty($param['rid']) ? '' : $param['rid'];
        $where = array();

        $where['a.adminid'] = ['<>',11];
        $where['b.rid'] = ['in',$room];
        if (!empty($keyword)) {
            $where['a.nickname'] = ['like', "%$keyword%"];
        }
        if(!empty($role)){
            $where['c.id'] = $role;
        }
        if (!empty($param['rid'])) {
            $where['a.rid'] = ['like',"%$rid%"];
        }else{
            $where['a.rid'] = ['in',$room];
        }

        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.reg_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.reg_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.reg_time'] = ['<= time', $endTime];
            }
        }
        $join = [
            ["__ROOM_BASIC__ b",'b.rid in (a.rid)','left'],
            ["__PROTAL_ROLE__ c",'a.adminid = c.keyword','left'],
        ];
        $mid = Db::name('portal_message')->field('distinct mid')->select()->toArray();
        $mid = $this->arr2str($mid);

        // dump($mid);die;
        $field = "a.id,a.nickname,b.room,a.phone,a.rid,a.qq,a.tuijianmid,a.reg_time,c.rolename,a.ip,a.login_count,a.money,a.remark,a.status";

        return $this->alias('a')->field($field)->join($join)->where($where)->whereNotIn('a.id',$mid)->paginate(10);

    }

    public function arr2str ($arr)
    {
        foreach ($arr as $v)
        {
            $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }
        $t = "";
        foreach($temp as $v){
            $t .= $v . ",";
        }
        $t = substr($t,0,-1);
        return $t;
    }

}

