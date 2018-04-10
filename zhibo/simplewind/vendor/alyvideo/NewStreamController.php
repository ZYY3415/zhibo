<?php

class NewStreamController{
    
    private $accessKeyId = "VHlrQUZyFJwKAdQJ";          //密钥ID  标识访问者的身份
    //private $accessKeyId = "LTAIn6FOFmeme6UW";          //密钥ID  标识访问者的身份
    private $accessKeySecret = "WLhxZCsIgclgW23Ug9tsYRrG3iphsk";      //密钥  加密签名字符串和服务器端验证签名字符串的密钥
   // private $accessKeySecret = "k3FQyR1S9CMEpWHMAO54uBKQVAdQ7W";      //密钥  加密签名字符串和服务器端验证签名字符串的密钥
    //直播产品直接使用这个live.aliyuncs.com
    //LIVE
    public  $version = "2016-11-01";                //API版本号
    //CDN
    //public  $version = "2014-11-11";                  //API版本号
    public  $format = "JSON";//XML                    //返回值类型
    private $domainParameters = "";                   //POST 域名参数
    public  $video_host='rtmp://video-center.alivecdn.com';                //推流域名
    public  $appName="live";                           //应用名
    public  $privateKey="zhibo123";                   //鉴权
    public  $vhost="zhibo.g9999.cn";             //加速域名
    //public  $vhost="t.g9999.com.cn";                  //加速域名
    public  $signatureVersion = '1.0';
    public  $msg;
    //控制台里面设置的是cn-shanghai
    public  $regionId = "cn-shanghai" ; //
    public  $client='';
    public  $signatureMethod = 'HMAC-SHA1';
    private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    protected  $protocolType = "http";
    //直播中心服务器 video-center.alivecdn.com
    
    public function _initialize () {
        //初始化客户端 深圳地区
        //vendor('aliyun.aliyun-php-sdk-core.Config');
        include "./aliyun-openapi-php-sdk-master/aliyun-php-sdk-core/Config.php";
        $iClientProfile = \DefaultProfile::getProfile($this->regionId, $this->accessKeyId, $this->accessKeySecret);
        //获取IAcsClient对象，该对象用来发送请求
        $this->client = new \DefaultAcsClient($iClientProfile);
    }
    
//-----------------------------------回调操作---------------------

   /**
    * 推流回调配置
    * NotifyUrl 设置直播流信息推送到的 URL 地址，必须以 http:// 开头
    * @param unknown $parame
    */
   public function setLiveNotifyUrlConfig () {
       $specialParameter = array(
           'Action'=>'SetLiveStreamsNotifyUrlConfig',
           'DomainName'=>$this->vhost,
           //'NotifyUrl' =>'http://one.jin520.cn/live/live/streamNotifyUrl',
           'NotifyUrl' =>'http://du.e9999.com.cn/streamNotifyUrl.php',
       );
       return $this->composeUrl($specialParameter)  ;
   }
    
    
   /**
    * 查询推流回调配置
    * NotifyUrl 设置直播流信息推送到的 URL 地址，必须以 http:// 开头
    * @param unknown $parame
    */
   public function getLiveNotifyUrlConfig () {
       $specialParameter = array(
           'Action'=>'DescribeLiveStreamsNotifyUrlConfig',
           'DomainName'=>$this->vhost,
       );
       //array_view( $this->composeUrl($specialParameter));
       return $this->composeUrl($specialParameter)  ;
   }
    
   /**
    * 录制回调配置
    * NotifyUrl	回调 url 地址, 必须以’http://‘ 或者 ‘https://‘ 开头，错误文档 需要做 url encode
    * @param unknown $parame
    */
   public function setLiveRecordNotifyUrlConfig () {
       $specialParameter = array(
           'Action'=>'AddLiveRecordNotifyConfig',
           'DomainName'=>$this->vhost,
           'NotifyUrl' =>"http://one.jin520.cn/live/live/recordNotifyUrl",
       );
       array_view( $this->composeUrl($specialParameter));
   }
   
   
   /**
    * 查询域名级别录制回调配置。
    * @param unknown $parame
    */
   public function getLiveRecordNotifyUrlConfig () {
       $specialParameter = array(
           'Action'=>'DescribeLiveRecordNotifyConfig',
           'DomainName'=>$this->vhost,
       );
       array_view( $this->composeUrl($specialParameter));
   }
   
