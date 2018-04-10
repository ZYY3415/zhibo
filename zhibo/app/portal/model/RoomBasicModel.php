<?php
namespace app\portal\model;

use think\Model;

class RoomBasicModel extends Model
{
    /**
     * 获取房间列表
     * @return array
     */
    public function getRooms()
    {
        return $this->field('rid,room')->select()->toArray();
    }

    /**
     * 获取默认房间
     * @return array|false|\PDOStatement|string|Model
     */
    public function getDefaultRoom()
    {
        return $this->field('rid,room')->find();
    }

    /**
     * 获取房间列表
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public function getRoomList($pageNo, $pageSize)
    {
        $offset = (intval($pageNo) - 1) * intval($pageSize);
        $selectTotal = $this->buildSqlQuery();
        //获取总记录数
        $total = $selectTotal->count();

        //获取当前页记录
        $select = $this->buildSqlQuery();
        $rooms = $select->limit($offset, $pageSize)->select()->toArray();

        $list = array_map(function ($item) {
            return array(
                'id' => $item['id'],
                'rid' => $item['rid'],
                'ftitle' => $item['ftitle'],
                'room_log' => empty($item['room_log']) ? '' : cmf_get_image_preview_url($item['room_log']),
            );
        }, $rooms);

        return array(
            'total' => $total,
            'rows' => $list,
            'pageNo' => intval($pageNo),
            'pageSize' => intval($pageSize),
        );
    }

    /**
     * 生成sql语句
     * @return $this
     */
    private function buildSqlQuery()
    {
        return $this->field('id,rid,ftitle,room_log');
    }
}