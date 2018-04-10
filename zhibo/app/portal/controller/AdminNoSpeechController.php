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
        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);  
        
		$MemberModel = new MemberModel();
		$member = $MemberModel -> getNoSpeech($param,$room);

        $data = array_map('switch_rid_ridname',$member->toArray()['data']);

		$role  = $this->getAdminUser();
        $member->appends($param);
        // dump($data);exit;
        $this->assign('role', $role);
        $this->assign('rooms', $rooms);
		$this->assign('members',$data);
        $this->assign('page', $member->render());
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('roleid', isset($param['roleid']) ? $param['roleid'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

		return view();
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
     * 获取用户组
     * @return array
     */
    private function getAdminUser()
    {
        return Db::name('protal_role')->field('id,rolename')->select()->toArray();
    }
}