<?php
/**
* 有关附件如图片等的处理方法
* 
*/
class cls_atm{
	
	
	
	/**
	 * 传入数据库格式图片，生成指定大小的缩略图，返回显示url
	 *
	 * @param  string  $dbstr    图片相对地址（数据库存储地址）
	 * @param  int  $width    指定缩略图宽度
	 * @param  int  $height    指定缩略图高度
	 * @param  int  $mode    缩略图生成方式，0最佳剪裁，1保留全图
	 * @param	int  $padding  补白方式 1 补白  0 不补白
	 * @return string  缩略图的显示url
	 */
	public static function thumb($dbstr,$width = 0,$height = 0,$mode = 0,$padding=0){
		//传入数据库格式，返回显示url，$mode为0最佳剪裁，1保留全图
		global $ftp_url;
		if(!($dbstr = str_replace(array('<!cmsurl />','<!ftpurl />'),'',$dbstr))) return '';
		if(!$width || !$height || !cls_url::islocal($dbstr,1)) return cls_url::tag2atm($dbstr);//远程图片不生成缩略图
		
		$isftp = cls_url::is_remote_atm($dbstr);
		$sourcefile = cls_url::local_atm($dbstr,1);//本地完整路径
		$thumbfile = cls_url::thumb_local($sourcefile,$width,$height);//本地完整路径
		$thumblogfile = str_replace('.jpg','.log',$thumbfile);//用于记录fpt是否生成了缩略图的本地标记文件
		$thumbview = cls_url::tag2atm(str_replace(M_ROOT,'',$thumbfile));
		
		//如果缩略图已经存在，则不再重复生成
		if(!file_exists($isftp ? $thumblogfile : $thumbfile)){
			if($isftp){
				include_once M_ROOT."include/http.cls.php";
				mmkdir($sourcefile,0,1);
				$m_http = new http;
				$m_http->savetofile($ftp_url.$dbstr,$sourcefile);
				unset($m_http);
			}
			$m_upload = cls_upload::OneInstance();
			$m_upload -> image_resize($sourcefile,$width,$height,$thumbfile,$mode,$padding);
			unset($m_upload);
			if($isftp){
				include_once M_ROOT."include/ftp.fun.php";
				$_ftp_re = ftp_upload($thumbfile,str_replace(M_ROOT,'',$thumbfile));
				$file = _08_FilesystemFile::getInstance();
				$file->delFile($sourcefile);
				$file->delFile($thumbfile);
				if($_ftp_re) @touch($thumblogfile);
			}
		}		
		return $thumbview;
	}
	
	/**
	 * 按原图的比例调整图片的大小
	 *
	 * @param  int  $width    原图宽度
	 * @param  int  $height    原图高度
	 * @param  int  $maxwidth    调整后的最大宽度
	 * @param  int  $maxheight   调整后的最大高度
	 * @return array('width' => 宽度,'height' => 高度)
	 */
	public static function ImageSizeKeepScale($width=0,$height=0,$maxwidth=0,$maxheight=0){
		if(!$width) $width = !$maxwidth ? '100' : $maxwidth;
		if(!$height) $height = !$maxheight ? '100' : $maxheight;
		$maxwidth = !$maxwidth ? $width : $maxwidth;
		$maxheight = !$maxheight ? $height : $maxheight;
		$size['width'] = $width;
		$size['height'] = $height;
		if($size['width'] > $maxwidth || $size['height'] > $maxheight) {
			$x_ratio = $maxwidth / $size['width'];
			$y_ratio = $maxheight / $size['height'];
			if(($x_ratio * $size['height']) < $maxheight) {
				$size['height'] = @ceil($x_ratio * $size['height']);
				$size['width'] = $maxwidth;
			} else {
				$size['width'] = @ceil($y_ratio * $size['width']);
				$size['height'] = $maxheight;
			}
		}
		return $size;
	}
	
	/**
	 * 说明：
	 *
	 * @param  array  &$item   
	 * @param  bool   $fmode  
	 * @return NULL   ---  
	 */
	public static function arr_image2mobile(&$item,$fmode = ''){return;//取消该方法
		/*
		$fmodearr = array(
		'' => array('fields','chid'),
		'f' => array('ffields','chid'),
		'm' => array('mfields','mchid'),
		'pa' => array('pafields','paid'),
		'ca' => array('cnfields',0),
		'cc' => array('cnfields','coid'),
		);
		if(!empty($fmodearr[$fmode])){
			$fields = @cls_cache::Read($fmodearr[$fmode][0],$fmodearr[$fmode][1] ? $item[$fmodearr[$fmode][1]] : 0);
			foreach($fields as $k => $v){
				if(isset($item[$k]) && $v['datatype'] == 'htmltext'){
					$item[$k] = self::image2mobile($item[$k]);
				}
			}
		}
		*/
	}
	
