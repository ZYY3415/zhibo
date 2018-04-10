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

use app\admin\model\PageModel;
use app\portal\model\PortalMessageModel;
use app\portal\model\RoomBasicModel;
use app\portal\model\SendRedbagModel;
use app\user\model\ConsumeModel;
use app\user\model\MemberModel;
use cmf\controller\HomeBaseController;
use app\portal\model\AdminHouseModel;
use app\portal\model\AdminHousevModel;
use think\Db;
use think\Exception;

class IndexController extends HomeBaseController
{
    protected $rid =0;



    /**前台首页**/
    public function index()
    {
        $defaultRid = $this->firstRid();         //获取房间列表中的以rid升序的第一个房间作为默认的房间号
        $this->rid = $this->request->param('rid',$defaultRid,'intval');


        $mobile = cmf_is_mobile();                 //登录设备检查   如果是pc则是false  否则为true
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        $cacheRoom = session_id().'_'.$this->rid.'_roomInfo';
        if(cache("?$cacheName"))
        {
            $userInfo = cmf_get_redis_value($cacheName);
        }else{
            $userInfo = session($cacheName);
        }


        //获取要访问的房间的密码
        if(!empty(cache("?$cacheRoom")))
        {
            $roomInfo = cmf_get_redis_value($cacheRoom);
        }else{
             $roomInfo = model('room_basic')
                 ->where('rid',$defaultRid)
                 ->find()
                 ->toArray();
        }

        $ridpwd = $roomInfo['password'];

        if(!session('?'.$this->rid."encode") || (session('?'.$this->rid."encode") &&  empty($ridpwd)))
        {
            $roomInfo = $this->rid_list();                 //房间检测
            $this->switch_house($roomInfo);              //房间开关
            $this->house_pwd($roomInfo);                 //房间密码

        }else{
            $decodeStr = base64_decode(session($this->rid."encode"));
            if(strpos($decodeStr,$this->rid) == 0)
            {
                $pwd = AdminHouseModel::where(['rid'=>$this->rid])->value('password');
                $passowrd = substr($decodeStr,4,-3);

                if(strtolower($pwd) != strtolower($passowrd))
                {
                    castError(404,'非法请求','您的请求被认定为非法！');
                }
            }else{
                castError(404,'非法请求','您的请求被认定为非法！');
            }
        }

        $this->tourist($roomInfo);        //房间是否禁止游客访问
        $this->ipBan();                    //ip检测
        $this->sightseer($roomInfo);                //第一次访问时，生成游客信息保存到session userInfo 中  有效期为一天
        $this->userBan();                  //用户检测
        $this->updateTourist();            //游客非第一次登登陆  更新登陆次数,ip
        // $this->init();
        $this->leftPage();                 //显示左侧单页
        $this->bottomPage();               //显示底部单页
        $this->roleInfo();                 //显示角色
        $this->bottomImg($roomInfo);                //底部轮播图

        if(!cmf_is_mobile())
        {
            $this->historyMsg(1);               //历史消息
            $this->historyGift();               //礼物记录
        }else{
            $this->historyMsg();               //历史消息
        }


        $this->assign('rid',$this->rid);
        $this->assign('userInfo',$userInfo);
        $this->assign('roomInfo',$roomInfo);
        $this->assign('userInfoStr',json_encode($userInfo));
        $this->assign('roomInfoStr',json_encode($roomInfo));
        $this->assign('sessionId',session_id());

        if($mobile)
        {
            return $this->fetch('mobile/index');
        }else{
            return $this->fetch('index/index');
        }

    }


    /**获取房间列表中的第一个房间**/
    public function firstRid()
    {
        $rid = cmf_get_default_rid();

        if($rid === null)
        {
            castError(404,'房间信息错误','您访问的房间不存在，请确认房间是否正确！');            //直播间中没有房间
        }

        return $rid;
    }

    //登录设备判断
    public function facility()
    {

        if(cmf_is_mobile())
        {

            $default_rid = cmf_get_default_rid();
            $rid = $this->rid;
            if(empty($this->rid))
            {
                $rid = $default_rid;
            }
                $this->redirect('/mobile',['rid'=>$rid]);
        }

    }


    /**判断房间是否存在**/
    public function rid_list()
    {
        $switchHouse = model('adminHouse')
                            ->where('rid',$this->rid)
                            ->find()->toArray();

        if($switchHouse === null)         //房间不存在
        {
            castError(404,'房间不存在','您访问的房间不存在，请确认房间是否正确！');
        }

        $prefix = session_id();                                      //获取用户的sessionId  将其作为key前缀
        cache($prefix.'_sessionId',$prefix,3600*24);
        $cacheName = $prefix.'_'.$this->rid.'_roomInfo';
        cache($cacheName,$switchHouse,3600*24);                       //将房间信息保存到reids 中


        return $switchHouse;
    }

