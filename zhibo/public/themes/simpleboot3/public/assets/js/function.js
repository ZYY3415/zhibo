

//点击显示弹窗
function openApp(obj){
    switch(obj.open_type){
        case '1' :       //如果为弹出层
            var index =  layer.open({
                type:2,
                title:obj.title,
                area:[obj.width+'px',obj.height+'px'],
                content:[obj.url+'?rid='+obj.rid,"no"],
             	scrollbar: false,
                moveOut:true,
                skin:'winning-class'
            });
            //设置弹出风格，设置背景颜色
           // $('body .winning-class').css('background',obj.bgcolor);

           // layer.style(index,{
             //   textAlign:'center'
           // });
            break;
        case '2' :            //如果为在新窗口打开
            $('#'+obj.id).attr('target','_self');
            $('#'+obj.id).attr('href',obj.url);

            break;
        case '3' :        //如果为在当前窗口打开
            $('#'+obj.id).attr('target','_blank');
            $('#'+obj.id).attr('href',obj.url);
    }
}


var market = [];
market['sjs1'] = Math.random().toString().replace('.', '');
market['sjs2'] = Math.random().toString().replace('.', '');
market['sjs3'] = Math.random().toString().replace('.', '');
var token = "44c9d251add88e27b65ed86506f6e5da";
//底部行情数据
function marketMsg1()           //上证指数
{
    //底部股票行情
        $.ajax({
            url: "http://nuff.eastmoney.com/EM_Finance2015TradeInterface/JS.ashx?id=0000011&token=" + token + "&cb=callback" + market['sjs1'],
            dataType: "jsonp",
            scriptCharset: "utf-8",
            jsonpCallback: "callback" + market['sjs1'],
            success: function (json) {
                if (json.Value.length > 0) {
                    var skd1 = json.Value;//基本数据
                    var attr = [];
                    $(".securities p").eq(0).find('span:first').html(skd1[25]);
                    $(".securities p").eq(0).find('span:last').html(skd1[29] + "%");
                    $(".securities p").eq(0).find('span:first').removeClass("red green");
                    $(".securities p").eq(0).find('span:last').removeClass("red green");
                    if(skd1[27]<0){
                        $(".securities p").eq(0).find('span:first').addClass("green");
                        $(".securities p").eq(0).find('span:last').addClass("green");
                    }else{
                        $(".securities p").eq(0).find('span:first').addClass("red");
                        $(".securities p").eq(0).find('span:last').addClass("red");
                    }
                }
            }
        });

}
function marketMsg2()           //深圳指数
{
    //底部股票行情
    $.ajax({
        url: "http://nuff.eastmoney.com/EM_Finance2015TradeInterface/JS.ashx?id=3990012&token=" + token + "&cb=callback" + market['sjs2'],
        dataType: "jsonp",
        scriptCharset: "utf-8",
        jsonpCallback: "callback" + market['sjs2'],
        success: function (json) {
            if (json.Value.length > 0) {
                var skd1 = json.Value;//基本数据
                var attr = [];
                $(".securities p").eq(1).find('span:first').html(skd1[25]);
                $(".securities p").eq(1).find('span:last').html(skd1[29] + "%");
                $(".securities p").eq(1).find('span:first').removeClass("red green");
                $(".securities p").eq(1).find('span:last').removeClass("red green");
                if(skd1[27]<0){
                    $(".securities p").eq(1).find('span:first').addClass("green");
                    $(".securities p").eq(1).find('span:last').addClass("green");
                }else{
                    $(".securities p").eq(1).find('span:first').addClass("red");
                    $(".securities p").eq(1).find('span:last').addClass("red");
                }
            }
        }
    });

}
function marketMsg3()           //综合指数
{
    //底部股票行情
    $.ajax({
        url: "http://nuff.eastmoney.com/EM_Finance2015TradeInterface/JS.ashx?id=3990062&token=" + token + "&cb=callback" + market['sjs3'],
        dataType: "jsonp",
        scriptCharset: "utf-8",
        jsonpCallback: "callback" + market['sjs3'],
        success: function (json) {
            console.log(json);
            if (json.Value.length > 0) {
                var skd1 = json.Value;//基本数据
                var attr = [];
                $(".securities p").eq(2).find('span:first').html(skd1[25]);
                $(".securities p").eq(2).find('span:last').html(skd1[29] + "%");
                $(".securities p").eq(2).find('span:first').removeClass("red green");
                $(".securities p").eq(2).find('span:last').removeClass("red green");
                if(skd1[27]<0){
                    $(".securities p").eq(2).find('span:first').addClass("green");
                    $(".securities p").eq(2).find('span:last').addClass("green");
                }else{
                    $(".securities p").eq(2).find('span:first').addClass("red");
                    $(".securities p").eq(2).find('span:last').addClass("red");
                }
            }
        }
    });

}


//假人初始化
function init_robot()
{
   if(ADMINID == 1)
   {
       $.ajax({
           url:'/portal/action/initRobot',
           data:{rid:CUR_FID},
           type:'post',
           dataType:'json',
           success:function(data)
           {
              if(data.code)
              {
                  $.each(data.data,function(k,v){
                      if($('#role option[value="r_'+ v.mid+'"]').length <= 0)
                      {
                          var str = '<option value="r_'+ v.mid+'" adminid="'+ v.adminid+'">'+ v.username+'</option>';
                          $('#role').append(str);
                      }
                  });
              }
           }
       });
   }
}

