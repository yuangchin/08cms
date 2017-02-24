<?php
/**
* 将模板(文件/标签/会员中心脚本)中的标签(区块标签/原始标签/复合标签)解释为可执行的PHP代码后，另存为PHP缓存文件
*/
class cls_Refresh{
	
	# 将一个来源(模板/标签/PHP文件)内的模板标签解释为可执行的PHP，存为PHP缓存文件并返回其名称
	public static function OneSource($ParseSource,$SourceType = 'tplname'){
		
		if(!($PHPCacheFileName = cls_Parse::PHPCacheFileName($ParseSource,$SourceType))) return false;
		switch($SourceType){
			case 'tplname':	# 解释普通模板
				$TplContent = cls_tpl::load($ParseSource);
				self::_OneSourceRefresh($TplContent,$PHPCacheFileName);
			break;
			case 'js':	# 动态JS标签解释
			case 'adv':	# 广告动态调用的标签解释
				self::_OneSourceRefresh($ParseSource,$PHPCacheFileName);
			break;
			case 'fragment':	# 碎片标签解释
				if(!empty($ParseSource['tclass'])){ # 复合标识
					self::_OneSourceRefresh($ParseSource,$PHPCacheFileName);
				}else{ # 区块标识中的template只是模板名称
					$TplContent = $ParseSource['template'];
					self::OneSource($TplContent);
				}
			break;
			case 'adminm': # 用于会员中心模板缓存，允许使用复合标识及区块标识
				_08_FilesystemFile::filterFileParam($ParseSource); # 目前只允许在会员中心的根目录下
				$sfile = MC_ROOTDIR.$ParseSource; # ?????常量定义需要调整
				$TplContent = @file2str($sfile);
				$TplContent = cls_tpl::ReplaceRtag($TplContent);
				self::_OneSourceRefresh($TplContent,$PHPCacheFileName);
			break;
		}
		return is_file($PHPCacheFileName) ? $PHPCacheFileName : false;
	}
	
	# 解释一个具体内容的来源。
	# 当$source为数组则来源为单个C标识，否则为页面模板字串
	# $SavePathFile:解释后内容的保存文件(完全路径)
	private static function _OneSourceRefresh($source,$SavePathFile){
		if(!$SavePathFile) return;
		if(!$source){
			str2file('',$SavePathFile);
		}else{
			SetRefreshVars();//初始化数组名堆栈
			if(is_array($source)){
				$str = self::_OneOpenCtag($source);
			}else{
				$str = self::_AllBtagsInStr($source);
				$str = self::_AllCtagsInStr($str);
			}
			$str = self::_AllPseudoCode($str);
			str2file($str,$SavePathFile);
		}
	}
	
	# 解释字串中所有的C标签
	private static function _AllCtagsInStr($str){
		if(!$str) return $str;
		$str = self::_CloseCtagsToOpenStr($str);//转换里面的封装标识=>开放标识
		$str = preg_replace("/\{c\\$(.+?)\s+(.*?)\{\/c\\$\\1\}/ies","self::_OneOpenCtagStr('\\1','\\2')",$str);
		return $str;
	}
	
	# 将字串中所有封装C标签转为字串格式的开放标签
	private static function _CloseCtagsToOpenStr($str){//将字符串中的封装标识=>开放标识
		if(!$str) return $str;
		$str = preg_replace("/\{c\\$([^\s]+?)\}/ies","self::_OneCloseCtagToOpenStr('\\1')",$str);
		return $str;
	}
	
	# 单个封装C标识=>字串格式的开放标签
	private static function _OneCloseCtagToOpenStr($tname){//单个封装标识=>开放标识
		if(!($tag = cls_cache::ReadTag('ctag',$tname)) || empty($tag['tclass'])) return '{Error_c_$'.$tname.'}';//变形一下，否则会重复处理
		$template = empty($tag['template']) ? '' : $tag['template'];
		$str = '{c$'.$tname;//起始符
		foreach(array('vieworder','template',) as $k) unset($tag[$k]);//去除$tag中的某些不需要用于解析的因素
		foreach($tag as $k => $v) $str .= ' ['.$k.'='.$v.'/]';
		$str .= "}";//参数中止
		$str .= self::_CloseCtagsToOpenStr($template);//递归处理模板内的封装标识
		$str .= '{/c$'.$tname.'}';//加入结束符
		return $str;
	}
	
