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
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
use think\Exception;
use app\portal\model\ActionModel;

/**
 * ajax 请求处理类
 **/
class ActionController extends HomeBaseController
{
    //讲师上课
    public function teacherTeach()
    {
        if ($this->request->isAjax()) {
            $defaultRid = cmf_get_default_rid();
            $rid = $this->request->param('rid', $defaultRid);
            $teacher = $this->request->param('teacher');

            $rel = Db::name('room_basic')
                ->where('rid','in',$rid)
                ->setField('teacher', $teacher);

            if (!$rel) {
                return $this->return_error();
            }

            return $this->return_success();
        }
    }

    //讲师下课
    public function teacherNoTeach()
    {
        if ($this->request->isAjax()) {
            $defaultRid = cmf_get_default_rid();
            $rid = $this->request->param('rid', $defaultRid);

            $rel = Db::name('room_basic')
                ->where('rid','in',$rid)
                ->setField('teacher', '');

            if (!$rel) {
                return $this->return_error();
            }

            return $this->return_success();
        }
    }

    //机器人自动上线
    public function rbAutoShangxian()
    {
        $defaultRid = cmf_get_default_rid();
        $rid = $this->request->param('rid', $defaultRid);
        $num = $this->request->param('num', 0, 'intval');
        $rid_attr = explode(',', $rid);

        if (empty($rid)) {
            return $this->return_error([], '房间号不能为空');
        }
        if (empty($num)) {
            return $this->return_error([], '次数不能为空');
        }

        if ($num == 1) {
            $ridList = $this->ridRobList($rid, 0);          //第一次时获取房间对应的机器人列表
            $ridZNum = $this->ridNum($rid, 0);               //第一次时获取房间对应的机器人     总数量
        } else {
            $ridList = cache($rid . '_robSlist');
            $ridZNum = cache($rid . '_robSfirstnum');
        }

        $ratioAttr = [0.1, 0.1, 0.2, 0.2, 0.2, 0.1, 0.1];
        if ($num == 1) {
            $actionNum = $ridZNum;                           //房间对应的可上线的真实数量   剩余机器人数量   = 总数量 - 上线的机器人数量
        } else {
            $actionNum = cache($rid . '_robSnum');             //房间对应的可上线的真实数量    剩余机器人数量
        }

        $ridActionNum = [];
        $ridsNum = [];
        //当前num 下要上线的机器人数量                     上线的机器人数量
        foreach ($ridZNum as $key => $value) {
            $ridsNum[$key] = ceil($value * $ratioAttr[$num - 1]) <= $actionNum[$key] ? ceil($value * $ratioAttr[$num - 1]) : $actionNum[$key];
            $ridActionNum[$key] = $actionNum[$key] - $ridsNum[$key];
        }
        if (empty($ridsNum) && empty($ridActionNum)) {
            return $this->return_error();
            exit;
        }
        cache($rid . '_robSnum', $ridActionNum, 3600 * 24);


        //批量上线机器人
        foreach ($rid_attr as $value) {
            if (!array_key_exists($value, $ridList)) {
                continue;
            }
            if ($ridsNum > 0) {
                for ($i = 0; $i < $ridsNum[$value]; $i++)               //循环遍历，取出要上线的机器人信息
                {
                    shuffle($ridList[$value]);
                    $up_online[$value][] = array_shift($ridList[$value]);
                }
            } else {
                return $this->return_error();
            }
        }
        cache($rid . '_robSlist', $ridList, 3600 * 24);

        //获取上线的机器人id
        foreach ($up_online as $key => $value) {
            foreach ($value as $v) {
                $attr_id[] = $v['mid'];
            }
            //更新数据库字段
            $rel = Db::name('portal_robot')
                ->where('mid', 'in', $attr_id)
                ->setField('status', 1);

            if (!$rel) {
                return $this->return_error();
            }
        }

        echo json_encode($up_online);
    }

