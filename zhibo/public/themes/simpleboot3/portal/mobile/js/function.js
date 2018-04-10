//删除聊天记录
function delMsgInfo(lid)
{
    if(ADMINID !=1)
    {
        layer.msg('您不具有权限',{
            icon:5,
        });
        return false;
    }

    layer.alert('确认删除该条信息？',{
        icon:3,
        shade:0,
        anim:5,
        skin:'layui-layer-lan',
        btn:['确定','取消'],
        resize:false,
        yes:function(index,layero){
            layer.close(index);
            $.ajax({
                url:'/portal/action/delMsgInfo',
                data:{lid:lid},
                type:"post",
                dataType:'json',
                success:function(data)
                {
                    if(data.code)
                    {
                        ws.send(JSON.stringify({token:TOKEN,type:'deleteliaotian',lid:lid,delfid:FID}));
                        layer.msg('删除成功！',{
                            icon:1,
                        });
                    }else{
                        layer.msg('删除失败！',{
                            icon:2,
                        });
                    }
                }
            });
        }
    });
}


//封IP
/**
 *
 * **/
function ipban(obj)
{
    banmid = obj.attr('mid');
    isuser = 1;
    if(banmid.indexOf('r_') != -1)
    {
        var isuser = 0;
    }

    if(ADMINID != 1)
    {
        layer.msg('您不具有权限',{
            icon:5,
        });
        return false;
    }
    if(MID == banmid)
    {
        layer.msg('不能够封自己',{
            icon:5,
        });
        return false;
    }
    if(!isuser)
    {
        layer.msg('机器人不能够封Ip',{
            icon:5,
        });
        return false;
    }

    layer.alert('确认封此用户IP？',{
        icon:3,
        shade:0,
        anim:5,
        skin:'layui-layer-lan',
        btn:['确定','取消'],
        resize:false,
        yes:function(index)
        {
            layer.close(index);
            $.ajax({
                url:'/portal/action/ipban',
                data:{banmid:banmid,mid:MID},
                type:'post',
                dataType:'json',
                success:function(data)
                {
                    if(data.code)
                    {
                        layer.msg('封IP成功！',{
                            icon:1,
                        });

                        var  pushobj = {
                            "type":"ipban",
                            "create_username":USERNAME,
                            "banmid":banmid,
                            "content":'您已被'+USERNAME+'踢出房间！',
                            "fid":FID,
                            "token":TOKEN
                        };
                        ws.send(JSON.stringify(pushobj));
                    }else{
                        layer.msg('封IP失败！',{
                            icon:2,
                        });
                    }
                }
            });
        }
    });
}

//封用户
function userban(obj)
{
    if(ADMINID != 1)
    {
        layer.msg('您不具有权限',{
            icon:5,
        });
        return false;
    }

    banmid = obj.attr('mid');
    isuser = 1;
    if(banmid.indexOf('r_') != -1)
    {
        var isuser = 0;
    }

    if(MID == banmid)
    {
        layer.msg('不能够禁言自己',{
            icon:5,
        });
        return false;
    }
    if(!isuser)
    {
        layer.msg('机器人不能够禁言',{
            icon:5,
        });
        return false;
    }

    layer.alert('确认禁言此人？', {
        icon: 3,
        shade: 0,
        anim: 5,
        skin: 'layui-layer-lan',
        btn: ['确定', '取消'],
        resize: false,
        yes: function (index) {
            layer.close(index);
            $.ajax({
                url:'/portal/action/userban',
                data:{banmid:banmid},
                type:'post',
                dataType:'json',
                success:function(data)
                {
                    if(data.code)
                    {
                        layer.msg('禁言成功！',{
                            icon:1,
                        });
                        var  pushobj = {
                            "type":"userban",
                            "create_username":USERNAME,
                            "banmid":banmid,
                            "content":'您已被'+USERNAME+'踢出房间！',
                            "fid":FID,
                            "token":TOKEN
                        };
                        ws.send(JSON.stringify(pushobj));
                    }else{
                        layer.msg('禁言失败！',{
                            icon:2,
                        });
                    }
                }
            });
        }
    });
}

//对谁说
function touser(obj)
{

    var tousername = obj.find('p span').html();
    var tomid = obj.attr('mid');
    var toadminid = obj.attr('adminid');
    if(tomid == MID)
    {
        return false;
    }
    if(ADMINID != 1)
    {
        return false;
    }

    $('#touser option:selected').prop('selected',false);

    if($('#touser option[value="'+tomid+'"]').length > 0)
    {
        $('#touser option[value="'+tomid+'"]').prop('selected',true);
    }else{
        var str = '<option value="'+tomid+'" adminid="'+toadminid+'" selected="true">'+tousername+'</option>';
        $('#touser').append(str);
    }
}

/**
 * 字符串和js拼接函数
 * type  拼接后字符串添加到的位置
 * data  要拼接的js 数据 obj类型
 * **/