	# 解释单个字串格式的开放型C标签
	private static function _OneOpenCtagStr($tname,$tstr){
		$tstr = RefreshStripSlashes($tstr);
		$tag = self::_OpenTagStrToConfig($tname,$tstr);
		if(empty($tag) || empty($tag['tclass'])) return '{Error_c_$'.$tname.'}';//非法标识只显示标识名
		return self::_OneOpenCtag($tag);
	}
	
	# 将单个字串格式C标签转为数组格式标签
	private static function _OpenTagStrToConfig($tname,$tstr){
		$tag = array();
		if(preg_match("/^\s*(.+?)\/\]\s*\}/is",$tstr,$matches)){
			if($str = $matches[0]){
				if(preg_match_all("/\[\s*(.+?)\s*\=\s*(.*?)\s*\/\]/is",$str, $matches)){
					$tag['ename'] = $tname;
					foreach($matches[1] as $k => $v) $tag[$v] = $matches[2][$k];
					$tag['template'] = preg_replace("/^\s*(.+?)\/\]\s*\}/is",'',$tstr);
				}
			}
		}
		return $tag;
	}
	
	# 解析单个(开放数组)复合标签
	private static function _OneOpenCtag($tag){
		if(empty($tag) || !is_array($tag) || !empty($tag['disabled'])) return '';
		$tname = $tag['ename'];
		$tc = $tag['tclass'];
		$val_var = empty($tag['val']) ? 'v' : $tag['val'];
		if(!in_array($tc,array_keys(cls_Tag::TagClass())) && !in_array($tc,array('advertising',))) return '';
		if(!empty($tag['js']) || !empty($tag['pmid'])){ # 如果设为js调用标签或标签内设置了权限方案，都解析为js
			
			# is_p参数作用：1)在js调用api是否要初始当前会员 2)是否需要js页面缓存
			if($tc == 'regcode'){
				$is_p = 1;
			}elseif($tc == 'member'){
				if(@$tag['id'] == -1){
					$is_p = 1;
				}
			}elseif(!empty($tag['pmid']) && in_array($tc,cls_Tag::TagClassByType('pmid'))){
				$is_p = 1;
			}
			
			# 重新生成名称
			$TagCacheName = substr(md5(var_export($tag,TRUE)),0,10);
			
			# 去掉js标记，将pmid另存，使得标签在js调用解析时，进入常规标签解析流程。
			if(!empty($tag['pmid'])) $tag['jspmid'] = $tag['pmid'];
			unset($tag['js'],$tag['pmid']);
						
			# 等同于生成模板缓存，但不能被更新模板缓存清空
			cls_CacheFile::cacSave($tag,'js_tag_'.$TagCacheName,cls_Parse::TplCacheDirFile('',2));
			
			$ReturnCode = '';
			if ( empty($tag['hidden']) )
			{
				$jsfile = 'tools/js.php?'.(empty($is_p) ? '' : 'is_p=1&').'tname='.$TagCacheName;
				$ReturnCode .= '<? $js_file=cls_Parse::$cms_abs.\''.$jsfile.'\';'.($tc == 'regcode' ? ' ?>' : 'if($_ActiveParams = cls_Parse::Get(\'_a\')) foreach($_ActiveParams as $_k_ => $_v_){ $_v_ && $js_file.= \'&data[\'.$_k_.\']=\'.rawurlencode($_v_);} ?>');
				$ReturnCode .= '<script type="text/javascript" src="<?=$js_file?>"></script><? unset($_k_,$_v_,$js_file);?>';
			}
			else
			{
                if (!empty($tag['jsVarname']))
                {
                    $jsVarname = preg_replace('/[^\w]/', '', $tag['jsVarname']);
                }
                
                if (empty($jsVarname))
                {
                    $jsVarname = '_08JSHidden';
                }
                
				$ReturnCode .= "<script type=\"text/javascript\"> var {$jsVarname} = '{$TagCacheName}'; </script>";
			}
					
			return $ReturnCode;
		}elseif(in_array($tc,array('regcode',))){//不定义$tag['val']
			$ReturnCode = '<? if(cls_Parse::Tag(array('.self::_CtagCongfigToStr($tag).'))){ ?>';
			$ReturnCode .= self::_AllBtagsInStr($tag['template']);
			$formIDStr = '';
			if ( preg_match('@_08_HTML::getCode.*\(.+, [\'|"](.*)[\'|"]@isU', $tag['template'], $formID) )
			{
				if(!empty($formID[1])){ //为空会有js错误
					$formIDStr .= '<script type="text/javascript"> if( !'.$formID[1].' ) { var ' . $formID[1] . ' = _08cms.validator(\'' . $formID[1] . '\'); } </script>';
				}
			}
			$ReturnCode .= '<? } else { echo "'.addcslashes($formIDStr, '"').'"; } ?>';
			return $ReturnCode;
		}elseif(in_array($tc,cls_Tag::TagClassByType('string'))){
			$ReturnCode = '<?=cls_Parse::Tag(array('.self::_CtagCongfigToStr($tag).'))?>';
			$ReturnCode .= '<?'.self::_ExtractMpConfig($tag).'?>';# 兼容目前的分页处理
			return $ReturnCode;
		}elseif(in_array($tc,cls_Tag::TagClassByType('list'))){
			$TagListResultVar = '_'.$tname;
			$t = self::_OuterOfRowBlock($tag['template']);
			$ReturnCode = '<? if($'.$TagListResultVar.'=cls_Parse::Tag(array('.self::_CtagCongfigToStr($tag).'))){';# 如果标签有内容->开始
			$ReturnCode .= self::_ExtractMpConfig($tag);# 兼容目前的分页处理
			$ReturnCode .= $t[1] ? '?>'.$t[1].'<? ' : '';
			$ReturnCode .= 'foreach($'.$TagListResultVar.' as $'.$val_var.'){ ';
			if($tc == 'advertising') $ReturnCode .= 'echo "<!--$'.$val_var.'[aid]-->";';
			$ReturnCode .= 'cls_Parse::Active($'.$val_var.');?>';//进入了具体的资料之后激活
			SetRefreshVars($val_var);//在循环之前处理数组名
			$t[2] = self::_AllBtagsInStr($t[2]);
			$t[2] = self::_AllCtagsInStr($t[2]);
			QuitRefreshVars();
			$ReturnCode .= $t[2];
			$ReturnCode .= '<? cls_Parse::ActiveBack();} unset($'.$TagListResultVar.',$'.$val_var.');?>';
			$ReturnCode .= $t[3];
			unset($t);
			$ReturnCode .= '<? }else{ '.self::_ExtractMpConfig($tag).' } ?>';# 如果标签有内容->结束
			return $ReturnCode;
		}else{
			$ReturnCode = '<? if($'.$val_var.'=cls_Parse::Tag(array('.self::_CtagCongfigToStr($tag).'))){';
			$ReturnCode .= 'cls_Parse::Active($'.$val_var.');?>';
			if(!empty($tag['jspmid']) && in_array($tc,cls_Tag::TagClassByType('pmid'))){
				$str = self::_TemplateByPmid($tag['template'],$tag['jspmid']);
			}else $str = $tag['template'];
			SetRefreshVars($val_var);
			$str = self::_AllBtagsInStr($str);
			$str = self::_AllCtagsInStr($str);
			QuitRefreshVars();
			
			$ReturnCode .= $str;
			unset($str);
			$ReturnCode .= '<? cls_Parse::ActiveBack();} unset($'.$val_var.');?>';
			return $ReturnCode;
		}
	}
	# 兼容目前的分页处理(将分页信息$_mp中的变量作为原始信息来处理，在主页面extract)
	private static function _ExtractMpConfig($tag){
		if(!in_array($tag['tclass'],cls_Tag::TagClassByType('mp')) || empty($tag['mp'])) return '';
		return "extract(cls_Parse::Get('_mp'),EXTR_OVERWRITE);";
	}
	