    /**房间是否关闭**/
   public function switch_house($roomInfo)
   {
       $switch_house =  $roomInfo['switch_house'];
      if(empty($switch_house)){
          castError('404','房间已关闭','该房间已被房间管理员关闭！');
      }
   }

    /**房间密码判断**/
    public function house_pwd($roomInfo)
    {
        if($this->request->isGet())
        {
            if(!empty($roomInfo['password']))
            {
                return $this->redirect('pwdLogin',['rid'=>$this->rid]);        //输入密码页面
            }

        }
    }
    //房间密码登录页面
    public function pwdLogin()
    {
        if($this->request->isGet())
        {
            $rid= $this->request->param('rid');
            return $this->fetch('password',['rid'=>$rid]);

        }else if($this->request->isAjax()){

            $default_rid = cmf_get_default_rid();
            $rid = $this->request->param('rid',$default_rid,'intval');

            $password = $this->request->param('pwd','','htmlspecialchars,addslashes,strip_tags');
            $rel = AdminHouseModel::get(['rid'=>$rid]);

            if(!$rel)
            {
                castError(404,'房间不存在','您访问的房间不存在，请确认房间是否正确！');
            }

            if(strtolower($password) != strtolower($rel->password))
            {
               return $this->return_error(['rid'=>$rid],'密码错误');

            }else{

                if(!session('?'.$rid."encode"))
                {
                    $encodeStr = base64_encode($rid.$rel->password.'123');
                    session($rid."encode",$encodeStr);
                }else{
                    $encodeStr = session($rid."encode");
                }

                return $this->return_success(['rid'=>$rid,'str'=>$encodeStr],'',"/rid/$rid");
            }
        }
    }

    /**判断房间是否禁止游客访问**/
    public function tourist($roomInfo)
    {

        if(!$roomInfo['sightseer_login'])
        {
            castError('404','禁止游客房间','当前房间暂不允许游客进入！');
        }
    }

    /**ip判断**/
    public function ipBan()
    {
        $ip = get_client_ip(0,true);

        $ipBan = Db::name('ipban')
            ->column('ip');

        if(in_array($ip,$ipBan))
        {
            castError('404','您已被禁止访问','由于您的不良行为已被该房间管理员拉进黑名单，如需要解封请联系房间管理员！');
        }
    }

    /**用户名判断**/
    public function userBan()
    {
        $mid = cmf_get_current_user_id();     //前台用户id
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        $userBan = true;
        if($mid)
        {
            $userBan = Db::name('member')
                ->where('id',$mid)
                ->value('ban');

        }else if(session("?$cacheName"))
        {
            $userInfo = unserialize(session($cacheName));
            $userBan = Db::name('tourist')
                           ->where('id',$userInfo['id'])
                           ->value('ban');
        }

        if($userBan)
        {
            castError('404','您的账号已被禁止访问','由于您的不良行为已被该房间管理员拉进黑名单，如需要解封请联系房间管理员！');
        }
    }


    /**更新游客的登录次数，Ip**/
    public function updateTourist()
    {
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        if(session("?$cacheName"))
        {
            $userInfo = unserialize(session($cacheName));

            $rel = Db::name('tourist')
                ->where('id',$userInfo['id'])
                ->update([
                    'ip'             =>    get_client_ip(0,true),
                    'login_count'    =>    $userInfo['login_count'] + 1
                ]);

            if($rel)
            {
                $data = DB::name('tourist')->find($userInfo['id']);
                session($cacheName,serialize($data));                //更新session 值
            }
        }
    }


