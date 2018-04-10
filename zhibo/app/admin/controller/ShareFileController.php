<?php
namespace app\admin\controller;

use app\admin\model\ShareFileModel;
use cmf\controller\AdminBaseController;

class ShareFileController extends AdminBaseController
{
    public function index()
    {
        $param = $this->request->param();
        $keyword = $this->request->param('keyword');
        $shareFileModel = new ShareFileModel();
        $select = $shareFileModel->alias('sf')
            ->field('id,filename,filepath,filesize,icon,list_order,create_uid,create_time');
        if (!empty($keyword)) {
            $select->where('filename', 'like', "%$keyword%");
        }
        $data = $select->paginate(config('admin_page_size'));

        $data->appends($param);

        $this->assign('sharefile', $data);
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function delete()
    {
        $intId = $this->request->param();
        $shareFileModel = new ShareFileModel();

        //删除单个信息
        if (isset($intId['id'])) {
            $shareFileModel->where(['id' => $intId['id']])->delete();
        }
        //批量删除信息
        if (isset($intId['ids'])) {
            $shareFileModel->whereIn('id', $intId['ids'])->delete();
        }

        $this->success(lang("DELETE_SUCCESS"), url('index'));
    }

    public function select()
    {
        $ids                 = $this->request->param('ids');
        $selectedIds         = explode(',', $ids);
        $data = [
            0 => ['id' => 1, 'icon' => 'icon/1.png'],
            1 => ['id' => 2, 'icon' => 'icon/2.png'],
            2 => ['id' => 3, 'icon' => 'icon/3.png'],
            3 => ['id' => 4, 'icon' => 'icon/4.png'],
            4 => ['id' => 5, 'icon' => 'icon/5.png'],
        ];
        $this->assign('icons', $data);
        $this->assign('selectedIds', $selectedIds);
        return $this->fetch();
    }

    public function uploadFile()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->return_error('上传文件失败');
        }
        $info = $file->validate(['size' => 80015678])->move(ROOT_PATH . 'public' . DS . 'upload');
        if (!$info) {
            return $this->return_error($info->getError());
        }

        // 成功上传后 获取上传信息
        $ext = $info->getExtension();

        $shareFile = ShareFileModel::create(array(
            'filename' => $info->getInfo('name'),
            'filepath' => $info->getSaveName(),
            'filesize' => $info->getSize(),
            'icon' => $this->getExtIcon($ext),
            'create_uid' => cmf_get_current_admin_id(),
            'create_time' => time(),
        ));

        return $this->return_success(array('id' => $shareFile['id'], 'filename' => $info->getInfo('name'), 'filepath' => cmf_get_image_preview_url($info->getSaveName())));
    }

    /**
     * 排序
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('share_file'));
        $this->success("排序更新成功！", '');
    }

    /**
     * 获取图标
     * @param $extension
     * @return mixed|string
     */
    private function getExtIcon($extension)
    {
        $data = [
            'pdf' => 'icon/1.png',
            'rar' => 'icon/2.png',
            'zip' => 'icon/2.png',
            'dmp' => 'icon/3.png',
            'jpg' => 'icon/3.png',
            'png' => 'icon/3.png',
            'jpeg' => 'icon/3.png',
            'gif' => 'icon/3.png',
            'exe' => 'icon/4.png',
        ];
        if (empty($data[$extension])) {
            return 'icon/5.png';
        } else {
            return $data[$extension];
        }
    }

}