    //机器人自动下线
    public function rbAutoXiaxian()
    {
        $defaultRid = cmf_get_default_rid();
        $rid = $this->request->param('rid', $defaultRid);
        $num = $this->request->param('num', 0, 'intval');
        $rid_attr = explode(',', $rid);

        if (empty($rid)) {
            return $this->return_error([], '房间号不能为空');
        }
        if (empty($num)) {
            return $this->return_error([], '次数不能为空');
        }

        if ($num == 1) {
            $ridList = $this->ridRobList($rid, 1);          //第一次时获取房间对应的机器人列表
            $ridZNum = $this->ridNum($rid, 1);               //第一次时获取房间对应的机器人     总数量
        } else {
            $ridList = cache($rid . '_robXlist');
            $ridZNum = cache($rid . '_robXfirstnum');
        }

        $ratioAttr = [0.1, 0.1, 0.2, 0.2, 0.2, 0.1, 0.1];

        if ($num == 1) {
            $actionNum = $ridZNum;                           //房间对应的可下线的真实数量   剩余机器人数量   = 总数量 - 上线的机器人数量
        } else {
            $actionNum = cache($rid . '_robXnum');             //房间对应的可下线的真实数量    剩余机器人数量
        }

        $ridActionNum = [];
        $ridsNum = [];
        //当前num 下要下线的机器人数量                     下线的机器人数量
        foreach ($ridZNum as $key => $value) {
            $ridsNum[$key] = ceil($value * $ratioAttr[$num - 1]) <= $actionNum[$key] ? ceil($value * $ratioAttr[$num - 1]) : $actionNum[$key];
            $ridActionNum[$key] = $actionNum[$key] - $ridsNum[$key];
        }

        if (empty($ridsNum) && empty($ridActionNum)) {
            return $this->return_error();
            exit;
        }

        cache($rid . '_robXnum', $ridActionNum, 3600 * 24);              //缓存剩余机器人数量


        //批量下线机器人
        foreach ($rid_attr as $value) {
            if ($ridsNum > 0) {
                for ($i = 0; $i < $ridsNum[$value]; $i++)               //循环遍历，取出要下线的机器人信息
                {
                    shuffle($ridList[$value]);
                    $up_online[$value][] = array_shift($ridList[$value]);
                }
            } else {
                return $this->return_error();
            }
        }

        cache($rid . '_robXlist', $ridList, 3600 * 24);

        //获取下线的机器人id
        foreach ($up_online as $key => $value) {
            foreach ($value as $v) {
                $attr_id[] = $v;
            }
            //更新数据库字段
            $rel = Db::name('portal_robot')
                ->where('mid', 'in', $attr_id)
                ->setField('status', 0);

            if (!$rel) {
                return $this->return_error();
            }
        }

        echo json_encode($up_online);

    }

    //房间对应的机器人上线列表
    public function ridRobList($rid, $status)
    {

        //select a.mid,a.username as client_name,a.aid as adminid,a.status,a.fid as roomid,b.login_switch from jiarenlist a left join configlist b on a.fid = b.fid where a.mid in ($sh_rbmid) and a.fid=$key
        $time = time();
        $week = date('w', $time);
        $strtime = date('H:i:s', $time);

        if ($status == 0) {
            $rel = Db::name('portal_robot')
                ->field('mid,username client_name,aid adminid,status,rid roomid')
                ->whereIn('rid', $rid)
                ->where('week', 'like', "%$week%")
                ->where('stime_str', '<=', $strtime)
                ->where('etime_str', '>=', $strtime)
                ->where('status', $status)
                ->select()
                ->toArray();

            $return = [];
            foreach ($rel as $key => $value) {
                $nkey = $value['roomid'];
                $return[$nkey][] = $value;
            }

            cache($rid . '_robSlist', $return, 3600 * 24);               //上线机器人列表

            return $return;
        } else {
            $rel = Db::name('portal_robot')
                ->field('rid,mid')
                ->whereIn('rid', $rid)
                ->where('week', 'like', "%$week%")
                ->where('stime_str', '<=', $strtime)
                ->where('etime_str', '>=', $strtime)
                ->where('status', $status)
                ->select()
                ->toArray();

            $return = [];

            foreach ($rel as $key => $value) {
                $return[$value['rid']][] = $value['mid'];
            }

            cache($rid . '_robXlist', $return, 3600 * 24);               //下线机器人列表

            return $return;
        }


    }

    //房间对应的机器人上线数量
    /**
     * $rid string 1001,1002,....
     * return array  [1001=>23,1002=>num]
     **/
    public function ridNum($rid, $status)
    {
        $time = time();
        $week = date('w', $time);
        $strtime = date('H:i:s', $time);

        $ridNum = DB::name('portal_robot')
            ->whereIn('rid', $rid)
            ->where('week', 'like', "%$week%")
            ->where('stime_str', '<=', $strtime)
            ->where('etime_str', '>=', $strtime)
            ->where('status', $status)
            ->group('rid')
            ->column('rid,count(*) num');

        if ($status == 0) {
            cache($rid . '_robSfirstnum', $ridNum, 3600 * 24);
        } else {
            cache($rid . '_robXfirstnum', $ridNum, 3600 * 24);
        }

        return $ridNum;
    }