	/**
	 * 在手机版中将html中的图片转为适宜手机用的缩略图
	 *
	 * @param  string  $html    字段中的html内容字串
	 * @param  int  $maxwidth    调整后的最大宽度
	 * @param  int  $maxheight   调整后的最大高度
     * @patam  array   $imageLocalSize   返回原图的资料
	 */
	public static function image2mobile($html,$w){
		if(empty($html)) return '';
		if(preg_match_all("/(=\s*['\"]?)((<\!cmsurl \/>|<\!ftpurl \/>)(.+?))['\" >]/i",$html,$arr) && !empty($arr[2])){
			foreach($arr[2] as $v){
				if(($url = trim($v)) && preg_match("/\.(jpg|jpeg|gif|bmp|png)$/i",$url)){                  
                    # 设定图片宽度640，高度960，
                    # 比较原图宽高，以大的为主，然后根据设定的宽高按比例缩放
        		    $imageLocalPath = cls_url::local_atm(str_replace(array('<!cmsurl />','<!ftpurl />'),'',$url), true);					
                    $maxwidth = empty($w)?640:$w;//调整后的最大宽度
                    $maxheight = 960;//调整后的最大高度
                    if ( is_file($imageLocalPath) )
                    {
                        $imageLocalSize = @getimagesize($imageLocalPath);  
                        $size = self::ImageSizeKeepScale($imageLocalSize[0],$imageLocalSize[1],$maxwidth,$maxheight);
                        $maxwidth = $size['width'];
                        $maxheight = $size['height'];                        
                    }else{//远程附件不生产缩略图
						$maxwidth = 0;
						$maxheight = 0;
					}              
					$url = self::thumb($url, $maxwidth, $maxheight, 1);
					$html = str_replace($v,$url,$html);
				}
			}
		}
		return $html;
	}
    
    public static function atm_delete($dbstr,$type = 'image')
    {
    	//考虑图片缩略图的删除，及ftp上附件的删除
    	$dbstr = str_replace(array('<!cmsurl />','<!ftpurl />'),'',$dbstr);
    	if(!$dbstr || strpos($dbstr,':') !== false)	return;
    	$dir = dirname($dbstr);
    	if(strpos(realpath(M_ROOT.$dir),realpath(M_ROOT)) === false) return;//防止跳出本系统删除文件
    	$arr = array($dbstr);
    	if($type == 'image'){//查找图片的缩略图或ftp已有缩略图的.log
    		$str = substr(basename($dbstr),0,strrpos(basename($dbstr),'.'));//文件套用格式
    		if(strlen($str) < 5) return;//防止根据非法地址而误删除其它文件
    		$na = findfiles($dir,$str,1);
    		foreach($na as $k) in_array("$dir/$k",$arr) || $arr[] = "$dir/$k";
    		unset($na);
    	}
    	if($isftp = cls_url::is_remote_atm($dbstr)) include_once(M_ROOT."include/ftp.fun.php");
        $file = _08_FilesystemFile::getInstance();
    	foreach($arr as $k){
    		$ex = strtolower(mextension($k));
    		if(in_array($ex,array('php','js','css','xml','txt','htm','html'))) continue;
            $exts = array_keys(self::getLocalFilesExts($type));
    		if($isftp){
    			if($ex == 'log'){
    				ftp_del(str_replace('.log','.jpg',$k));
    				$file->delFile($k, $exts);
    			}else ftp_del($k);
    		}else 
            {
                $file->delFile($k, $exts);
            }
    	}
    }
    
    /**
     * 获取允许本地上传的类型
     */
    public static function getLocalFilesExts( $type )
    {
        if ( substr($type,-1) == 's' )
        {
            $type = substr($type,0,-1);
        }
        $_localfiles = array();
        $localfiles = cls_cache::Read('localfiles');
        if ( isset($localfiles[$type]) )
        {
            $localfiles = $localfiles[$type];
    		foreach($localfiles as $k => $v){
    			if(empty($v['islocal'])){
    				unset($localfiles[$k]);
    			}
    		}
            
            $_localfiles = $localfiles;
        }
        
        return $_localfiles;
    }


	/**
	 * 下载指定的本地附件
	 *	注意使用此方法前，请做好文件安全检查
	 * @param  string    $file      指定需要下载的文件(完全路径)
	 * @param  string    $filename  下载提示信息中的文件名(如：**下载文件名.扩展名)
	 * @return NULL      ---        无返回
	 */
    public static function Down($file, $filename = ''){
		if(!is_file($file)) return;
		$filename = $filename ? $filename : basename($file);
		$filetype = mextension($filename);
		$filesize = filesize($file);
		$timestamp = cls_env::GetG('timestamp');
		ob_end_clean();
		@set_time_limit(900);
		header('Cache-control: max-age=31536000');
		header('Expires: '.gmdate('D, d M Y H:i:s', $timestamp + 31536000).' GMT');
		header('Content-Encoding: none');
		header('Content-Length: '.$filesize);
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Type: '.$filetype);
		readfile($file);
		exit;
    }
	
	
}
