<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\admin\Model\TeacherModel;
use think\Db;

class TeacherController extends HomeBaseController
{
    /**
     * 讲师列表
     * @return \Jump\json
     */
    public function teacherList()
    {
        $field = "a.*,b.jname,r.room";
		$teacher = Db::name('teacher_info')
						->alias('a')
						->field($field)
						->join("__TEACHERS__ b",'a.jid = b.jid','left')
						->join("__ROOM_BASIC__ r",'b.rid = r.rid','left')
						->select()->toArray();
    	// dump($teacher);die;
    	foreach ($teacher as &$v) {
    		if(!empty($v['create_time'])){
    			$v['create_time'] = date('Y-m-d H:i', $v['create_time']);
    		}
            $v['icon'] = cmf_get_image_preview_url($v['icon']);
    	}
    	return $this->return_success($teacher);
    }
}