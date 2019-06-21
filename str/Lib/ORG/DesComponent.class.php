<?php

class DesComponent {
	var $key = 'sndhrand';

	function encrypt($string) {

		$ivArray=array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
		$iv=null;
		foreach ($ivArray as $element)
			$iv.=CHR($element);


 		$size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );  
       $string = $this->pkcs5Pad ( $string, $size );  

		$data =  mcrypt_encrypt(MCRYPT_DES, $this->key, $string, MCRYPT_MODE_CBC, $iv);
		$data = base64_encode($data);
		return $data;
	}

	function decrypt($string) {

		$ivArray=array(0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF);
		$iv=null;
		foreach ($ivArray as $element)
			$iv.=CHR($element);

		$string = base64_decode($string);
		//echo("****");
		//echo($string);
		//echo("****");
		$result =  mcrypt_decrypt(MCRYPT_DES, $this->key, $string, MCRYPT_MODE_CBC, $iv);
   $result = $this->pkcs5Unpad( $result );  

		return $result;
	}
	
	
	 function pkcs5Pad($text, $blocksize)  
    {  
        $pad = $blocksize - (strlen ( $text ) % $blocksize);  
        return $text . str_repeat ( chr ( $pad ), $pad );  
    }  
  
    function pkcs5Unpad($text)  
    {  
        $pad = ord ( $text {strlen ( $text ) - 1} );  
        if ($pad > strlen ( $text ))  
            return false;  
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)  
            return false;  
        return substr ( $text, 0, - 1 * $pad );  
    }  
	
}


$des = new DesComponent();
echo ($des->encrypt("19760519"));
echo "<br />";

//die($des->decrypt("zLVdpYUM0qw="));
//die($des->decrypt("zLVdpYUM0qzEsNshEEI6Cg=="));

$t2 =$des->decrypt("zLVdpYUM0qw="); 
echo $t2;
echo "--";
echo strlen($t2);
echo is_utf8($t2);


echo "<br />";
$t3 = mb_convert_encoding($t2,"GB2312", "utf-8");
echo $t3;
echo "--";
echo strlen($t3);
echo is_utf8($t3);


echo "<br />";


$t1 =$des->decrypt("zLVdpYUM0qzEsNshEEI6Cg=="); 
echo $t1;
echo "--";
echo strlen($t1);
echo is_utf8($t1);

echo "<br />";
$t3 = mb_convert_encoding($t1, "utf-8","GB2312");
echo $t3;
echo "--";
echo strlen($t3);
echo is_utf8($t3);

function is_utf8($string) { 
return preg_match('%^(?: 
[\x09\x0A\x0D\x20-\x7E] # ASCII 
| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte 
| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs 
| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte 
| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates 
| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3 
| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15 
| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16 
)*$%xs', $string); 
}
?>