    /**第一次访问时为游客访问模式**/
    public function sightseer($roomInfo)
    {
        $mid = cmf_get_current_user_id();     //前台用户id,不存在为0
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        if(!$mid && !session("?$cacheName"))
        {
            $randomStr = cmf_random_string();
            $adminid = config('portal_role_config.tourist');

            $arr=array(
                'username'  =>  "游客".$randomStr,                //用户名
                'nickname'  =>  "游客".$randomStr,                //昵称
                'adminid'   =>  $adminid,                               //游客所属的角色id
                'login_time'=>  time(),                           //登录时间
                'type'      =>  1,                                //用户类型 0 注册会员  1游客
                'sex'       =>  mt_rand(1,2),
                'rid'       =>  $this->rid,                       //房间
                'ip'        =>  get_client_ip(0,true),            //登录的ip地址
                'remark'    =>  '游客登录',                        //备注
            );

            $insert_id = Db::name('tourist')
                ->insertGetId($arr);

            if(!$insert_id)
            {
                castError('404','页面不存在',"请求的页面丢失在黑洞中！");
            }

            $tourist_info = Db::name('tourist')
                ->alias('t')
                ->field('t.*,m.username tuijianusername,m.nickname tuijiannickname,m.adminid tuijianadminid')
                ->join('__MEMBER__ m','t.tuijianmid = m.id','LEFT')
                ->where('t.id',$insert_id)
                ->find();


            cache($cacheName,$tourist_info,3600*24);
            session($cacheName,serialize($tourist_info));

        }else if(!$mid && session("?$cacheName"))
        {

            $usInfo = unserialize(session($this->rid.'_userInfo'));
            $userInfo = Db::name('tourist')
                ->alias('t')
                ->field('t.*,m.username tuijianusername,m.nickname tuijiannickname,m.adminid tuijianadminid')
                ->join('__MEMBER__ m','t.tuijianmid = m.id','LEFT')
                ->find($usInfo['id']);

           // $cacheName = session_id().'_'.$this->rid.'_userInfo';

            cache($cacheName,$userInfo,3600*24);

            session($cacheName,serialize($userInfo));
        }
    }

    /**左侧单页显示**/
    public function leftPage()
    {
        $leftPage = Db::name('page')
            ->where('is_start', PageModel::START_ENABLE)
            ->where('position', PageModel::POSITION_LEFT)
            ->where('rid', $this->rid)
            ->order('list_order desc')
            ->limit(12)
            ->select();

        foreach ($leftPage as &$item) {
            if (strpos($item['link'], "http") === 0) {
                continue;
            } else if (strpos($item['link'], "/") === 0) {
                continue;
            } else {
                $item['link'] = empty($item['link']) ? '' : url($item['link']);
            }
        }

        $this->assign('leftPage', $leftPage);
    }

    /**
     * 底部单页显示
     */
    public function bottomPage()
    {
        $limit = 4;
        $roomBasic = model('adminHouse')->where('rid', $this->rid)->find();
        if ($roomBasic) {
            $limit = $roomBasic['img_carousel'] == 1 ? 4 : 5;
        }
        $pageModel = new PageModel();
        $bottomPage = $pageModel->getBottomPage($this->rid, $limit);
        $data = $bottomPage->toArray();
        if(!$roomBasic['img_carousel'])
        {
            array_shift($data);
        }

        $this->assign('bottomPage', $data);
        $this->assign('img_carousel', $roomBasic['img_carousel']);
    }

    //底部轮播图
    public function bottomImg($roomInfo)
    {
        if($roomInfo['img_carousel'])
        {
            $data = Db::name('carousel')
                ->where('rid',$this->rid)
                ->value('carousel');

            $carousel = explode(',',$data);
            rsort($carousel);
        }else{
            $carousel = [];
        }
        $this->assign('carousel',$carousel);
    }

     //历史聊天消息
    public function historyMsg($type = '')
    {
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        if(cache("?$cacheName"))
        {
            $userInfo = cmf_get_redis_value($cacheName);
        }else{
            $userInfo = unserialize(session($cacheName));
        }

         $msgnum = Db::name('portal_message')->count('id');
         if($msgnum > 100)
         {
             $start = intval($msgnum) - 100;
         }else{
             $start = '';
         }
        $where = '';

        //检查审核状态
        if($userInfo['adminid'] != 1)
        {
            $where['approve_status'] = 1;
        }

        $userrid = $userInfo['rid'];
        if(!$userrid)
        {
            castError('404','页面不存在',"请求的页面丢失在黑洞中！");
        }
        if(!empty($type))
        {
            $where['m.type'] = intval($type);
        }


         $data = Db::name('portal_message')
             ->distinct(true)
             ->alias('m')
             ->field('m.*')
             ->join('__MSG_RID__ mr','mr.msid = m.id')
             ->where('mr.rid','in',$userrid)
             ->where($where)
             ->order('id desc')
             ->limit($start)
             ->select()
             ->toArray();

        array_multisort($data,SORT_ASC);

        $this->assign('historymsg',$data);
    }
    //历史礼物记录
    public function historyGift()
    {
        $cacheName = session_id().'_'.$this->rid.'_userInfo';
        if(cache("?$cacheName"))
        {
            $userInfo = cmf_get_redis_value($cacheName);
        }else{
            $userInfo = unserialize(session($cacheName));
        }

        $userrid = $userInfo['rid'];

        if(!$userrid)
        {
            castError('404','页面不存在',"请求的页面丢失在黑洞中！");
        }

        $data = Db::name('portal_message')
            ->alias('m')
            ->field('m.*')
            ->join('__MSG_RID__ mr','mr.msid = m.id')
            ->where('mr.rid','in',$userrid)
            ->where('m.type',3)
            ->select()
            ->toArray();

        $this->assign('historygift',$data);
    }
    /**
     * 角色信息
     */
    public function roleInfo()
    {

        $roleInfo = Db::name('protal_role')
            ->where('status',1)
            ->column('keyword,roleicon');
       $this->assign('roleInfo',json_encode($roleInfo));
       $this->assign('roleAttr',$roleInfo);
    }


