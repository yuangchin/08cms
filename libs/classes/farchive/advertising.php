<?php
/**
 * 广告处理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */

class _08_Advertising
{
    /**
     * 刷新广告模板解析缓存
     *
     * @param  string	$fcaid 广告位ID
     * @return bool 	清空成功返回TRUE，否则返回FALSE
     * @static
     * @since  1.0
     */
    public static function cleanTag($fcaid)
    {
		$tpl_cache = cls_Parse::TplCacheDirFile('adv_' . $fcaid . '.php');
		$file = _08_FilesystemFile::getInstance();
        $file->delFile($tpl_cache);
        $content_cache = M_ROOT . 'dynamic.adv_cache.adv_' . $fcaid;
        _08_FileSystemPath::checkPath($content_cache, true);
        return $file->cleanPathFile($content_cache, 'php');
    }

    /**
     * 刷新所有广告标签缓存
     *
     * @static
     * @since  1.0
     */
    public static function cheanAllCache()
    {
		$fcatalogs = cls_fcatalog::InitialInfoArray();
		foreach($fcatalogs as $k => $v){
			self::cleanTag($k);
		}
    }

    /**
     * 设置广告展示数
     *
     * @param string $content 要获取的广告内容模板
     * @since 1.0
     */
    public function setViews($viewscachefile)
    {
    	global $db,$tblprefix;
        if(empty($viewscachefile) || !is_file($viewscachefile)) return false;
        $file = _08_FilesystemFile::getInstance();
        $file->_fopen($viewscachefile, 'rb');
        if( $file->_flock(LOCK_SH) )
        {
            $ids = $file->_fread();
            $file->_flock(LOCK_UN);
        }
        if(empty($ids)) return false;
        // 因用了文件里的第一个逗号前的数来代表文件创建时间，所以处理数据库时需要过滤该值
        $ids = array_reverse(array_count_values(explode(',', $ids)), true);
        array_pop($ids);
        if(is_array($ids))
        {
            foreach($ids as $aid => $count)
            {
                $aid = (int)$aid;
                $db->query("
                    UPDATE `{$tblprefix}farchives`
                    SET `views` = `views` + {$count}
                    WHERE `aid` = {$aid}"
                );
            }
        }
    }

    /**
     * 获取广告展示ID
     *
     * @param  string $content 要获取的广告模板
     * @return array           返回广告ID数组
     * @static
     * @since  1.0
     */
    public static function getViews($content)
    {
        if ( preg_match_all('@<!--(\d+)-->@isU', $content, $views) )
        {
            return $views[1];
        }
        else {
            return array();
        }
    }

    /**
     * 设置某个附件分类信息
     *
     * @param  array $adv_config 要设置的参数
     * @return bool              设置成功返回TRUE，否则返回FALSE
     */
    public static function setFcatalog(array $adv_config, $fcaid)
    {
		return cls_fcatalog::ModifyOneConfig($fcaid,$adv_config,false) ? true : false;
    }

    /**
     * 获取广告配置原始信息(按数据源读取)
     *
     * @param  int   $fcaid 广告ID
     * @return array        返回获取到的广告信息数组，如果获取不成功则返回FALSE
     * @since  1.0
     */
    public static function getAdvConfig($fcaid)
    {
		$re = cls_fcatalog::InitialOneInfo($fcaid);
		if(empty($re) || empty($re['ftype'])){
			return false;
		}else return $re;
    }
	
    /**
     * 删除一个副件分类时，清除广告位的相关因素
     *
     * @param  int		$fcaid		广告位ID
     * @return bool		设置成功返回TRUE，否则返回FALSE
     */
    public static function DelOneAdv($fcaid){
		$fcaid = cls_fcatalog::InitID($fcaid);
		self::cleanTag($fcaid);
		cls_CacheFile::Del('advtag',"adv_$fcaid");
		return true;
    }
    /**
     * 复制广告位的模板标签到另一广告位
     *
     * @param  int		$fromID 来源广告位ID
     * @param  int		$toID 目的广告位ID
     * @return bool		设置成功返回TRUE，否则返回FALSE
     */
    public static function AdvTagCopy($fromID,$toID){
		if(!(self::getAdvConfig($fromID))){
			throw new Exception('广告模板标签复制失败：来源广告位不存在。');
		}
		if(!(self::getAdvConfig($toID))){
			throw new Exception('广告模板标签复制失败：目的广告位不存在。');
		}
		if($tag = cls_cache::Read('advtag',"adv_$fromID")){
			$tag['ename'] = "adv_$toID";
			$tag['setting']['casource'] = $toID;
			cls_CacheFile::Save($tag,cls_cache::CacheKey('advtag',$tag['ename']),'advtag');
		}else{
			throw new Exception('广告模板标签复制失败：未找到来源标签。');
		}
		return true;
    }

