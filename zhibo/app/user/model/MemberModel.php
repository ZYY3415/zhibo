<?php
namespace app\user\model;

use think\Db;
use think\Model;

class MemberModel extends Model
{
    const STATUS_DISABLE = 0;//未激活
    const STATUS_ENABLE = 1;//已激活

    /**
     * 用手机号码注册
     * @param $data
     * @return int
     */
    public function registerMobile($data)
    {
        $result = $this->where('phone', $data['phone'])
            ->whereOr('username', $data['username'])->find();

        if (empty($result)) {
            $member = array(
                'username' => $data['username'],
                'phone' => $data['phone'],
                'password' => cmf_password($data['password']),
                'reg_time' => time(),
                'adminid' => 13,
            );
            $userId = Db::name("member")->insertGetId($member);
            $member = Db::name("member")->where('id', $userId)->find();

            session('member', $member);

            $token = cmf_generate_user_token($userId, 'web');
            if (!empty($token)) {
                session('token', $token);
            }
            return 0;
        }
        return 1;
    }

    /**
     * 使用用户名登陆
     * @param $data
     * @return int
     */
    public function doName($data)
    {
        $result = $this->where('username', $data['username'])->find();
        if (!empty($result)) {
            $defaultRid = cmf_get_default_rid();
            $rid = isset($data['rid']) ? $data['rid'] :$defaultRid;
            $ridAttr = explode(',',$result['rid']);
            if(!in_array($rid,$ridAttr)) {
                return 4;
            }
            $comparePasswordResult = cmf_compare_password($data['password'], $result['password']);
            $hookParam = [
                'user' => $data,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                if ($result['status'] == self::STATUS_DISABLE) {
                    return 3;
                }
                session('member', $result);
                $data = [
                    'login_time' => time(),
                    'login_count' => $result['login_count'] + 1,
                    'online' => time(),
                    'ip' => get_client_ip(0, true)
                ];
                $result->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $data,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    /**
     * 使用手机登陆
     * @param $data
     * @return int
     */
    public function doMobile($data)
    {
        $result = $this->where('phone', $data['username'])->find();

        if (!empty($result)) {
            $defaultRid = cmf_get_default_rid();
            if($result['rid']!=$defaultRid) {
                return 4;
            }
            $comparePasswordResult = cmf_compare_password($data['password'], $result['password']);
            $hookParam = [
                'user' => $data,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                if ($result['status'] == self::STATUS_DISABLE) {
                    return 3;
                }
                session('member', $result);
                $data = [
                    'login_time' => time(),
                    'login_count' => $result['login_count'] + 1,
                    'online' => time(),
                    'ip' => get_client_ip(0, true)
                ];
                $this->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $data,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function editPassword($member)
    {
        $userId = cmf_get_current_user_id();
        $userQuery = Db::name("member");
        if ($member['password'] != $member['repassword']) {
            return 1;
        }
        $pass = $userQuery->where('id', $userId)->find();
        if (!cmf_compare_password($member['old_password'], $pass['password'])) {
            return 2;
        }
        $data['password'] = cmf_password($member['password']);
        $userQuery->where('id', $userId)->update($data);
        return 0;
    }

    /**
     * 判断会员是否登陆
     * @return bool
     */
    public function is_member_login()
    {
        $sessionMember = session('member');
        return !empty($sessionMember);
    }

    public function getRecommendList($pageNo, $pageSize, $keyword)
    {
        $offset = (intval($pageNo) - 1) * intval($pageSize);
        $selectTotal = $this->buildSqlQuery($keyword);
        //获取总记录数
        $total = $selectTotal->count();

        //获取当前页记录
        $select = $this->buildSqlQuery($keyword);
        $recommend = $select->limit($offset, $pageSize)->select()->toArray();

        $userId = cmf_get_current_user_id();

        $list = array_map(function ($item) use ($userId) {
            $count = $this->where('tuijianmid', $userId)->where('adminid', '<>', '14')->count();
            return array(
                'id' => $item['id'],
                'nickname' => $item['nickname'],
                'rolename' => $item['rolename'],
                'qq' => $item['qq'],
                'phone' => $item['phone'],
                'count' => $count,
            );
        }, $recommend);

        return array(
            'total' => $total,
            'rows' => $list,
            'pageNo' => intval($pageNo),
            'pageSize' => intval($pageSize),
        );
    }

    private function buildSqlQuery($keyword)
    {
        $mid = cmf_get_current_user_id();
        $select = $this->alias('m')->join('__PROTAL_ROLE__ pr', 'm.adminid=pr.keyword', 'LEFT')
            ->where('m.adminid', '<>', 14);
        if (!empty($keyword)) {
            $select->where('m.nickname|pr.rolename', 'LIKE', "'%{$keyword}%'");
        }
        return $select->where('m.tuijianmid', $mid)
            ->field('m.id,m.nickname,pr.rolename,m.qq,m.phone');
    }
}