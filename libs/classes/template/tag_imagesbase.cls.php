<?PHP
/**
* [图片列表/单个图片] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_ImagesBase extends cls_TagParse{
	
	
	# 返回数据结果
	protected function TagReSult(){
		if($this->tag['tclass'] == 'images'){
			return $this->TagAtmArray();
		}else{
			return $this->TagOneAtm();
		}
	}
	
	protected function TagAtmArray(){
		$ReturnArray = array();		
		$AtmArray = @array_slice(unserialize($this->tag['tname']),$this->TagInitStart(),$this->TagInitLimits(),TRUE);		
		if(!empty($AtmArray)){
			foreach($AtmArray as $k => $v){
				$v['fid'] = $k;
				$v['sn_row'] = $i = empty($i) ? 1 : ++ $i;
                $v['link'] = empty($v['link']) ? '' : $v['link'];				
				$ReturnArray[] = array_merge($v,$this->TagImageParse($v['remote'],@$v['width'],@$v['height'],@$v['link']));				
			}
		}			
		return $ReturnArray;
	}
	
	protected function TagOneAtm(){
		$TempArray = @explode('#',$this->tag['tname']);
		return $this->TagImageParse(@$TempArray[0],@$TempArray[1],@$TempArray[2]);
	}
	
	protected function TagImageParse($UrlFormDB,$w,$h){//$UrlFormDB为数据库储存字串
		$UrlFormDB = str_replace(array('<!cmsurl />','<!ftpurl />'),'',$UrlFormDB);//兼容以前带ftp标记的储存格式
		$ReturnArray = array('url' => cls_url::tag2atm($UrlFormDB));	
		foreach(array('width','height') as $x) {
			$ReturnArray[$x] = @$this->tag['max'.$x];		
		}					
		if(!empty($UrlFormDB)){
		    # 如果图片标签里只填了宽度或高度时自动计算出等比例的高度或宽度。
		    $imageLocalPath = cls_url::local_atm($UrlFormDB, true);
            if ( is_file($imageLocalPath) )
            {
                $imageLocalSize = @getimagesize($imageLocalPath);				
				/*
				1.保留完整图片,补白.(maxheight,maxwidth,padding=1,thumb=2) 
				2.保留完整图片,不补白(maxheight,maxwidth,padding=0或不填,thumb=2)
				5.瀑布流方式.补白 (maxheight||maxwidth,padding=1或不填) 
				6.瀑布流方式.不补白 (maxheight||maxwidth,padding=0 或不填) 
				*/
				if( !empty($imageLocalSize[0]) && !empty($imageLocalSize[1])){
					if(@$this->tag['maxwidth'] && @$this->tag['maxheight']){					   
						if(!isset($this->tag['padding']) || @$this->tag['padding']){	//补白		
							$ReturnArray['height'] = $this->tag['maxheight'];
							$ReturnArray['width'] = $this->tag['maxwidth'];
						}else{		//不补白
							if($imageLocalSize[0]<$this->tag['maxwidth']&&$imageLocalSize[1]<$this->tag['maxheight']){
								$ReturnArray['width'] = $imageLocalSize[0];
								$ReturnArray['height'] = $imageLocalSize[1];
							}else{
                            if(isset($this->tag['thumb']) && $this->tag['thumb']==1){//最佳化裁剪默认启用补白
                                 $this->tag['padding'] = 1;
                                 $ReturnArray['width'] = $this->tag['maxwidth'];
                                 $ReturnArray['height'] = $this->tag['maxheight'];
                            }else{
                                $radio = $imageLocalSize[0]/$imageLocalSize[1];
								$tag_radio = $this->tag['maxwidth']/$this->tag['maxheight'];
								if($radio>$tag_radio){
									$ReturnArray['width'] = $this->tag['maxwidth'];
									$ReturnArray['height'] = ceil($ReturnArray['width']/$radio);
								}else{								
									$ReturnArray['height'] = $this->tag['maxheight'];
									$ReturnArray['width'] = ceil($ReturnArray['height']*$radio);
								}
                            }     	
							}
						}                        
					}elseif(@$this->tag['maxheight']){
						$this->tag['thumb'] = 2;
						if(!isset($this->tag['padding']) || @$this->tag['padding']){//补白
							if($imageLocalSize[1]<=$this->tag['maxheight']){
								$ReturnArray['height'] = $this->tag['maxheight'];
								$ReturnArray['width'] = $imageLocalSize[0];
							}else{
								$ReturnArray['height'] = $this->tag['maxheight'];
								$ReturnArray['width'] = $ReturnArray['height'] * $imageLocalSize[0]/$imageLocalSize[1];
							}	
						}else{//不补白						
							if($imageLocalSize[1]<=$this->tag['maxheight']){
								$ReturnArray['height'] = $imageLocalSize[1];
								$ReturnArray['width'] = $imageLocalSize[0];
							}else{
								$ReturnArray['height'] = $this->tag['maxheight'];
								$ReturnArray['width'] = $ReturnArray['height']*$imageLocalSize[0]/$imageLocalSize[1];
							}
						}	
					}elseif(@$this->tag['maxwidth']){						
						$this->tag['thumb'] = 2;
						if(!isset($this->tag['padding']) || @$this->tag['padding']){//补白
							if($imageLocalSize[0]<=$this->tag['maxwidth']){
								$ReturnArray['width'] = $this->tag['maxwidth'];
								$ReturnArray['height'] = $imageLocalSize[1];
							}else{
								$ReturnArray['width'] = $this->tag['maxwidth'];
								$ReturnArray['height'] = $ReturnArray['width']*$imageLocalSize[1]/$imageLocalSize[0];
							}
						}else{//不补白
							if($imageLocalSize[0]<=$this->tag['maxwidth']){
								$ReturnArray['width'] = $imageLocalSize[0];
								$ReturnArray['height'] = $imageLocalSize[1];
							}else{
								$ReturnArray['width'] = $this->tag['maxwidth'];
								$ReturnArray['height'] = $ReturnArray['width']*$imageLocalSize[1]/$imageLocalSize[0];
							}
						}
					}
				}
            }
            
			if(@$this->tag['thumb'] && @$ReturnArray['width'] && @$ReturnArray['height'] && cls_url::islocal($UrlFormDB,1)){
				$ReturnArray['url_s'] = cls_atm::thumb($UrlFormDB,@$ReturnArray['width'],@$ReturnArray['height'],@$this->tag['thumb'],(!isset($this->tag['padding']) || @$this->tag['padding'])?1:0);
			}else{
				$ReturnArray['url_s'] = $ReturnArray['url'];
				$wh = cls_atm::ImageSizeKeepScale($w,$h,@$ReturnArray['width'],@$ReturnArray['height']);
				foreach(array('width','height') as $x) $ReturnArray[$x] = $wh[$x];
			}
		}elseif(!empty($this->tag['emptyurl'])){
			$ReturnArray['url'] = $ReturnArray['url_s'] = cls_url::tag2atm($this->tag['emptyurl']);
		}
		foreach(array('url','url_s') as $k){
			if(empty($ReturnArray[$k])){
				$ReturnArray[$k] = cls_url::tag2atm('images/common/nopic.gif');
			}
		}
		return $ReturnArray;
	}
	
	# 分页处理self::$_mp['acount']等不同类型标签的差异化部分
	protected function TagCustomMpInfo(){
		if($TempArray = @unserialize($this->tag['tname'])){
			self::$_mp['acount'] = count($TempArray);
		
		}
	}
	
}
