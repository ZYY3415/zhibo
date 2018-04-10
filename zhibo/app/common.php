<?php
use think\Db;
use tree\Tree;
use think\exception\HttpResponseException;
use think\Response;
use think\View as ViewTemplate;
use think\Config;
/**
 * 获取后台用户所属的房间
 * return array or  string  用户所属的房间列表
**/
if(!function_exists('cmf_get_current_admin_rid')){
    function cmf_get_current_admin_rid(){
       if(cmf_get_current_admin_id() === 1)
       {
           $rid_attr = model('app\portal\model\AdminHouseModel')
               ->order('rid')
               ->column('rid');

          return $rid_attr;
       }else{
           $level = cmf_get_current_admin_level();                                                 //当前角色的父级角色id
           $role = empty(cmf_get_current_admin_role()) ? 0 : cmf_get_current_admin_role();         //null   or    number  角色id

           $role_array = Db::name('role')
               ->where('status',1)
               ->select()
               ->toArray();

           $tree = new Tree();
           if($tree->init($role_array,$role))
           {
               $child_role = $tree->getTreeTdArray($level,0,true);     //或去当前用户下的所有子级角色id
               //根据子级角色id 获取用户子级房间数组
               $child_rid = Db::name('role')
                   ->where('id','in',$child_role)
                   ->where('status',1)
                   ->column('rid');

               $return = [];
               foreach($child_rid as $value)
               {
                   if(strpos($value,',') === false)
                   {
                       $return[] = $value;
                   }else{
                       $return = array_merge($return,explode(',',$value));
                   }
               }
               return array_unique($return);
           }else{
               return 'userinfo session undefined';
           }
       }
    }
}
/**
 * 获取后台用户所属角色的父角色id
 * return false or number
 **/
if(!function_exists('cmf_get_current_admin_level'))
{
    function cmf_get_current_admin_level()
    {
        $id = cmf_get_current_admin_id();
        $level = DB::name('role_user')
            ->alias('ru')
            ->join('__ROLE__ r','ru.role_id = r.id')
            ->where('ru.user_id',$id)
            ->where('r.status',1)
            ->value('parent_id');

        if($level === null)
        {
            return 0;
        }else{
            return $level;
        }
    }
}
/**切换用户所属的房间rid 转换为ridname
 * $data array   数组中必须有rid 字段
 * 返回的房间名字段为ridname
 * return array
 **/
if(!function_exists('switch_rid_ridname')) {
    function switch_rid_ridname($data)
    {

        $rid = is_array(cmf_get_current_admin_rid()) ? cmf_get_current_admin_rid() :'';
        //如果用户所属的角色为空
        if(empty($rid))
        {
            return $data;
        }
        //如果数据中不包含rid字段
        if(!key_exists('rid',$data))
        {
            $data['ridname'] = '';
            return $data;
        }


        $return= '';
        //获取当前角色所属房间的所有信息
        $rid_list = model('app\portal\model\AdminHouseModel')
            ->whereIn('rid',$rid)
            ->column('rid,room');

        //超级管理员
       if(isset($data['isall']) && $data['isall'] == 1 && $data['rid'] == 0)
       {
           $return = $data;
           $return['ridname'] = '所有房间';

       }elseif(strpos($data['rid'],',') === false and key_exists($data['rid'],$rid_list))
        {
            //普通用户单个房间
            $return = $data;
            $return['ridname'] = $rid_list[$data['rid']];

        }elseif(strpos($data['rid'],',') !== false)
        {
            //多个房间
            $rid_attr = explode(',',$data['rid']);
            $ridname = [];
            foreach($rid_attr as $value)
            {
                if(key_exists($value,$rid_list))
                {
                    $ridname[] = $rid_list[$value];
                }
            }
            $return = $data;
            $return['ridname'] = implode(',',$ridname);
        }
        return $return;
    }
}
/**
 * 获取后台用户所属角色的id
 * @status 获取角色的状态
 * null  获取角色为1的用户的角色id
 * return 角色id   一个用户对应一个角色
 **/

if(!function_exists('cmf_get_current_admin_role'))
{
    function cmf_get_current_admin_role($status=null)
    {
        $id = cmf_get_current_admin_id();
        if($status == null)
        {
            $rid = DB::name('role_user')
                ->alias('ru')
                ->join('__ROLE__ r','ru.role_id = r.id')
                ->where('ru.user_id',$id)
                ->where('r.status',1)
                ->value('r.id');
        }else{
            $rid = DB::name('role_user')
                ->alias('ru')
                ->join('__ROLE__ r','ru.role_id = r.id')
                ->where('ru.user_id',$id)
                ->where('r.status',$status)
                ->value('r.id');
        }
        return $rid;
    }
}

/**
 * 获取默认的房间号
 * return rid   null or $rid
 **/

if(!function_exists('cmf_get_default_rid'))
{
    function cmf_get_default_rid()
    {
        $rid = model('app\portal\model\AdminHouseModel')
            ->order('rid')
            ->value('rid');

        session('default_Rid',$rid);
        return $rid;
    }
}


/**
 * 获取redis 中的值
 **/
if(!function_exists('cmf_get_redis_value'))
{
    function cmf_get_redis_value($name)
    {
           if(strpos($name,'.') === false)
           {
               return cache($name);
           }else{
               $nameAttr = explode('.',$name);
               return cache($nameAttr[0])[$nameAttr[1]];
           }
    }
}
/**
 * 获取redis 中的值
 **/


if(!function_exists('castError'))
{
    function castError($code = 404,$msg = '',$msgInfo= '',$param = [],array $header = [])
    {

        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $param,
            'msgInfo' =>$msgInfo
        ];

        $type = 'html';

        $template = Config::get('template');
        $view = Config::get('view_replace_str');

        $result = ViewTemplate::instance($template, $view)
             ->fetch(Config::get('http_exception_template.404'), $result);


        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);

    }
}

if(!function_exists('mergeRid')) {
    function mergeRid($data)
    {
       $return = [];
       foreach($data as $v)
       {
             if(!array_key_exists($v['msid'],$return))
              {
                  $return[$v['msid']] = $v['room'];
              }else{
                  $return[$v['msid']] .= ','.$v['room'];
              }
       }
        return $return;
    }
}
