<?php

/**
+--------------------------------
* 检查字符数量，格式化返回
+--------------------------------
* @date: 2017年8月7日 下午3:22:46
* @author: Str
* @param: $GLOBALS
* @return:
*/
function formatString($str){
	$countStr=strlen($str);
	$num=4+(48-$countStr)/3;
	for($i==0;$i<$num;$i++){
		$nbsp.="&emsp;";
	}
	return $str.$nbsp;
}


/**
+--------------------------------
* 时间格式转化为帧 Y-m-d HH:mm:ss:zz
+--------------------------------
* @date: 2016-6-30 上午11:11:55
* @author: Str
* @param: $GLOBALS
* @return: string
*/
function formatTimeToFrame($time){
	list($year,$month,$day,$hour,$minute,$second,$frame)=split ("[-: ]",$time);
	$seconds=mktime($hour,$minute,$second,$month,$day,$year);
	$frames=$seconds*25+$frame;
	return $frames;
}

/**
+--------------------------------
* 帧数转化为时间格式 Y-m-d HH:mm:ss:zz
+--------------------------------
* @date: 2016-6-30 上午11:14:27
* @author: Str
* @param: $GLOBALS
* @return: string
*/
function frameToFormatTime($frame){
	$seconds=explode(".",$frame/25);
	$time=getdate($seconds[0]);
	$frames=("0.".$seconds[1])*25;
	$frames<10?$frames="0".$frames:'';
	foreach($time as $k=>$v){
		if($v<10){
			$time[$k]="0".$v;
		}
	}
	return $time['year']."-".$time['mon']."-".$time['mday']." ".
	$time['hours'].":".$time['minutes'].":".$time['seconds'].":".$frames;
}

/**
+--------------------------------
* hh:mm:ss:zz 转换为帧
+--------------------------------
* @date: 2016-7-28 下午2:39:39
* @author: Str
* @param: $GLOBALS
* @return:string
*/

function timeToFrame($time){
	$date=explode(":",$time);
	return $date[0]*3600*25+$date[1]*60*25+$date[2]*25+$date[3];
}

/**
 +--------------------------------
 * 帧转换为hh:mm:ss:zz 
 +--------------------------------
 * @date: 2016-7-28 下午2:39:39
 * @author: Str
 * @param: $GLOBALS
 * @return:string
 */

function frameToTime($frame){
	$hour=intval($frame/3600/25);
	$min=intval(($frame-$hour*3600*25)/(60*25));
	$seconds=intval(($frame-$hour*3600*25-$min*60*25)/25);
	$frames=$frame-$hour*3600*25-$min*60*25-$seconds*25;
	$hour<10?$hour="0".$hour:'';
	$min<10?$min="0".$min:'';
	$seconds<10?$seconds="0".$seconds:'';
	$frames<10?$frames="0".$frames:'';
	return $hour.":".$min.":".$seconds.":".$frames;
}

/**
+--------------------------------
* 根据开始时间和持续时间，计算结束时间
+--------------------------------
* @date: 2016-6-30 下午4:19:57
* @author: Str
* @param: $GLOBALS
* @return: string
*/
function getEndTime($startTime,$duration){
	$frames=formatTimeToFrame($startTime)+timeToFrame($duration);
	return frameToFormatTime($frames);
}

/**
+--------------------------------
* hh:ss 转化为秒
+--------------------------------
* @date: 2017年8月7日 下午3:22:46
* @author: Str
* @param: $GLOBALS
* @return:
*/
function hsToSeconds($time){
    $hsArray=explode(":", $time);
    return ($hsArray[0]*60+$hsArray[1]);
}

/**
+--------------------------------
* 获取持续时间
+--------------------------------
* @date: 2017年8月7日 下午3:34:51
* @author: Str
* @param: $GLOBALS
* @return:
*/
function getDurationTime($startTime,$endTime){
    return (strtotime($endTime)-strtotime($startTime));
}

//保存日志
//
function saveLog($type,$P_ID){
		$data[L_Type]=$type;
		$data[L_P_ID]=$P_ID;//任务ID
		$data[L_DateTime]=date("Y-m-d H:i:s",time());//时间
		$data[L_Man]=$_SESSION[M_ID];//操作人
		//根据PID查询表单内容
		$Playlist=M('playlist');
		$playlist=$Playlist->where("P_ID=$P_ID")->find();
		if($playlist){
			$data[L_Detail]=json_encode($playlist);
			$Log=M('log');
			$Log->add($data);
		}else{
			$data[L_Detail]=$P_ID;
			$Log=M('log');
			$Log->add($data);
		}
	}