   /**
    * 删除录制回调
    */
   public function delLiveRecordNotifyUrlConfig () {
       $specialParameter = array(
           'Action'=>'DeleteLiveRecordNotifyConfig',
           'DomainName'=>$this->vhost,
       );
       array_view( $this->composeUrl($specialParameter));
   }
 //---------------------------------------------推流操作------------------------------------   
    
    /**
     * OK
     * 生成推流地址
     * @param $streamName 用户专有名
     * @param $vhost 加速域名
     * @param $time 有效时间单位秒
     */
    public function getPushSteam($streamName,$time=3600) {
        ///AppName/StreamName?vhost=zhobo.g9999.cn&auth_key=1511161511-0-0-725f145a120d293b137eda9922889a2b
        /*
         * OBS
         * Url :rtmp://zhibo.g9999.cn/AppName
    
         StreamKey : StreamName?vhost=zhobo.g9999.cn&auth_key=1511161511-0-0-725f145a120d293b137eda9922889a2b
         */
        $vhost = $this->vhost;
        $time = time()+$time;
        $videohost = $this->video_host;
        $appName=$this->appName;
        $privateKey=$this->privateKey;
        if($privateKey){
            $auth_key =md5('/'.$appName.'/'.$streamName.'-'.$time.'-0-0-'.$privateKey);
            $url =$videohost.'/'.$appName.'/'.$streamName.'?vhost='.$vhost.'&auth_key='.$time.'-0-0-'.$auth_key;
        }else{
            //             $auth_key =md5('/'.$appName.'/'.$streamName.'-'.$time.'-0-0-'.$privateKey);
            //             $url =$videohost.'/'.$appName.'/'.$streamName.'?vhost='.$vhost.'&auth_key='.$time.'-0-0-'.$auth_key;
    
            $url = $videohost.'/'.$appName.'/'.$streamName.'?vhost='.$vhost;
        }
        return $url;
    }
    
    /**
     * OK
     * 结束推流
     * 必要参数
     * Action 应用名称 StreamName 流名称  LiveStreamType = publisher
     * 次要参数
     * ResumeTime 恢复流的时间
     * @param unknown $parame
     */
    public function endPushStream ($parame) {
        /*
         *  $parame = array(
         'StartTime'=>'2017-11-17T19:00:00Z',
         'EndTime'=>'2017-11-21T07:00:00Z',
         'AppName'=>'live',
         'StreamName'=>'StreamName',
         );
         return $this->getPushStreamHistory($parame);
         */
        $specialParameter = array(
            'Action'=>'ForbidLiveStream',
            'DomainName'=>$this->vhost,
            'LiveStreamType'=>'publisher',
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName'],
        );
        $diff = array_diff_assoc($parame,$specialParameter);
        if (!empty($diff)) {
            //ResumeTime
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $specialParameter = array_merge($specialParameter,$newParam);
            }
        }
//         return $specialParameter);
        //array_diff_assoc
        

