<?php
/**
 * 管理后台的查看原始标识,生成及更新原始标识列表
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
class _08_btags extends cls_AdminHeader
{
    private $db = null;
    /**
     * 建立模型界面句柄
     *
     * @var object
     */
    private $build = null;

    /**
     * 原始标识分类名称
     *
     * @var array
     * @static
     */
    public static $bclasses = array(
    	'common' => '通用信息',
    	'archives' => '文档相关',
    	'catalogs' => '类目相关',
    	'farchives' => '副件相关',
        'pushs' => '推送相关',
    	'commus' => '交互相关',
    	'members' => '会员相关',
    	'others' => '其它',
    );

    /**
     * 原始标识数据类型名称
     *
     * @var array
     * @static
     */
    public static $datatypearr = array(
    	'text' => '单行文本',
    	'multitext' => '多行文本',
    	'htmltext' => 'Html文本',
    	'image' => '单图',
    	'images' => '图集',
    	'flash' => 'Flash',
    	'flashs' => 'Flash集',
    	'media' => '视频',
    	'medias' => '视频集',
    	'file' => '单点下载',
    	'files' => '多点下载',
    	'select' => '单项选择',
    	'mselect' => '多项选择',
    	'cacc' => '类目选择',
    	'date' => '日期(时间戳)',
    	'int' => '整数',
    	'float' => '小数',
    	'map' => '地图',
    	'vote' => '投票',
    	'texts' => '文本集',
    );

    /**
     * 自动维护的标识数组
     * 
     * @var array
     */ 
    private $btagnames = array();
    
    /**
     * 手动维护的标识数组
     * 
     * @var array
     */
    private $notautobtagnames = array();
    
    /**
     * 手动删除的标识数组
     * 
     * @var array
     */
    private $del_btagnames = array();

    /**
     * 当前操作的子分类ID数组
     * 
     * @var array
     */ 
    private $sclasses = array();
    
    private $url = '';

    private $sClass = 0;//记录小分类
	
    public function __construct()
    {
        global $db;
        parent::__construct('tpl');
        $this->setBclass();
        
        # 读取手动管理的标识缓存
        $this->notautobtagnames = cls_cache::getCacheClassVar('cac_btagnames');    
        
        # 读取手动删除的标识缓存
        $this->del_btagnames = cls_cache::getCacheClassVar('cac_btagnames_del');
        $this->db = $db;
        if(!empty($this->_params['textid']))
        {
            if(empty($this->_params['floatwin_id']) || $this->_params['floatwin_id'] == 'null') {
                $this->_params['floatwin_id'] = 'main';
            } else if(is_numeric($this->_params['floatwin_id'])) {
                $this->_params['floatwin_id'] = "_08winid_" . (int)$this->_params['floatwin_id'];
            } else {
                $this->_params['floatwin_id'] = $this->_params['floatwin_id'];                
            }
            $this->url = "&caretpos={$this->_params['caretpos']}&types={$this->_params['types']}&textid={$this->_params['textid']}&floatwin_id={$this->_params['floatwin_id']}";
            cls_phpToJavascript::insertParentWindowString($this->_params['floatwin_id'], $this->_params['textid'], '', 0, false);
        }
    }
    
    /**
     * 设置当前操作参数
     */ 
    public function setBclass()
    {        
        # 所有未定义分类的自动定向到其它分类里
        if( empty($this->_params['bclass']) ) 
        {
            $this->_params['bclass'] = 'common';
        }
        else if( !array_key_exists($this->_params['bclass'], self::$bclasses) )
        {
            # 兼容在复合标识插入时的情况
            if( in_array($this->_params['bclass'], array('searchs', 'archive', 'acount')) ) // 文档
            {
                $this->_params['bclass'] = 'archives';
                $this->_params['sclass'] < 0 && $this->_params['sclass'] = 0;
            }
            else if( in_array($this->_params['bclass'], array('member', 'msearchs', 'mcount')) ) // 会员
            {
                $this->_params['bclass'] = 'members';
            }
            else if( in_array($this->_params['bclass'], array('farchive',)) ) // 副件
            {
                $this->_params['bclass'] = 'farchives';
            }
            else if( in_array($this->_params['bclass'], array('commu')) ) // 交互
            {
                $this->_params['bclass'] = 'commus';
            }
            else if( in_array($this->_params['bclass'], array('mccatalogs', 'cnode', 'nownav', 'mcnode')) ) // 栏目或类系
            {
                $this->_params['bclass'] = 'catalogs';
            }
            else 
            {
				if( in_array($this->_params['bclass'], array('vote', 'votes'))){//投票
                	$this->_params['sclass'] = 'vote';
				}
				else if( in_array($this->_params['bclass'], array('images', 'image', 'files', 'file', 'flashs', 'flash', 'medias', 'media')) ) // 附件
				{
					$this->_params['sclass'] = 'attachment';
				}
				else if( in_array($this->_params['bclass'], array('keyword',)) ) // 关键词
				{
					$this->_params['sclass'] = 'keyword';
				}
 				else if( in_array($this->_params['bclass'], array('mcatalogs','mnownav')) ) //空间栏目
				{
					$this->_params['sclass'] = 'mcatalogs';
				}
  				else if( in_array($this->_params['bclass'], array('texts',)) ) //文本集
				{
					$this->_params['sclass'] = 'texts';
				}
              $this->_params['bclass'] = 'others';
				
            }
        }
        /**
         * 因从复合标识管理点击进来的窗口分类ID与该ID不一致，
         * 所以独立处理副件的sclass ID
         */ 
        if(
            ($this->_params['bclass'] == 'farchives') && !empty($this->_params['textid']) && 
            !empty($this->_params['handlekey'])
        )
        {            
            $this->_params['sclass'] = cls_fcatalog::Config($this->_params['sclass'],'chid');
        } else if ($this->_params['bclass'] == 'catalogs' && !empty($this->_params['textid']))
        {            
            $this->_params['sclass'] = str_replace('co', '', @$this->_params['sclass']);
        }
    }

    public function init()
    {
    	$arr = array();
        # 显示当前分类信息
    	foreach(self::$bclasses as $k => $v)
        {
            $arr[] = ($this->_params['bclass'] == $k ? "<b>-$v-</b>" : "<a href=\"?entry=btags&bclass=$k{$this->url}\">$v</a>");
        }
    	echo tab_list($arr,count(self::$bclasses),0);

        # 设置当前页面的子分类ID
        $this->setSclasses();
        
        # 获取要在界面显示的字段标识数据
        $showdatas = $this->getShowDatas(); 
        # 搜索时过滤不匹配的行
        if( submitcheck('bbtagsearch') )
        {
            $this->getSearchValue($showdatas);
        }
        # 开始建立界面
        $this->_build->table( $showdatas );
    }

    /**
     * 设置默认分类标识，该块其实应该放模型里
     * 处理自动标识$btagnames
     */
    public function setSclasses()//可以在这里加入附加处理
    {
		$this->sClass = empty($this->_params['sclass']) ? 0 : $this->_params['sclass'];
        switch($this->_params['bclass'])
        {
            # 文档相关
           case 'archives' :
                $this->setDatas('channels', 'archives');
          break;

            #会员相关
            case 'members' :
                $this->setDatas('mchannels', 'members', 'mfields');
                $this->getCommonDatas('grouptypes');
            break;

            # 副件相关
            case 'farchives' :
                $this->setDatas('fchannels', 'farchives', 'ffields');
            break;
            
            # 推送相关
            case 'pushs' :
                # 设置推送相关数据
                $this->setSclass('pushareas');
				if(empty($this->sClass)){
					$pushareas = cls_PushArea::Config();
					$keys = array_keys($pushareas);
					$this->sClass = $keys[0];
				}
				if($pusharea = cls_PushArea::Config($this->sClass)){
                    $fields = $this->db->getTableColumns('#__'.cls_PushArea::ContentTable($this->sClass), false);
                    cls_Array::setObjectDOM($fields, 'maintable', 1);
                    $this->setFields($fields, $this->sClass, '');
				}
            break;

            #交互相关
            case 'commus' :
                $this->setSclass('commus', 'cuid');
                $commus = cls_commu::Config();
				if(empty($this->sClass)){
					$keys = array_keys($commus);
					$this->sClass = $keys[0];
				}
				if($commu = cls_commu::Config($this->sClass)){
                    $fields = $this->db->getTableColumns("#__{$commu['tbl']}", false);
                    cls_Array::setObjectDOM($fields, 'maintable', 1);
                    $this->setFields($fields,$this->sClass, '');       
                }
            break;

            #类目相关
            case 'catalogs' :
				$this->sClass = intval($this->sClass);
                $this->sclasses = array(
        			'catalogs' => '栏目',
        		);
                $this->setSclass('cotypes');
				if(empty($this->sClass)){
					$fields = $this->db->getTableColumns("#__catalogs", false);
					cls_Array::setObjectDOM($fields, 'maintable', 1);
					$this->setFields($fields, 'catalogs', 'cnfields_0');
				}else{
                	$cotypes = cls_cache::Read('cotypes');
					if($cotype = @$cotypes[$this->sClass]){
						$fields = $this->db->getTableColumns("#__coclass{$this->sClass}", false);
						cls_Array::setObjectDOM($fields, 'maintable', 1);
						$this->setFields($fields, $this->sClass, 'cnfields_1');          
					
					}
				}
            break;

            #其它
            case 'others' :
                $this->sclasses = array(
        			'mp' => '分页',
        			'attachment' => '附件',
                    'vote' => '投票',
                    'keyword' => '关键词',
                    'mcatalogs' => '空间栏目',
                    'texts' => '文本集',
       		);
	           $this->sClass || $this->sClass = 'mp';
            break;
            default : 
                $this->getCommonDatas();
            break;
        }
		$this->AutoTagSupplement();
    }
	
    /**
     * 对得到的自动标识$btagnames补充处理
     * 追加中文名称，置换键值
     */ 
    public function AutoTagSupplement(){
		$cnames = array();
        switch($this->_params['bclass']){
			case 'archives' ://类系名称，合辑项目名称等
				$cnames['catalog'] = '所属栏目标题';
				$source = cls_cache::Read('cotypes');
				foreach($source as $k => $v){
					$cnames["ccid$k"] = "[{$v['cname']}]分类ID";
					$cnames["ccid{$k}title"] = "[{$v['cname']}]分类标题";
					$cnames["ccid{$k}date"] = "[{$v['cname']}]分类到期";
				}
				$source = cls_cache::Read('abrels');
				foreach($source as $k => $v){
					if(!$v['tbl']){
						$cnames["pid$k"] = "[{$v['cname']}]合辑ID";
						$cnames["inorder{$k}"] = "[{$v['cname']}]辑内排序";
						$cnames["incheck{$k}"] = "[{$v['cname']}]辑内审核";
					}
					
				}
			break;
			case 'members' ://类系名称，合辑项目名称等
				$source = cls_cache::Read('grouptypes');
				foreach($source as $k => $v){
					$cnames["grouptype$k"] = "[{$v['cname']}]组ID";
					$cnames["grouptype{$k}date"] = "[{$v['cname']}]组到期";
				}
				$source = cls_cache::Read('mctypes');
				foreach($source as $k => $v){
					$cnames["mctid$k"] = $v['cname'];
				}
				$source = cls_cache::Read('abrels');
				foreach($source as $k => $v){
					if(!$v['tbl']){
						$cnames["pid$k"] = "[{$v['cname']}]合辑ID";
						$cnames["inorder{$k}"] = "[{$v['cname']}]辑内排序";
						$cnames["incheck{$k}"] = "[{$v['cname']}]辑内审核";
					}
					
				}
				$source = cls_cache::Read('currencys');
				foreach($source as $k => $v){
					$cnames["currency$k"] = $v['cname'];
				}
			break;
		}
		foreach($this->btagnames as $k => $v){
			if(!$v['cname'] && isset($cnames[$v['ename']])) $v['cname'] = $cnames[$v['ename']];
			if(!$v['cname']) $v['cname'] = $v['ename'];
			$this->btagnames[$v['ename']] = $v;
			unset($this->btagnames[$k]);
		}
	}        
	
	
    
    /**
     * 设置数据
     * 
     * @param string $cache_name 缓存名称
     * @param string $table_name 数据表名称
     */ 
    public function setDatas($cache_name, $table_name, $type_cache = 'fields')
    {        
        $this->setSclass($cache_name);
        $caches = cls_cache::Read($cache_name);
		
		if(empty($this->sClass)){
			$keys = array_keys($caches);
			$this->sClass = $keys[0];
		}
		
		if($cache = @$caches[$this->sClass]){
			# 获取数据主表结构
			$maintable = ($table_name == 'archives' ? ($table_name . $cache['stid']) : $table_name);
			$fields1 = $this->db->getTableColumns('#__' . $maintable, false);
			# 给结构对象增加值
			cls_Array::setObjectDOM($fields1, 'maintable', 1);
			$fields2 = $this->db->getTableColumns("#__{$table_name}_{$this->sClass}", false);
			cls_Array::setObjectDOM($fields2, 'maintable', 0);
			# 合并主表与副表的结构对象
			$fields = array_merge($fields2, $fields1);
			# 根据结构对象设置要显示的字段标识
			$this->setFields($fields, $this->sClass, $type_cache); 
		}
	}
		
    
    /**
     * 获取系统配置（通用信息）或是会员组名称标识信息
     * 按不同架构附加某些规则性标识(自动)
     */ 
    public function getCommonDatas( $cache_name = 'tpl_fields' )
    {
        $btags = cls_cache::Read($cache_name);
        $enames = $cnames = $bclasses = '';
        $datatype = 'text';
        foreach($btags as $ename => $btag)
        {            
            switch($cache_name)
            {
                # 会员组
                case 'grouptypes' :
                    $enames = 'grouptype'.$ename.'name';
                    $cnames = $btag['cname'].'会员组';
                    $bclasses = 'member';
                break;
                # 通用信息
                default :
                    $enames = 'user_'.$ename;
                    $cnames = $btag['cname'];
                    $bclasses = 'common';
                    isset($btag['type']) && $datatype = $btag['type'];
                    isset($btag['datatype']) && $datatype = $btag['datatype'];
                break;
            }
            // 过滤手动维护存在的标识
                $this->btagnames[] = array(
                    'ename' => $enames,
                    'cname' => $cnames,
                    'bclass' => $bclasses,
                    'sclass' => 0,
                    'datatype' => $datatype,
                    'iscommon' => 0,
                    'maintable' => 1
                );
        }
    }

    /**
     * 设置标识信息
     *
     * @param string $fields     数据表字段信息
     * @param int    $chid       数据表模型ID
     * @param bool   $maintable  该标识是否属于主表，1为主表，0为附表
     *
     * @since 1.0
     */
    public function setFields( $fields, $chid, $type_cache, $maintable = 1 )
    {
        # 读取在系统里添加的标识自定义类型
        if(false !== strpos($type_cache, 'cnfields'))
        {
            $parts = explode('_', $type_cache);
            $type_caches = cls_cache::Read($parts[0], isset($parts[1]) ? (int)$parts[1] : 0);
        }
        else
        {
            $type_caches = cls_cache::Read($type_cache, $chid);
        }
		
		
        foreach($fields as $ename => $field)
        {
            if(!is_object($field)) continue;
            $types = explode(' ', $field->Type);
            $type = preg_replace('/\(.*\)/', '', $types[0]);
            # 获取自定义类型
            if(array_key_exists($ename, $type_caches))
            {
                $type = $type_caches[$ename]['datatype'];
				$field->Comment || $field->Comment = $type_caches[$ename]['cname'];
				
            }
            else
            {
                self::getCustom($type);
#				$field->Comment || $field->Comment = $ename;
            }
            // 过滤手动维护存在的标识
			  $this->btagnames[] = array(
				  'ename' => $ename,
				  'cname' => $field->Comment,
				  'bclass' => $this->_params['bclass'],
				  'sclass' => $chid,
				  'datatype' => $type,
				  'iscommon' => 0,
				  'maintable' => $field->maintable
			  );
        }
    }
    
    /**
     * 获取自定义类型，目前只处理了用得比较多的
     * 
     * @param  string $type 传递原始数据库类型
     * @return string $type 返回本系统自定义的数据类型
     * 
     * @since  1.0
     */ 
    public static function getCustom(&$type)
    {
        false !== stripos($type, 'double') && $type = 'float';
        false !== stripos($type, 'text') && $type = 'multitext';
        false !== stripos($type, 'int') && $type = 'int';
        false !== stripos($type, 'char') && $type = 'text';
        false !== stripos($type, 'time') && $type = 'date';
    }

    /**
     * 设置单个子标识分类
     *
     * @param string $cache_name 要读取的缓存名称
     * @param string $array_key  如果值为数组时可用该值指定获取的下标
     */
    public function setSclass($cache_name, $array_key = '')
    {
        $caches = cls_cache::Read($cache_name);
		foreach($caches as $k => $v)
        {
			$this->sclasses[empty($array_key) ? $k : $v[$array_key]] = $v['cname'];
		}
    }

    /**
     * 获取搜索结果值
     *
     * @param array $datas 所有标识信息
     */
    public function getSearchValue( array &$datas )
    {
        foreach($datas['showdatas'] as $k => $data)
        {
            $data = array_map('strip_tags', $data);
            if (
                # 搜索使用样式
                !empty($this->_params['ename']) &&
                !in_str($this->_params['ename'], $data[0]) &&
                !in_str($this->_params['ename'], $data[1]) &&
                !in_str($this->_params['ename'], $data[2]) ||
                # 搜索标识名称
                (!empty($this->_params['cname']) && !in_str($this->_params['cname'], $k))
            )
            {
                # 如果都不搜索项时则过滤该行
                unset($datas['showdatas'][$k]);
            }
        }
    }

    /**
     * 获取要展示现原始标识数据
     *
     * @return array $config 返回获取到的数据
     * @since  1.0
     */
    public function getShowDatas()
    {
        empty($this->_params['ename']) && $this->_params['ename'] = '';
        empty($this->_params['cname']) && $this->_params['cname'] = '';
        # 获取select信息
        if(empty($this->sclasses))
        {
            $select_str = '';
        }
        else
        {
            if(empty($this->_params['sclass']))
            {
                $keys = array_keys($this->sclasses);
                $this->_params['sclass'] = $keys[0];
            }
            $select_str = $this->_build->select(
                array(
                    'selectname' => 'sclass',
                    'selectdatas' => $this->sclasses,
                    'selectedkey' => (isset($this->_params['sclass']) ? $this->_params['sclass'] : 0),
                    'selectstr' => 'onchange="location.href=\'?entry=btags&bclass='.$this->_params['bclass'].$this->url.'&sclass=\' + this.value"'
                )
            );
        }

        # 获取数据
        $config = array(
            'title' => self::$bclasses[$this->_params['bclass']] .
<<<EOT
              >>&nbsp;&nbsp;&nbsp;&nbsp;{$select_str}
              标识使用样式<input type="text" name="ename" value="{$this->_params['ename']}" class="txt" />
              标识标题<input type="text" name="cname" value="{$this->_params['cname']}" class="txt" />
              <input class="btn" type="submit" name="bbtagsearch" value="搜索" />
EOT
            , 'tabletitle' => array('标识标题', '样式1', '样式2', '样式3', '数据类型', '主表')
            , 'showdatas' => array()
        );
        !empty($this->_params['textid']) && $config['title'] .= ' (点击样式插入)';
        $this->getBtagnames($this->btagnames, $config );
       return $config;
    }
	
    /**
     * 获取要显示的原始标识缓存数据
     *
     * @param array $btagnames 标识缓存数据
     * @param array $config    存放到视图里的数据数组
     */
    public function getBtagnames( array $btagnames, &$config )
    {
		foreach($this->notautobtagnames as $k => $v){//只合并当前分类的标识
            if($v['bclass'] != $this->_params['bclass'] ) continue;
			if(empty($v['iscommon']) && $this->sClass != @$v['sclass']) continue;
			$btagnames[$k] = $v;
		}		
		
        foreach($btagnames as $key => $btagname)
        {
			$comArr = array('ename'=> $btagname['ename'], 'bclass'=> $btagname['bclass'], 'sclass'=> $btagname['sclass']);
			if(cls_Array::_in_array($comArr, $this->del_btagnames, true)) continue;
			
			if(!empty($this->_params['textid']))
			{   
				# 只有从“标识内模板”点击过来的窗口才出现插入原始标识功能
				$ename1 = '<a href="javascript:obj.insertTagStr(\''.$this->_params['textid'].'\', \'{'.$btagname['ename'].'}\', '.(int)$this->_params['caretpos'].');">{<b>' . $btagname['ename'] . '</b>}</a>';
				$ename2 = '<a href="javascript:obj.insertTagStr(\''.$this->_params['textid'].'\', \'{$'.$btagname['ename'].'}\', '.(int)$this->_params['caretpos'].');">{$<b>' . $btagname['ename'] . '</b>}</a>';
				$ename3 = '<a href="javascript:obj.insertTagStr(\''.$this->_params['textid'].'\', \'{$v['.$btagname['ename'].']}\', '.(int)$this->_params['caretpos'].');">{$<b>v[' . $btagname['ename'] . ']</b>}</a>';
			}
			else 
			{
				# 否则只显示原始使用样式
				$ename1 = '{<b>' . $btagname['ename'] . '</b>}';
				$ename2 = '{$<b>' . $btagname['ename'] . '</b>}';
				$ename3 = '{$<b>v[' . $btagname['ename'] . ']</b>}';
			}
			$array = array(
				$ename1,
				$ename2,
				$ename3,
				@self::$datatypearr[$btagname['datatype']],
				empty($btagname['maintable']) ? '否' : '是'
			);
			empty($btagname['cname']) ? array_push($config['showdatas'], $array) : $config['showdatas'][$btagname['cname']] = $array;                
        }
    }
}