//点击聊天记录或者在线列表添加角色
function addrole(obj)
{
    if(ADMINID != 1)
    {
        return false;
    }
    var username = $(obj).find('span').html();
    var mid = $(obj).attr('mid');
    var adminid = $(obj).attr('adminid');

    if($('#role option[value="'+mid+'"]').length > 0)
    {
        return false;
    }

    var str = '<option value="'+mid+'" adminid="'+ adminid+'" selected>'+ username+'</option>';
    $('#role').append(str);

}

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
            var userImg =  Role_info[data.adminid] != undefined ? Role_info[data.adminid] : "/static/images/23-1F5111I049-53.png";
            var onlineStr =
                '<li mid="' + data.mid + '" adminid="' + data.adminid + '" facility="' + data.facility + '" onclick="addrole(this)" >' +
                '<img class="vipimg" width="20" height="20" src="' + userImg + '"/><span>' + data.client_name + '</span>' + data.rbIcon +
                '</li>';
            //添加到的位置
            var mid = MID;
            if(ADMINID == 14)
            {
                mid = 'y_'+MID;
            }

            //如果在线列表中的用户id为当前用户的id则放在第一位
            if(data.mid === mid)
            {
                if($('.online ul li').length > 0)
                {
                    $(onlineStr).insertBefore($('.online ul li:first'));
                }else{
                    $('.online ul').append(onlineStr);
                }
                $('.online ul li:first').attr('index','user');
                return false;                    //结束循环  相当于break
            }else{

                if($('.online ul li').length > 0)
                {

                    var obj = $('.online ul li');

                    //遍历在线列表，如果要插入的用户aminid 小于或者等于在线列表的某一个adminid则在其之前插入，否则插入到在线列表最后面
                    obj.each(function(k,v){

                        /**
                         * 如果要比对的元素为自己 则跳过当前比对，如果用户列表只有一个元素则直接插入到在线列表中
                         * **/
                       if($(v).attr('mid') == mid && $('.online ul li').length == 1)
                       {
                           $('.online ul').append(onlineStr);
                           return false;                        //结束循环

                       }else if($(v).attr('mid') == mid){
                           return true;                          //跳过当前循环 相当于continue
                       }

                       /**
                        * 要比对的元素不是自己
                        * 则如果 adminid 小于或者等于就插入到元素前面，否则直接插入到后面
                        * **/
                      if(data.adminid <= $(v).attr('adminid'))
                      {

                          $(onlineStr).insertBefore($('.online ul li').eq(k));
                          return false;

                      }else if(data.adminid > $(v).attr('adminid')){

                          $('.online ul').append(onlineStr);
                          return false;
                      }
                    });
                }else{
                    $('.online ul').append(onlineStr);
                    return false;
                }
            }


            break;

        case 2:                                              //礼物列表
            var userJrImg = '';
            if(ADMINID == 1)
            {
                 userJrImg =  data.isuser == 0 ? '<img class="robotimg" src="/themes/simpleboot3/public/assets/images/shouye/robot.png"/>':'';
            }

            var userImg =  Role_info[data.adminid] != undefined ? Role_info[data.adminid] : "/static/images/23-1F5111I049-53.png";


            var flowerstr = '<p gid="' + data.gid + '"><span id="">' + data.time + '</span><img src="' + userImg + '" width="20" height="20" class="v_img"/>' +
                '<strong id="u_name">' + data.username + '</strong>'+userJrImg+'<label>送给</label><label id="t_name">' + data.teacher + '&nbsp;&nbsp;1个&nbsp;'+data.giftname+'</label>' + data.sh_content + '</p>';
            $('.gitlists').append(flowerstr);

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

                var msgstr = '<li lid="' + data.lid + '" mid="' + data.mid + '" adminid="'+data.adminid+'">' +
                    '<p>' +
                    '<img width="20" height="20" src="' + userImg + '"/><span>' + data.username + '</span>'+userJrImg+'对<img width="20" height="20" src="' + toUserImg + '"/><span>' + data.tousername + '</span>'+touserJrImg+'说<span class="sentime">' + data.time + '</span>' +
                    '</p>' +
                    '<div class="sentcontent">' + data.content + '</div>' +suffix+
                    '</li>';
            } else {
                var msgstr = '<li lid="' + data.lid + '" mid="' + data.mid + '"><p><img width="20" height="20" src="' + userImg + '"/><span>' + data.username + '</span>'+userJrImg+'<span class="sentime">' + data.time + '</span></p><div class="sentcontent">' + data.content + '</div>'+suffix+'</li>';
            }

             $('.chatbox ul').append(msgstr);
            break;

        case 4:                                              //用户上线
            var loginStr = '<li><div class="sentcontent tipscolor">' + data.username + '加入了房间</div></li>';
            $('.chatbox ul').append(loginStr);                                     //显示登录信息               XXX加入了房间
            break;

    }
}