        //$host cdn
        return json_encode($this->composeUrl($specialParameter));
    }
    
    /**
     * OK
     * 恢复推流
     * 必要参数
     * Action 应用名称 StreamName 流名称  LiveStreamType = publisher
     * @param unknown $parame
     */
    public function resumePushStream ($parame) {
        $specialParameter = array(
            'Action'=>'resumeLiveStream',
            'DomainName'=>$this->vhost,
            'LiveStreamType'=>'publisher',
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName'],
        );
        //无次要参数
        array_view($this->composeUrl($specialParameter));
    }
    
    /**
     * OK
     * 推流黑名单
     * 必要参数
     * Action 应用名称  DomainName
     */
    public function blackPushStream () {
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamsBlockList',
            'DomainName'=>$this->vhost,
        );
        //无次要参数
        return json_encode( $this->composeUrl($specialParameter) ) ;
    }
    
    /**
     * OK
     * 结束推流后
     * 获取推流历史记录
     * 必要参数
     * StartTime EndTime 
     * 次要参数
     * AppName 应用名称 StreamName 必要参数
     * PageSize 分页大小 默认3000 PageNumber 取得第几页
     * @param unknown $parame
     */
    public function getPushStreamHistory ($parame) {
        /*
         *  $parame = array(
            'StartTime'=>'2017-11-17T19:00:00Z',
            'EndTime'=>'2017-11-21T07:00:00Z',
            'AppName'=>'live',
            'StreamName'=>'StreamName',
        );
        return $this->getPushStreamHistory($parame);
         */
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamsPublishList',
            'DomainName'=>$this->vhost,
            'StartTime'=>$parame['StartTime'],
            'EndTime'=>$parame['EndTime'],
        );
        $diff = array_diff_assoc($parame,$specialParameter);
        if (!empty($diff)) {
            //次要参数AppName StreamName PageSize PageNumber
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $specialParameter = array_merge($specialParameter,$newParam);
            }
        }
       return json_encode($this->composeUrl($specialParameter));
    }
    
    /**
     * OK
     * 查询推流在线列表
     * 次要参数
     * AppName 应用名称 
     * @param unknown $parame
     */
    public function getPushStreamsOnlineList($parame){
        $apiParams = array(
            'Action'=>'DescribeLiveStreamsOnlineList',
            'DomainName'=>$this->vhost,
        );
        $diff = array_diff_assoc($parame,$apiParams);
        if (!empty($diff)) {
            //次要参数AppName 
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $apiParams = array_merge($apiParams,$newParam);
            }
        }
      return  json_encode($this -> composeUrl($apiParams)) ;
    }
    
    /**
     * 注意:阿里统计在线人数
     * rtmp格式 观众需要在线观看三分钟左右才会记录
     * 查询推流在线列表
     * 次要参数
     * AppName 直播流所属应用名称  StreamName 	直播流名称  
     */
    public function getStreamsOnlineUsers($parame){
        $apiParams = array(
            'Action'=>'DescribeLiveStreamOnlineUserNum',
            'DomainName'=>$this->vhost,
        );
        $diff = array_diff_assoc($parame,$apiParams);
        if (!empty($diff)) {
            //次要参数AppName StreamName
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $apiParams = array_merge($apiParams,$newParam);
            }
        }
        return  json_encode($this -> composeUrl($apiParams)) ;
    }
    
    /**
     * 注意:阿里统计在线人数
     * rtmp格式 观众需要在线观看三分钟左右才会记录
     * 查询直播流历史在线人数
     */
    public function getStreamsHistroyUsers($parame){
        $apiParams = array(
            'Action'=>'DescribeLiveStreamHistoryUserNum',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName'],
            'StartTime'=>$parame['StartTime'],
            'EndTime'=>$parame['EndTime'],
        );
        //无次要参数
        return json_encode($this -> composeUrl($apiParams)) ;
    }
    
    /**
     * 查询流控历史
     */
    public function getStreamsHistroyController($parame){
        /*
         * "LiveStreamControlInfo": [
         {
             "Action": "forbid", 
             "TimeStamp": "2015-12-01T16:36:18Z", 
             "ClientIP": "10.207.197.201", 
             "StreamName": "test101.aliyunlive.com/diaoliang/dd"
         }
     ]
         */
        $apiParams = array(
            'Action'=>'DescribeLiveStreamsControlHistory',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            'StartTime'=>$parame['StartTime'],
            'EndTime'=>$parame['EndTime'],
        );
        //无次要参数
        return json_encode($this -> composeUrl($apiParams)) ;
    }
    
    
