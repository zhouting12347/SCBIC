jQuery(function($){
	//查询
	$("#search").click(function(){
		var source=$("#source").val();
		var data="&nowPage=1&condition=1&keyword="+$("#keyword").val()+"&source="+source;
		$.ajax({
			url: '/index.php/Home/Live/ajaxGetLive',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:data,
			success: function(msg) {
				if(msg.status){
					$('#page').html(msg.data[0]);
					$('#TB01 tbody').html(msg.data[1]);
					$('#liveMain').html("<主>");
					$('#live1').html("<1>");
					$('#live2').html("<2>");
					$('#live3').html("<3>");
				}else{
					
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//查询
	$("#liveSearch").click(function(){
		var data="&nowPage=1&condition=1&keyword="+$("#liveKeyword").val();
		$.ajax({
			url: '/index.php/Home/Live/ajaxGetManageLive',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:data,
			success: function(msg) {
				if(msg.status){
					$('#livePage').html(msg.data[0]);
					$('#TB02 tbody').html(msg.data[1]);
				}else{
					
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//管理分页栏ajax读取数据
	$("#livePage").delegate("a","click",function(){
		var nowPage=$(this).attr('page');
		var data="nowPage="+nowPage;
		$.ajax({
			url: '/index.php/Home/Live/ajaxGetManageLive',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:data,
			success: function(msg) {
				if(msg.status){
					$('#livePage').html(msg.data[0]);
					$('#TB02 tbody').html(msg.data[1]);
				}else{
					
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	//直播流地址添加操作
	$("#save").click(function(){
	if(!$("input[name='programName']").val()){
		 alert("填写频道名称！");
		 return 0;
	}
	if(!$("input[name='address']").val()){
		 alert("填写直播地址！");	
		 return 0;
	}
		$.ajax({
			url: '/index.php/Home/Live/ajaxSaveAddressHandler',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:$("#form1").serialize(),
			success: function(msg){
				if(msg.status){
					alert("保存成功！");
					$("#liveSearch").click();//刷新数据
				}else{
					alert("保存失败！");
				}
			},
			error:function(){
				alert("发生错误！");
			}
		});
		$("#form1")[0].reset();
	});
	
	//删除按钮
	$("#TB02 tbody").delegate("button[class='btn btn-danger']","click",function(){
		var id=$(this).parent().parent().attr("id");
		var r=confirm("是否确定删除条目?");
		if (r==true){
			$.ajax({
				url: '/index.php/Home/Live/ajaxDelAddressHandler?id='+id,
				dataType: 'json',
				type: 'GET',
				cache:false,
				success: function(msg) {
					if(msg.status){
						 alert("删除成功！");
						 $("#liveSearch").click();//刷新数据
					}else{
						alert("删除失败！");
					}
				},
				error:function(){
					alert("发生错误！");
				}
			});
		}else{
			return false;
		}
	});
	
	//播放按钮大
	$("#TB01 tbody").delegate("button[class='btn btn-info']","click",function(){
		var address=$(this).parent().prev().html();
		var channelName=$(this).parent().parent().find("td:eq(0)").html();
		
		if(liveMain){
			if(liveMain!=live1&&liveMain!=live2&&liveMain!=live3){
				$("#"+liveMain+"").removeAttr("class");//如果没有其他按钮也选中该行，删除上一个选择行颜色
			}
			$("#"+liveMain+"").find("td:eq(2)").find("button:eq(0)").attr("class","btn btn-info");//恢复按钮颜色
		}
		liveMain=$(this).parent().parent().attr("id");//记录当前行id
		$(this).parent().parent().attr("class","warning"); //行颜色
		$(this).parent().find("button:eq(0)").attr("class","btn btn-danger");//按钮颜色
		
		$("#liveMain").html("<主> "+channelName);
		loadVideo(address,'big');
	});
	
	//播放按钮1
	$("#TB01 tbody").delegate("button[class='btn btn-success 1']","click",function(){
		var address=$(this).parent().prev().html();
		var channelName=$(this).parent().parent().find("td:eq(0)").html();
		if(live1){
			if(live1!=liveMain&&live1!=live2&&live1!=live3){
				$("#"+live1+"").removeAttr("class");//如果没有其他按钮也选中该行，删除上一个选择行颜色
			}
			$("#"+live1+"").find("td:eq(2)").find("button:eq(1)").attr("class","btn btn-success 1");//恢复按钮颜色
		}
		live1=$(this).parent().parent().attr("id");//记录当前行id
		$(this).parent().parent().attr("class","warning"); //行颜色
		$(this).parent().find("button:eq(1)").attr("class","btn btn-danger");//按钮颜色
		
		$("#live1").html("<1> "+channelName);
		loadVideo(address,1);
	});
	
	//播放按钮2
	$("#TB01 tbody").delegate("button[class='btn btn-success 2']","click",function(){
		var address=$(this).parent().prev().html();
		var channelName=$(this).parent().parent().find("td:eq(0)").html();
		if(live2){
			if(live2!=liveMain&&live2!=live1&&live2!=live3){
				$("#"+live2+"").removeAttr("class");//如果没有其他按钮也选中该行，删除上一个选择行颜色
			}
			$("#"+live2+"").find("td:eq(2)").find("button:eq(2)").attr("class","btn btn-success 2");//恢复按钮颜色
		}
		live2=$(this).parent().parent().attr("id");//记录当前行id
		$(this).parent().parent().attr("class","warning"); //行颜色
		$(this).parent().find("button:eq(2)").attr("class","btn btn-danger");//按钮颜色
		
		$("#live2").html("<2> "+channelName);
		loadVideo(address,2);
	});
	
	//播放按钮3
	$("#TB01 tbody").delegate("button[class='btn btn-success 3']","click",function(){
		var address=$(this).parent().prev().html();
		var channelName=$(this).parent().parent().find("td:eq(0)").html();
		if(live3){
			if(live3!=liveMain&&live3!=live1&&live3!=live2){
				$("#"+live3+"").removeAttr("class");//如果没有其他按钮也选中该行，删除上一个选择行颜色
			}
			$("#"+live3+"").find("td:eq(2)").find("button:eq(3)").attr("class","btn btn-success 3");//恢复按钮颜色
		}
		live3=$(this).parent().parent().attr("id");//记录当前行id
		$(this).parent().parent().attr("class","warning"); //行颜色
		$(this).parent().find("button:eq(3)").attr("class","btn btn-danger");//按钮颜色
		
		$("#live3").html("<3> "+channelName);
		loadVideo(address,3);
	});
	
});

//加载视频
function loadVideo(url,vlc){
	switch(vlc){
		case "big":
			var vlc=document.getElementById("vlcBig");
			break;
		case 1:
			var vlc=document.getElementById("vlc1");
			break;
		case 2:
			var vlc=document.getElementById("vlc2");
			break;
		case 3:
			var vlc=document.getElementById("vlc3");
			break;
	}
	
	vlc.playlist.stop();
	vlc.playlist.clear();
	vlc.playlist.add(url);
	vlc.playlist.play();
}