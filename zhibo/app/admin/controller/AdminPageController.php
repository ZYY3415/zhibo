<?php
namespace app\admin\controller;

use app\admin\model\PageModel;
use app\portal\model\RoomBasicModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;

class AdminPageController extends AdminBaseController
{
    /**
     * 单页顶部
     * @return mixed
     */
    public function top()
    {
        $this->assginPage(1);
        return $this->fetch();
    }

    public function topadd()
    {
        $this->assginAdd();
        return $this->fetch();
    }

    public function leftadd()
    {
        $this->assginAdd();
        return $this->fetch();
    }

    public function topedit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $page = PageModel::get($id);
        $this->assign('page', $page);
        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        $this->assign('arrOpenType', PageModel::getOpenTypeLabel());
        $this->assign('arrIsStart', PageModel::getIsStartLabel());
        return $this->fetch();
    }

    /**
     * 排序
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('page'));
        $this->success("排序更新成功！", '');
    }

    public function delete()
    {
        $intId = $this->request->param();
        $pageModel = new PageModel();

        //删除单个信息
        if (isset($intId['id'])) {
            $pageModel->where(['id' => $intId['id']])->delete();
        }
        //批量删除信息
        if (isset($intId['ids'])) {
            $pageModel->whereIn('id', $intId['ids'])->delete();
        }

        $this->success(lang("DELETE_SUCCESS"), '');
    }

    public function addPost()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $result = $this->validate($params, 'Page');
            if ($result !== true) {
                $this->error($result);
            }

            $pageModel = new PageModel();
            $pageModel->addPost($params);

            $url = $params['position'] == PageModel::POSITION_TOP ? url('AdminPage/top') : url('AdminPage/left');
            $this->success('保存成功', $url);
        }
    }

    private function assginPage($position)
    {
        $param = $this->request->param();
        $keyword = $this->request->param('keyword', '');

        $child_rid = array_keys($this->get_rid_list());
        $pageModel = new PageModel();
        $select = $pageModel->alias('p')->join('__ROOM_BASIC__ rb', 'rb.rid=p.rid', 'LEFT')
            ->field('p.id,rb.room,p.title,p.link,p.icon,p.open_type,p.is_start,p.list_order')
            ->where('p.position', $position)
            ->where('rb.rid','in',$child_rid)->order('p.list_order');
        if (!empty($keyword)) {
            $select->where('rb.room|p.title', 'like', "%$keyword%");
        }
        if (!empty($param['rid'])) {
            $select->where('p.rid', $param['rid']);
        }
        $data = $select->paginate(config('admin_page_size'));

        $data->appends($param);

        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        $this->assign('rid', isset($param['rid']) ? $param['rid'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('pages', $data->items());
        $this->assign('page', $data->render());
    }

    private function assginAdd()
    {
        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        $this->assign('arrOpenType', PageModel::getOpenTypeLabel());
        $this->assign('arrIsStart', PageModel::getIsStartLabel());
        $this->assign('arrLoginShow', PageModel::getLoginShowLabel());
    }

    public function left()
    {
        $this->assginPage(2);
        return $this->fetch();
    }

    public function leftedit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $page = PageModel::get($id);
        $this->assign('page', $page);
        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        $this->assign('arrOpenType', PageModel::getOpenTypeLabel());
        $this->assign('arrIsStart', PageModel::getIsStartLabel());
        $this->assign('arrLoginShow', PageModel::getLoginShowLabel());
        return $this->fetch();
    }

    public function bottom()
    {
        $this->assginPage(3);
        return $this->fetch();
    }

    public function bottomadd()
    {
        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        return $this->fetch();
    }

    public function bottomedit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $page = PageModel::get($id);
        $this->assign('page', $page);
        $rooms = $this->get_rid_list();
        $this->assign('rooms', $rooms);
        return $this->fetch();
    }

    public function addBottomPost()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $validate = new Validate([
                'title' => 'require',
                'rid' => 'require',
                'link' => 'require',
                'content' => 'require',
            ], [
                'title.require' => '标题不能为空',
                'rid.require' => '房间不能为空',
                'link.require' => 'URL/QQ不能为空',
                'content.require' => '内容不能为空',
            ]);
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $pageModel = new PageModel();
            $pageModel->addPost($params);

            $this->success('保存成功', url('AdminPage/bottom'));
        }
    }
}