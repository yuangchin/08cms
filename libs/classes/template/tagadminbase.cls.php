<?php
/**
* 标签管理的操作类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_TagAdminBase{
	
	/**
	 * 字符串转成标签数组（代码转成非封装标识数据）
	 * 非属于setting元素值的元素：cname,ename,tclass,template,vieworder,disabled
	 * 注：$tagArr['tag_type']为解释的标签类型，non-encapsulated为非封装标识，encapsulated 为封装标识，其它自定义
	 *
	 * @param  string $string 要转换的字符串
	 * @return array  $tagArr 转换后的标签数据数组
	 */
	public static function CodeToTagArray($string)
	{
		$tagArr = array('old_str' => $string);
		if(empty($string)) return $tagArr;
		$index = array('cname', 'ename', 'tclass', 'template', 'vieworder', 'disabled');
		preg_match('@\{(u|c|p)\$([^\s]+?)(.*)\]\s*\}(.*)\{/\1\$\2\}@isU', $string, $matches);
		$tagArr['ename'] = @trim($matches[2]);
		if(!empty($matches[3]))
		{
			$matches[3] .= ']';
			preg_match_all('@\[(\w+)=(.*)/\]@isU', $matches[3], $setting);
			if(!empty($setting[1]) && !empty($setting[2]))
			{
				$tagArr['template'] = $matches[4];
				$len = count($setting[1]);
				for($i = 0; $i < $len; ++$i)
				{
					$k = trim($setting[1][$i]);
					$v = trim($setting[2][$i]);
					if(in_array($k, $index)) {
						$tagArr[$k] = $v;
					} else {
						$tagArr['setting'][$k] = $v;
					}
				}
				// 标识为非封装标识
				$tagArr['tag_type'] = 'non-encapsulated';
			}
		} else {
			// 兼容之前的封装标识
			if(preg_match("/\{(u|c|p)\\$(.+?)(\s|\})/is",$string,$matches))
			{
				$tagArr = cls_cache::Read($matches[1] . 'tag', $matches[2]);
				// 标识为封装标识
				$tagArr['tag_type'] = 'encapsulated';
				$tagArr['old_str'] = $string;
			}
		}
	
		return $tagArr;
	}
	
}
