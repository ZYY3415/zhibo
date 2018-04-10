$(function($) {

	//首页打开一个新的页面
	$('.openApp').click(function() {
		var obj = {};
		obj.title = $(this).attr('title');
		obj.width = $(this).attr('w').split('.')[0];
		obj.height = $(this).attr('h').split('.')[0];
		obj.sort = $(this).attr('sort');
		obj.open_type = $(this).attr('open_type');
		obj.id = $(this).attr('id');
		obj.bgcolor = $(this).attr('bgcolor');
		obj.url = $(this).attr('url');
		obj.rid = $(this).attr('rid');
		openApp(obj);
		
	});

	init_robot();
	marketMsg1();
	marketMsg2();
	marketMsg3();
	var timer_sk = null;
	var timer_xk = null;
	
	
	//管理工具展开列表
	$('.managertools').click(function() {
		$('.toolsbox').toggle();
	})
	$('.toolsbox').find('li').click(function(){
		$('.toolsbox').hide();
	})
	//讲师上课方法
	$(".switch").click(function() {
		if($(this).attr('class').indexOf('teachon') !== -1) //切换为正在讲课
		{
			$(this).html('停止讲课');
			$(this).removeClass('teachon');
			$(this).addClass('teachoff');

			ws.send(JSON.stringify({ "type": "shangxian", "username": NICKNAME, "fid": FID, "token": TOKEN }));

			//发送ajax请求 改变数据库中房间
			$.ajax({
				url: "/portal/action/teacherTeach",
				type: "POST",
				data: { rid: FID, teacher: User_info.nickname },
				dataType: "json",
				cache: false,
				success: function(msg) { //msg为返回的数据，在这里做数据绑定
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {}
			});


			var num = 0;

			if(timer_xk !== null) {
				clearInterval(timer_xk);
			}

			timer_sk = setInterval(function() {
				num++;
				$.ajax({
					url: '/portal/action/rbAutoShangxian',
					data: { rid: FID, num: num },
					type: "POST",
					dateType: "json",
					success: function(data) {
						if(typeof data === 'string') {
							//data:"{"1001":[{"mid":"58","client_name":"\u5b97\u6b63\u4e1a","adminid":"12","status":"1","roomid":"1001","login_switch":"1","istrueuser":0}
							//"data:{"1001":[{"mid":52,"client_name":"\u968b\u81f4\u8fdc","adminid":9,"status":0,"roomid":1001}]}"

							ws.send(JSON.stringify({ "type": "batch_login", "data": data, "token": TOKEN }));
						}
					}
				});
				if(num > 6) {
					clearInterval(timer_sk);
				}
			}, 10000);
		} else if($(this).attr('class').indexOf('teachoff') !== -1) { //切换为停止讲课
			$(this).html('开始讲课');
			$(this).removeClass('teachoff');
			$(this).addClass('teachon');

			ws.send(JSON.stringify({ "type": "xiaxian", "fid": FID, "token": TOKEN }));


			$.ajax({
				url: "/portal/action/teacherNoTeach",
				type: "POST",
				data: { rid: FID },
				dataType: "json",
				cache: false,
				success: function(msg) { //msg为返回的数据，在这里做数据绑定
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {}

			});
			if(timer_sk !== null) {
				clearInterval(timer_sk);
			}

			var num = 0;
			timer_xk = setInterval(function() {
				num++;
				$.ajax({
					url: '/portal/action/rbAutoXiaxian',
					data: { fid: FID, num: num },
					type: "POST",
					dateType: "json",
					success: function(data) {
						if(typeof data === 'string') {
							ws.send(JSON.stringify({ "type": "batch_logout", "data": data, "token": TOKEN }));
						}
						//data:"{"1001":["70","61","63","57","56"]}"
					}
				});
				if(num > 6) {
					clearInterval(timer_xk);
				}
			}, 10000);
		}
	});


	//聊天框点击用户名显示小弹框
	$('#chatlists').delegate('li p','click',function(){

		if(ADMINID != 1){
			return false;
		}
		if($(this).siblings('.popup-box').length > 0){
			$(this).siblings('.popup-box').remove();
			$(this).find('span:first').css('color','');
		}else{
			$('#chatlists').find('.popup-box').remove();
			$('#chatlists').find('li p span').css('color','');
			$(this).find('span:first').css('color','#d67400');

			var str = '<span class="popup-box"><span class="tospeak">对TA说</span><span class="ipban">封IP</span><span class="userban">屏蔽</span></span>';
			$(this).after(str);
		}
	});

	//对他说
	$('#chatlists').delegate('.tospeak','click',function(){
		var obj = $(this).parents('li[lid]');
		touser(obj);
	});

	//封ip
	$('#chatlists').delegate('.ipban','click',function(){
		var obj = $(this).parents('li[lid]');
		ipban(obj);
	});

	//踢用户
	$('#chatlists').delegate('.userban','click',function(){
		var obj = $(this).parents('li[lid]');
		userban(obj);
	});

	$('#chatlists').delegate('.delmsg','click',function(){

		var lid = $(this).parents('li[lid]').attr('lid');
		delMsgInfo(lid);
	});

});

$(document).ready(function() {
	//美化主页滚动条
	$(".gitlists , #chatlists , #onlinelist ").niceScroll({
		styler:"fb",
		cursorcolor:"#65cea7",
		cursorwidth: '3',
		cursorborderradius: '0px',
		background: '#424f63', 
		spacebarenabled:false,
		cursorborder: '0'
	});

	//底部菜单操作
	$(".menu_tap nav a").on('click', function() {
		$(this).addClass('checked').siblings().removeClass('checked');
		var bottomId = $(this).attr('data-id');
		$("#" + bottomId).show().siblings().hide();

	});
	//红包、礼物、表情
	$(".pangbtn").on('click', function() {

		$('.pangdetail').siblings().hide();
		$('.pangdetail').toggle();

	});
	$(".hongbtn").on('click', function() {

		$('.hongdetail').siblings().hide();
		$('.hongdetail').toggle();

	});
	

	//点赞
	$("#dianzan").on('click', function() {
		var sum = $(this).text();
		if($(this).hasClass("clicked")) {
			return false;
		} else {
			var osun = parseInt(sum) + 1;
			$(this).text(osun).addClass('clicked');
		}
	});

	//收藏
	$("#collection").on('click', function() {
		$(this).addClass('collected').text('已收藏');
	});

	/* 清除聊天记录 */
	$(".clearbtn").on('click', function() {
		var tips = '<div class="tipstext">还没有聊天记录哦!</div>';
		var oli = $('<li>' + tips + '</li>');
		$("#chatlists").html(oli);

	})

	//发送聊天
	$("#sentto").on('click', function() {

		sendMeg();
		
	});
	
	//礼物数据
	var chargelwArry = [
		{ "name1": "201703011122553902", "name2": "201703011122577753","text": "鼓掌" ,"mes":"跟着我左手右手一个慢动作，这样拍起来比较洋气。" },
		{ "name1": "201703011137126416", "name2": "201703011137143856","text": "赞" ,"mes":"给你点一个大大的赞，如果不够就来32个。"},
		{ "name1": "201703011140291347", "name2": "201703011140316152","text": "啤酒" ,"mes":"大金链子小手表，夏日看直播还得啤酒干起来。"},
		{ "name1": "201703011140485259", "name2": "201703011140516597","text": "蛋糕" ,"mes":"旋转跳跃我闭着眼，送个蛋糕，生日快乐！" },
		{ "name1": "201703011141188433", "name2": "201703011141191699","text": "鲜花" ,"mes":"你看到鲜花第一眼感觉到什么？我满满的爱。"},
		{ "name1": "201703011141463238", "name2": "201703011141486616","text": "妹子" ,"mes":"送你个妹子，接好！从此右手是右手！"},
		{ "name1": "201703011142403798", "name2": "201703011142425864","text": "帅哥" ,"mes":"哥，走的那都是气质路线。"},
		{ "name1": "201703011144552523", "name2": "201703011144592640","text": "喊单神器" ,"mes":"来来来，都到碗里来。"},
		{ "name1": "201703011147011234", "name2": "201703011147037002","text": "兰博基尼" ,"mes":"是时候开出我的跑车带你出去兜兜风。"},
		{ "name1": "201703011148129638", "name2": "201703011148142881","text": "法拉利" ,"mes":"要的就是这激情，玩的就是这心跳。"},
		{ "name1": "201703011153248995", "name2": "201703011153279670","text": "别墅" ,"mes":"别墅，送你，小菜一碟。"},
		{ "name1": "201703011154087952", "name2": "201703011154101568","text": "劳斯莱斯" ,"mes":"对方很看好你，并给你送了一辆劳斯莱斯。"},
		{ "name1": "201703011154336783", "name2": "201703011154369090","text": "游轮" ,"mes":"我可从来没跟你说过，坐上游艇以后发型很飘逸。"},
		{ "name1": "201703011155314244", "name2": "201703011155369271","text": "飞机" ,"mes":"巴拉巴拉魔法，开启轰炸模式。"},
	];
	
	/*点击礼物图标，展示礼物列表 */
	$(".liwubtn").on('click', function() {
		var __TMPL__ = "/themes/simpleboot3";
		var liwudetail = $('.liwudetail');
		if(liwudetail.css('display') == "none") {
			$('.liwu_lists').html('');
			var bqlist = '';
			for(var i = 0; i < 14; i++) {	
				bqlist += '<a style="background-image: url(' + __TMPL__ + '/public/assets/images/chargelw/' + chargelwArry[i].name1 + '.gif)" data-content = "' + chargelwArry[i].text +'" data-mes = "' + chargelwArry[i].mes +'" id="'+ chargelwArry[i].name2 +'"></a>';
			};
			liwudetail.css('display', "block").siblings().css('display', "none");
			$('.liwu_lists').append(bqlist);
			console.log();
		} else {
			$('.liwu_lists').html('');
			liwudetail.css('display', "none");
		}
	});

	/*鼠标悬浮礼物，展示礼物信息详情 */
	$('.liwu_lists').on('mouseenter','a',function(){
		$(this).parent().siblings().show();
		var __TMPL__ = "/themes/simpleboot3";
		var mid=$(this).attr('id');
		var dcontent=$(this).attr('data-content');
		var mes=$(this).attr('data-mes');
		var mlist='<dl>'+
						'<dt style="background-image: url(' + __TMPL__ + '/public/assets/images/chargelw/' +  mid + '.gif)"></dt>'+
						'<dd>'+
							'<h3>'+dcontent+'</h3>'+
							'<p>'+mes+'</p>'+
						'</dd>'+
				  '</dl>';
		
		$('.detail_content').append(mlist); 
		
	}).on('mouseout','a',function(){
		$('.detail_content').html(' '); 
		$(this).parent().siblings().hide();
	})

	//点击礼物
	$(".liwudetail").on('click', 'a', function() {
		//清除stime计时器；
		window.clearTimeout(stime);
		var __TMPL__ = "/themes/simpleboot3";
		var mid=$(this).attr('id');
		var mes=$(this).attr('data-mes');
		var flowename = $(this).attr('data-content');
		var roleid = $('#role option:selected').val();
		var roleusername = $('#role option:selected').html();
		var roleadminid = $('#role option:selected').attr('adminid');
		if(roleid == MID)
		{
			roleid = '';
			roleusername = '';
			roleadminid = '';
		}

        var content = '<img src="' + __TMPL__ + '/public/assets/images/chargelw/' + mid + '.gif"/>' +
		'<label>'+mes+'</label>';
		
		
      $.ajax({
		  url:'/portal/action/addgift',
		  data:{uid:MID,username:User_info.nickname,adminid:ADMINID,roleid:roleid,roleusername:roleusername,roleadminid:roleadminid,rid:FID,content:content,remarks:mes,giftname:flowename},
		  type:'post',
		  dataType:'json',
		  success:function(data)
		  {
             if(data.code)
			 {
				 var dataobj = data.data;
                 var obj = {
					 type:'sendflower',
					 sh_content:dataobj.remarks,
					 number:1,
					 mid:dataobj.mid,
					 username:dataobj.username,
					 adminid:dataobj.adminid,
					 rid:dataobj.rid,
					 gid:dataobj.id,
					 teacher:dataobj.teacher,
					 time:dataobj.create_time,
					 token:TOKEN,
					 giftname:dataobj.gift_name
				 };
				 ws.send(JSON.stringify(obj));
				 layer.msg('送礼成功！',{
					 icon:6
				 });
				  
			 }else{
				 layer.msg('送礼失败！',{
					 icon:5
				 });
			 }
		  }
	  });
		
		//礼物动画放大效果
 		var content1 = '<img src="' + __TMPL__ + '/public/assets/images/chargelw/' + mid + '.gif" class="dongtu"/>';
		$(".gitlists").append(content1);
		var stime=setTimeout(function(){$(".gitlists").find('.dongtu').remove()},3000);

		$(".liwudetail").hide();
	});

	var faceArray = [
		{ "name": "Expression_1", "text": "[鼓掌]" },
		{ "name": "Expression_2", "text": "[跳]" },
		{ "name": "Expression_3", "text": "[kiss]" },
		{ "name": "Expression_4", "text": "[来电]" },
		{ "name": "Expression_5", "text": "[贱笑]" },
		{ "name": "Expression_6", "text": "[陶醉]" },
		{ "name": "Expression_7", "text": "[兴奋]" },
		{ "name": "Expression_8", "text": "[鄙视]" },
		{ "name": "Expression_9", "text": "[得意]" },
		{ "name": "Expression_10", "text": "[偷笑]" },
		{ "name": "Expression_11", "text": "[挖鼻孔]" },
		{ "name": "Expression_12", "text": "[衰]" },
		{ "name": "Expression_13", "text": "[流汗]" },
		{ "name": "Expression_14", "text": "[伤心]" },
		{ "name": "Expression_15", "text": "[鬼脸]" },
		{ "name": "Expression_16", "text": "[狂笑]" },
		{ "name": "Expression_17", "text": "[发呆]" },
		{ "name": "Expression_18", "text": "[害羞]" },
		{ "name": "Expression_19", "text": "[可怜]" },
		{ "name": "Expression_20", "text": "[气愤]" },
		{ "name": "Expression_21", "text": "[惊吓]" },
		{ "name": "Expression_22", "text": "[困了]" },
		{ "name": "Expression_23", "text": "[再见]" },
		{ "name": "Expression_24", "text": "[感动]" },
		{ "name": "Expression_25", "text": "[晕]" },
		{ "name": "Expression_26", "text": "[可爱]" },
		{ "name": "Expression_27", "text": "[潜水]" },
		{ "name": "Expression_28", "text": "[强]" },
		{ "name": "Expression_29", "text": "[囧]" },
		{ "name": "Expression_30", "text": "[窃笑]" },
		{ "name": "Expression_31", "text": "[疑问]" },
		{ "name": "Expression_32", "text": "[装逼]" },
		{ "name": "Expression_33", "text": "[抱歉]" },
		{ "name": "Expression_34", "text": "[鼻血]" },
		{ "name": "Expression_35", "text": "[睡觉]" },
		{ "name": "Expression_36", "text": "[委屈]" },
		{ "name": "Expression_37", "text": "[笑哈哈]" },
		{ "name": "Expression_38", "text": "[贱贱地笑]" },
		{ "name": "Expression_39", "text": "[被电]" },
		{ "name": "Expression_40", "text": "[转发]" },
		{ "name": "Expression_41", "text": "[求关注]" },
		{ "name": "Expression_42", "text": "[路过这儿]" },
		{ "name": "Expression_43", "text": "[好激动]" },
		{ "name": "Expression_44", "text": "[招财]" },
		{ "name": "Expression_45", "text": "[加油啦]" },
		{ "name": "Expression_46", "text": "[转转]" },
		{ "name": "Expression_47", "text": "[围观]" },
		{ "name": "Expression_48", "text": "[推撞]" },
		{ "name": "Expression_49", "text": "[来嘛]" },
		{ "name": "Expression_50", "text": "[啦啦啦]" },
		{ "name": "Expression_51", "text": "[切克闹]" },
		{ "name": "Expression_52", "text": "[给力]" },
		{ "name": "Expression_53", "text": "[威武]" },
		{ "name": "Expression_54", "text": "[流血]" },
		{ "name": "Expression_55", "text": "[顶一个]"},
		{ "name": "Expression_56", "text": "[赞一个]"},
		{ "name": "Expression_57", "text": "[掌声]"},
		{ "name": "Expression_58", "text": "[鲜花]"},
	];
	
	/* 点击打开表情框  */
	$("#biaoqing").on('click', function() {
		$('.yangshibox').css('display', "none").find('ul').html('');
		$('.wenzibox').css('display', "none");
		var __TMPL__ = "/themes/simpleboot3";
		var tangkuang = $('.tangkuang');
		if(tangkuang.css('display') == "none") {
			var bqlist = '';
			for(var i = 0; i < 54; i++) {
				bqlist += '<li class="listyle" style="background-image: url(' + __TMPL__ + '/public/assets/images/face/' + faceArray[i].name + '.gif);" data-content = "' + faceArray[i].text + '"></li>';
			}
			tangkuang.css('display', "block").find('ul').append(bqlist);
		} else {
			tangkuang.css('display', "none").find('ul').html('');
		}

	})
	
	/*点击各个弹出框之外的地方，弹出框关闭 */
	$(document).click(function (e) {
	    var wenzibox = $('.wenzibox'),
	        wenzi = $('#wenzi')[0],
	        tangkuang = $('.tangkuang'),
	        biaoqing = $('#biaoqing')[0],
	        yangshibox = $('.yangshibox'),
	        yangshi = $('#yangshi')[0],
	        liwudetail = $('.liwudetail'),
	        liwubtn = $('.liwubtn')[0],
	        pangdetail = $('.pangdetail'),
	        pangbtn = $('.pangbtn')[0],
	        hongdetail = $('.hongdetail'),
	        hongbtn = $('.hongbtn')[0],
	        target = e.target;
	    if (wenzi !== target && !$.contains(wenzi, target)) {
	        wenzibox.hide();
	    }
	    if (biaoqing !== target && !$.contains(biaoqing, target)) {
	        tangkuang.hide();
	    }
	    if (yangshi !== target && !$.contains(yangshi, target)) {
	        yangshibox.hide();
	    }
	    if (liwubtn !== target && !$.contains(liwubtn, target)) {
	        liwudetail.hide();
	    }
	    if (pangbtn !== target && !$.contains(pangbtn, target)) {
	        pangdetail.hide();
	    }
	    if (hongbtn !== target && !$.contains(hongbtn, target)) {
	        hongdetail.hide();
	    }
	});
	
	
	/* 点击表情，在发送框显示  */
	$('.tangkuang').on('click', 'li', function() {
		var datacontent = $(this).attr('data-content');
		$('.inputext').append(datacontent);
		$('.tangkuang').css('display', "none").find('ul').html('');
	});
	
	var caitiaoArray = [
		{ "name": "Expression_55", "text": "[顶一个]" ,"mes":"顶一个"},
		{ "name": "Expression_56", "text": "[赞一个]" ,"mes":"赞一个"},
		{ "name": "Expression_57", "text": "[掌声]" ,"mes":"掌声"},
		{ "name": "Expression_58", "text": "[鲜花]" ,"mes":"鲜花"},
	
	];
	
	
	/* 点击打开彩条框  */
	$('#yangshi').on('click', function() {
		$('.tangkuang').css('display', "none").find('ul').html('');
		$('.wenzibox').css('display', "none");
		var __TMPL__ = "/themes/simpleboot3";
		var yangshibox = $('.yangshibox');
		if(yangshibox.css('display') == "none") {
			var bqlist = '';
			for(var i = 0; i < 4; i++) {
				bqlist += '<li class="listyle" data-content = "' + caitiaoArray[i].text + '">'+caitiaoArray[i].mes+'</li>';
			}
			yangshibox.css('display', "block").find('ul').append(bqlist);
		} else {
			yangshibox.css('display', "none").find('ul').html('');
		}

	});
	
	/* 点击彩条，在发送框显示  */
	$('.yangshibox').on('click', 'li', function() {
		var datacontent = $(this).attr('data-content');
		$('.inputext').append(datacontent);
		$('.yangshibox').css('display', "none").find('ul').html('');
	});
	
	/* 点击字体  */
	$('#wenzi').on('click', function() {
		$('.yangshibox ,.tangkuang').css('display', "none").find('ul').html('');
		$('.wenzibox').toggle();
	});
	/* 选择显示的字体大小  */
	$('.wenzibox').on('click', 'li', function() {
		$(this).addClass("checked").siblings().removeClass("checked").parents(".wenzibox").hide();
		var fsize=$(this).text();
		$('#wenzi').attr("data-fsize",fsize);
		var ofsize=fsize+'px';
		$('.sentcontent').css('font-size', ofsize);
	});

	//点击发送按钮
	function sendMeg() {

		var inputext = $(".inputext").text();                         //获取输入框内容
		if(inputext == "") {                                         //内容为空时
			return false;
		}

		var __TMPL__ = "/themes/simpleboot3";
        var roleid = $('#role option:selected').val();               //角色id
		var rolename = $('#role option:selected').html();               //角色名称
		var roleaid= $('#role option:selected').attr('adminid');      //角色adminid
		if(roleid == MID)
		{
			roleid = '';
			rolename='';
			roleaid='';
		}

		var istrueuser = 1;
        if(roleid !=null && roleid != '')
		{
			 istrueuser = 0;
		}

        var tomid = $('#touser option:selected').val();
        if(tomid == '')
		{
			var tousername = '';
		}else{
			var tousername = $('#touser option:selected').html();
		}
        var toadminid = $('#touser option:selected').attr('adminid');





		//匹配输入框中的表情文字
		inputext = inputext.replace(/\[.{1,4}]/g,function(kword){
			var emojiName = "";
			faceArray.forEach(function(value) {
				if(value.text == kword) {
					emojiName = value.name + ".gif";
					console.log(emojiName);
				}
			});
			return '<img src="' + __TMPL__ + '/public/assets/images/face/' + emojiName + '" alt="' + kword + '"/>';

		});

        $.ajax({
			url:'/portal/action/addMsg',
			type:'post',
			dataType:'json',
			data:{content:inputext,username:NICKNAME,mid:MID,aid:ADMINID,fid:FID,fname:Room_info.room,toid:tomid,tousername:tousername,toaid:toadminid,roleid:roleid,roleusername:rolename,roleaid:roleaid,istrueuser:istrueuser},
			success:function(data)
			{
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
					   icon:5
				   });
			   }
			}
		});		
        if ($(".chatbox li").children().hasClass('tipstext')) {
            $("#chatlists").html('');
        }

		$(".inputext").text(' ');
		$('.tangkuang').hide().find('ul').html('');
		$('.yangshibox').hide().find('ul').html('');
		
	}
	

});