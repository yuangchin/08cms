<?php
/**
 * JSON文档处理类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_Documents_JSON
{
    /**
     * 把数据转成JSON格式（让支持中文显示）
     * 
     * @param  mixed $datas         要转换的数据
     * @param  bool  $restoreCoding 还原编码，如果为TRUE时把数据还原回原来数据编码
     * @param  bool  $conversion    是否转换中文，TRUE为转换，FALSE为不转换，注：用JQuery的getJSON方法时请不要转换
     * @return mixed                返回转换后的JSON格式数据
     * 
     * @since  nv50
     */
    public static function encode( $datas, $restoreCoding = false, $conversion = true )
    {
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        $datas = cls_string::iconv($mcharset, 'UTF-8', $datas);
        
        if (version_compare(PHP_VERSION, '5.4.0') >= 0)
        {
            $datas = json_encode($datas, JSON_UNESCAPED_UNICODE);
        }
        else
        {
            if ($conversion)
            {
                $datas = cls_url::encode( $datas );
            }
            
            $datas = json_encode($datas);
            if ($conversion)
            {
                $datas = cls_url::decode( $datas );
            }
        }
        
        if ($restoreCoding)
        {
            $datas = cls_string::iconv('UTF-8', $mcharset, $datas);
        }
        
        return $datas;
    }
    
    /**
     * 对已经用{@see self::encode}转换过的JSON数据进行还原
     * 
     * @param  string $json_datas 要还原的JSON数据
     * @param  bool   $assoc      当该参数为 TRUE 时，将返回 array 而非 object
     * @return mixed              返回还原后的JSON数据
     * @since  nv50
     */
    public static function decode( $json_datas, $assoc = true )
    {
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
        $json_datas = cls_string::iconv($mcharset, 'UTF-8', $json_datas);        
        $json_datas = json_decode($json_datas, $assoc);        
        $json_datas = cls_string::iconv('UTF-8', $mcharset, $json_datas);
        
        return $json_datas;
    }
}