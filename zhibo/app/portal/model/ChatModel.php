<?php

namespace app\portal\model;

use think\Model;
use think\Db;

class ChatModel extends Model
{
	protected $table = 'cmf_portal_message';

    public function getCreateTimeAttr($value)
    {
        return Date('Y-m-d H:i:s',$value);
    }

    public function getTypeAttr($value)
    {
        $type = [1 => '聊天信息', 2 => '红包信息', 3 => '礼物信息'];
        return isset($type[$value]) ? $type[$value] : '';
    }

	/**
	 * 获取聊天信息
	 */
	public function getChat($param = '')
	{
		$role  = empty($param['role']) ? '' : $param['role'];
		$keyword = empty($param['keyword']) ? '' : $param['keyword'];
		$where = array();
		if(!empty($param['sensitive'])){
			$where['is_sensitive'] = $param['sensitive'];		
		}

        if (!empty($keyword)) {
            $where['username|to_username|content'] = ['like', "%$keyword%"];
        }

        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['create_time'] = ['<= time', $endTime];
            }
        }
        $return['where'] = $where;
        $return['messageId'] = $this->field('id')->where($where)->order('id')->paginate(10);



        return $return;
	}
}