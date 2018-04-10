<?php
use think\Db;
use tree\Tree;
use think\exception\HttpResponseException;
use think\Response;
use think\View as ViewTemplate;
use think\Config;
/**
 * 获取后台用户所属的房间
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
           $level = cmf_get_current_admin_level();
           $role = cmf_get_current_admin_role();

           $role_array = Db::name('role')->where('status',1)->select()->toArray();
           $tree = new Tree();

           if($tree->init($role_array,$role))
           {
               $child_role = $tree->getTreeTdArray($level,0,true);
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
 * 获取后台用户所属角色的最高层级
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
            return false;
        }else{
            return $level;
        }
    }
}
if(!function_exists('switch_rid_ridname')) {
    function switch_rid_ridname($data)
    {

        $rid = cmf_get_current_admin_rid();
        $return= '';
        $rid_list = model('app\portal\model\AdminHouseModel')
            ->whereIn('rid',$rid)
            ->column('rid,room');

       if(isset($data['isall']) && $data['isall'] == 1 && $data['rid'] == 0)
       {
           $return = $data;
           $return['ridname'] = '所有房间';

       }elseif(strpos($data['rid'],',') === false and key_exists($data['rid'],$rid_list))
        {
            $return = $data;
            $return['ridname'] = $rid_list[$data['rid']];

        }elseif(strpos($data['rid'],',') !== false)
        {

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
 **/

if(!function_exists('cmf_get_current_admin_role'))
{
    function cmf_get_current_admin_role($status=null)
    {
        if($status == null)
        {
            $id = cmf_get_current_admin_id();
            $rid = DB::name('role_user')
                ->alias('ru')
                ->join('__ROLE__ r','ru.role_id = r.id')
                ->where('ru.user_id',$id)
                ->where('r.status',1)
                ->value('r.id');

            return $rid;
        }else{
            $id = cmf_get_current_admin_id();
            $rid = DB::name('role_user')
                ->alias('ru')
                ->join('__ROLE__ r','ru.role_id = r.id')
                ->where('ru.user_id',$id)
                ->where('r.status',$status)
                ->value('r.id');

            return $rid;
        }
    }
}

/**
 * 获取前台用户选择的房间号
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