/**
  +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
  +----------------------------------------------------------
 * @static
 * @access public
  +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function musubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if (function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix)
        return $slice . "…";
    return $slice;
}

//根据秒 格式化时间
function formatTime($seconds){
	$hour=round($seconds/3600,0);
	$min=round(($seconds-$hour*3600)/60,0);
	$sec=$seconds-$hour*3600-$min*60;
	if($hour>0){
		return $hour."小时".$min."分".$sec."秒";
	}else{
		return $min."分".$sec."秒";
	}
}

//页面得到不同语言字段值
function getField($id, $field, $lang, $length) {
    $arrfield = getFieldStr($lang);
    $objCate = M("Detail");
    $arrCate = $objCate->find($id);
    $title = $arrCate[$arrfield[$field]];
    $title = musubstr($title, 0, $length);
    return $title;
}

function toDate($time, $format = 'Y-m-d H:i:s') {
    if (empty($time)||($time=='-1')) {
        return '';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}

/**
 * 短信发送
 */
function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
}

//文件夹判断是否为空
function my_judge_empty_dir($module)  
{  
	//截取'Common'的位置
	$tpl_pos=strrpos(__FILE__,"Common");
	//得到模块对应模版的路径地址
	$dir=substr(__FILE__,0,$tpl_pos)."Tpl\default\\".$module."\\".MODULE_NAME;
    $handle = opendir($dir);  
    while (($file = readdir($handle)) !== false)  
    {  
        if ($file != "." && $file != "..")  
        {  
            closedir($handle);  
            return false;  
        }  
    }  
    closedir($handle);
    return true;  
}   

//生成短信验证码随机数
function random_verify($number){
	$randStr = str_shuffle('1234567890');
	$rand = substr($randStr,0,$number);
	return $rand;
}

//发送短信验证码
function sendVerify(){
	$User=M('User');
	//查询上一次发送验证码的时间，计算和现在请求发送短信验证码的时间差
	$lastSendTime=$User->where("MACAddress='".$_SESSION['mac']."'")->field('SendVerifyTime,SendPhone')->find();
	$_SESSION['interval']=time()-intval($lastSendTime['SendVerifyTime']);
	if(($lastSendTime['SendVerifyTime']!='')&&($_SESSION['interval']<60)&&($_SESSION['tempPhone']==$lastSendTime['SendPhone'])){
		return false;
	}else{
		//产生随机4位的验证码
		$_SESSION["verifyNumber"]=random_verify(4);
		//保存验证短信发送时间戳,60秒内不允许重复
		$User->where("MACAddress='".$_SESSION['mac']."'")->setField(array('SendVerifyTime','SendPhone'),array(time(),$_SESSION['tempPhone']));
		$_SESSION['interval']=0;
		//手机发送短信
	}
}

//保存电话号码
function savePhone($phone,$changeNum){
	$User=M('User');
	//第一个电话号码字段为空时
	if(!$_SESSION['phone1']){
		$User->where("MACAddress='".$_SESSION['mac']."'")->setField('PhoneNo1',$phone);
	}
	//第二个电话号码字段为空时
	else if(!$_SESSION['phone2']){
		$User->where("MACAddress='".$_SESSION['mac']."'")->setField('PhoneNo2',$phone);
	}
	//第三个电话号码字段为空时
	else if(!$_SESSION['phone3']){
		$User->where("MACAddress='".$_SESSION['mac']."'")->setField('PhoneNo3',$phone);
	}
	//如果保存电话的三个字段都满了，替换修改的那个号码
	else{
		$User->where("MACAddress='".$_SESSION['mac']."'")->setField('PhoneNo'.$changeNum,$phone);
	}
}

//插入一段字符串
function str_insert($str, $i, $substr)
{
	for($j=0; $j<$i; $j++){
		$startstr .= $str[$j];
	}
	for ($j=$i; $j<strlen($str); $j++){
		$laststr .= $str[$j];
	}
	$str = ($startstr . $substr . $laststr);
	return $str;
}


//二维码生成
function QRcode(){
	import('@.ORG.QRcode');
	import('@.ORG.Image');
	$data="http://www.163.com";
	$outfile='./Public/images/QRcode/text.png';
	$water='./Public/images/sitv_water.png';
	// L水平 7%的字码可被修正【官方推荐】
	// M水平 15%的字码可被修正
	// Q水平 25%的字码可被修正
	// H水平 30%的字码可被修正
	$level='M';
	$size=9;
	$margin=1;
	QRcode::png($data, $outfile, $level, $size,$margin);
	 
	$Image = new Image();
	//给图片添加logo水印
	$Image->water($outfile,$water);
}

/**对excel里的日期进行格式转化*/
function GetData($val){
	$jd = GregorianToJD(1, 1, 1970);
	$gregorian = JDToGregorian($jd+intval($val)-25569);
	return $gregorian;/**显示格式为 “月/日/年” */
}

