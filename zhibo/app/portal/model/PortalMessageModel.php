<?php
namespace app\portal\model;

use think\Model;

class PortalMessageModel extends Model
{
    const TYPE_MESSAGE = 1;//普通消息
    const TYPE_REDBAG = 2;//红包信息

    const APPROVE_STATUS_NO = 0;//
    const APPROVE_STATUS_YES = 1;//

    public function getMessageReport()
    {
        $start_date = date('Y-m-d', strtotime('-6 day'));
        $end_date = date('Y-m-d', strtotime('+1 day'));

        $message = $this->alias('m')->field('date,count(1) as sum')
            ->join('__MSG_RID__ mr', 'mr.msid=m.id')
            ->whereBetween('date', [$start_date, $end_date])
            ->where('mr.rid', 'in', cmf_get_current_admin_rid())
            ->group('date')
            ->select()->toArray();

        $series = array_map(function ($item) {
            $shortDate = substr($item['date'], 5);
            return array(
                'name' => $shortDate,
                'y' => $item['sum'],
                'drilldown' => $shortDate,
            );
        }, $message);

        $dates = implode(',', array_column($message, 'date'));

        $dayRooms = $this->alias('m')
            ->field('m.date,b.room,count(*) as sum')
            ->join('__MSG_RID__ mr', 'mr.msid=m.id')
            ->join('__ROOM_BASIC__ b', 'mr.rid = b.rid')
            ->whereIn('m.date', $dates)
            ->group('m.date,b.room')
            ->select()->toArray();

        $drilldown = array_map(function ($item) use ($dayRooms) {
            $dayRoom = array_filter($dayRooms, function ($v) use ($item) {
                return $v['date'] == $item['date'];
            });
            $shortDate = substr($item['date'], 5);
            $list = array();
            foreach ($dayRoom as $room) {
                $list[] = array($room['room'], $room['sum']);
            }
            return array(
                'name' => $shortDate,
                'id' => $shortDate,
                'data' => $list,
            );
        }, $message);

        return ['series' => $series, 'drilldown' => $drilldown];
    }
}