    /**
     * 发送红包
     * @return \Jump\json
     */
    public function sendRedBag()
    {
        $mid = cmf_get_current_user_id();
        $member = MemberModel::get($mid);
        //如果是游客
        if ($member['adminid'] == 14) {
            return $this->return_error(null, 'invalidate_role');
        }
        //余额不足
        $money = $this->request->param('money', 0, 'doubleval');
        if ($member['money'] <= 0 || $member['money'] < $money) {
            return $this->return_error(null, 'invalidate_total');
        }
        //金额数小于红包个数，最小金额0.01
        $num = $this->request->param('num', 0, 'intval');
        if (($money * 100) < $num) {
            return $this->return_error(null, 'invalidate_num');
        }
        $content = $member['username'] . '送出了一个红包！';

        Db::startTrans();
        //写入红包记录
        $sendRedbagModel = SendRedbagModel::create(array(
            'mid' => $mid,
            'num' => $num,
            'total' => $money,
            'content' => $content,
            'num_balance' => $num,
            'total_balance' => $money,
            'create_time' => time(),
        ));

        $redBagId = $sendRedbagModel->getLastInsID();
        //处理消息内容
        $roleId = $this->request->param('roleid');
        $roleName = $this->request->param('rolename');
        $roleAId = $this->request->param('roleaid');

        $roomId = $this->request->param('rid');
        $roleId = $member['adminid'] < 3 && $roleId ? $roleId : $member['id'];
        $roleAId = $member['adminid'] < 3 && $roleAId ? $roleAId : $member['adminid'];
        $roleName = $member['adminid'] < 3 && $roleName ? $roleName : $member['username'];

        $portalMessageModel = PortalMessageModel::create(array(
            'mid' => $roleId,
            'username' => $roleName,
            'content' => $content . '<img rel="' . $redBagId . '"  src="/images/redbag_open.png" style="width:186px;float:left;cursor:pointer" onclick="getRedbag(this)"/>',
            'create_time' => time(),
            'approve_time' => time(),
            'type' => PortalMessageModel::TYPE_REDBAG,
            'approve_status' => PortalMessageModel::APPROVE_STATUS_YES,
            'rid' => $roomId,
            'adminid' => $roleAId,
        ));

        $lid = $portalMessageModel->getLastInsID();

        //处理消费记录
        ConsumeModel::create(array(
            'mid' => $member['id'],
            'count' => $money,
            'content' => $content,
            'create_time' => time(),
            'remark' => '送红包',
        ));
        Db::commit();

        $data = array(
            'lid' => $lid,
            'content' => $content . '<img rel="' . $redBagId . '"  src="/images/redbag_open.png" style="width:186px;float:left;cursor:pointer" onclick="getRedbag(this)"/>',
            'balance' => $member['money'] - $money,
            'rid' => $roomId,
            'mid' => $roleId,
            'username' => $roleName,
            'adminid' => $roleAId,
            'time' => time()
        );
        return $this->return_success($data);
    }

    /**首页视频播放方法**/
    public function play()
    {
        $defaultRid = cmf_get_default_rid();
        if($defaultRid == null)
        {
            abort('系统错误！');
        }

        $rid = $this->request->param('rid',$defaultRid,'intval');
        $facility = $this->request->param('facility','pc');


        $videoInfo = model('adminHousev')
            ->field('play_source,pc_play_site,pc_play_indate,m_play_site,m_play_indate,YYfcode,YYcode')
            ->where('rid',$rid)
            ->find();


        $this->assign('videoInfo',$videoInfo);
        $this->assign('facility',$facility);

        return $this->fetch(':play');
    }

    //移动端主页
    public function mobileIndex()
    {
        $defaultrid =cmf_get_default_rid();
        $rid = $this->request->param('rid',$defaultrid,'intval');

        $this->assign('rid',$rid);
        return $this->fetch('mobile/index');
    }

    //移动端登录
    public function mobileLogin()
    {
        return $this->fetch('mobile/login');
    }

}
