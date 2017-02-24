<?php
/**
 * 保存标签缓存（在标识还原时用到）
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/save_tag_cache/createrange/ddddd/fn/ddd/
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 * @since     nv50
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Save_Tag_Cache_Base extends _08_Models_Base
{
    public function __toString()
    {    	
        if($re = $this->_curuser->NoBackFunc('tpl')) cls_message::show($re);
        $fn = preg_replace('/[^\w]/', '', trim(@$this->_get['fn']));
        $createrange = @$this->_get['createrange'];
        if(in_array(true, array(empty($createrange), empty($fn)))) {
            exit('请先选中内容！');
        }
        
        _08_FileSystemPath::checkPath(_08_TEMP_TAG_CACHE, true);
        try {
            // 清空超过一小时的缓存文件
            $iterator = new DirectoryIterator(_08_TEMP_TAG_CACHE);
            $_file = _08_FilesystemFile::getInstance();
            foreach ($iterator as $file)
            {
                if(@$iterator->isFile($file) && ((time() - $iterator->getMTime()) >= 3600)) {
                    $_file->delFile($iterator->getPathname());
                }
            }
        } catch (RuntimeException $e) {
            die($e->getMessage());
        }
    
        $createrange = (array)cls_TagAdmin::CodeToTagArray($createrange);
    	cls_Array::array_stripslashes($createrange);//不存数据库，将转义取消
    	
        // 保存当前选中文本到缓存文件
        cls_CacheFile::cacSave($createrange, $fn, _08_TEMP_TAG_CACHE);
    }
}