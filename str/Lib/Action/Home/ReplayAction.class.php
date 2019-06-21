<?php
class ReplayAction extends Action
{
    /**
    +--------------------------------
    * 回看页面
    +--------------------------------
    * @date: 2017年8月2日 上午11:06:59
    * @author: Str
    * @param: variable
    * @return:
    */
    public function index(){
        $Program=M("zhjc_program_number");
        $program=$Program->select();
        $this->assign("program",$program);
        $this->display();
    }
    
    /**
    +--------------------------------
    * 拼接播放地址
    +--------------------------------
    * @date: 2017年9月12日 下午4:37:20
    * @author: Str
    * @param: variable
    * @return:
    */
    public function getVideoURL(){
        $date=$_POST['date'];
		$programName=$_POST['programName'];
        $date_arr=explode(" ",$date);
        $ymd_arr=explode("-",$date_arr[0]);
        $his_arr=explode(":",$date_arr[1]);
        
        $Program=M("zhjc_program_number");
		$res=$Program->where("programName='$programName'")->find();
	    //查找目录中对应的文件
		$path="Z:/".$res[path]."/".$date_arr[0]."/";
       
        $path=iconv('UTF-8','GB18030',$path);
        $file_array=scandir($path);
        $hour=$his_arr[0];
        foreach($file_array as $k=>$v){
            if($hour==substr($v,8,2)){
                $fileName=$v;
                $key=$k;
                break;
            }
        }
        $fileName2=$file_array[$key+1];
		$startSeconds=$his_arr[1]*60+$his_arr[2]; //需要跳转的时间
		
		$videoURL="http://10.2.1.29:9000/".$res[path]."/".$date_arr[0]."/".$fileName;
		$videoURL2="http://10.2.1.29:9000/".$res[path]."/".$date_arr[0]."/".$fileName2;
		
        $this->ajaxReturn($videoURL,$startSeconds,$videoURL2);
    }
}
?>