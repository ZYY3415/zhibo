<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\RoomBasicModel;
use app\portal\model\CourseModel;
use app\admin\model\TeacherModel;
use think\Db;
use think\Validate;

class AdminCourseController extends AdminBaseController
{
	/**
	 * 课程信息列表
	 */
	public function CourseList()
	{
		$param = $this->request->param();

        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);

		$CourseModel = new CourseModel();
	
		$course = $CourseModel->getCourse($param,$room);

        $course->appends($param);

        $this->assign('page', $course->render());
		$this->assign('course',$course);
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('rooms', $rooms);

		return $this->fetch();
	}

    /**
     * 获取房间列表
     * @return array
     */
    private function getRooms($room)
    {
        $roomBasicModel = new RoomBasicModel();
        return $roomBasicModel->field('rid,room')->whereIn('rid',$room)->select()->toArray();
    }

    /**
     * add
     */
    public function add()
    {
    	$param = $this->request->param();
        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);
        $TeacherModel = new TeacherModel();
        $teacher = $TeacherModel->getTeaList($param,$room);

        $this->assign('rooms', $rooms);
		$this->assign('teacher',$teacher);
    	return $this->fetch();
    }

    /**
     * 点击保存按钮提交
     */
    public function addPost()
    {
        $param = $this->request->param();
        $result = $this->validate($param, 'AdminCourse');
        if ($result !== true) {
            $this->error($result);
        }
        $courseModel = new courseModel();
        $courseModel->addPost($param);

        $this->success('成功', url('AdminCourse/CourseList'));
    }
	
	/**
     * edit
     */
    public function edit()
    {
    	$param = $this->request->param();
        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);
		$TeacherModel = new TeacherModel();
		$teacher = $TeacherModel->getTeaList($param,$room);

		$field = "a.*,b.room";
		$course = Db::name('course')
				->alias('a')
				->field($field)
				->join('__ROOM_BASIC__ b','a.rid = b.rid','left')
				->where('a.id',$param['id'])
				->find();
		$roomName = Db::name('room_basic')->whereIn('rid', explode(',', $course['rid']))->column('room');
		$this->assign('roomname', implode(',', $roomName));
        $this->assign('course', $course);
        $this->assign('rooms', $rooms);
		$this->assign('teacher',$teacher);
    	return $this->fetch();
    }

    /**
     * delete
     */
    public function delete()
    {

        $param            = $this->request->param();
        $CourseModel = new CourseModel();

        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result       = $CourseModel->where(['id' => $id])->delete();            
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $CourseModel->where(['id' => ['in', $ids]])->delete();
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');	
        }
    }

    /**
     * courseTeacher
     * @return array
     */
    public function courseTeacher()
    {   
		$param = $this->request->param();

        $rooms = $this->get_rid_list();

		$teacher = Db::name('teacher')->alias('a')->field('a.jid,a.jname,b.rid,b.room')->join('__ROOM_BASIC__ b','a.rid = b.rid','left')->paginate(10);
        $teachers['jname'] = '';
        $teachers['rid'] = '';
        if(!empty($param['jid'])){
            $teachers = Db::name('teacher')->alias('a')->field('a.jid,a.jname,b.rid,b.room')->join('__ROOM_BASIC__ b','a.rid = b.rid','left')->where('a.jid',$param['jid'])->find();
        }                       
        // dump($teachers);die;
        $this->assign('teachers',$teachers);
		$this->assign('teacher',$teacher);
        $this->assign('rooms', $rooms);
        $this->assign('page',$teacher);
		return $this->fetch();
    }

	/**
	 * TeacherAddddPost
	 * @return  boolean
	 */
	public function TeacherAddPost()
    {
        $param = $this->request->param();
        $result = $this->validate($param, 'PortalTeacher');
        if ($result !== true) {
            $this->error($result);
        }

        if (empty($param['jid'])) {
            Db::name('teacher')->insert($param);
        } else {
            Db::name('teacher')->update($param);
        }
        $this->success('成功', url('AdminCourse/courseTeacher'));
    }

	/**
	 * TeacherDelete
	 */
	public function TeacherDelete()
	{
        $param       = $this->request->param();

        if (isset($param['jid'])) {
            $jid     = $this->request->param('jid', 0, 'intval');
            $result  = Db::name('teacher')->where(['jid' => $jid])->delete();            
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = DB::name('teacher')->where(['jid' => ['in',$ids]])->delete();
            if (!$result) {
                $this->error('删除失败',"");
            }
                $this->success("删除成功！", '');
        }

	}

    /**
     * 排序
     *
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('course'));
        $this->success("排序更新成功！",'');
    }
}