//-----------------------------------------------转码相关---------------------------
    /**
     * 仅支持rtmp和flv直播流协议
     * 直播转码设置
     * App  直播流所属应用名称  Template 转码模版，
     * 目前有标准质量模板：lld流畅、lsd标清、lhd高清、lud超清，
     * 高品质（窄带高清转码）模板：ld、sd、hd、ud
     * Record String yes or no，是否需要录制
     * Snapshot String yes or no，是否需要截图
     * 无次要参数
     */
    public function setTransPushStreamCode ($parame) {
        $specialParameter = array(
            'Action'=>'AddLiveStreamTranscode',
            'Domain'=>$this->vhost,
            'App'=>'live',
            'Template'=>$parame['Template']
        );
        $diff = array_diff_assoc($parame,$specialParameter);
        if (!empty($diff)) {
            //ResumeTime
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $specialParameter = array_merge($specialParameter,$newParam);
            }
        }
        array_view($this->composeUrl($specialParameter));
    }
    
    /**
     * 获取直播转码信息
     * DomainTranscodeName  加速域名，
     * 无次要参数
     */
    public function getTransPushStreamCode () {
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamTranscodeInfo',
            'DomainTranscodeName'=>$this->vhost,
        );
    
        return json_encode($this->composeUrl($specialParameter));
    }
    
    /**
     * 删除直播转码配置
     * Domain  App  Template
     */
    public function delTransPushStreamCode ($parame) {
        $specialParameter = array(
            'Action'=>'DeleteLiveStreamTranscode',
            'Domain'=>$this->vhost,
            'App'=>$parame['App'],
            'Template'=>$parame['Template']
        );
        array_view($this->composeUrl($specialParameter));
    }
    
    
//------------------------------------------------拉流操作-------------------------------
//拉取第三方直播流 非阿里  
    
    /**
     * 添加拉流信息
     * 必要参数
     * AppName 	直播流所属应用名称 StreamName 	直播流名
     * SourceUrl 用户的直播流所在的源站，多个源站可以填多个地址，用分号分隔
     * StartTime 拉流开始时间，UTC格式, StartTime和EndTime时间间隔在7天内
     * EndTime 拉流结束时间，UTC格式, StartTime和EndTime时间间隔在7天内,且EndTime超过当前时间
     * @param unknown $parame
     */
    public function addPullStream ($parame) {
        $apiParams = array(
            'Action'=>'AddLivePullStreamInfoConfig',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName'],
            'SourceUrl'=>$parame['SourceUrl'],
            'StartTime'=>$parame['StartTime'],
            'EndTime'=>$parame['EndTime'],
        );
        return $this -> composeUrl($apiParams) ;
    }
    
    /**
     * 删除拉流信息
     * 必要参数
     * AppName 	直播流所属应用名称 StreamName 	直播流名
     * @param unknown $parame
     */
    public function delPullStream ($parame) {
        $apiParams = array(
            'Action'=>'DeleteLivePullStreamInfoConfig',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName']
        );
        return $this -> composeUrl($apiParams) ;
    }
    
    /**
     * 查询拉流信息
     * AppName 	直播流所属应用名称
     * StreamName 直播流名
     * @param unknown $parame
     */
    public function queryPullStreamInfo ($parame) {
        $apiParams = array(
            'Action'=>'DeleteLivePullStreamInfoConfig',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            'StreamName'=>$parame['StreamName'],
        );
        return $this -> composeUrl($apiParams) ;
    }


    /**
     * 生成拉流地址
     * @param $streamName 用户专有名
     * @param $vhost 加速域名
     * @param $type 视频格式 支持rtmp、flv、m3u8三种格式
     */
    public function getPullSteam($streamName,$time=3600,$type='rtmp'){
        //         $streamName,$vhost,
        $vhost = $this->vhost;
        $time = time()+$time;
        $appName=$this->appName;
        $privateKey=$this->privateKey;
        $url='';
        switch ($type){
            case 'rtmp':
                $host = 'rtmp://'.$vhost;
                $url = '/'.$appName.'/'.$streamName;
                break;
            case 'flv':
                $host = 'http://'.$vhost;
                $url = '/'.$appName.'/'.$streamName.'.flv';
                break;
            case 'm3u8':
                $host = 'http://'.$vhost;
                $url = '/'.$appName.'/'.$streamName.'.m3u8';
                break;
        }
        if($privateKey){
            $auth_key =md5($url.'-'.$time.'-0-0-'.$privateKey);
            $url = $host.$url.'?auth_key='.$time.'-0-0-'.$auth_key;
        }else{
            $url = $host.$url;
        }
        return $url;
    }
