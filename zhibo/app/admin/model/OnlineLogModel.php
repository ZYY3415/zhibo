<?php
namespace app\admin\model;


use think\Model;

class OnlineLogModel extends Model
{
    public function getOnlineReport()
    {
        $rooms = $this->getRooms();
        $list = array();
        foreach ($rooms as $room) {
            $list[] = $this->getRoomOnlienUser($room['rid']);
        }
        return $list;
    }

    private function getRoomOnlienUser($room)
    {
        $onlineLogs = $this->field('id,rid,count,create_time')->where('rid', $room)
            ->whereBetween('create_time', [strtotime(date('Y-m-d')), time()])
            ->select();
        $hours = $this->getDayHours();
        $data = array();
        foreach ($hours as $k => $v) {
            $hour = $k + 1;
            $currentTime = date('Y-m-d', time()) . " $hour:00:00";
            foreach ($onlineLogs as $item) {
                $onlineTime = date('Y-m-d H', $item['create_time']) . ":00:00";
                if (strtotime($onlineTime) == strtotime($currentTime)) {
                    $data[$k] = $item['count'];
                    break;
                }
            }
            if (!isset($data[$k])) {
                $data[$k] = 0;
            }
        }
        return array('name' => '房间' . $room, 'data' => $data);
    }

    /**
     * 统计今天直播的房间
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function getRooms()
    {
        $admin_rid = cmf_get_current_admin_rid();
        $result = $this->whereBetween('create_time', [strtotime(date('Y-m-d')), time()])
            ->field('rid')->where('rid', 'in', $admin_rid)->distinct('rid')->select();
        return $result;
    }

    /**
     * 获取当前小时数
     * @return array
     */
    public function getDayHours()
    {
        $hour = date('H');
        $hourArr = array();
        for ($i = 1; $i <= $hour; $i++) {
            array_push($hourArr, $i . ':00');
        }
        return $hourArr;
    }
}