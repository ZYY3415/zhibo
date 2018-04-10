<?php
namespace app\admin\controller;

use app\admin\model\TeacherModel;
use app\portal\model\RoomBasicModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;

class AdminTeacherController extends AdminBaseController
{
	/**
	 * addTeacher
	 * 
	 */
	public function addTeacher()
	{
		$teac = Db::name('teacher')->select()->toArray();
		$this ->assign('teachers',$teac);
		return $this -> fetch('add');
	}

	/**
	 * editTeacher
	 * 
	 */
	public function editTeacher()
	{
		$id = $this->request->param('id',0,'intval');

		$TeacherModel = new TeacherModel();
		$teacher = $TeacherModel->editTeacher($id);
		$teac = Db::name('teacher')->select()->toArray();

		$this ->assign('teac',$teac); 
		$this ->assign('teacher',$teacher);
		return $this -> fetch('edit');
	}

	/**
	 * teacher list
	 */
	public function TeacherList()
	{
		$param = $this->request->param();
        $room = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);

		$TeacherModel = new TeacherModel();
		$teacher = $TeacherModel->getTeacherList($param,$room);

        $this->assign('rooms', $rooms);
		$this->assign('teacher',$teacher);
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('page',$teacher);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
		return $this->fetch('list');
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
	 * addPost
	 * 添加讲师提交
	 */
	public function addPost()
	{

        if($this->request->isPost()){
            $data = $this->request->param();

            $validate = new Validate([
                'jid' => 'require',
                'icon' => 'require',
                'content' => 'require',
            ], [
                'jid.require' => '讲师不能为空',
                'icon.require' => '头像不能为空',
                'content.require' => '内容不能为空',
            ]);

            if (!$validate->check($data['post'])) {
                $this->error($validate->getError());
            }

            $TeacherModel = new TeacherModel();

            $res = $TeacherModel->PostTeacher($data['post']);
            // dump($res);die;
            if(!$res){
            	$this->error('失败', url('AdminTeacher/TeacherList'));
            }
            $this->success('成功!', url('AdminTeacher/TeacherList'));

        }
	}

    /**
     * 删除讲师
     * 
     */
    public function delete()
    {
        $param        = $this->request->param();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $result = Db::name('teacher_info')->where(['id' => $id])->update(['delete_time' => time()]);
        }

        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = Db::name('teacher_info')->where(['id' => ['in', $ids]])->update(['delete_time' => time()]);
        }
        $this->success("删除成功！", url('TeacherList'));
    }
}