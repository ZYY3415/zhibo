<?php
namespace app\admin\model;

use think\Model;

class PageModel extends Model
{
    const START_ENABLE = 1;//开启
    const START_DISABLE = 2;//未开启

    const LOGIN_SHOW_DEFAULT = 1;//不需登陆显示
    const LOGIN_SHOW_NEED = 2;//需要登陆后展示

    const OPEN_TYPE_POP = 1;//弹窗
    const OPEN_TYPE_NEW_PAGE = 2;//新页面
    const OPEN_TYPE_BLANK = 3;//新窗口

    const POSITION_TOP = 1;//顶部
    const POSITION_LEFT = 2;//左部
    const POSTTION_BOTTOM = 3;//底部

    public function addPost($data)
    {
        if (empty($data['id'])) {
            $data['create_time'] = time();
            $data['create_uid'] = cmf_get_current_admin_id();
            $this->allowField(true)->data($data, true)->isUpdate(false)->save();
        } else {
            $this->allowField(true)->data($data, true)->isUpdate(true)->save();
        }
    }

    /**
     * 获取底部分页
     * @param $rid
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBottomPage($rid, $limit = 1)
    {
        return $this->where('is_start', PageModel::START_ENABLE)
            ->where('position', PageModel::POSTTION_BOTTOM)
            ->where('rid', $rid)
            ->order('list_order')->order('id')
            ->limit($limit)
            ->select();
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * post_content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

    /**
     * 是否开启
     * @param null $key
     * @return array|mixed
     */
    public static function getIsStartLabel($key = null)
    {
        $data = array(
            self::START_ENABLE => '开启',
            self::START_DISABLE => '未开启',
        );
        if ($key !== null && !empty($data[$key])) {
            return $data[$key];
        } else {
            return $data;
        }
    }

    /**
     * 是否需要登陆后展示
     * @param null $key
     * @return array|mixed
     */
    public static function getLoginShowLabel($key = null)
    {
        $data = array(
            self::LOGIN_SHOW_DEFAULT => '不需登陆展示',
            self::LOGIN_SHOW_NEED => '需登陆后展示',
        );
        if ($key !== null && !empty($data[$key])) {
            return $data[$key];
        } else {
            return $data;
        }
    }

    /**
     * 打开类型
     * @param null $key
     * @return array|mixed
     */
    public static function getOpenTypeLabel($key = null)
    {
        $data = array(
            self::OPEN_TYPE_POP => '弹出框',
            self::OPEN_TYPE_NEW_PAGE => '原页面',
            self::OPEN_TYPE_BLANK => '新窗口',
        );
        if ($key !== null && !empty($data[$key])) {
            return $data[$key];
        } else {
            return $data;
        }
    }
}