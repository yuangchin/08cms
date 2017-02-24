<?php
/**
 * 展示统计数
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_View_Count_Base extends _08_Models_Base
{
    /**
     * 查询条件ID
     * 
     * @var int
     */
    protected $infoid = 0;
    
    /**
     * 模型ID
     * 
     * @var int
     */
    protected $modid = 0;
    
    /**
     * 当前操作的类型
     * 
     * @var string
     */
    protected $_type;
    
    /**
     * 字段白名单，只有在白名单的字段才能获取统计数
     * 
     * @var array
     */
    protected $whiteList = array(
        'a' => array('clicks', 'wclicks', 'mclicks'),
        'm' => array('msclicks'),
        'cu' => array('aid', 'ccid', 'tomid', 'mid')
    );
    
    /**
     * 统计数字段名称
     * 
     * @var string
     */
    protected $field = '';
    
    protected $parmas = array();
    
    /**
     * 注：该方法不能被重写
     */
    final public function __toString()
    {
        defined( 'M_NOUSER' ) || define( 'M_NOUSER', 1 );
        
        $this->infoid = (empty($this->infoid) ? 0 : max(0,intval($this->infoid)));	
        $this->modid = (empty($this->_get['modid']) ? 0 : max(0,intval($this->_get['modid'])));	
        $this->field = (empty($this->_get['field']) ? '' : preg_replace('/[^\w]/', '', $this->_get['field']));

        if(empty($this->_get['type'])) {
            $this->_type = 'a'; 
        } else {
            $this->_type = strtolower(trim($this->_get['type']));
        }
        $method = "_{$this->_type}Statistics";
        
        if( (empty($this->infoid) && ($this->_type !== 'cu')) || empty($this->modid) || empty($this->field) || !method_exists($this, $method) )
        {
            return '0';
        }
        
        $fieldWhiteList = "{$this->_type}FieldWhiteList";
        
        if ( method_exists($this, $fieldWhiteList) )
        {
            foreach ( (array) $this->$fieldWhiteList() as $value ) 
            {
                if ( !in_array($value, $this->whiteList[$this->_type]) )
                {
                    array_push($this->whiteList[$this->_type], $value);
                }                
            }
        }
        
        return $this->ex_Statistics($method);
    }
    
	//扩展方法,核心中只一条语句,其它留给扩展系统扩展
    protected function ex_Statistics($method)
    {
		return $this->$method();
	}
	
    /**
     * 文档统计数
     */
    protected function _aStatistics()
    {
       $channels = cls_cache::Read('channels');
	   if ( empty($channels[$this->modid])){
	   		return '0';
	   }
        if ( !in_array($this->field, $this->whiteList[$this->_type]) )
        {
            return '0';
        }
        
        $row = $this->_db->select($this->field)
                         ->from('#__' . atbl($this->modid) . ' a')
                         ->innerJoin("#__archives_{$this->modid} c")->_on('c.aid=a.aid')
                         ->where(array('a.aid' => $this->infoid))
                         ->_and('checked=1')
                         ->limit(1)#->setDebug()
                         ->exec()->fetch();
        return $row[$this->field];
    }
    
    /**
     * 会员统计数
     */
    protected function _mStatistics()
    {
       $mchannels = cls_cache::Read('mchannels');
	   if ( empty($mchannels[$this->modid])){
	   		return '0';
	   }
	    if ( !in_array($this->field, $this->whiteList[$this->_type]) )
        {
            return '0';
        }
        
        $row = $this->_db->select($this->field)
                         ->from("#__members_{$this->modid} c")
                         ->innerJoin("#__members m")->_on('c.mid=m.mid')
                         ->innerJoin("#__members_sub s")->_on('s.mid=m.mid')
                         ->where(array('c.mid' => $this->infoid))
                         ->limit(1)#->setDebug()
                         ->exec()->fetch();
        return $row[$this->field];
    }
    
    /**
     * 交互统计数
     */
    protected function _cuStatistics()
    {
        $commu = cls_cache::Read('commu', $this->modid);
        if ( !isset($commu['tbl']) || !in_array($this->field, $this->whiteList[$this->_type]) )
        {
            return '0';
        }
        
        $this->_db->select("COUNT(*) AS num")->from("#__{$commu['tbl']}");
        if ($this->infoid)
        {
            $this->_db->where(array($this->field => $this->infoid)); 
        }
        else
        {
        	$this->_db->where('1 = 1'); 
        }
                  
        if ( isset($this->_get['_and']) )
        {
            $this->_splitParmas($this->_get['_and']);
        } 
                  
        if ( isset($this->_get['_or']) )
        {
            $this->_splitParmas($this->_get['_or'], '_or');
        }
        
        $row = $this->_db->_and('checked=1')
                         ->limit(1)#->setDebug()
                         ->exec()->fetch();
        return $row['num'];
    }
    
    /**
     * 类系统计数
     */
    protected function _coStatistics()
    {
        $row = $this->_db->select($this->field)
                         ->from("#__coclass{$this->modid}")
                         ->where(array('ccid' => $this->infoid))
                         ->limit(1)#->setDebug()
                         ->exec()->fetch();
        return $row[$this->field];
    }
    
    /**
     * 分隔参数
     * 
     * @param string $params 要分隔的参数字符串
     * @param string $type   要组装SQL的参数类型，一般为 _and 和 _or，该值与{@see _08_MysqlQuery::$type}类方法一致
     */
    protected function _splitParmas( $params, $type = '_and' )
    {
        $params = array_map('trim', array_filter(explode(',', $params)));
        
        foreach ( $params as $param ) 
        {
            $_params = explode('_', $param);
            if ( count($_params) > 2 )
            {
                $_params[1] = str_ireplace(
                    array('not', 'large', 'small', 'largeand', 'smalland'),
                    array('!=', '>', '<', '>=', '<='),
                    $_params[1]
                );
                
                $this->_db->$type(array($_params[0] => implode('_', array_slice($_params, 2))), $_params[1]);
            }
            else
            {
            	$this->_db->$type(array($_params[0] => $_params[1]));
            }            
        }
    }
}