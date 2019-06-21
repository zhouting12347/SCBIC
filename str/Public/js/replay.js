/**
 * 
 */
jQuery(function($){
	$("#replay").click(function(){
		var date=$("#startDate").val();
		var programName=$("#channel").find("option:selected").text();
		var data="date="+date+"&programName="+programName;
		$.ajax({
			url: '/index.php/Home/Replay/getVideoURL',
			dataType: 'json',
			type: 'POST',
			cache:false,
			data:data,
			success: function(msg){
				videoURL=msg.data;
				videoURL2=msg.status; //下一个视频地址
				startSecond=msg.info;
				loadVideo(msg.info);
				replayStatus=setInterval(replayStatus,200); //查询视频状态
			},
			error:function(){
				alert("发生错误！");
			}
		});
	});
	
	$('.contral').click(function(){
		if(action){
			$.playBar.Stop();
			action=false;
			pause();
			$(this).html("开始");
			}else{
				$.playBar.Begin();
				action=true;
				$(this).html("停止");
				play();
			}
		});
 	$.playBar.addBar($('.playBar'),1000*3600);//第一个参数是需要显示播放器的容器，第二个参数为时间，单位毫秒
	$.playBar.changeBarColor("#72dfff");//设置进度条颜色
});

//监测视频播放状态
function replayStatus(){
	var vlc=document.getElementById("vlc");
	if(vlc.input.state==6){
		vlc.playlist.stop();
		vlc.playlist.clear();
		vlc.playlist.add(videoURL2); //加载下一个视频
		play();
		videoURL2='';
		startSecond=0;
		$.playBar.addBar($('.playBar'),1000*3600);//第一个参数是需要显示播放器的容器，第二个参数为时间，单位毫秒
		$.playBar.changeBarColor("#72dfff");//设置进度条颜色
		clearInterval(replayStatus);
	}
}
