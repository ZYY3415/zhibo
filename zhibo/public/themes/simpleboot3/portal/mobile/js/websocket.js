$(function () {
    var reconnect = false;
    init();                //初始化

    function init() {
       // var wshost = "192.168.1.110";            //websocket 服务器地址
        var wshost = "120.79.152.68";            //websocket 服务器地址
       // var wsport = "2345";                      //端口
        var wsport = "39001";                      //端口

        ws = new WebSocket("ws://" + wshost + ":" + wsport);

        ws.onopen = function () {
            onopen();
        };

        ws.onmessage = function (e) {

            var data = JSON.parse(e.data);                                  //转换为对象
            switch (data.type) {
                case 'ping':                                                // 服务端ping客户端,心跳检测
                    ws.send(JSON.stringify({"type": "pong", "token": TOKEN}));
                    break;
                case 'login':                                                // 登录 更新用户列表
                    login_hint();
                    wsocket_login(data);
                    break;
                case 're_login':                                            // 断线重连，只更新用户列表
                    login_hint();
                    console.log(data['client_name'] + "重连成功");
                    break;
                case 'login_error':                                        // 登录出错
                    layer.msg('对不起，系统检测您的账号已处于登录状态',{
                        icon:5,
                    });
                    //跳转到错误页面
                    break;
                case 'say':                                                // 发言
                    say(data);
                    break;
                case 'batch_login':                                        //机器人批量上线
                    var robot_list = JSON.parse(data.data);

                    for (var i in robot_list){
                        if (CUR_FID == i) {
                            $.each(robot_list[i], function (k, v) {
                                var loginObj = {username: v.client_name};
                                str_content(4,loginObj);
                                char_frame();                                              //聊天框内容滚动
                            });
                        }
                    }
                    break;
                case 'batch_logout':
                    var robot_list = JSON.parse(data.data);

                    for (var i in robot_list) {
                        if (CUR_FID == i) {
                            $.ajax({
                                url: '/portal/action/batchLgInfo',
                                data: {ids: robot_list[i]},
                                dataType: 'json',
                                method: 'post',
                                success: function (data) {
                                    if (data.code) {
                                        $.each(data.data, function (k, v) {
                                            str_content(6,{username: v.username});
                                            char_frame();                                                 //聊天框内容滚动
                                        });
                                    }
                                }
                            });
                        }
                    }
                    break;
                case 'shangxian':                                                  //讲师开始讲课
                    var user_ridattr = FID.split(',');                            //用户所属房间数组
                    var msg_ridattr = data.fid.split(',');                        //消息所属房间数组
                    var rel = attr_difference(user_ridattr, msg_ridattr);          //求两个数组差集
                    if (rel.length != 0) {
                        return false;
                    }

                   str_content(7,{teachername:data.username});

                    break;
                case 'xiaxian':                        //讲师下课
                    var user_ridattr = FID.split(',');                            //用户所属房间数组
                    var msg_ridattr = data.fid.split(',');                      //消息所属房间数组
                    var rel = attr_difference(user_ridattr, msg_ridattr);          //求两个数组差集
                    if (rel.length != 0) {
                        return false;
                    }

                    break;
                case 'loginout':
                    //是否单点登录  1否   0是
                    var userOnline = $('.online ul li[mid="' + data.mid + '"]');

                    if (data.adminid == 14) {
                        userOnline = $('.online ul li[mid="y_' + data.mid + '"]');
                    }
                    if (Room_info.single_login != 1) {
                        if (userOnline.length > 0) {
                            var facility = userOnline.attr('facility');   //获取多点登录设备，如果pc和移动都登录了则显示 facity="0,1"
                            newfacility = facility.replace(data.facility, '');

                            if (newfacility == '') {
                                userOnline.remove();
                                $num = parseInt($('.selectbox span').html());
                                $('.selectbox span').html($num - 1);
                            }
                        }
                    } else {
                        userOnline.remove();
                        $num = parseInt($('.selectbox span').html());
                        $('.selectbox span').html($num - 1);
                    }
                    break;
                case 'sendflower':
                    var user_ridattr = FID.split(',');                            //用户所属房间数组
                    var msg_ridattr = data.rid.split(',');                      //消息所属房间数组
                    var rel = attr_difference(user_ridattr, msg_ridattr);

                    if (rel.length != 0) {
                        return false;
                    }

                    var mid = data.mid;
                    var adminid = data.adminid;
                    var username = data.username;

                    var isuser = 2;
                    if(mid.indexOf('r_') != -1)
                    {
                        isuser = 0;
                    }else if(mid.indexOf('y_') != -1)
                    {
                        isuser = 1;
                    }

                    //php时间戳转换为时间格式
                    var newDate = new Date();
                    newDate.setTime(parseInt(data.time) * 1000);
                    var time = newDate.getHours() + ':' + newDate.getMinutes();

                    var flowerobj = {
                        gid:data.gid,
                        time:time,
                        adminid:adminid,
                        username:username,
                        teacher:data.teacher,
                        sh_content:data.sh_content,
                        giftname:data.giftname,
                        isuser:isuser,
                    };

                    str_content(2,flowerobj);
                    char_frame();
                    break;
                case "deleteliaotian":
                    var lid = data.lid;
                    if ($('.chat-box ul[lid="' + lid + '"]').length > 0) {
                        $('.chat-box ul[lid="' + lid + '"]').remove();
                        char_frame();//聊天框滚动
                    }
                    break;
                case 'userban':
                    banmid = data.banmid;

                    if(ADMINID == 14)
                    {
                        if(banmid == 'y_'+MID)
                        {
                            layer.msg(data.content,{
                                icon:5,
                            },function(){
                                window.location.reload();
                            });
                        }
                    }else{
                        if(banmid == MID)
                        {
                            layer.msg(data.content,{
                                icon:5,
                            },function(){
                                window.location.reload();
                            });
                        }
                    }
                    break;
                case 'ipban':
                    banmid = data.banmid;

                    if(ADMINID == 14)
                    {
                        if(banmid == 'y_'+MID)
                        {
                            layer.msg(data.content,{
                                icon:5,
                            },function(){
                                window.location.reload();
                            });
                        }
                    }else{
                        if(banmid == MID)
                        {
                            layer.msg(data.content,{
                                icon:5,
                            },function(){
                                window.location.reload();
                            });
                        }
                    }
                    break;
                case 'other_login':
                    if(data.mid == MID)
                    {
                        layer.msg('您的账号已在其他地方登陆！',{
                            icon:5,
                        },function(){
                            $.cookie('PHPSESSID',null);
                            window.location.reload();
                        });
                    }
                    break;
            }

        }
    }

    //websocket 开始连接方法
    function onopen() {
        // timeid && window.clearInterval(timeid);

        if (!USERNAME) {
            window.location.reload();
            return ws.close();
        }
        var login_data = JSON.stringify({
            "type": "login",                              //websocket类型
            "client_name": NICKNAME,                      //客户端名称
            "roomid": FID,                                //用户所属的房间，多个房间号以逗号隔开
            "mid": MID,                                   //用户id
            "adminid": ADMINID,                           //用户adminid
            "tuijianmid": TUIJIANMID,                     //用户推荐人id
            "tuijianusername": TUIJIANUSERNAME,
            "tuijianadminid": TUIJIANADMINID,
            "login_count": LOGIN_COUNT,                    //登录次数
            "Login_switch": LOGIN_SWITCH,                  //    ?可丟奇
            "token": TOKEN,                                //token
            "facility": 0,                                 //登录设备   0移动  1pc
            "logins": SINGLE_LOGIN,                    //    ?可丢弃
            "login_switch": Room_info.dlts,                //登录提示
            "loginid": LOGINID                             //登录id
        });

        if (reconnect == false) {            //登录
            console.log("websocket握手成功，发送登录数据:" + login_data);
            ws.send(login_data);
            reconnect = true;
            return;
            //发送登录消息
        } else {   //断线重连
            console.log("websocket握手成功，发送重连数据:" + relogin_data);
            ws.send(relogin_data);
            reconnect = false;
        }
    }

    //websocket 登录消息
    function wsocket_login(data) {
        if (LOGIN_TIP == 1) {                                               //是否显示登录信息
            if (data.adminid != 1) {                                        //不显示管理员的登录信息
                var loginobj = {username:data.username};
                str_content(4,loginobj);                                 //消息内容方法
            }
        }

        char_frame();                                                      //聊天框内容滚动
    }
    //登录提示
    function login_hint()
    {

        if(Room_info.login_hint)
        {
            str_content(5,new Array());
        }
    }

    //聊天框内容方法
    function char_frame() {
        if ($(".chat-box").length > 100) {                    //聊天框中的信息超过100条时,将第一条踢出
            $(".chat-box:first").remove();
        }

        autoscroll();
    }

    //滚动条自动滚到最底部
    function autoscroll() {
        containerHeight = $(".chat-container").height();
        boxHeight = $(".chat-box").height();
        if(boxHeight > containerHeight){
            $(".chat-container").scrollTop(boxHeight- containerHeight + transformFontSize(10));
        }
    }

    //用户列表中是否存在改用户
    function user_exists(mid, single_login, facility) {
        if (single_login == null && facility == null) {
            if ($('.online>ul>li[mid="' + mid + '"]').length > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            var online = $('.online>ul>li[mid="' + mid + '"]');
            if (online.length > 0 && online.attr('facility').indexOf(facility) != -1) {
                return 1;
            } else {
                return 0;
            }
        }

    }

    //单个用户登录刷新信息
    /**
     * {
     * aid:8,
       mid:57,
       rid:1001
       username:"巩华"
       }
     * **/
    function one_user(data) {
        var mid = 'r_' + data.mid;
        if (user_exists(mid, null, null)) {
            return false;
        }


        var rbIcon = '';
        if (ADMINID == 1)                      //v.istrueuser=1  真实用户   v.istrueuser=0  假人
        {
            rbIcon = '<img class="robotimg" src="/themes/simpleboot3/public/assets/images/shouye/robot.png"/>';       //机器人头像

        }


        //字符串拼接
        var strobj = {mid:mid,adminid: data.adminid,facility: 1, client_name: data.client_name,rbIcon:rbIcon};

        str_content(1,strobj);

        //更新在线人数
        var poplenum = $('#selectbox').find('span').html();
        $('#selectbox').find('span').html(parseInt(poplenum) + 1);

    }

    //单个用户退出刷新信息
    /**
     * {
     * aid:8,
       mid:57,
       rid:1001
       username:"巩华"
       }
     * **/
    function oneout_user(data) {
        var mid = 'r_' + data.mid;
        if (!user_exists(mid, null, null)) {
            return false;
        }

        $(".online ul li").each(function () {
            if ($(this).attr('mid') == mid) {
                $(this).remove();

                var poplenum = $('#selectbox').find('span').html();
                $('#selectbox').find('span').html(parseInt(poplenum) - 1);
            }
        });

    }

    //用户插入到用户列表
    //$str  用户字符串
    function insert_online(str, data) {
        $(".online ul li").each(function () {
            if (parseInt($(this).attr('adminid')) >= parseInt(data.aid)) {
                $(this).before(str);
            }
        });
    }


    //websocket 接收聊天信息
    /**
     * adminid:1                                          //消息发送人adminid
     content:"11"                                         //内容
     from_client_id:"12097b7d3ddc7ae894b21bac721f73b8"
     from_client_name:"ZYY"                               //消息发送人
     lid:"92"                                             //该消息id
     mid:"10"                                             //消息发送人id
     quangping:0                                          //是否全屏显示    0 否 1是
     roomid:"1001"                                        //接收消息的房间号   如多个则用逗号隔开
     shstatus:1                                           //是否审核  0否   1是
     toadminid:""
     token:"20180112091612"                               //token
     tomid:""
     tousername:""
     type:"say"                                           //消息类型
     * **/
    function say(data) {
        //data.contentsource  内容中包含敏感字符，转义后的内容
        var content = data.content;
        if (data.contentsource != undefined && ADMINID == 1) {
            content = data.contentsource;
        }

        //消息是否审核判断  0 未审核 1审核
        if (data.shstatus == 0 && ADMINID != 1) {
            return false;
        }


        var user_ridattr = FID.split(',');                            //用户所属房间数组
        var msg_ridattr = data.roomid.split(',');                      //消息所属房间数组
        var rel = attr_difference(user_ridattr, msg_ridattr);          //求两个数组差集
        /**
         * 消息可能属于多个房间  data.roomid   如1001,1002,1003
         * 用户所属的房间可能有多个  FID       如 1001,1002,1003
         * 如果消息所属房间为  1001  则对于房间包含1001的用户才可以看到该消息
         * **/
        if (rel.length != 0) {
            return false;
        }


        var adminid = data.adminid;                        //角色adminid
        var mid = data.mid;                                //角色id
        var username = data.from_client_name;                //角色名称
        var isuser = 2;
        /**
         * 如果消息data中存在roleid 属性，则该条消息的发送人为角色id,角色名
         * **/
        if (data.roleid != '')                       //角色判断
        {
            adminid = data.roleaid;
            mid = data.roleid;
            username = data.roleusername;

            if(data.roleid.indexOf('r_') != -1)
            {
                isuser = 0;
            }else if(data.roleid.indexOf('y_') != -1)
            {
                isuser = 1;
            }
        }


        /* if ($(".chatbox li").children().hasClass('tipstext')) {
         $("#chatlists").html(msgstr);
         } else {
         $('.chatbox ul').append(msgstr);
         }
         autoscroll('#chatlists');*/


        //对谁说
        var toadminid = data.toadminid;
        var tomid = data.tomid;
        var tousername = data.tousername;
        var istouser = 2;
        if (tomid != '')
        {
            if(tomid.indexOf('r_') != -1)
            {
                istouser = 0;
            }else if(tomid.indexOf('y_') != -1)
            {
                istouser = 1;
            }
        }

        //php时间戳转换为时间格式
        var newDate = new Date();
        newDate.setTime(parseInt(data.time) * 1000);
        var time = newDate.getHours() + ':' + newDate.getMinutes();

        var msgobj = {
            lid:data.lid,
            mid:mid,
            adminid:adminid,
            username:username,
            tomid:tomid,
            toadminid:toadminid,
            tousername:tousername,
            time:time,
            content:content,
            isuser:isuser,
            istouser:istouser,
        };

        str_content(3,msgobj);
        autoscroll('#chatlists');
    }

    //求两个数组的差集
    /**
     * attr1  被比较的数组
     * attr2  比较数组
     * return rel
     * **/
    function attr_difference(attr1, attr2) {
        var rel = [];
        for (var i in attr2)                   //如果消息所属房间数组中的每一个值都在用户所属的房间中则 rel为空，否则不为空
        {
            var flag = true;
            for (var j in attr1) {
                if (attr2[j] == attr1[i]) {
                    flag = false;
                    break;
                }
            }
            if (flag) {
                rel.push(attr1[i]);
            }
        }
        return rel;
    }
});