    /**
     * 设置广告模板配置缓存
     *
     * @param  array $mtagnew 广告配置
     * @return bool           设置成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public static function setAdvCache(array $mtagnew)
    {
        global $unsetvars, $unsetvars1, $fcaid, $ttype, $tclass, $iscopy;
        if(!is_array($unsetvars) || !is_array($unsetvars1)) return false;
		$fcaid = cls_fcatalog::InitID($fcaid);
        try {
    		$mtagnew['setting'] = empty($mtagnew['setting']) ? array() : $mtagnew['setting'];
    		if(!empty($mtagnew['setting'])){
    			foreach($mtagnew['setting'] as $key => $val){
    				if(in_array($key,$unsetvars) && empty($val)) unset($mtagnew['setting'][$key]);
    				if(!empty($unsetvars1[$key]) && in_array($val,$unsetvars1[$key])) unset($mtagnew['setting'][$key]);
    			}
    		}
    		$mtagnew['template'] = empty($mtagnew['template']) ? '' : stripslashes($mtagnew['template']);
    		$mtagnew['disabled'] = $iscopy || empty($mtag['disabled']) ? 0 : 1;
    		$mtag = array(
        		'ename' => $mtagnew['ename'],
        		'tclass' => $tclass,
        		'template' => $mtagnew['template'],
        		'setting' => $mtagnew['setting']
    		);
    		cls_CacheFile::Save($mtag,cls_cache::CacheKey($ttype,$mtagnew['ename']),$ttype);
            self::cleanTag($fcaid);
#            cls_CacheFile::Update('fcatalogs');
            return true;
        } catch (Exception $error) {
            return false;
        }
    }

    /**
     * 显示复制按钮
     * 运用该方法必须加入JQ库，并且定义一个返回函数 ：$.closeClipBoard，类似于：
     * $.closeClipBoard = function() {
     *     alert('复制成功！');
     * }
     *
     * @param  string $value  要复制的值，外部传递时请用base64_encode(rawurlencode($value))编码下，并尽量使用POST方法
     * @param  bool   $type   如果显示完整的按钮请设置为TRUE，否则只显示“复制”按钮
     * @return string $string 返回带值的复制按钮HTML代码
     *
     * @since  1.0
     */
    public static function showCopyCode($value, $id = 'flashcopier')
    {
        global $cms_abs, $mcharset;
        $value = base64_decode($value);
        // 如果当前编辑不是UTF编码时转换成UTF8
        if(false === stripos($mcharset, 'UTF'))
        {
            $value = rawurlencode(cls_string::iconv($mcharset, 'UTF-8', rawurldecode($value)));
        }
        $string = '
            <div id="' . $id . '" class="flashcopier">
                <span style="float:left;">(</span> <div class="flashcopier_div">
                    <object id="' . $id . '_flash" height="120" width="166" type="application/x-shockwave-flash" data="' . $cms_abs . 'images/common/copy.swf" class="flashcopier_flash">
                        <param value="always" name="allowScriptAccess">
                        <param value="url=' . $value . '" name="flashvars">
                        <param value="' . $cms_abs . 'images/common/copy.swf" name="movie">
                        <param value="opaque" name="wmode">
                        <param value="high" name="quality">
                        <div>
                            <h4>页面需要新版Adobe Flash Player.</h4>
                            <p>
                                <a target="_blank" href="http://www.adobe.com/go/getflashplayer">
                                    <img height="33" width="112" src="' . $cms_abs . 'images/common/get_flash_player.gif" alt="获取新版Flash">
                                </a>
                            </p>
                        </div>
                    </object>
                </div> <span style="float:left;">)</span>
            </div>
        ';
        return $string;
    }
}