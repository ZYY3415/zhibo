<?php
namespace app\user\model;

use think\Model;
use think\Session;

class ConsumeModel extends Model
{
    /**
     * 获取消费记录列表
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
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
                'count' => $item['count'],
                'time' => date('Y-m-d H:i:s', $item['create_time']),
                'remark' => $item['remark'],
                'nickname' => $item['nickname'],
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
        return $this->alias('c')->join('__MEMBER__ m', 'm.id=c.mid', 'LEFT')
            ->where('c.mid', $mid)
            ->field('c.id,c.count,c.create_time,c.remark,m.nickname');
    }
}