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

use app\admin\model\ShareFileModel;
use app\portal\model\CourseModel;
use cmf\controller\HomeBaseController;
use think\Db;
use think\Exception;
/**
 * ajax 请求处理类
 **/
class AppHtmlController extends HomeBaseController
{
    //共享文档方法
   public function shareHtml()
   {
     return $this->fetch();
   }

    //讲师团队
    public function teacherTeam()
    {
        $rid = $this->request->param('rid');
        return $this->fetch();
    }

    //课程安排
    public function course()
    {
        $rid = $this->request->param('rid');
        $date = $this->request->param('date', date('Y-m-d'));
        if (empty($rid)) {
            //读取默认房间
            $rid = session('default_Rid');
        }
        $courseMolde = new CourseModel();
        $course = $courseMolde->getCourseList($rid, $date);
        $this->assign('date', date('Y年m月'));
        $this->assign('course', $course);
        return $this->fetch();
    }

    public function getShareFileList()
    {
        $pageNo = $this->request->param('pageNo', 1);
        $pageSize = $this->request->param('pageSize', 10);

        //获取分页后的结果
        $shareFileModel = new ShareFileModel();
        $list = $shareFileModel->getShareFileList($pageNo, $pageSize);

        return $this->return_success($list);
    }


    //手机直播方法
    public function qrCode()
    {
        return $this->fetch();
    }


    //喊单提醒
    public function hdWarn()
    {
        return $this->fetch();
    }

    //在线解答
    public function teacherSolve()
    {
        return $this->fetch();
    }

    //换肤
    public function switchSkin()
    {
        $default = cmf_get_default_rid();
        $rid = $this->request->param('rid',$default,'intval');
        $default_imgdir = ROOT_PATH.'public/static/images/skin';
        $imgAttr = scandir($default_imgdir);

        $data = array_filter($imgAttr,function($index) use($default_imgdir){

            if($index == '.' || $index == '..')
            {
                return false;
            }else if(strpos($index,'x.jpg') !== false)
            {
                echo $default_imgdir.'/'.$index;exit;
                return $default_imgdir.'/'.$index;
            }
        });
        dump($data);exit;
        sort($data);

        $bgimg = Db::name('bgimg')
            ->field('imgs')
            ->where('rid',$rid)
            ->limit(24)
            ->find();
        if($bgimg == null)
        {
            $num = 0;
            $bgimg = [];
        }else{
            $num =24 - count($bgimg);
        }
         $attr = [];
        for($i=0;$i<$num;$i++)
        {
            shuffle($data);
            $fdata = array_shift($data);
            if(!in_array($fdata,$attr))
            {
                $attr[] = $fdata;
            }
        }

        $child_img = explode(',',$bgimg['imgs']);


        $new_img = array_merge($child_img,$attr);
        dump($new_img);exit;

        $this->assign('data',$bgimg['child_img']);
        return $this->fetch();
    }
}