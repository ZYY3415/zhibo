<?php
namespace app\portal\controller;

use app\portal\model\PortalHandanModel;
use cmf\controller\AdminBaseController;
use app\portal\model\AnswerModel;
use think\Db;
use think\Validate;
/**
    前台首页附加应用的控制器接口
 **/
class ApplyController extends AdminBaseController
{
    /**
     * 提问方法
    **/
    public function ajax_wenda()
    {
        if($this->request->IsAjax())
        {
            $param = $this->request->param();

           $rel = $this->validate($param,'Question');

            if($rel !== true)
            {
                return  $this->return_error($rel);
            }
            $param['time'] = time();

            $result = PortalQuestionModel::create($param);

            if(!$result)
            {
                return $this->error([],'数据库插入失败');
            }

            return $this->success();
        }
    }
    /**
     * 回答
     **/
    public function ajax_answer()
    {
        if($this->request->IsAjax())
        {
            $param = $this->request->param();
            $rel = $this->validate($param,'Answer');

            if($rel !== true)
            {
                return  $this->return_error($rel);
            }
            $param['time'] = time();

            $result = AnswerModel::create($param);

            if(!$result)
            {
                return $this->error([],'数据库插入失败');
            }

            return $this->success();
        }
    }

    /**
     * 显示提问状态不为1的提问信息
    **/
    public function question_index()
    {

    }

    /**
     * 喊单功能
    **/
    public function ajax_handan()
    {
        if($this->request->isAjax())
        {

            $param = $this->request->param();
            $rel = $this->validate($param,'handan');

            if($rel !== true)
            {
              return  $this->return_error($rel);
            }
            $param['create_time'] = time();

            $result = model('PortalHandan')->isUpdate(false)
                ->allowField(true)
                ->save($param);

            if(!$result)
            {
                return $this->return_error([],'插入数据库失败');
            }

            return $this->return_success();
        }
    }

    public function handan_index()
    {
        $param['pageNo'] = $this->request->param('pageNo',1,'intval');
        $param['pageSize'] = $this->request->param('pageSize',10,'intval');

        $data['pageNo'] = $param['pageNo'];
        $data['pageSize'] = $param['pageSize'];
        $data['total'] = model('PortalHandan')->count();
        $data['rows'] = PortalHandanModel::page($param['pageNo'],$param['pageSize'])->select()->toArray();

        return $this->return_success($data);
    }

}