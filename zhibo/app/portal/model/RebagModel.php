<?php

namespace app\portal\model;
use think\Model;
use app\portal\model\AdminHouseModel;
use app\portal\model\MemberModel;
use think\Db;
class RebagModel extends Model
{
    protected $table = 'cmf_send_redbag';

    public function getRebagList($param)
    {
        $where = '';
        $rid_list = cmf_get_current_admin_rid();
        if(!empty($param['room']))
        {
           $where['r.rid']  = $param['room'];
        }
        if(!empty($param['keyword']))
        {
            $where['r.id|rb.room|m.nickname|r.content']  = ['like',"%".$param['keyword']."%"];
        }
        if(!empty($param['tid']))
        {
            $where['r.tid']  = $param['tid'];
        }else{
            $where['r.rid'] = ['in',$rid_list];
        }

        if(!empty($param['start_time']))
        {
            $where['r.create_time']  = ['>=',$param['start_time']];
        }
        if(!empty($param['end_time']))
        {
            $where['r.create_time']  = ['<=',$param['end_time']];
        }


        $rid_list = cmf_get_current_admin_rid();
        $data = $this->alias('r')
            ->field('r.*,rb.room,m.nickname')
            ->join('__ROOM_BASIC__ rb','rb.rid = r.rid')
            ->join('__MEMBER__ m','m.id = r.mid')
            ->where($where)
            ->order('create_time desc')
            ->paginate(config('admin_page_size'))
            ->appends($param);

        return $data;
    }

    public function getRedbagInfo($id,$param)
    {
        $where = '';
        if(!empty($param['start_time']))
        {
            $where['ri.time'] = ['>=',$param['start_time']];
        }
        if(!empty($param['end_time']))
        {
            $where['ri.time'] = ['<=',$param['end_time']];
        }
        if(!empty($param['keyword']))
        {
            $where['ri.id|ri.redbag_id|m.nickname|ri.money'] = ['like',"%".$param['keyword']."%"];
        }

        $return['redbag'] = $this
            ->alias('r')
            ->field('r.*,rb.room,m.nickname')
            ->join('__MEMBER__ m','m.id = r.mid')
            ->join('__ROOM_BASIC__ rb','rb.rid = r.rid')
            ->where('r.id',$id)
            ->select();

        $return['redbagInfo'] = Db::name('portal_redbag_info')
            ->alias('ri')
            ->field('ri.*,m.nickname')
            ->join('__MEMBER__ m','m.id = ri.uid')
            ->where('ri.redbag_id',$id)
            ->where($where)
            ->order('time desc')
            ->paginate(config('admin_page_size'))
            ->appends($param);

        return $return;
    }
}