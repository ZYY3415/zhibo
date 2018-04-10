<?php

namespace app\portal\model;
use think\Model;
use app\portal\AdminHouseModel;
use app\portal\model\MemberModel;
use think\Db;
class PortalGiftModel extends Model
{
    public function getGift($param)
    {
        $rid_list = cmf_get_current_admin_rid();
        $gift_type = isset($param['type']) ? $param['type'] : 0;


       if(!empty($param['room']))
       {
           $where['g.rid'] = $param['room'];
       }else{
           $where['g.rid'] = ['in',$rid_list];
       }
       if(!empty($param['teacher']))
       {
           $where['g.tid'] = $param['teacher'];
       }
       if(!empty($param['start_time']))
       {
           $where['g.time'] = $param['start_time'];
       }
       if(!empty($param['end_time']))
       {
           $where['g.time'] = $param['end_time'];
       }
       if(!empty($param['keyword']))
       {
           $where['g.id|m.nickname|rb.room|g.num|g.one_money|g.total_money|g.content|g.gift_type|g.remarks'] = ['like','%'.$param['keyword'].'%'];
       }

       $result = $this->alias('g')
           ->field('g.*,m.nickname,rb.room')
           ->join('__MEMBER__ m','g.uid = m.id')
           ->join('__ROOM_BASIC__ rb','rb.rid IN(g.rid)')
           ->where($where)
           ->where('g.gift_type',$gift_type)
           ->order('g.time desc')
           ->paginate(config('admin_page_size'))
           ->appends($param);
        return $result;
    }

    public function getConsumeList($pageNo, $pageSize)
    {
        $offset = (intval($pageNo) - 1) * intval($pageSize);
        $selectTotal = $this->buildSqlQuery();
        //获取总记录数
        $total = $selectTotal->count();

        //获取当前页记录
        $select = $this->buildSqlQuery();
        $consume = $select->limit($offset, $pageSize)->select()->toArray();

        $list = array_map(function ($item) {
            return array(
                'id' => $item['id'],
                'count' => $item['num'],
                'time' => date('Y-m-d H:i:s', $item['time']),
                'remark' => htmlspecialchars_decode($item['remarks']),
                'nickname' => $item['username'],
            );
        }, $consume);

        return array(
            'total' => $total,
            'rows' => $list,
            'pageNo' => intval($pageNo),
            'pageSize' => intval($pageSize),
        );
    }

    private function buildSqlQuery()
    {
        $mid = cmf_get_current_user_id();
        return $this->alias('c')->join('__MEMBER__ m', 'm.id=c.uid', 'LEFT')
            ->where('c.uid', $mid)
            ->order('time', 'DESC')
            ->field('c.id,c.num,c.time,c.remarks,m.username');
    }
}