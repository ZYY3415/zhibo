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
use traits\model\SoftDelete;
use app\portal\model\AdminHousevModel;
use app\admin\model\PageModel;
use app\admin\model\TeacherInfoModel;
use app\admin\model\TeacherModel;
use app\portal\model\CourseModel;
use \Exception;
use think\Db;
class AdminHouseModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $table = 'cmf_room_basic';
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
    }
    /**
      复制房间配置信息
     * 包括
     * 房间基本信息
     * 房间视频信息
     * 房间单页信息
     * 房间课程信息
     **/
    public function copyHouse($id)
    {
        $room_basic = $this->where('id',$id)
            ->find()->toArray();

        if(!$room_basic)
        {
            return false;
        }

            //开启事务
        Db::startTrans();

        try{
            $randomRid = rand(1001,1100);             //新生成的随机房间号
            $rid = $room_basic['rid'];                 //要复制的房间号

            /***复制房间基本信息**/
            $resultHbR = $this->onlyTrashed()->where('rid',$randomRid)->find();     //验证新房间号是存在被软删除的数据中
            if ($resultHbR){                                                       //如果存在新生成的房间号信息则删除
                $this->where('rid',$randomRid)->delete(true);
            }

            $resultHb = $this->where('rid',$randomRid)->find();     //验证新房间号是存在没有别删除的房间中

            if(!$resultHb)
            {

                $room_basic['rid'] = $randomRid;
                unset($room_basic['id']);
                $this->isUpdate(false)->save($room_basic);

            }else{
                exception();                //如果存在改房间号则抛出错误，重新生成一个房间号
            }
            /***复制房间基本信息**/


            /***复制房间视频拉流地址**/
            $roomHv = AdminHousevModel::where('rid',$rid)->find();

            $resultHvR = AdminHousevModel::onlyTrashed()->where('rid',$randomRid)->find();             //查询新生成的房间号是否存在被删除的信息中
            if($resultHvR)
            {
                AdminHousevModel::destroy(['rid'=>$randomRid],true);
            }

            $resultHv = AdminHousevModel::where('rid',$randomRid)->find();

            if(!$resultHv){
                $roomHv['rid'] = $randomRid;
                unset($roomHv['id']);
                model('AdminHousev')->isUpdate(false)->allowField(true)->save($roomHv);
            }else{
                exception();                                    //如果存在该房间号则抛出错误，重新生成一个房间号
            }

            /***复制房间视频拉流地址**/

            /***复制房间单页**/
            $roomPage = PageModel::where('rid',$rid)->select()->toArray();
            $roomHp = PageModel::where('rid',$randomRid)->select()->toArray();

            if(empty($roomHp))
            {
               foreach($roomPage as $key=>$value)
               {
                   $roomPage[$key]['rid'] = $randomRid;
                   unset($roomPage[$key]['id']);
               }
                model('app\admin\model\PageModel')->isUpdate(false)->saveAll($roomPage);
            }else{
                exception();
            }

            /***复制房间单页**/


            /***复制房间讲师**/
            $teacher = model('app\admin\model\TeacherModel')
                ->alias('t')
                ->field('t.jname,t.qq,t.rid,ti.content,ti.icon')
                ->join('__TEACHER_INFO__ ti','t.jid = ti.jid')
                ->where('t.rid',$rid)
                ->select()
                ->toArray();

            $teacherHt = TeacherModel::where('rid',$randomRid)->select()->toArray();

            if(empty($teacherHt))
            {
                foreach($teacher as $key=>$value)
                {
                    $teacher[$key]['rid'] = $randomRid;
                }

                $createId = model('app\admin\model\TeacherModel')->isUpdate(false)->allowField(true)->saveAll($teacher)->column('jid');

                $teacher_info = array_map(function($createId,$teacher){
                    $teacher['jid'] = $createId;
                    $teacher['create_time'] = time();
                    $teacher['create_uid'] = cmf_get_current_admin_id();
                    return $teacher;
                },$createId,$teacher);

                 model('app\admin\model\TeacherInfoModel')->isUpdate(false)->allowField(true)->saveAll($teacher_info);
            }else{
                exception();
            }
            /***复制房间讲师**/


           /* //复制课程表信息
            $Course = model('Course')->where('rid','like',$rid)->select()->toArray();
            $CourseHc = model('Course')->where('rid','like',$randomRid)->select()->toArray();

            if(empty($CourseHc))
            {
                foreach($Course as $key=>$value)
                {
                    $Course[$key]['rid'] = $randomRid;
                    unset($Course['id']);
                }
                model('Course')->isUpdate(false)->saveAll($Course);
            }else{

            }*/
             // 提交事务
            Db::commit();

            return true;
        }catch (\Exception $e){
        // 回滚事务
        Db::rollback();
        }
        return true;
    }
}