/**
 +----------------------------------------------------------
 * Import Excel | 2013.08.23
 * Author:HongPing <hongping626@qq.com>
 +----------------------------------------------------------
 * @param  $file   upload file $_FILES  文件相对路径
 *                 $column 读取xml表格的列数
 +----------------------------------------------------------
 * @return array   array("error","message")
 +----------------------------------------------------------
 */
function importExcel($file,$Column=17){
	if(!file_exists($file)){
		return array("error"=>0,'message'=>'file not found!');
	}
	require_once './Common/Classes/PHPExcel.php';
	$objReader = new PHPExcel_Reader_Excel2007();
	try{
		$PHPReader = $objReader->load($file);
	}catch(Exception $e){
	}
	if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
	$allWorksheets = $PHPReader->getAllSheets();
	$i = 0;
	foreach($allWorksheets as $objWorksheet){
		$sheetname=$objWorksheet->getTitle();
		$allRow = $objWorksheet->getHighestRow();//how many rows
		$highestColumn = $objWorksheet->getHighestColumn();//how many columns
		$allColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$allColumn=$Column;
		$array[$i]["Title"] = $sheetname;
		$array[$i]["Cols"] = $allColumn;
		$array[$i]["Rows"] = $allRow;
		$arr = array();
		$isMergeCell = array();
		foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
			foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
				$isMergeCell[$cellReference] = true;
			}
		}
		for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){
			$row = array();
			for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){
				;
				$cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
				$afCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
				$bfCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
				$col = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
				$address = $col.$currentRow;
				$value = $objWorksheet->getCell($address)->getValue();
				if(substr($value,0,1)=='='){
					return array("error"=>0,'message'=>'can not use the formula!');
					exit;
				}
			 	if($cell->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC){
					$cellstyleformat=$cell->getStyle( $cell->getCoordinate() )->getNumberFormat();
					$formatcode=$cellstyleformat->getFormatCode();
					if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[dy]/i', $formatcode)) {
						$value=gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));
					}else{
						$value=PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
					}
				} 
				if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
					$temp = $value;
				}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
					$value=$arr[$currentRow-1][$currentColumn];
				}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
					$value=$temp;
				}
				$row[$currentColumn] = $value;
			}
			$arr[$currentRow] = $row;
		}
		$array[$i]["Content"] = $arr;
		$i++;
	}
	spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
	unset($objWorksheet);
	unset($PHPReader);
	unset($PHPExcel);
	//unlink($file);
	return array("error"=>1,"data"=>$array);
}

//
function getXmlToArray($file){
	$res=importExcel($file);
	//删除表格中的空行
	$arr_excel=array();
	foreach($res['data'][0]['Content'] as $key=>$val){
		$res=array_filter($val);//去除数组中的空字符元素
		if($res){
			$arr_excel[$key]=$val;
		}
	}
	return $arr_excel;
}

//随机字符串
function GetRandStr($length){
	$str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$len=strlen($str)-1;
	$randstr='';
	for($i=0;$i<$length;$i++){
		$num=mt_rand(0,$len);
		$randstr .= $str[$num];
	}
	return $randstr;
}

//xml处理CDATA
function uncdata($xml){
    $state = 'out';
    $a =str_split($xml);
    $new_xml = '';
    foreach ($a AS$k => $v) {
        // Dealwith "state".
        switch ($state ) {
            case'out':
                if( '<' == $v ) {
                    $state = $v;
                }else {
                    $new_xml .= $v;
                }
                break;
            case'<':
                if( '!' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<!':
                if( '[' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![':
                if( 'C' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![C':
                if( 'D' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![CD':
                if( 'A' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![CDA':
                if( 'T' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![CDAT':
                if( 'A' == $v  ) {
                    $state = $state . $v;
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'<![CDATA':
                if( '[' == $v  ) {
                    $cdata = '';
                    $state = 'in';
                }else {
                    $new_xml .= $state . $v;
                    $state = 'out';
                }
                break;
            case'in':
                if( ']' == $v ) {
                    $state = $v;
                }else {
                    $cdata .= $v;
                }
                break;
            case']':
                if(  ']' == $v  ) {
                    $state = $state . $v;
                }else {
                    $cdata .= $state . $v;
                    $state = 'in';
                }
                break;
            case']]':
                if(  '>' == $v  ) {
                    $new_xml .= str_replace('>','&gt;',
                        str_replace('>','&lt;',
                            str_replace('"','&quot;',
                                str_replace('&','&amp;',
                                    $cdata))));
                    $state = 'out';
                } else {
                    $cdata .= $state . $v;
                    $state = 'in';
                }
                break;
        } // switch
    }
    return$new_xml;
}

?>