function str_content(type,data)
{
    if(parseInt(type) == 0)
    {
        return false;
    }

    switch(type)
    {
        case 1:                                            //在线列表
            var onlineStr =
                '<li mid="' + data.mid + '" adminid="' + data.adminid + '" facility="' + data.facility + '" onclick="addrole(this)" >' +
                '<img class="vipimg" src="' + Role_info[data.adminid] + '"/><span>' + data.client_name + '</span>' + data.rbIcon +
                '</li>';

            $('.online ul').append(onlineStr);

            break;

        case 2:                                              //礼物列表
            var userJrImg = '';
            if(ADMINID == 1)
            {
                userJrImg =  data.isuser == 0 ? '<img class="robotimg" src="/themes/simpleboot3/public/assets/images/shouye/robot.png"/>':'';
            }
            var userImg =  Role_info[data.adminid] != undefined ? Role_info[data.adminid] : "/static/images/23-1F5111I049-53.png";

            var flowerstr =
                '<ul class="chat-item" lid="'+data.gid+'">' +
                    '<li class="item-img"> ' +
                        '<img src="'+userImg+'" alt="游客图像"> ' +
                    '</li>' +
                    '<li class="item-main"> ' +
                        '<div class="chat-info"> ' +
                            '<span class="item-nick">'+data.username+'</span>' +
                            userImg+
                            '<span class="item-nick">送给讲师'+data.teacher+'1个'+data.giftname+'</span>' +
                            '<span class="item-time">'+data.time+'</span>' +
                        '</div>' +
                        '<div class="item-content">'+data.sh_content+'</div> ' +
                    '</li>' +
                '</ul>';
            $('.chat-box').append(flowerstr);

            break;
        case 3:                                           //聊天框消息
            var  suffix = '';
            var userJrImg = '';
            var touserJrImg = '';

            if(ADMINID == 1)
            {
                 suffix = '<span class="delmsg">删除</span>';
                 userJrImg =  data.isuser == 0 ? '<img class="robotimg" src="/themes/simpleboot3/public/assets/images/shouye/robot.png"/>':'';
                 touserJrImg =  data.istouser == 0 ? '<img class="robotimg" src="/themes/simpleboot3/public/assets/images/shouye/robot.png"/>':'';
            }

            var userImg =  Role_info[data.adminid] != undefined ? Role_info[data.adminid] : "/static/images/23-1F5111I049-53.png";

            if (data.tomid != '')                             //对谁说
            {
                toUserImg =  Role_info[data.toadminid] != undefined ? Role_info[data.toadminid] : "/static/images/23-1F5111I049-53.png";

                var msgStr =
                    '<ul class="chat-item">'
                       +'<li class="item-img">'
                           +'<img src="'+userImg+'" alt="游客图像">'
                       +'</li>'
                       +'<li class="item-main">'
                           +'<div class="chat-info">'
                               +'<span class="item-nick">'+data.username+'</span>'
                               +userJrImg
                               +'<span class="item-nick">对 '+data.tousername+
                               '说</span>'
                               + '<span class="item-time">'+data.time+'</span>'
                           +'</div>'
                           +'<div class="item-content">'+data.content+'</div>' +suffix
                       +'</li>'
                    +'</ul>';
            } else {
            var msgStr =
                '<ul class="chat-item">'
                    +'<li class="item-img">'
                        +'<img src="'+userImg+'" alt="游客图像">'
                    +'</li>'
                    +'<li class="item-main">'
                       +'<div class="chat-info">'
                           +'<span class="item-nick">'+data.username+'</span>'
                           +userJrImg
                           +'<span class="item-time">'+data.time+'</span>'
                       +'</div>'
                       +'<div class="item-content">'+data.content+'</div>'+suffix
                    +'</li>'
                +'</ul>';
            }
            $('.chat-box').append(msgStr);
            break;

        case 4:                                              //用户上线
       var loginStr =
           '<div class="info-msg">欢迎' + data.username + '加入了房间</div>';
            $('.chat-box').append(loginStr);                                     //显示登录信息               XXX加入了房间
            break;
        case 5:
           var hintStr = '<div class="info-msg">欢迎来到' + Room_info.room + '直播间!</div>';
            $('.chat-box').append(hintStr);
            break;
        case 6:
            var hintStr = '<div class="info-msg">' + data.username + '退出了直播间!</div>';
            $('.chat-box').append(hintStr);
            break;
        case 7:
            var hintStr = '<div class="info-msg">讲师' + data.teachername + '开课了。</div>';
            $('.chat-box').append(hintStr);
            break;
    }
}
//发送
function sendMeg(){
    var inputContent = $(".text-input").val();
    if(inputContent == ""){
        return;
    }
  
    //表情替换
    inputContent = inputContent.replace(/\[.{1,4}]/g,function(kword){
        var emojiName = "";
        var transform = false;
        emojiArray.forEach(function(value){
            if(value.text == kword){
                emojiName = value.name + ".gif";
                transform = true;
            }
        });
        if(transform == true){
            var img = '<img src="/themes/simpleboot3/public/assets/images/face/' + emojiName + '" alt="' + kword +'"/>';
            return img;
        }else{
            return kword;
        }
    });

    //清空输入框中的内容
    $(".text-input").val("");

    $.ajax({
        url:'/portal/action/addMsg',
        type:'post',
        dataType:'json',
        data:{content:inputContent,username:NICKNAME,mid:MID,aid:ADMINID,fid:FID,fname:Room_info.room,toid:'',tousername:'',toaid:'',roleid:'',roleusername:'',roleaid:'',istrueuser:''},
        success:function(data){
            if(data.code)
            {
                var obj = null;
                var dataInfo = data.data;

                obj = {
                    token:TOKEN,
                    type:'say',
                    roomid:dataInfo.roomid,
                    tomid:dataInfo.toid,
                    tousername:dataInfo.tousername,
                    toadminid:dataInfo.toaid,
                    roleid:dataInfo.rolemid,
                    roleusername:dataInfo.roleusername,
                    roleaid:dataInfo.roleadminid,
                    lid:dataInfo.lid,
                    content:dataInfo.contentsource,                 //源字符串
                    content:dataInfo.content,                       //过滤后字符串
                    quangping:0,
                    shstatus:1,
                    time:dataInfo.time
                };
                ws.send(JSON.stringify(obj));
            }else{
                layer.msg(data.msg,{
                    icon:2,
                });
            }
        }
    });

}