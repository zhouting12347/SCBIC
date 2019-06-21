
<?php
class uploadExcel
{
public $maxSize;//文件大小
public $uploadTpye;//文件类型
public $uploadPath;//上传的路径
public $file;
	function __construct()
	{
	$this -> maxSize = MAXFILESIZE;
	$this -> uploadTpye = ALLOWEXTENSION;
	$this -> uploadPath = UPLOADPATH;
	}
	
	function upload($file="file")
	{
	   
		if ($_FILES[$file]['tmp_name'] != "")
			{
			
				//1 如果文件的大小都不合适 为什么要运行一次is_uploaded_file函数呢？？？？？
				if ($_FILES[$file]['size'] > $this -> maxSize)
				{
					die("文件大小超过限制，最大只能上传500K的文件");
				}
					################################################################
				if (is_uploaded_file($_FILES[$file]['tmp_name']))
				{
				
					$allow = explode(",",$this -> uploadTpye);
					if (!in_array($_FILES[$file]['type'],$allow))
						die("文件类型已被禁止。");
					$ext = explode(".",$_FILES[$file]['name']);
					$ext = $ext[count($ext)-1];
					do
					{
						$fileName = time().(microtime() * pow(10,6)).mt_rand(1000,9999).".".$ext;
					}
					while (file_exists(UPEXCEL.$fileName));

					if (move_uploaded_file($_FILES[$file]['tmp_name'],UPEXCEL.$fileName))
					{
						return $fileName;
					}
						
				}
				
		}
	}
}
?>