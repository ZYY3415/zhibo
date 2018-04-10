<?php
use think\Db;
//判断前台用户所属的rid  房间字符串是否在 该后台角色下的房间列表中
if(!function_exists('portal_rid_exists')){
    function portal_rid_exists($rid)
    {
         $rid = isset($rid) ? $rid : '';
         $rid_list = cmf_get_current_admin_rid();
         if(strpos($rid,',') === false)
         {
            if(in_array($rid,$rid_list))
            {
                return true;
            }else{
                return false;
            }
         }else{
             $user_rid = explode(',',$rid);

             $new_attr = array_merge($rid_list,$user_rid);

             if(empty(array_diff($new_attr,$rid_list)))
             {
                 return true;
             }else{
                 return false;
             }
         }
    }
}

if(!function_exists('switch_rid_ridname')) {
    function switch_rid_ridname($data)
    {

        $rid = cmf_get_current_admin_rid();
        $rid_list = model('adminHouse')
            ->whereIn('rid',$rid)
            ->column('rid,room');

        if(strpos($data['rid'],',') === false and key_exists($data['rid'],$rid_list))
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


