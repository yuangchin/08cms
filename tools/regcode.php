<?php
define('NOROBOT', TRUE);
include_once dirname(dirname(__FILE__)).'/include/general.inc.php';
$timestamp = time();
$x_size=empty($regcode_width) ? 60 : $regcode_width;
$y_size=empty($regcode_height) ? 20 : $regcode_height;
empty($verify) && $verify = '08cms_regcode';#同页多验证码支持
$nmsg = cls_string::Random(4, empty($regcode_mode) ? 1 : (int)$regcode_mode);
_08_Http_Request::clearCache();

// 暂时保留旧样式
if ( empty($regcode_style) )
{
    if(!isset($m_cookie[$verify.'_t']) || @$m_cookie[$verify.'_t'] != $t ) {
        msetcookie($verify, authcode($timestamp."\t".$nmsg, 'ENCODE'));
        msetcookie($verify.'_t', $t);
    }
    if(function_exists('imagecreate') && function_exists('imagecolorallocate') && function_exists('imagepng') &&
    function_exists('imagesetpixel') && function_exists('imageString') && function_exists('imagedestroy') && function_exists('imagefilledrectangle') && function_exists('imagerectangle')){
    	$aimg = imagecreate($x_size,$y_size);
    	$back = imagecolorallocate($aimg,255,255,255);
    	$border = imagecolorallocate($aimg,183,216,239);
    	imagefilledrectangle($aimg,0,0,$x_size - 1,$y_size - 1,$back);
    	imagerectangle($aimg,0,0,$x_size - 1,$y_size - 1,$border);
      
        for($i=1; $i<=20;$i++){
    		$dot = imagecolorallocate($aimg,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255));
    		imagesetpixel($aimg,mt_rand(2,$x_size-2), mt_rand(2,$y_size-2),$dot);
        }  
    	for($i=1; $i<=10;$i++){
    		imageString($aimg,1,$i*$x_size/12+mt_rand(1,3),mt_rand(1,13),'.',imageColorAllocate($aimg,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255)));
    	}
        for ($i=0;$i<strlen($nmsg);$i++){
    		imageString($aimg,mt_rand(4,5),$i*$x_size/4+mt_rand(1,5),mt_rand(1,6),$nmsg[$i],imageColorAllocate($aimg,mt_rand(50,255),mt_rand(0,120),mt_rand(50,255)));
        }
    	header("Pragma:no-cache");
    	header("Cache-control:no-cache");
    	header("Content-type: image/png");
        imagepng($aimg);
        imagedestroy($aimg);
    } else{
    	header("Pragma:no-cache");
    	header("Cache-control:no-cache");
    	header("ContentType: Image/BMP");
    
    	$Color[0] = chr(0).chr(0).chr(0);
    	$Color[1] = chr(255).chr(255).chr(255);
    	$_Num[0]  = "1110000111110111101111011110111101001011110100101111010010111101001011110111101111011110111110000111";
    	$_Num[1]  = "1111011111110001111111110111111111011111111101111111110111111111011111111101111111110111111100000111";
    	$_Num[2]  = "1110000111110111101111011110111111111011111111011111111011111111011111111011111111011110111100000011";
    	$_Num[3]  = "1110000111110111101111011110111111110111111100111111111101111111111011110111101111011110111110000111";
    	$_Num[4]  = "1111101111111110111111110011111110101111110110111111011011111100000011111110111111111011111111000011";
    	$_Num[5]  = "1100000011110111111111011111111101000111110011101111111110111111111011110111101111011110111110000111";
    	$_Num[6]  = "1111000111111011101111011111111101111111110100011111001110111101111011110111101111011110111110000111";
    	$_Num[7]  = "1100000011110111011111011101111111101111111110111111110111111111011111111101111111110111111111011111";
    	$_Num[8]  = "1110000111110111101111011110111101111011111000011111101101111101111011110111101111011110111110000111";
    	$_Num[9]  = "1110001111110111011111011110111101111011110111001111100010111111111011111111101111011101111110001111";
    
    	echo chr(66).chr(77).chr(230).chr(4).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(54).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(10).chr(0).chr(0).chr(0).chr(1).chr(0);
    	echo chr(24).chr(0).chr(0).chr(0).chr(0).chr(0).chr(176).chr(4).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);
    
    	for ($i=9;$i>=0;$i--){
    		for ($j=0;$j<=3;$j++){
    			for ($k=1;$k<=10;$k++){
    				if(mt_rand(0,7)<1){
    					echo $Color[mt_rand(0,1)];
    				}else{
    					echo $Color[substr($_Num[$nmsg[$j]], $i * 10 + $k, 1)];
    				}
    			}
    		}
    	}
    }
}
else
{
	$code = new _08_Verification_Code(
        array('width' => $x_size, 'height' => $y_size, 'varname' => $verify, 'randString' => $nmsg)
    );
    $code->CreateImage();
}
exit;