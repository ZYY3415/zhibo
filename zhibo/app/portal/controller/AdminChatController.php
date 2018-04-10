<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use app\portal\model\RoomBasicModel;
use app\portal\model\ChatModel;
use think\Db;
use think\Validate;
use Kernal\Common;

class AdminChatController extends AdminBaseController
{
	/**
	 * 聊天信息首页
	 */
    public function index()
    {

        $param = $this->request->param();

        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);
        
        $ChatModel = new ChatModel();
        $chat = $ChatModel -> getChat($param);
        $this->msgRid($chat,$param);

        //$data = array_map('switch_rid_ridname',$chat->toArray()['data']);
        $_SESSION['admin_chat'] = serialize($chat);

        $this->assign('rooms', $rooms);
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');

        return $this->fetch();
    }

    function msgRid($obj,$param)
    {

        $msIdAttr = array_map(function($data){
            return $data->id;
        },$obj['messageId']->all());
        $rid = empty($param['rid']) ? '' : $param['rid'];
        $room  = cmf_get_current_admin_rid();
        $rooms = $this->getRooms($room);
        if (!empty($param['rid'])) {
            $where['mr.rid'] = ['like',"%$rid%"];
        }else{
            $where['mr.rid'] = ['in',$room];
        }

        $page = $obj['messageId']->render();
        $obj['messageId']->appends($param);
        $this->assign('page',$page);

        //获取分页后的房间id对应的房间号
        $idroom = Db::name('msg_rid')
            ->alias('mr')
            ->join('__ROOM_BASIC__ rb','rb.rid = mr.rid')
            ->where('mr.msid','in',$msIdAttr)
            ->field('mr.msid,rb.room')
            ->where($where)
            ->select()
            ->toArray();
        $ridname = mergeRid($idroom);

        $ridAttr = array_keys($ridname);     //获得房间号数组

        $field = "a.*";

        $data = model('Chat')
                ->alias('a')
                ->field($field)
                ->where($obj['where'])
                ->where('a.id','in',$ridAttr)
                ->select();

        $return= array_map(function($attr) use ($ridname){
            if(array_key_exists($attr['id'],$ridname))
            {
                $attr['ridname'] = $ridname[$attr['id']];
                return $attr;
            }
        },$data->toArray());

        $this->assign('chat', $return);

    }

    /**
     * 获取房间列表
     * @return array
     */
    private function getRooms($room)
    {
        $roomBasicModel = new RoomBasicModel();
        return $roomBasicModel->field('rid,room')->whereIn('rid',$room)->select()->toArray();
    }

    /**
     * delete
     */
    public function delete()
    {

        $param   = $this->request->param();
        $ChatModel = new ChatModel();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            Db::transaction(function() use($ChatModel,$id){

               $ChatModel->where(['id' => $id])->delete();
               Db::name('msg_rid')->where('msid',$id)->delete();
            });

        }elseif(isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            Db::transaction(function() use ($ChatModel,$ids){

                $ChatModel->where(['id' => ['in', $ids]])->delete();
                Db::name('msg_rid')->where('msid','in',$ids)->delete();
            });
        }

        $this->success("删除成功！", '');
    } 

    /**
     * 导出excel表
     * 
     */
    public function export()
    {
        if(!isset($_SESSION['admin_chat'])){
            return 'error';
        }
        $obj = unserialize($_SESSION['admin_chat']);

        if(!is_array($obj)){
            $this->error('参数类型错误');
        }
       
        foreach($obj as $key=>$value)
        {

            $data[$key] = $value->toArray();            //导出的数据
        }

        $table_header = array_keys($obj[0]->toArray());           //表头

        $table_name = '聊天信息表';                          //表名

        $excel = new Common();


        /**
        $data  要导出的数据 array array  二维数组
         * $table_header  表头信息  array  一位数组
         * $table_name  string  导出的Excel表名
         * $field_width array  [列名=>宽度]  这里宽度不要px  且只能是数字  列名注意要大写
         **/

        $field_width = ['F'=>20,'H'=>20];

        $excel->Excel($data,$table_header,$table_name,$field_width);        //调用Excel  导出方法

    }

}