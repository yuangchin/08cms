<?php
/**
 * 通过楼盘/二手房/出租房源名称获取对应的文档信息
 *
 * @example   请求范例URL：index.php?/ajax/front_archiveinfo/wid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Front_ArchiveInfo extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;
		header("Content-Type:text/html;CharSet=$mcharset");
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		$chid  = empty($this->_get['chid']) ? 0 : max(1,intval($this->_get['chid']));
        $aid  = empty($this->_get['aid']) ? '' : cls_string::iconv('utf-8',$mcharset,$this->_get['aid']);
        $limit  = empty($this->_get['limit']) ? 50 : max(1,intval($this->_get['limit']));

        if(!in_array($chid = max(1,intval($this->_get['chid'])),array(2,3,4))){
            echo "var data='请提供正确的文档模型ID';";
            exit();
        }

        if(empty($aid)){
            echo "var data='请提供正确的搜索关键字';";
            exit();
        }
        $data = array();
        $data = $this->getArchiveData($chid,$aid,$limit);
        $data = cls_string::iconv($mcharset, "UTF-8", $data);
        echo 'var data = ' . json_encode($data) . ';';
	}

    /**
     * 楼盘/二手房/出租中，根据文档模型字段查找对应的数据（不一定是全部的文档字段，会排除某些字段）
     * @param int    $chid     文档模型ID
     * @param string $aid      查询aid
     * @param int    $limit    限制查询条数
     * return array  array(字段0=>字段值0,字段1=>字段值1) 仅返回一个文档的数据
     */
    protected function getArchiveData($chid,$aid){
        $db = $this->_db;
        $tblprefix = $this->_tblprefix;
        $mconfigs = cls_cache::Read('mconfigs');
        //图片的域名
        $hostUrl = empty($mconfigs['ftp_enabled'])?$mconfigs['cms_abs']:$mconfigs['ftp_url'];
        //获取文档模型字段
        $archiveFields = cls_cache::Read('fields',$chid);
        //需要排除的字段
        $putAwayArr = array('subject','author','stpic','lphf','loupanlogo','dt','keywords','abstract','xqt','content','fdname','fdtel','fdnote','qqqun','xqjs','xqhs');
        //循环文档模型字段，将单选，多选的字段对应的选项组成数组，数组名以下划线+字段名命名
        foreach($archiveFields as $k => $v){
            $arr = array();
            $fieldArr = array();
            if(in_array($v['datatype'],array('select','mselect'))){
               $arr = explode("\n",$v['innertext']);
               foreach($arr as $key => $val){
                    $arr_sub = explode("=",$val);
                    $fieldArr[$arr_sub[0]] = $arr_sub[1];
               } 
               $_{$k} = $fieldArr;            
            }                      
        }   
        unset($arr,$fieldArr);        
    

        //搜索字段字符串
        $selectStr = 'SELECT subject,a.aid';
    	foreach($archiveFields as $k => $v){
    		if(!in_array($k, $putAwayArr)){
                $selectStr .= ','.$k;
    		}
    	}        
  
        $data = array();
       
        $row = $db->fetch_one(" $selectStr FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_".$chid." c ON a.aid = c.aid WHERE a.aid='$aid' LIMIT 1 ");            
        foreach($row as $k => $v){              
            if(!in_array($k,array_keys($archiveFields))) continue;
            $arr = array();
            if($archiveFields[$k]['datatype'] == 'select'){
                $data[$k] = empty($_{$k}[$v])?'-':$_{$k}[$v];
            }else if($archiveFields[$k]['datatype'] == 'mselect'){
                $filedStr = '';            
                $arr = explode(" ",$v);
                preg_match_all('/\S\s/isU',$v,$arr);           
                if(!empty($arr)){
                    foreach($arr[0] as $key=>$val){       
                        $val = max(0,intval($val));
                        if(empty($_{$k}[$val])) continue;
                        $filedStr .= ",".$_{$k}[$val];
                    }
                    $data[$k] = substr($filedStr,1);
                }         
            }else if($archiveFields[$k]['datatype'] == 'image'){
                $data[$k] = $hostUrl.$v;
            }else{
                $data[$k] = $v;
            }
        }  
        $data['aid'] = $row['aid'];
        $row = $db->fetch_one(" select * FROM {$tblprefix}".atbl($chid)." a  WHERE a.aid='$aid' LIMIT 1 ");
        $data['url'] = cls_url::view_arcurl($row);      
        return $data;
    }

}

?>