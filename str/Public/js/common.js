//获取视频播放地址
function getVideoURL(){
	$.ajax({
		url: '/index.php/Home/TechMonitoring/getVideoURL?id='+alarmID,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg){
			if(msg.status==1){
				videoURL=msg.data;
				startSecond=msg.info;
				//videoURL="http://localhost/video/1.ts";
				//startSecond=500;
				//loadVideo(startSecond);
				player.src(msg.data);  //重置video的src
				player.load(msg.data);  //使video重新加载
				
				loadingStatus=0;
				loadingLayer(loadingStatus);
			}else if(msg.status==0){
				videoURL='';
				alert("点播地址获取失败");
				loadingStatus=0;
				loadingLayer(loadingStatus);
			}
		},
		error:function(){
			//alert("发生错误！");
		}
	});
}

//获取音频的播放地址
function getRadioURL(){
	$.ajax({
		url: '/index.php/Home/Radio/getRadioURL?id='+alarmID,
		dataType: 'json',
		type: 'GET',
		cache:false,
		success: function(msg) {
			if(msg.status){
				videoURL=msg.data;
				startSecond=msg.info;
				//videoURL="http://localhost/video/1.ts";
				//startSecond=500;
				loadVideo(startSecond);
			}else{
				videoURL='';
			}
		},
		error:function(){
			//alert("发生错误！");
		}
	});
}



//填充报警详情内容
function alarmDetail(){
	$("#startTime1").val(startTime);
	$("#endTime1").val(endTime);
	$("#duration1").val(duration);
	$("#channel1").val(channel);
	$("#alarmType-1").val(alarmType);
	$("input[name='alarmLevel1'][value="+alarmLevel+"]").prop("checked",true);
	$("input[name='sureStatus1'][value="+sureStatus+"]").prop("checked",true);	
}


function formatTime(time){
	var time=Math.round(time*3600);
	if(time>=3600){
		return "01:00:00";
	}else{
		var h = 0,
        m = 0,
        s = 0,
        _h = '00',
        _m = '00',
        _s = '00';
		h = Math.floor(time / 3600);
		time = Math.floor(time % 3600);
		m = Math.floor(time / 60);
		s = Math.floor(time % 60);
		_s = s < 10 ? '0' + s : s + '';
		_m = m < 10 ? '0' + m : m + '';
		_h = h < 10 ? '0' + h : h + '';
		return _h + ":" + _m + ":" + _s;
	}
}

function seek(CurrTime){
	var oDate=new Date();
	if(getFormatYMD()==videoDate && videoHour==oDate.getHours()){
		vlc.input.position=(CurrTime/1000)/(oDate.getMinutes()*60+oDate.getSeconds());
	}else{
		vlc.input.position=(CurrTime/1000)/3600;
	}
}


function loadingLayer(status){
	//1显示，0不显示
	if(status==1){
		$(".loading").css("display","block");
	}else if(status==0){
		$(".loading").css("display","none");
	}
}

function getFormatYMD(){
	var oDate=new Date();
	var Y=oDate.getFullYear();
	var M=oDate.getMonth()+1;
	var D=oDate.getDate();
	if(M<10){
		M=0+M.toString();
	}
	if(D<10){
		D=0+D.toString();
	}
	return Y+"-"+M+"-"+D;
}