//-------------------------------------------------录制相关------------------------------

    /**
     * 录制配置
     * AppName 	直播流所属应用名称
     * StreamName 直播流名
     * @param unknown $parame
     */
    public function addRecordConfig ($parame) {
        //实时 m3u8  flv mp4 ts {表示 ts 切片名称 record/{AppName}/{StreamName}/{UnixTimestamp}_{Sequence}}
//         $sequence = date("Ym",time());
//         $escapedStartTime = date("Y-m-d-H-i-s",time());
//         $escapedEndTime = date("Y-m-d-H-i-s",time()+3600*6);
//         $unixTimestamp = time();
//         $sliceOssObjectPrefix = $unixTimestamp.'_'.$sequence;
//         $format  = 'record/'.$sequence.'_'.$escapedStartTime.'_'.$escapedEndTime;
        //'RecordFormat.1.OssObjectPrefix' =>"record/{AppName}/{StreamName}/{StartTime}/{EndTime}", 可以
        //{StartTime}_{EndTime}
        //record/{AppName}/{Sequence}_{StartTime}_{EndTime}
        $apiParams = array(
            'Action'=>'AddLiveAppRecordConfig',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
            //oss访问域名
            //g9999bucket.oss-cn-shanghai.aliyuncs.com
            'OssEndpoint'=>"oss-cn-shanghai.aliyuncs.com",
            'OssBucket'=>'g9999bucket',
            //FLV 是FLASH VIDEO的简称，FLV流媒体格式是一种新的视频格式  m3u8 苹果公司开发的一项新型播放格式
            //默认支持 1 小时周期录制，最小周期时间 15 分钟，最多 6 小时。
            'RecordFormat.1.Format'=>'flv',
            'RecordFormat.1.OssObjectPrefix' =>"record/{Date}/{AppName}/{StreamName}/flv/{StartTime}_{EndTime}",
            'RecordFormat.2.Format'=>'mp4',
            'RecordFormat.2.OssObjectPrefix' =>"record/{Date}/{AppName}/{StreamName}/mp4/{StartTime}_{EndTime}",
            //m3u8索引 断流3分钟后才生成  必须断流
            'RecordFormat.3.Format'=>'m3u8',
            'RecordFormat.3.OssObjectPrefix' =>"record/{Date}/{AppName}/{StreamName}/m3u8/{StartTime}_{EndTime}",
            'RecordFormat.3.SliceOssObjectPrefix' => "record/{Date}/{AppName}/{StreamName}/ts/{UnixTimestamp}_{Sequence}",
        );
        array_view( $this -> composeUrl($apiParams)) ;
    }
    

    
    
    /**
     * 解除录制配置 
     */
    public function relieveRecordConfig ($parame) {
        $apiParams = array(
            'Action'=>'DeleteLiveAppRecordConfig',
            'DomainName'=>$this->vhost,
            'AppName'=>$parame['AppName'],
        );
        array_view( $this -> composeUrl($apiParams)) ;
    }
    
    
    /**
     * 查询域名下录制配置列表
     * 次要参数
     * AppName 直播流所属应用名称
     * PageNum 分页的页码，默认值：1
     * PageSize 每页大小，默认值：10，范围：5~30
     * Order 排序，asc：升序，desc：降序，默认：asc
     */
    public function getRecordConfig ($parame) {
        $specialParameter = array(
            'Action'=>'DescribeLiveRecordConfig',
            'DomainName'=>$this->vhost,
        );
        $diff = array_diff_assoc($parame,$specialParameter);
        if (!empty($diff)) {
            //ResumeTime
            $newParam = $this->keyIsExist($diff);
            if (!empty($newParam)) {
                $specialParameter = array_merge($specialParameter,$newParam);
            }
        }
        return json_encode( $this -> composeUrl($specialParameter)) ;
    }
    
    /**
     * 查询录制内容
     */
    public function getRecordContent ($parame) {
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamRecordContent',
            'DomainName'=>$this->vhost,
            'AppName' =>$parame['AppName'],
            'StreamName' =>$parame['StreamName'],
            'StartTime' =>$parame['StartTime'],
            'EndTime' =>$parame['EndTime'],
        );
        $re =  $this -> composeUrl($specialParameter);
        $res = array();
        if (count($re) == 2) {
            foreach ($re['RecordContentInfoList']['RecordContentInfo'] as $v) {
                $indexParam = array(
                    'OssObject' => $v['OssObjectPrefix'],
                    'AppName' =>$parame['AppName'],
                    'StreamName' =>$parame['StreamName'],
                    'StartTime' =>$parame['StartTime'],
                    'EndTime' =>$parame['EndTime'],
                );
                $res[] =  $this -> composeUrl($specialParameter) ;
            }
        }
        array_view(array('record'=>$re,'index'=>$res));
    }
    
    /**
     * 创建录制索引文件
     * 无次要参数
     */
    public function createRecordIndex ($parame) {
        $specialParameter = array(
            'Action'=>'CreateLiveStreamRecordIndexFiles',
            'DomainName'=>$this->vhost,
            'OssEndpoint'=>"oss-cn-shanghai.aliyuncs.com",
            'OssBucket'=>'g9999bucket',
            'OssObject' =>$parame['OssObject'],
            'AppName' =>$parame['AppName'],
            'StreamName' =>$parame['StreamName'],
            'StartTime' =>$parame['StartTime'],
            'EndTime' =>$parame['EndTime'],
        );
        array_view( $this -> composeUrl($specialParameter)) ;
    }
    
