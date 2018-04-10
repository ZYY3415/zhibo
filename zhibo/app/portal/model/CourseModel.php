<?php

namespace app\portal\model;

use think\Model;
use think\Db;

class CourseModel extends Model
{
    /**
     * addPost
     * @return  boolean
     */
    public function addPost($param)
    {
        if (empty($param['id'])) {
            return $this->allowField(true)->data($param, true)->isUpdate(false)->save();
        } else {
            return $this->allowField(true)->data($param, true)->isUpdate(true)->save();
        }
    }

    /**
     * 获取讲师列表
     * @return array
     */
    public function getTeacherList($param, $room)
    {

        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
        $where = array(
            "a.delete_time" => 0,
        );
        $where['b.rid'] = ['in', $room];

        if (!empty($keyword)) {
            $where['b.jname'] = ['like', "%$keyword%"];
        }
        if (!empty($param['rid'])) {
            $where['b.rid'] = $param['rid'];
        }

        $field = "a.*,b.jname,b.rid,r.room";
        return $teacher = Db::name('teacher_info')
            ->alias('a')
            ->field($field)
            ->join("__TEACHER__ b", "a.jid = b.jid", "left")
            ->join("__ROOM_BASIC__ r", 'b.rid = r.rid', 'left')
            ->where($where)
            ->paginate();
    }

    /**
     * 获取课程信息
     * @param $param
     * @param $room
     * @return \think\Paginator
     */
    public function getCourse($param, $room)
    {
        $rid = empty($param['rid']) ? '' : $param['rid'];
        $where = array();
        $where['a.rid'] = ['in', $room];
        if (!empty($param['rid'])) {
            $where["find_in_set({$param['rid']}, a.rid)"] = ['>', 0];
        }
        $field = "a.*,r.room";

        return $this->alias('a')
            ->field($field)
            ->join("__ROOM_BASIC__ r", 'a.rid = r.rid', 'left')
            ->where($where)
            ->order('a.list_order desc')
            ->paginate();
    }

    /**
     * 获取课程表列表
     * @param $room
     * @return array
     */
    public function getCourseList($room, $date)
    {
        list($weekBeginDate, $weekEndDate) = $this->_getWeekDate($date);
        $courses = $this->field('id,date,starttime,endtime,teacher,course')
            ->where("find_in_set({$room},rid)>0")
            ->where('date', '>=', $weekBeginDate)
            ->where('date', '<=', $weekEndDate)
            ->select();
        $list = array();
        foreach ($courses as $item) {
            $weekStr = $this->_getWeek($item['date']);
            $list[$weekStr][] = [
                'id' => $item['id'],
                'date' => $item['date'],
                'starttime' => $item['starttime'],
                'endtime' => $item['endtime'],
                'teacher' => $item['teacher'],
                'course' => $item['course'],
            ];
        }
        return $list;
    }

    private function _getWeek($date)
    {
        $data = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        return $data[date('w', strtotime($date)) - 1];
    }

    private function _getWeekDate($date)
    {
        $dayOfWeek = date('w', strtotime($date));
        $end = 7 - $dayOfWeek;//计算每周最后一天
        $before = $dayOfWeek - 1;//计算每周第一天
        $weekBeginDate = date('Y-m-d', strtotime("-{$before} days", strtotime($date)));
        $weekEndDate = date('Y-m-d', strtotime("+{$end} days", strtotime($date)));
        return [$weekBeginDate, $weekEndDate];
    }
}
