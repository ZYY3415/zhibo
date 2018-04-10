<?php
namespace app\admin\model;

use think\Model;

class ShareFileModel extends Model
{
    public function getShareFileList($pageNo, $pageSize)
    {
        $offset = (intval($pageNo) - 1) * intval($pageSize);
        $selectTotal = $this->buildSqlQuery();
        //获取总记录数
        $total = $selectTotal->count();

        //获取当前页记录
        $select = $this->buildSqlQuery();
        $result = $select->limit($offset, $pageSize)->select();

        return array(
            'total' => $total,
            'rows' => $result,
            'pageNo' => intval($pageNo),
            'pageSize' => intval($pageSize),
        );
    }

    public function buildSqlQuery()
    {
        return $this->field('id,filename,filepath,filesize,icon');
    }

}