//     function test () {
// //         echo strtotime("2017-11-29T10:18:47Z");exit();
// //        1511951735 1511952099 1511950727
//         $parame = array(
//             'AppName' =>'live',
//             'StreamName' =>'c2c9b85ca706a894bf26d20a858e6929e803a615',
//             'StartTime' => gmdate('Y-m-d\TH:i:s\Z', 1511951735-3600),
//             'EndTime' => gmdate('Y-m-d\TH:i:s\Z', 1511952099+3600),
//         );
//         $re =  $this->getRecordConfig($parame);
//         if (is_string($re)) {
//             array_view(json_decode($re,true));
//         }
//     }
    
//     function test1 () {
//         //         echo strtotime("2017-11-29T10:18:47Z");exit();
//         //        1511951735 1511952099 1511950727
//         $parame = array(
//             'AppName' =>'live',
//             'StreamName' =>'c2c9b85ca706a894bf26d20a858e6929e803a615',
//             'StartTime' => gmdate('Y-m-d\TH:i:s\Z', 1513340578-7200),
//             'EndTime' => gmdate('Y-m-d\TH:i:s\Z', 1513340578+7200),
//         );
//         $re =  $this->getRecordContent($parame);
//         if (is_string($re)) {
//             array_view(json_decode($re,true));
//         } else {
//             array_view(json_decode($re,true));
//         }
//     }
    /**
     * 查询录制索引文件
     * 次要参数
     * PageNum 分页的页码，默认值：1
     * PageSize 每页大小，默认值：10，范围：5~30
     * Order 	排序，asc：升序，desc：降序，默认：asc
     */
    public function getAllRecordIndex ($parame) {
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamRecordIndexFiles',
            'DomainName'=>$this->vhost,
            'AppName' =>$parame['AppName'],
            'StreamName' =>$parame['StreamName'],
            'StartTime' =>$parame['StartTime'],
            'EndTime' =>$parame['EndTime'],
        );
        array_view( $this -> composeUrl($specialParameter)) ;
    }
    
    
    /**
     * 查询单个录制索引文件
     * RecordID 	索引文件 ID
     */
    public function getOneRecordIndex ($parame) {
        $specialParameter = array(
            'Action'=>'DescribeLiveStreamRecordIndexFile',
            'DomainName'=>$this->vhost,
            'AppName' =>$parame['AppName'],
            'StreamName' =>$parame['StreamName'],
            'RecordID' =>$parame['RecordID'],
        );
        array_view( $this -> composeUrl($specialParameter)) ;
    }
    
    
    
//-------------------------------------------------跨域相关----------------------------------    

    public function gethttp () {
        $specialParameter = array(
            'Action'=>'DescribeDomainConfigs',
            'DomainName'=>$this->vhost,
            'ConfigList' =>'http_header'
        );
       // array_view( ) ;
        return $this -> composeUrl($specialParameter,'cdn.aliyuncs.com');
    }

    public function gethttpheader () {
        $specialParameter = array(
            'Action'=>'SetHttpHeaderConfig',
            'DomainName'=>$this->vhost,
            'HeaderKey' =>'access-control-allow-origin',
            'HeaderValue'=>'http://local_svncmf.com'
//             'HeaderValue'=>'http://127.0.0.1/shop/shop/adminAddGoods'
        );
       return  $this -> composeUrl($specialParameter,'cdn.aliyuncs.com');
       // array_view( ) ;
    }
    
