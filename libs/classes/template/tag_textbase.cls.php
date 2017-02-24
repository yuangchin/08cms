<?PHP
/**
* [文本处理] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_TextBase extends cls_TagParse{
	
	protected $_TextContent = '';			# 当页的文本内容
	
	protected function TagReSult(){
		return $this->TagResultText();
	}
	
	# 初始化当前标签
	protected function _TagInit(){
		$this->_TextContent = $this->tag['tname'];
		if(!$this->_TextContent) return '';
		unset($this->tag['tname']);
	}
	
	protected function TagResultText(){
		if($this->_TextContent){
			if(!empty($this->tag['dealhtml'])){
				switch($this->tag['dealhtml']){
				case 'clearhtml':
                    if (isset($this->tag['dealhtml_tags']))
                    {
                        if (is_string($this->tag['dealhtml_tags']))
                        {
                             $this->tag['dealhtml_tags'] = explode('|', $this->tag['dealhtml_tags']);
                             $this->tag['dealhtml_tags'] = array_fill_keys($this->tag['dealhtml_tags'], array('on'));
                        }
                        $tags = array_map('strtolower', array_keys($this->tag['dealhtml_tags']));
                        if (isset($tags['all']))
                        {
                            $this->_TextContent = cls_string::HtmlClear($this->_TextContent);
                        }
                        elseif(!empty($tags))
                        {
                        	$textContentInstance = _08_Documents_HTML::getInstance($this->_TextContent);
                            $this->_TextContent = $textContentInstance->pQuery($tags)->remove();
                        }
                    }
                    else # 暂时保留以作兼容，以免影响客户升级
                    {
                    	$this->_TextContent = cls_string::HtmlClear($this->_TextContent);
                    }
					
					break;
				case 'disablehtml':
					$this->_TextContent = mhtmlspecialchars($this->_TextContent);
					break;
				case 'safehtml':
					$this->_TextContent = cls_string::SafeStr($this->_TextContent);
					break;
                // 暂时保留以作兼容
				case 'html_cleara': //仅删除超链接(+保护性过滤Html)
					$this->_TextContent = cls_string::SafeStr($this->_TextContent);
					//$this->_TextContent = preg_replace('/(<a).+>(.)+</a>/i',"\${1}",$this->_TextContent);
					$this->_TextContent = preg_replace("/<a [^>]*>|<\/a>/i","",$this->_TextContent);
					break;
				case 'html_decode':
					$this->_TextContent = cls_env::deRepGlobalValue($this->_TextContent);
					break;
				//case 'html_keepa': //仅保留超链接(启用时再完善)
					//$this->_TextContent = strip_tags($this->_TextContent, "<a>");
					//$this->_TextContent = nl2br($this->_TextContent); // 参考cls_string::HtmlClear($str)
					//break;
				}
			}
			if(!empty($this->tag['trim'])) $this->_TextContent = cls_string::CutStr($this->_TextContent,$this->tag['trim'],empty($this->tag['ellip']) ? '' : $this->tag['ellip']);
			if(!empty($this->tag['color'])) $this->_TextContent = "<font color='".$this->tag['color']."'>".$this->_TextContent."</font>";
			if(!empty($this->tag['badword'])) cls_Tag::BadWord($this->_TextContent);
			if(!empty($this->tag['wordlink'])) cls_Tag::WordLink($this->_TextContent);
			if(!empty($this->tag['face'])) cls_Tag::Face($this->_TextContent);
			if(!empty($this->tag['nl2br'])) $this->_TextContent = mnl2br($this->_TextContent);
			if(!empty($this->tag['randstr'])){
				$this->_TextContent = preg_replace("/\<br\s?\/\>/ie", "cls_Tag::RandStr(0)", $this->_TextContent);
				$this->_TextContent = preg_replace("/\<\/p\>/ie", "cls_Tag::RandStr(1)", $this->_TextContent); // </p>
				$this->_TextContent = preg_replace("/\<p\>/ie", "cls_Tag::RandStr(2)", $this->_TextContent); // <p>
			}
			if(!empty($this->tag['injs'])) $this->_TextContent = addcslashes($this->_TextContent, "'\\\r\n");
			// 注意 url地址中的<!cmsurl />','<!ftpurl /> 可能影响正则,可放到tag2atm后面
			// 如果图片url不用引号界定,则会出错,但测试编辑器会自动加上双引号
			if(!empty($this->tag['noimgwh'])){ 
				$reg = "/(<img.*?)(src=[\"|']?([^\"|\']{1,255})[\"|']?).*?([^>]+>)/is";
				//preg_match_all($reg,$this->_TextContent,$ma); print_r($ma);
				$this->_TextContent = preg_replace($reg,'<img $2 />',$this->_TextContent);
			}
			if(defined('IN_MOBILE')){				
				$this->_TextContent = cls_atm::image2mobile($this->_TextContent,@$this->tag['maxwidth']);
				$this->_TextContent = cls_url::tag2atm($this->_TextContent,1);
			}

			
		}
		// 标签不需分页,则清除已有分页标记
		if(empty($this->tag['mp'])){
			$this->_TextContent = preg_replace('/\[#.*?#\]/','',$this->_TextContent);
		} //var_dump($this->tag['mp']);
		return $this->_TextContent;
	}
	
	
	
	# 分页处理的不同类型的差异部分
	protected function TagCustomMpInfo(){
		
		self::$_mp['limits'] = 1;
		if($bodysarr = SplitHtml2MpArray($this->_TextContent)){
			$i = 0;
			foreach($bodysarr as $k => $v){
				if(!($k % 2) && !preg_match("/^[\s|　| |\&nbsp;|<p>|<\/p>|<br \/>]*$/is",$v)){
					$i++;
					self::$_mp['titles'][$i] = isset($bodysarr[$k-1]) ? $bodysarr[$k-1] : '';
					if($i == self::$_mp['nowpage']){
						$this->_TextContent = $v.'</p>';
					}
				}
			}
			if($i) self::$_mp['acount'] = $i;
			if(self::$_mp['nowpage'] > $i) $this->_TextContent = '';
		}
		if(isset(self::$_mp['titles'][self::$_mp['nowpage']])){
			self::$_mp['mptitle'] = self::$_mp['titles'][self::$_mp['nowpage']];
		}
		unset($bodysarr);
	}
	
	
}