    public function batchLgInfo()
    {
        $ids = $this->request->param()['ids'];
        if (empty($ids)) {
            return $this->return_error();
        }

        $rel = Db::name('portal_robot')
            ->whereIn('mid', $ids)
            ->select()
            ->toArray();

        return $this->return_success($rel);
    }

    //初始化房间上线的机器人
    public function initRobot()
    {
        $defRid = cmf_get_default_rid();
        $rid = $this->request->param('rid', $defRid, 'intval');

        $data = Db::name('portal_robot')
            ->where('rid', $rid)
            ->field('mid,username,aid adminid')
            ->select()
            ->toArray();

        if (!$data) {
            return $this->return_error();
        }

        shuffle($data);

        $robot = array_splice($data, 0, 20);

        return $this->return_success($robot);
    }

    //添加聊天信息
    /**
     * content:inputext,username:USERNAME,mid:MID,fid:CUR_FID,fname:Room_info.room,toid:tomid,tousername:tousername,toaid:toadminid,roleid:roleid,rolename:rolename,roleaid:roleaid
     **/
    public function addMsg()
    {
        if ($this->request->isAjax()) {

            $param = $this->request->param();

            $rel = $this->validate($param, 'Action');

            if ($rel !== true) {
                return $this->return_error([], $rel);
            }

            // $model = new ActionModel();
            //$rel = $model->checkMsg();
            //ip确认
            $checkIp = $this->check_ip();
            if ($checkIp) {
                return $this->return_error([], 'ip已被封');
            }
            //验证用户
            $checkUser = $this->check_user($param['mid']);
            if ($checkUser) {
                return $this->return_error([], '账号已被封');
            }

            //发言间隔
            $rel = $this->intval_time($param['fid']);
            if ($rel === 0) {
                return $this->return_error([], '请不要频繁发言');
            }
            //字符串过滤
            $param['content'] = $this->charFiltration($param['content']);             //字符串过滤

            $insterId = $this->check_content($param);

            if (!$insterId) {
                return $this->return_error();
            }


            $chat = array(
                'lid'        => $insterId,
                'rolemid'        => (($param['aid'] == 1) && $param['roleid']) ? $param['roleid'] : '',
                'roleusername'   => (($param['aid'] == 1) && $param['roleid']) ? $param['roleusername'] : '',
                'roleadminid'    => (($param['aid'] == 1) && $param['roleid']) ? $param['roleaid'] : '',
                'content'    => htmlspecialchars_decode($param['content']),
                'time'       => time(),
                'roomid'     => $param['fid'],
                'fname'      => $param['fname'],
                'toid'       => $param['toid'],
                'tousername' => $param['tousername'],
                'toaid'      => $param['toaid'],
                'shstatus'   => 1,

            );

            $contentFilt = $this->field_filtration($param['fid'], $param['content']);

            if ($contentFilt) {
                $chat['contentsource'] = htmlspecialchars_decode($contentFilt);
            }

            return $this->return_success($chat);

        }
    }

    //字符串过滤，将敏感字符转换为***
    public function field_filtration($rid, $content)
    {
        //获取房间敏感字段  字符串 以  |隔开  如骗人|QQ
        $shield_field = Db::name('room_basic')
            ->where('rid', $rid)
            ->value('shield_field');

        if ($shield_field) {

            $field_attr = explode('|', $shield_field);

            foreach ($field_attr as $value) {
                if (strpos($content, $value) !== false) {
                    $content = preg_replace_callback("/$value/i", function ($sta) {

                        return str_repeat('*', mb_strlen($sta[0], 'utf8'));
                    }, $content);
                }
            }
            return $content;

        }
    }

    //验证ip
    public function check_ip()
    {
        $ip = $this->request->server('REMOTE_ADDR');
        $checkIp = DB::name('ipban')
            ->where('ip', $ip)
            ->find();
    }
    //发言间隔
    /**
     * 当前时间time()  减去上次发言时间的值  和数据库中改房间设置的发言间隔进行比较
     **/
    public function intval_time($rid)
    {
        if (session('?speaktime')) {
            $interval_time = time() - session('speaktime');
            $value = Db::name('room_basic')
                ->where('rid', $rid)
                ->value('speak_intval');

            if ($interval_time < intval($value)) {
                return 0;
            }
        }
    }

