<?php
use think\Db;


if(!function_exists('cmf_get_current_user_rid')){
    function cmf_get_current_user_rid(){
          if(session('?ADMIN_ID'))
          {
              $uid = session('ADMIN_ID');
              $rid_attr = DB::name('role_user')
                ->alias('rb')
                ->join('__ROLE__ r','r.id = ru.role_id')
                ->where('ru.user_id',$uid)
                ->column('r.rid');

              $rid_ata = [];
              foreach($rid_attr as $value)
              {
                  if(strpos($value,',') === false)
                  {
                      $rid_ata[] = $value;
                  }else{
                      $rid_ata = array_merge($rid_ata,explode(',',$value));
                  }
              }

              $rid_ata = array_unique($rid_ata);
              return $rid_ata;
          }else
          {
              return 'userinfo session undefined';
          }
    }
}

















