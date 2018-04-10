<?php
namespace app\portal\controller;

use app\portal\model\LoginInfoModel;
use app\portal\model\RoomBasicModel;
use cmf\controller\AdminBaseController;

class LoginReportController extends AdminBaseController
{
    /**
     * 会员登录信息统计
     * @return mixed
     */
    public function index()
    {
        $param = $this->request->param();
        $keyword = $this->request->param('keyword', '');
        $child_rid = array_keys($this->get_rid_list());
        $loginInfoModel = new LoginInfoModel();
        $select = $loginInfoModel->alias('li')->join('__ROOM_BASIC__ rb', 'rb.rid=li.rid', 'LEFT')
            ->field('li.id,li.mid,li.username,li.login_time,li.last_time,li.online_time,li.login_ip,li.area,li.device,rb.room')
            ->where('rb.rid', 'in', $child_rid);
        if (!empty($keyword)) {
            $select->where('rb.room|li.area|li.login_ip', 'like', "%$keyword%");
        }
        if (!empty($param['rid'])) {
            $select->where('li.rid', $param['rid']);
        }
        if (!empty($param['start_time'])) {
            $select->where('li.login_time', '>=', strtotime($param['start_time']));
        }
        if (!empty($param['end_time'])) {
            $select->where('li.login_time', '<', strtotime($param['end_time']));
        }
        $data = $select->paginate(config('admin_page_size'));
        foreach ($data as $item) {
            $onlineTime = empty($item['online_time']) ? (time() - $item['login_time']) : $item['online_time'];
            $item['online_time'] = $this->calcDate($onlineTime);
            $item['device'] = LoginInfoModel::getDeviceLabel($item['device']);
        }

        $data->appends($param);
        // dump($data);die;
        $this->assign('rooms', $this->get_rid_list());
        $this->assign('devices', LoginInfoModel::getDeviceLabel());
        $this->assign('device', isset($param['device']) ? $param['device'] : '');
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('logininfo', $data->items());
        $this->assign('page', $data->render());
        return $this->fetch();
    }

    public function delete()
    {
        $intId = $this->request->param();
        $loginInfoModel = new LoginInfoModel();

        //删除单个信息
        if (isset($intId['id'])) {
            $loginInfoModel->where(['id' => $intId['id']])->delete();
        }
        //批量删除信息
        if (isset($intId['ids'])) {
            $loginInfoModel->whereIn('id', $intId['ids'])->delete();
        }

        $this->success('删除成功','index');
    }

    /**
     * 计算日期
     * @param $time
     * @return string
     */
    private function calcDate($time)
    {
        $day = intval($time / 86400);
        $remain = $time % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        $date = '';
        if (!empty($day)) $date .= $day . '天';
        if (!empty($hours)) $date = $hours . '时';
        if (!empty($mins)) $date .= $mins . '分';
        $date .= $secs . '秒';
        return $date;
    }
}