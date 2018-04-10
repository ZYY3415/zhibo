<?php
namespace app\portal\controller;

use app\portal\model\RoomBasicModel;
use cmf\controller\HomeBaseController;

class HouseController extends HomeBaseController
{
    /**
     * 获取房间列表
     */
    public function roomList()
    {
        $pageNo = $this->request->param('pageNo', 1, 'intval');
        $pageSize = $this->request->param('pageSize', 10, 'intval');

        $roomBasicModel = new RoomBasicModel();
        $list = $roomBasicModel->getRoomList($pageNo, $pageSize);

        return $this->return_success($list);
    }
}