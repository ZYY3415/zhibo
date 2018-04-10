<?php

namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\IpBanModel;
use think\Validate;
use think\Db;
use Kernal\Common;
class AdminIpBanController extends AdminBaseController
{
	/**
	 * ip黑名单首页
	 */
	public function index()
	{
		$IpBanModel = new IpBanModel();
		$ban = $IpBanModel->getIpBan();
		// dump($ban);die;
		$this->assign('ban',$ban);
		return view();
	}

    /**
     * delete
     */
    public function delete()
    {
        $param  = $this->request->param();
        $IpBanModel = new IpBanModel();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $result = $IpBanModel->where(['id' => $id])->delete();            

        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $IpBanModel->where(['id' => ['in', $ids]])->delete();
        }

        if (!$result) {
            $this->error('删除失败',"");
        }
            $this->success("删除成功！", '');
    } 
}