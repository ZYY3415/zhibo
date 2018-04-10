<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\model;

use think\Model;
use think\Db;

class IpBanModel extends Model
{
    protected $table = 'cmf_ipban';

    /**
     * 获取屏蔽ip用户信息
     */
    public function getIpBan($param)
    {
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
		$where = '';
        if (!empty($keyword)) {
            $where['t1.ip|t2.nickname|t3.username'] = ['like', "%$keyword%"];
        }
		if (!empty($param['start_time'])) {
			$where['t1.create_time'] = ['>=', "%".$param['start_time']."%"];
		}
		if (!empty($param['end_time'])) {
			$where['t1.create_time'] = ['<=', "%".$param['end_time']."%"];
		}

    	$field = "t1.*,t2.nickname,t3.username";
    	$join = [
        	["__MEMBER__ t2",'t1.mid = t2.id','left'],
        	["__USER__ t3",'t1.create_uid = t3.mid','left'],
    	];
    	return $this->alias('t1')
    				->field($field)
    				->join($join)
				    ->where($where)
    				->order('id','asc')
    				->paginate(config('admin_page_size'));
    }
}