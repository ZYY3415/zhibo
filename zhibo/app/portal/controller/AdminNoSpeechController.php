<?php

namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\RoomBasicModel;
use app\portal\model\MemberModel;
use think\Validate;
use think\Db;

class AdminNoSpeechController extends AdminBaseController
{
	/**
	 * 未发言首页
	 */
	public function index()
	{
		$param = $this->request->param();
        $room  = is_array(cmf_get_current_admin_rid()) ? cmf_get_current_admin_rid() :'';
        //获取当前后台角色所属的房间列表
        $rooms = $this->getRooms($room);

		$MemberModel = new MemberModel();
		$member = $MemberModel -> getNoSpeech($param,rooms);

		$role  = $this->getAdminUser();
        $data = array_map('switch_rid_ridname',$member->toArray()['data']);
        $member->appends($param);
        $this->assign('role', $role);
        $this->assign('rooms', $rooms);
		$this->assign('members',$data);

        /**搜索条件
         * 分页    page
         * 房间    rid
         * 角色    roleid
         * 开始时间 start_time
         * 结束时间 end_time
         * 关键字   keyword
         **/
        $this->assign('page', empty($memeber) ? '' : $member->render());
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('roleid', isset($param['roleid']) ? $param['roleid'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        /**搜索条件**/

		return view();
	}

    /**
     * 获取房间列表
     * @return array
     */
    private function getRooms($room)
    {
        $roomBasicModel = new RoomBasicModel();

        $data = $roomBasicModel->field('rid,room')->whereIn('rid',$room)->select()->toArray();

        return $data;
    }

    /**
     * 获取用户组
     * @return array
     */
    private function getAdminUser()
    {
        return Db::name('protal_role')->field('id,rolename')->select()->toArray();
    }

    /**
     * 删除未发言用户
     * 将用户表中
     **/
}