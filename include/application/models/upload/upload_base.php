<?php
/**
 * 上传组件视图模型基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Upload_Base extends _08_Models_Base
{    
    protected $_userfiles = null;
    
    /**
     * 删除附件
     * 
     * @param string $ufid 要删除的图片ID，为了要兼容之前的图片所以当ID不为数字时不删除
     * @since nv50
     */
    public function delete( $ufid )
    {
//        if ( !is_numeric($ufid) ) return false;
//        
//        $row = $this->_userfiles->where(array('ufid' => $post['ufid']))->read('url', false);
//        if ( $row )
//        {
//            // 如果成功删除数据库信息则删除上传文件
//            if ( $this->_userfiles->delete() )
//            {
//                # 暂时不删除文件，以前图片会有问题
//               # cls_atm::atm_delete($row['url'], $this->type);
//            }
//        }
        
        return true;
    }
    
    /**
     * 通过文档表的aid与图片地址获取该图片的ufid
     * 
     * @param  int    $aid    当前文档ID
     * @param  string $imgurl 当前图片地址
     * @return int    $ufid   附件表ID
     * 
     * @since  nv50
     */
    public function getUFidForAid( $aid, $imgurl )
    {
        $ufid = $imgurl;
        $aid = (int) $aid;
        $this->_userfiles->select('ufid, url', true)->where(array('aid' => $aid))->exec();
        while($row = $this->_userfiles->fetch())
        {
            if ( $imgurl == $row['url'] )
            {
                $ufid = $row['ufid'];
                break;
            }
        }
        
        return $ufid;
    }
    
    /**
     * 通过ufid获取userfiles表数据
     * 
     * @reutn array $info 返回表数据
     * @since nv50
     */
    public function getInfoForUFid( $ufid, $fields = '*' )
    {
        $ufid = (int) $ufid;
        $info = $this->_userfiles->where(array('ufid' => $ufid))->read($fields);
        return $info;
    }
    
    public function __construct()
    {
        $this->_userfiles = parent::getModels('UserFiles_Table');
    }
}