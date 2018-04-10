<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class TeacherModel extends Model
{
	/**
	 * 添加、修改讲师提交
	 * @return array 
	 */
	public function PostTeacher($data)
	{

        if (empty($data['id'])) {
            $data['create_time'] = time();
            $data['create_uid'] = cmf_get_current_admin_id();
	        if (!empty($data['content'])) {
	            $data['content'] = htmlspecialchars_decode($data['content']);
	        }  
          
            return $res = Db::name('teacher_info')->insert($data);
        } else {
	        if (!empty($data['content'])) {
	            $data['content'] = htmlspecialchars_decode($data['content']);
	        }  
            return $res = Db::name('teacher_info')->where('id',$data['id'])->update($data,true);
        }
	}

	/**
	 * 获取讲师列表
	 * @return array 
	 */
	public function getTeacherList($param = "",$room)
	{
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];

        $where = array(
        	'a.delete_time' => 0,
        );
        if (!empty($keyword)) {
            $where['b.jname'] = ['like', "%$keyword%"];
        }
        $where['b.rid'] = ['in',$room];
        if (!empty($param['rid'])) {
            $where['b.rid'] = $param['rid'];
        }
        $field = "a.*,b.jname,r.room";
		return $teacher = Db::name('teacher_info')
						->alias('a')
						->field($field)
						->join("__TEACHER__ b",'a.jid = b.jid','left')
						->join("__ROOM_BASIC__ r",'b.rid = r.rid','left')
						->where($where)
						->paginate(10);
	}

	/**
	 * 获取讲师列表
	 * @return array 
	 */
	public function getTeaList($param = "",$room)
	{
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
        $where = array();
        $where['a.rid'] = ['in',$room];
        if (!empty($keyword)) {
            $where['a.jname'] = ['like', "%$keyword%"];
        }
        if (!empty($param['rid'])) {
            $where['a.rid'] = $param['rid'];
        }
        $field = "a.*,r.room";
		return $teacher = Db::name('teacher')
						->alias('a')
						->field($field)
						->join("__ROOM_BASIC__ r",'a.rid = r.rid','left')
						->where($where)
						->paginate(10);
	}

	/**
	 * 编辑讲师信息
	 * @return array
	 * @param  id
	 */
	public function editTeacher($id)
	{
		$field = "a.*,b.jname,b.rid,r.room";
		$where = [
			"a.id" => $id,
		];
		// dump($id);die;
		return $teacher = Db::name('teacher_info')
						->alias('a')
						->field($field)
						->join("__TEACHER__ b","a.jid = b.jid","left")
						->join("__ROOM_BASIC__ r",'b.rid = r.rid','left')
						->where($where)
						->find();
	}

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }
}