//------------------------------------------------请求操作-------------------------------    
    /**
     * 构造并请求aliyun
     * 暂时仅支持GET请求
     * @param unknown $specialParameter
     * @param string $host
     * @param string $credential
     * @return boolean|unknown
     */
    function composeUrl ($specialParameter,$host='live.aliyuncs.com',$credential="GET") {
        //公共参数
        //$specialParameter  特别参数
        $publicParameter = array(
            "Format" => $this->format,
            "Version" => $this->version,
            "SignatureMethod" => $this->signatureMethod,
            "SignatureNonce" => md5(uniqid(mt_rand(), true)),
            "SignatureVersion" => $this->signatureVersion,
            "AccessKeyId" => $this->accessKeyId,
            "Timestamp" => gmdate('Y-m-d\TH:i:s\Z', time())
        );
        $parameter = array_merge($publicParameter, $specialParameter);
        
        $request = $this->request($host,$parameter,$this->accessKeySecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$request);
        $content = curl_exec($ch);
        if (false === $content) {
            $content =  curl_errno($ch);  //返回最后一次的错误号
            $this->message = 'curl方法出错，错误号：'.$content;
            return false;
        }
        curl_close($ch);
        
        if( $this->format == "JSON")
            //json转数组
        return json_decode($content,true) ;
        elseif($this->format =="XML"){
            //xml转数组
        return $this->xmlToArray($content) ;
        }else
        return $content;
    }
    
   
    
    

    
//  ------------------签名方法 -------------------勿动------------------------------   
    
    /**
     * url拼接
     * @param unknown $host
     * @param unknown $params
     * @param unknown $secret
     * @return string
     */
    protected function request($host,$params,$secret)
    {
        $param_string = "";
        foreach($params as $key => $value)
        {
            $param_string .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        return  "http://". $host . "/?" . $param_string . "Signature=" . $this->pop_encode($this->sign("GET",$params,$secret."&"));
    }
    
    /**
     * 生成签名
     * @param unknown $method
     * @param unknown $params
     * @param unknown $secret
     * @return string
     */
    protected function sign($method,$params,$secret)
    {
        ksort($params);
        $canonicalizedQueryString = '';
        foreach($params as $key => $value)
        {
            $canonicalizedQueryString .= '&' . $this->pop_encode($key). '=' . $this->pop_encode($value);
        }
        $stringToSign = $method . '&%2F&' . $this->pop_encode(substr($canonicalizedQueryString, 1));
    
        return  base64_encode(hash_hmac('sha1', $stringToSign, $secret, true));
    }
    
    /**
     * url编码
     * @param unknown $str
     * @return mixed
     */
    protected function pop_encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
    
    /**
     * xml转成数组
     * @param $xml
     * @return mixed
     */
    protected function xmlToArray($xml){
        //禁止引用外部xml实体
        //将XML转为array
        //禁止引用外部xml实体
        //     	libxml_disable_entity_loader(true); //这句导致出现上述问题
        //设置是否禁止从外部加载XML实体，设为true就是禁止 目的是防止XML注入攻击（详情自行百度），本意是好的
        //，但这个在设置后存在BUG（具体没深究，以后有时间可以研究下，也许这个BUG在高版本php中已经解决了，
        //，没有验证，总之存在这么个BUG 影响了服务的正常运行。
        libxml_disable_entity_loader(true);
        //$xml 必需。规定要使用的 XML 字符串。
        //新对象的 class  SimpleXMLElement
        //。规定附加的 Libxml 参数。 LIBXML_NOCDATA 16384
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        //编码解码 转为数组
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
    
    /**
     * 拼接处理数组
     */
    protected function keyIsExist ($parame) {
        //参数数组集合 in_array 然后组合数组
        $arr = array('AppName','StreamName','PageSize','PageNumber','ResumeTime');
        $newParam = array();
        foreach ($parame as $k=>$v) {
            if (in_array($k, $arr)) {
                $newParam[$k] = $v;
            }
        }
        return $newParam;
    }
}

