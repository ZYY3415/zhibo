<?php
namespace app\admin\controller;

use app\user\model\BalanceModel;
use app\user\model\CachePayModel;
use app\user\model\MemberModel;
use app\user\model\PayBankModel;
use app\user\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Log;

class PayController extends AdminBaseController
{
    function _initialize()
    {
        parent::_initialize();
    }

    #合作身份者ID
    private $appid = '91';

    private $client = 'TFzhibo';

    #接口密钥，安全检验码，由数字和字母组成的32位字符串
    private $appkey = "abmRsJf8x1tYIiAdnARKgQZTfPy5ZAHz";

    #入金异步回调
    private $notify_url = "http://www.zhibo.com/admin/pay/notify.html";

    #入金成功页面
    protected $payinsuccess = "";

    //充值成功页面
    protected $chargesuccess = "http://www.zhibo.com/admin/pay/userIndex.html";

    /**
     * 
     */
    public function index()
    {
        $order = $this->request->param('order',0);
        $payModel = new PayBankModel();
        $bank = $payModel -> getBankList();

        $this->assign('bank',$bank);
        return view('profile/pay');
    }

    /**
     * 发起支付申请
     */
    public function payin()
    {
        if ($this->request->isAjax()) {
            $param = $this->request->param();

            $a = mt_rand(1000, 9999);
            $out_trade_no = 'tfzhibo' . date('Ymdhis') . $a;
            $type = isset($param['type']) ? $param['type'] : CachePayModel::TYPE_CHARGE;

            $total_fee = isset($param['total_fee']) ? $param['total_fee'] : '0';
            if (empty($total_fee)) {
                $this->error('支付金额为0');
            }
            $subject = isset($param['subject']) ? $param['subject'] : $out_trade_no;
            $paytype = isset($param['paytype']) ? $param['paytype'] : 'ICBC';


            $data['rname'] = isset($param['rname']) ? $param['rname'] : '测试账号';
            $data['mt4account'] = isset($param['mt4account']) ? $param['mt4account'] : '999999';
            $data['return_url'] = $this->chargesuccess;
            $data['notify_url'] = $this->notify_url;

            #快捷支付所需参数
            $data['phoneNo'] = isset($param['phoneNo']) ? $param['phoneNo'] : '';
            $data['cardIdcardType'] = isset($param['cardIdcardType']) ? $param['cardIdcardType'] : '';
            $data['cardIdcardNo'] = isset($param['cardIdcardNo']) ? $param['cardIdcardNo'] : '';
            $data['cardType'] = isset($param['cardType']) ? $param['cardType'] : '';
            $data['cardNo'] = isset($param['cardNo']) ? $param['cardNo'] : '';
            $data['veriCode'] = isset($param['veriCode']) ? $param['veriCode'] : '';//验证码

            #快捷支付信用卡所需要的参数
            $data['expDate'] = isset($param['expDate']) ? $param['expDate'] : '';//信用卡有效期
            $data['cvn2'] = isset($param['cvn2']) ? $param['cvn2'] : '';//信用卡附加码

            $user = UserModel::get(cmf_get_current_admin_id());

            //处理账户余额
            $total_fee = doubleval($total_fee);

            //生成支付缓存
            CachePayModel::create([
                'order_no' => $out_trade_no,
                'money' => $total_fee,
                'balance' => 0,
                'order_remark' => isset($param['order_remark']) ? $param['order_remark'] : '',
                'type' => $type,
                'pay_bank' => $param['paytype'],
                'bank_name' => $this->getBankName($param['paytype']),
                'create_uid' => $user['mid'],
                'create_time' => time(),
            ]);

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "appid" => $this->appid,
                "return_url" => $data['return_url'],
                "notify_url" => $data['notify_url'],
                "out_trade_no" => $out_trade_no,//商户订单号
                "subject" => $subject,
                "total_fee" => $total_fee,
                "paytype" => $paytype,
                "partnerUserId" => $this->appid,
                "client" => $this->client,
                "rname" => $data['rname'],
                'mt4account' => $data['mt4account'],
                'tel' => isset($user['phone'])?$user['phone']:'',
                'phoneNo' => $data['phoneNo'],
                'cardIdcardType' => $data['cardIdcardType'],
                'cardIdcardNo' => $data['cardIdcardNo'],
                'cardType' => $data['cardType'],
                'cardNo' => $data['cardNo'],
                'veriCode' => $data['veriCode'],
                'expDate' => $data['expDate'],
                'cvn2' => $data['cvn2']
            );

            //除去待签名参数数组中的空值和签名参数
            $parameter = $this->paraFilter($parameter);

            //对待签名参数数组排序
            $parameter = $this->argSort($parameter);

            //***生成签名结果***
            //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
            $prestr = $this->createLinkstring($parameter);

            $mysign = $this->md5Sign($prestr, $this->appkey);

            //将签名结果加入请求提交参数组中
            $parameter['sign'] = $mysign;

            //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
            $request_data = $this->createLinkstringUrlencode($parameter);
            // dump($request_data);die;
            //生成支付请求Uri
            $request_Uri = "http://www.jin88.com.cn/Bestpay/BestPay/payin?" . $request_data;

            //建立请求，跳转页面
            if (($paytype == 'FASTPAY') && !$param['veriCode']) { //如果是快捷支付，并且veriCode为空，就不跳转
                $Response_Contents = $this->getHttpResponseGET($request_Uri);
                echo $Response_Contents;
            } else {
                $this->success('success', $request_Uri);
            }
        }
    }

    /**
     * 根据支付类型获取银行名称
     * @param $payBank
     * @return mixed|string
     */
    private function getBankName($payBank)
    {
        $data = [
            'alipay' => '支付宝',
            'alipayQrCode' => '支付宝',
            'weixin' => '微信',
            'weixinQrCode' => '微信',
        ];
        if (isset($data[$payBank])) {
            return $data[$payBank];
        }
        $payBankModel = new PayBankModel();
        $result = $payBankModel->getBankName($payBank);
        if (empty($result)) {
            return '其他支付';
        }
        return $result;
    }

    /**
     * // 用户支付完成后，系统会使用POST方式将参数通过URL返回到notify
     */
    public function notify()
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($_POST);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //***生成签名结果***
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        //生成签名结果
        $isSign = $this->md5Verify($prestr, $_POST['sign'], $this->appkey);

        Log::info('pay notify success' . date('Y-m-d H:i:s'));
        if ($isSign) {
            // 交易成功
            if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                Log::info(json_encode($_POST));
                // 获取订单编号
                $out_trade_no = $_POST['out_trade_no'];
                // 获取付款金额
                $total_fee = $_POST['total_fee'];

                Log::info('do with order ' . $out_trade_no . 'date' . date('Y-m-d H:i:s'));

                Db::startTrans();
                //开始处理订单
                $cachePayModel = new CachePayModel();
                $cachePay = $cachePayModel->where('order_no', $out_trade_no)->find();
                if (empty($cachePay)) {
                    Db::rollback();
                    Log::error('order no ' . $out_trade_no . 'cache pay is null');
                    goto P;
                }

                Log::info('do with user' . date('Y-m-d H:i:s'));
                $User = UserModel::get($cachePay['create_uid']);
                $User->money = doubleval($User['money']) + doubleval($cachePay['money']);
                $User->save();

                Log::info('do with balance' . date('Y-m-d H:i:s'));
                //将缓存写入财务记录
                BalanceModel::create([
                    'order_no' => $cachePay['order_no'],
                    'money' => $cachePay['money'],
                    'balance' => $cachePay['balance'],
                    'order_remark' => $cachePay['order_remark'],
                    'type' => $cachePay['type'],
                    'pay_bank' => $cachePay['pay_bank'],
                    'bank_name' => $cachePay['bank_name'],
                    'create_uid' => $cachePay['create_uid'],
                    'create_time' => time(),
                ]);

                Db::commit();

                Log::info('pay success' . date('Y-m-d H:i:s'));
                //异步POST通知，输出success字符串，告知服务器已经完成处理
                echo "success";
            }
        } else {
            Log::info('pay fail' . date('Y-m-d H:i:s'));
            //验证失败
            P:
            echo "fail";
        }
    }

    /**
     * 除去数组中的空值和签名参数
     * @param array $para 签名参数组
     * @return array  去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param array $para 排序前的数组
     * @return array 排序后的数组
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 签名字符串
     * @param string $prestr 需要签名的字符串
     * @param string $key 私钥
     * @return string 签名结果
     */
    private function md5Sign($prestr, $key)
    {
        $prestr = $prestr . $key;
        return md5($prestr);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param array $para 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . rawurlencode($val) . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 验证签名
     * @param string $prestr 需要签名的字符串
     * @param string $sign 签名结果
     * @param string $key 私钥
     * @return bool 签名结果
     */
    private function md5Verify($prestr, $sign, $key)
    {
        $prestr = $prestr . $key;
        $mysgin = md5($prestr);

        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param $url
     * @return mixed
     */
    private function getHttpResponseGET($url)
    {
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);  //定义超时3秒钟
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //执行并获取url地址的内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        return $output;
    }

    /**
     * 商户余额首页
     */
    public function userIndex()
    {
        return view('profile/index');
    }

}