    //验证用户是否被封
    public function check_user($mid)
    {
        $checkUser = DB::name('member')
            ->where('id', $mid)
            ->where('status', 1)
            ->value('ban');

        return $checkUser;
    }

    //内容过滤
    public function charFiltration($content)
    {
        trim($content);
        $content = preg_replace('/(&lt;script&gt;)/i', '', $content);
        $content = preg_replace('/(&lt;\/script&gt;)/i', '', $content);
        $content = preg_replace('/(&lt;\/html&gt;)/i', '', $content);
        $content = preg_replace('/(&lt;\/html&gt;)/i', '', $content);

        return $content;
    }

    //验证内容是否包含敏感字
    public function check_content($param)
    {
        //获取房间设置的敏感字段
        $shield_field = Db::name('room_basic')
            ->where('rid', $param['fid'])
            ->value('shield_field');

        $is_sensitive = 0;             //是否是敏感信息
        if ($shield_field) {
            $field_attr = explode('|', $shield_field);
            foreach ($field_attr as $value) {
                //包含敏感字
                if (strpos($param['content'], $value) !== false) {
                    $is_sensitive = 1;
                }
            }
        }

        /**
         * content:inputext,username:USERNAME,mid:MID,fid:CUR_FID,fname:Room_info.room,toid:tomid,tousername:tousername,toaid:toadminid,roleid:roleid,rolename:rolename,roleaid:roleaid,istrueuser:istrueuser
         **/
        $mid = $param['mid'];
        $username = $param['username'];
        $adminid = $param['aid'];
        $mid = $param['mid'];
        $tomid = $param['toid'];
        $tousername = $param['tousername'];
        $toadminid = $param['toaid'];
        $is_true_user = 2;
        $tois_true_user = 2;
        if(!empty($param['roleid']))
        {
            if(strpos($param['roleid'],'y_') !== false)
            {
                $mid = explode('y_',$param['roleid'])[1];
                $is_true_user = 1;
            }else if(strpos($param['roleid'],'r_') !== false)
            {
                $mid = explode('r_',$param['roleid'])[1];
                $is_true_user = 0;
            }
            $username = $param['roleusername'];
            $adminid = $param['roleaid'];
        }


        if(strpos($param['toid'],'y_') !== false)
        {
            $tomid = explode('y_',$param['toid'])[1];
            $tois_true_user = 1;
        }else if(strpos($param['toid'],'r_') !== false)
        {
            $tomid = explode('r_',$param['toid'])[1];
            $tois_true_user = 0;
        }


        $data['mid'] = $mid;
        $data['username'] = $username;
        $data['adminid'] = $adminid;
        $data['to_mid'] = $tomid;
        $data['to_username'] = $tousername;
        $data['to_adminid'] = $toadminid;
        $data['content'] = $param['content'];
        $data['date'] = date('Y-m-d');
        $data['create_time'] = time();
        $data['is_true_user'] = $is_true_user;
        $data['tois_true_user'] = $tois_true_user;
        $data['is_sensitive'] = $is_sensitive;

        session('speaktime', time());                    //时间间隔

        $insertId = Db::transaction(function() use ($data,$param){

            $insertId = Db::name('portal_message')
                ->insertGetId($data);

            $fid_attr = explode(',',$param['fid']);
            $insertAttr = array_map(function($rid) use ($insertId){
               return [
                   'msid'=>$insertId,
                   'rid' =>$rid
               ];
            },$fid_attr);

            Db::name('msg_rid')->insertAll($insertAttr);
            return $insertId;
        });

        return $insertId;
    }

    //删除聊天信息
    public function delMsgInfo()
    {
        if ($this->request->isAjax()) {
            $lid = $this->request->param('lid', 0, 'intval');            //获取聊天id
            $rel = Db::name('portal_message')
                ->delete($lid);

            if (!$rel) {
                return $this->return_error();
            }

            return $this->return_success();
        }
    }

