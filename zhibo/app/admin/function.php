<?php


  if(!function_exists('switch_user_status')){
      function switch_user_status($id = 0){
          $id = intval($id);
          $attr = ['已拉黑','正常','未验证'];

          if(array_key_exists($id,$attr))
          {
              return $attr[$id];
          }else
          {
              return '未验证';
          }
      }
  }