	# 根据权限展示不同内容的模板
	private static function _TemplateByPmid($template,$pmid=0){
		if(!$pmid) return;
		$arr = explode('[#pm#]',$template);
		return '<? if(cls_Parse::Pm('.$pmid.')){ ?>'.$arr[0].'<? }else{ ?>'.(empty($arr[1]) ? 'NoPermission' : $arr[1]).'<? } ?>';
	}
	
	# 将单个数组格式C标签拼装成字串格式，模拟PHP函数传参
	private static function _CtagCongfigToStr($tag){
		$re = '';
		foreach($tag as $k => $v){
			if(!in_array($k,array('cname','val','template','vieworder','disabled',))){
				if(in_array($k,array('tname','color')) && preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/is",$v)){
					//tname或color中直接输入变量名xxx来提取上层标识的数据：$v['xxx']，这是一个特别处理环节。
					$v = self::_TagResultVar() ? '$'.self::_TagResultVar().'['.$v.']' : '$'.$v; 
				}
				$re .= is_numeric($v) ? "'$k'=>$v," : "'$k'=>\"$v\",";
			}
		}
		return $re;
	}
	
	# 解释字串中的原始标签(排除内嵌的复合标签或PHP代码中的原始标签)
	private static function _AllBtagsInStr($str){
		if(!$str) return $str;
		$hiddens = self::_HiddenInnerCode($str);
		/*$str = preg_replace("/<\\?(?!php\\s|=|\\s)/i", '<?=\'<?\'?>', $str);//处理<?xml 等非PHP标记*/
		$str = str_replace('{else}','{else }',$str);//伪代码中的{else}避开原始标识格式
		$str = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/is",self::_TagResultVar() ? '{$'.self::_TagResultVar().'[\\1]}' : '{$\\1}', $str);//将{xxx}换成{$v[xxx]}或{$xxx}，从当前资料取值
		$str = preg_replace("/\{((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)\}/es", "self::_AddQuote('<?=\\1?>')", $str);/*将{$xxx}转为<?=$xxx?>*/
		$str = self::_ViewInnerCode($str,$hiddens);
		return $str;
	}
	
