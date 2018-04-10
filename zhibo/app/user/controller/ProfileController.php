<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use app\portal\model\PortalGiftModel;
use app\portal\model\ProtalRoleModel;
use app\user\model\CachePayModel;
use app\user\model\ConsumeModel;
use app\user\model\MemberModel;
use app\user\model\PayBankModel;
use cmf\lib\Storage;
use think\Validate;
use think\Image;
use cmf\controller\UserBaseController;
use app\user\model\UserModel;
use think\Db;

class ProfileController extends UserBaseController
{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 上传图像
     * @return \Jump\json
     */
    public function uploadAvatar()
    {
        $file = $this->request->file('file');
        $info = $file->validate(['size' => 80015678, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'upload');
        if (!$info) {
            return $this->return_error($info->getError());
        }
        // 成功上传后 获取上传信息
        $filename = cmf_get_image_preview_url($info->getSaveName());
        return $this->return_success(array('avatar' => $filename));
    }

    /**
     * 保存图像
     * @return \Jump\json
     */
    public function modifyAvatar()
    {
        $avatar = $this->request->param('avatar', '');
        $member = MemberModel::get(cmf_get_current_user_id());
        $member->avatar = $avatar;
        $member->save();

        session('member', $member);
        return $this->return_success();
    }

    /**
     * 充值
     * @return mixed
     */
    public function pay()
    {
        $type = $this->request->param('type', CachePayModel::TYPE_CHARGE);
        $subject = '充值';
        $total_fee = $this->request->param('total_fee', 0.01);
        $order_remark = $this->request->param('order_remark', '');

        $payBankModel = new PayBankModel();
        $bank = $payBankModel->getBankList();

        $this->assign('bank', $bank);
        $this->assign('type', $type);
        $this->assign('subject', $subject);
        $this->assign('total_fee', $total_fee);
        $this->assign('order_remark', $order_remark);

        return $this->fetch();
    }

    /**
     * 会员中心首页
     */
    public function profile()
    {
        $userId = cmf_get_current_user_id();
        $default = cmf_get_default_rid();
        $rid = $this->request->param('rid',$default,'intval');


        $memberModel = new MemberModel();
        $member = $memberModel->where('id', $userId)->find();
        $this->assign('member', $member);

        $count = $memberModel->where('tuijianmid', $userId)->where('adminid', '<>', '14')->count();
        $this->assign('count', $count);

        $portalRoleModel = new ProtalRoleModel();
        $role = $portalRoleModel->where('keyword', $member['adminid'])->find();
        $this->assign('role', $role);
        $this->assign('title', '个人中心');
        $this->assign('rid', $rid);
        return $this->fetch();
    }

    /**
     * 修改用户信息
     * @return \Jump\json
     */
    public function profilePost()
    {
        if ($this->request->isPost()) {
            $nickname = $this->request->param('nickname', '');
            $phone = $this->request->param('phone', '');
            //获取当前用户
            $member = MemberModel::get(cmf_get_current_user_id());
            $member->nickname = $nickname;
            $member->phone = $phone;
            $member->save();

            session('member', $member);
            return $this->return_success();
        }
    }

    /**
     * 个人中心修改密码
     */
    public function password()
    {
        $this->assign('title', '修改密码');
        return $this->fetch();
    }

    /**
     * 我的消费记录
     * @return mixed
     */
    public function consumption()
    {
        $this->assign('title', '我的消费');
        return $this->fetch();
    }

    /**
     * 我的下线
     * @return mixed
     */
    public function promotion()
    {
        $this->assign('title', '我的下线');
        return $this->fetch();
    }

    /**
     * 个人中心修改密码提交
     */
    public function passwordPost()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'old_password' => 'require|min:6|max:32',
                'password' => 'require|min:6|max:32',
                'repassword' => 'require|min:6|max:32',
            ]);
            $validate->message([
                'old_password.require' => lang('old_password_is_required'),
                'old_password.max' => lang('old_password_is_too_long'),
                'old_password.min' => lang('old_password_is_too_short'),
                'password.require' => lang('password_is_required'),
                'password.max' => lang('password_is_too_long'),
                'password.min' => lang('password_is_too_short'),
                'repassword.require' => lang('repeat_password_is_required'),
                'repassword.max' => lang('repeat_password_is_too_long'),
                'repassword.min' => lang('repeat_password_is_too_short'),
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $member = new MemberModel();
            $log = $member->editPassword($data);
            switch ($log) {
                case 0:
                    $this->success(lang('change_success'), url('user/profile/password'));
                    break;
                case 1:
                    $this->error(lang('password_repeat_wrong'));
                    break;
                case 2:
                    $this->error(lang('old_password_is_wrong'));
                    break;
                default :
                    $this->error(lang('ERROR'));
            }
        } else {
            $this->error(lang('ERROR'));
        }
    }

    // 用户头像编辑
    public function avatar()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    // 用户头像上传
    public function avatarUpload()
    {
        $file   = $this->request->file('file');
        $result = $file->validate([
            'ext'  => 'jpg,jpeg,png',
            'size' => 1024 * 1024
        ])->move('.' . DS . 'upload' . DS . 'avatar' . DS);

        if ($result) {
            $avatarSaveName = str_replace('//', '/', str_replace('\\', '/', $result->getSaveName()));
            $avatar         = 'avatar/' . $avatarSaveName;
            session('avatar', $avatar);

            return json_encode([
                'code' => 1,
                "msg"  => "上传成功",
                "data" => ['file' => $avatar],
                "url"  => ''
            ]);
        } else {
            return json_encode([
                'code' => 0,
                "msg"  => $file->getError(),
                "data" => "",
                "url"  => ''
            ]);
        }
    }

    // 用户头像裁剪
    public function avatarUpdate()
    {
        $avatar = session('avatar');
        if (!empty($avatar)) {
            $w = $this->request->param('w', 0, 'intval');
            $h = $this->request->param('h', 0, 'intval');
            $x = $this->request->param('x', 0, 'intval');
            $y = $this->request->param('y', 0, 'intval');

            $avatarPath = "./upload/" . $avatar;

            $avatarImg = Image::open($avatarPath);
            $avatarImg->crop($w, $h, $x, $y)->save($avatarPath);

            $result = true;
            if ($result === true) {
                $storage = new Storage();
                $result  = $storage->upload($avatar, $avatarPath, 'image');

                $userId = cmf_get_current_user_id();
                Db::name("user")->where(["id" => $userId])->update(["avatar" => $avatar]);
                session('user.avatar', $avatar);
                $this->success("头像更新成功！");
            } else {
                $this->error("头像保存失败！");
            }

        }
    }

    /**
     * 绑定手机号或邮箱
     */
    public function binding()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 绑定手机号
     */
    public function bindingMobile()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'username'          => 'require|number|unique:user,mobile',
                'verification_code' => 'require',
            ]);
            $validate->message([
                'username.require'          => '手机号不能为空',
                'username.number'           => '手机号只能为数字',
                'username.unique'           => '手机号已存在',
                'verification_code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $userModel = new UserModel();
            $log       = $userModel->bindingMobile($data);
            switch ($log) {
                case 0:
                    $this->success('手机号绑定成功');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

    /**
     * 绑定邮箱
     */
    public function bindingEmail()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'username'          => 'require|email|unique:user,user_email',
                'verification_code' => 'require',
            ]);
            $validate->message([
                'username.require'          => '邮箱地址不能为空',
                'username.email'            => '邮箱地址不正确',
                'username.unique'           => '邮箱地址已存在',
                'verification_code.require' => '验证码不能为空',
            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }
            $userModel = new UserModel();
            $log       = $userModel->bindingEmail($data);
            switch ($log) {
                case 0:
                    $this->success('邮箱绑定成功');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }

    /**
     * 消费记录列表
     * @return \Jump\json
     */
    public function consumeList()
    {
        $pageNo = $this->request->param('pageNo', 1, 'intval');
        $pageSize = $this->request->param('pageSize', 10, 'intval');

        $portalGiftModel = new PortalGiftModel();
        $list = $portalGiftModel->getConsumeList($pageNo, $pageSize);

        return $this->return_success($list);
    }

    public function recommendList()
    {
        $pageNo = $this->request->param('pageNo', 1, 'intval');
        $pageSize = $this->request->param('pageSize', 10, 'intval');
        $keyword = $this->request->param('keyword');

        $memberModel = new MemberModel();
        $list = $memberModel->getRecommendList($pageNo, $pageSize, $keyword);

        return $this->return_success($list);
    }
}