    //封ip
    public function ipban()
    {
        if ($this->request->isAjax()) {

            $banmid = $this->request->param('banmid');                 //要封IP的用户id
            $mid = $this->request->param('mid', 0, 'intval');            //操作的用户id

            if (strpos($banmid, 'r_') !== false) {
                return $this->return_error([], '机器人不能够封Ip');
            } else {
                if (strpos($banmid, 'y_') !== false) {
                    $banmid = explode('r_', $banmid)[1];                    //获取游客表中改用户的ip
                    $ip = Db::name('tourist')
                        ->where('id', $banmid)
                        ->value('ip');
                } else {
                    $ip = Db::name('member')//获取会员表中用户的ip
                    ->where('id', $banmid)
                        ->value('ip');
                }
            }

            if (!$ip) {
                return $this->return_error();
            }

            $data['ip'] = $ip;
            $data['mid'] = $banmid;
            $data['create_uid'] = $mid;
            $data['create_time'] = time();

            $rel = Db::name('ipban')->insert($data);

            if (!$rel) {
                return $this->return_error();
            }
            return $this->return_success();
        }
    }

    //封用户
    public function userban()
    {
        if ($this->request->isAjax()) {
            //获取将要封的用户id
            $banmid = $this->request->param('banmid');

            if (strpos($banmid, 'r_') !== false)              //假人
            {
                return $this->return_error([], '机器人不能够禁言');

            } else {
                if (strpos($banmid, 'y_') !== false)           //游客
                {
                    $banmid = explode('y_', $banmid)[1];

                    $rel = Db::name('tourist')//游客表对于字段改为1
                    ->where('id', $banmid)
                        ->setField('ban', 1);
                } else {
                    $rel = Db::name('member')//会员表对于字段改为1
                    ->where('id', $banmid)
                        ->setField('ban', 1);
                }
            }

            if (!$rel) {
                return $this->return_error();
            }
            return $this->return_success();
        }
    }

    //送礼物
    public function addgift()
    {
        /**
         * uid:MID,                             用户id
         * username:User_info.nickname,         用户名
         * adminid:ADMINID,                     adminid
         * role:role,                           角色id
         * roleusername:roleusername,           角色名称
         * roleadminid:roleadminid,             角色adminid
         * rid:CUR_FID,                         房间  （现单房间）
         * content:content                      礼物内容
         **/

        $param = $this->request->param();
        $rid = $param['rid'];
        if(strpos($param['rid'],',') !== false)
        {
              $ridattr = explode(',',$param['rid']);
              $rid = $ridattr[0];
        }

        //获取房间讲师名称
        $data['teacher'] = Db::name('room_basic')
            ->where('rid',$rid)
            ->value('teacher');

        if(!$data['teacher'])
        {
            $data['teacher'] = '';
        }

        //角色判断                  如果$data['role'] ='' 则是真实用户  is_true_user = 2
        $roleid = $param['roleid'];
        $data['is_true_user'] = 2;
        $username = $param['username'];
        $mid = $param['uid'];
        $adminid = $param['adminid'];
        if (!empty($param['roleid'])){
            if (strpos($param['roleid'], 'r_') !== false) {
                $data['is_true_user'] = 0;                   //假人
                $mid = explode('r_',$param['roleid'])[1];
            } else {
                if (strpos($param['roleid'], 'y_') !== false) {
                    $data['is_true_user'] = 1;                  //游客
                    $mid = explode('y_',$param['roleid'])[1];
                }
            }
            $username = $param['roleusername'];
            $adminid = $param['roleadminid'];
        }

        $data['mid'] = $mid;
        $data['username'] = $username;
        $data['adminid'] = $adminid;
        $data['create_time'] = time();
        $data['content'] = $param['content'];
        $data['remarks'] = $param['remarks'];
        $data['gift_name'] = $param['giftname'];
        $data['date'] = date('Y-m-d');
        $data['type'] = 3;
        $data['gift_num'] = 1;

       $data['id'] =  Db::transaction(function() use ($data,$param){

            $insertId = Db::name('portal_message')->insertGetId($data);

            $rid = explode(',',$param['rid']);
            $insertAttr = array_map(function($rid) use ($insertId){
                return [
                    'msid'=>$insertId,
                    'rid' =>$rid
                ];
            },$rid);

            Db::name('msg_rid')->insertAll($insertAttr);

           return $insertId;
        });


        if (!$data['id']) {
            return $this->return_error();
        }

        $data['remarks'] = htmlspecialchars_decode($param['content']);
        $data['rid'] = $param['rid'];
        $data['mid'] =  $data['is_true_user'] != 2 ? $param['roleid'] : $mid;
        return $this->return_success($data);
    }
}