	# 处理[row]***[/row]区块之外的部分模板中标签
	# 不含row则默认全部为row区块，row区块内部分用于列表循环，不在这里处理
	# 因为区块外内容的存在，即使单个标识，也需要初始化数组名堆栈
	# 返回array(1 => row区块前的部分,2 => row内部区块,3 => row区块后的部分)
	private static function _OuterOfRowBlock($str){
		if(!$str) return $str;
		$hiddens = self::_HiddenInnerCode($str);
		$narr = array(1 => '',2 => $str,3 => '',);
		if(preg_match("/^(.*?)\[row\](.*)\[\/row\](.*?)$/is",$str,$matches)){
			unset($matches[0]);
			foreach($matches as $x => $y){
				if($y){
					$y = self::_ViewInnerCode($y,$hiddens);
					if($x != 2){
						$y = self::_AllBtagsInStr($y);
						$y = self::_AllCtagsInStr($y);
					}
					$narr[$x] = $y;
				}
			}
		}else $narr[2] = self::_ViewInnerCode($narr[2],$hiddens);
		return $narr;
	}
	
	# 将字串中的复合标识及PHP代码先隐藏并暂存起来
	private static function _HiddenInnerCode(&$str){ # 注意使用引用传参
		$na = array(
			'TAG' => "/\{c\\$(\w+)\s+(.*?)\{\/c\\$\\1\}/is",
			'PHP' => "/<\\?(php|=|\\s)(.*?)($|\\?>)/is",
		);
		$re = array();
		foreach($na as $k => $v){
			if(preg_match_all($v,$str,$matches)){//只处理非封装标识
				$re[$k] = $matches[0];
				$re[$k] = RefreshMultisort($re[$k]);
				foreach($re[$k] as $kk => $vv) $str = str_replace($vv,"_{$k}_{$kk}_",$str);
			}
		}
		return $re;
	}
	# 恢复字串中隐藏的复合标识及PHP代码
	private static function _ViewInnerCode($str,$arr){
		if(!$str || !$arr) return $str;
		foreach(array('PHP','TAG') as $k){
			if(!empty($arr[$k])){
				foreach($arr[$k] as $kk => $vv) $str = str_replace("_{$k}_{$kk}_",$vv,$str);
			}
		}
		return $str;
	}
	
	# 伪代码处理，支持：{if}、{else}、{loop}、{/if}、{/loop}
	private static function _AllPseudoCode($str){
		$str = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "self::_stripvtags('<? echo \\1; ?>','')", $str);
		$str = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/ies", "self::_stripvtags('\\1<? if(\\2) { ?>\\3','')", $str);
		$str = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "self::_stripvtags('\\1<? } elseif(\\2) { ?>\\3','')", $str);
		$str = preg_replace("/\{else\s*\}/i", "<? } else { ?>", $str);
		$str = preg_replace("/\{\/if\s*\}/i", "<? } ?>", $str);
		$str = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "self::_stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2) { ?>','')", $str);
		$str = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "self::_stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>','')", $str);
		$str = preg_replace("/\{\/loop\s*\}/i", "<? } ?>", $str);
		/*$str = preg_replace("/\{\?(.*?)\?\}/is", "<?\\1?>", $str);//????*/
		$str = preg_replace("/\{\\\$ (.*?)\}/is", "{\$\\1}", $str);//兼容之前版本中在php中使用的{$ xx}，置换成{$xx}
		return $str;
	}
	private static function _AddQuote($var){
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
	}
	private static function _stripvtags($expr, $statement) {
		$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
		$statement = str_replace("\\\"", "\"", $statement);
		return $expr.$statement;
	}
	
	# 当前标签的单条记录数组名(标签中的val)，不属任何标签时为''
	private static function _TagResultVar(){
		return cls_env::GetG('_a_var');
	}
		
	
}
