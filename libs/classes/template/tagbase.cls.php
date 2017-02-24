<?php
/**
* 与标签有关的外部方法汇总
* 
*/
defined('M_COM') || exit('No Permission');
abstract class cls_TagBase{
	
	# 在简化标签配置时，只要为空或为0就可以清除的变量名
	protected static $UnsetVars = array();
	
	public static function BadWord(&$source){
		$badwords = cls_cache::Read('badwords');
        // preg_replace对中文必须两边用UTF8编码才能匹配
        if (!empty($badwords['wsearch']))
        {
            $mcharset = cls_env::getBaseIncConfigs('mcharset');
            $badwords['wsearch'] = cls_string::iconv($mcharset, 'UTF-8', $badwords['wsearch']);
            $source = cls_string::iconv($mcharset, 'UTF-8', $source);
            $source = preg_replace($badwords['wsearch'],$badwords['wreplace'],$source);
            $source = cls_string::iconv('UTF-8', $mcharset, $source);
        }
	}
	public static function WordLink(&$source){
		$wordlinks = cls_cache::Read('wordlinks');
		if(!empty($wordlinks['swords'])){
			if(preg_match_all("/<.*?>/s", $source, $matchs)){
				$matchs = array_unique($matchs[0]);
				foreach($matchs as $k => $v) $source = str_replace($v,":::$k:::", $source);
				$source = preg_replace($wordlinks['swords'],$wordlinks['rwords'],$source,1);
				$source = preg_replace("/:::(\d+):::/se", '$matchs[$1]', $source);
			}
		}
		return $source;
	}
	public static function Face(&$source){
		$faceicons = cls_cache::Read('faceicons');
		if(!empty($faceicons['from'])){
			$tos = array();
			foreach($faceicons['to'] as $v) $tos[] = '<img src="'.cls_env::mconfig('cms_abs').$v.'">';
			$source = str_replace($faceicons['from'],$tos,$source);
			unset($tos);
		}
		return $source;
	}
	// type: 0-br, 1-</p>, 2-<p style='XXXX'>
	public static function RandStr($type=0){
		$str = ''; 
		for($i = 0;$i < mt_rand(5,15);$i++)  $str .= chr(mt_rand(0,59)).chr(mt_rand(63,126));
		$tags = array('a','b','i','em','span'); 
		$tag = $tags[mt_rand(0, 4)];
		$str = "<$tag style='display:none'>$str</$tag>"; //font？不用了
		if($type==1){
			return mt_rand(0, 1) ? '</p>' : $str.'</p>'; //一部分加,一部分不加
		}elseif($type==2){
			return mt_rand(0, 1) ? '<p>' : '<p>'.$str; //一部分加,一部分不加
		}else{
			return mt_rand(0, 1) ? '<br />'.$str : $str.'<br />'; //一部分加在前,一部分不加在后
		}
	}
		
	
	# 根据特征(列表性二维数组结果list/单维数组结果single/字串结果string/分页mp/可定义权限方案pmid)取得相应的标签类型
	public static function TagClassByType($Type = 'list'){
		$ClassArray = array();
		switch($Type){
			case 'list':
				$ClassArray = array(
					'archives','outinfos','functions','members','searchs','msearchs','commus','catalogs','mccatalogs','mcatalogs','pushs',
					'farchives','fromids','keyword','votes','vote','nownav','mnownav','images','files','medias','flashs','texts','advertising',
				);
			break;
			case 'single':
				$ClassArray = array('archive','member','farchive','commu','cnode','mcnode','acount','mcount','image','file','flash','media','fromid',);
			break;
			case 'string':
				$ClassArray = array('freeurl','fragment','date','text','field','regcode',);
			break;
			case 'mp':
				$ClassArray = array('archives','catalogs','commus','farchives','functions','images','mccatalogs','members','searchs','msearchs','outinfos','text','votes',);
			break;
			case 'pmid':
				$ClassArray = array('archive','member','farchive','commu',);
			break;
		}
		return $ClassArray;
	}
	
	# 在简化标签配置时，只要为空或为0就可以清除的Key
	public static function UnsetVars(){
		if(empty(self::$UnsetVars)){
			self::$UnsetVars = array(
				'casource','cainherit','caidson','urlmode','chsource','space','ucsource','detail','rec','orderby','orderby1','orderstr','startno','wherestr','simple','alimits',
				'fmode','date','time','tmode','width','height','maxwidth','maxheight','expand','emptyurl','emptytitle','dealhtml','trim','badword','wordlink','nl2br','randstr',
				'next','chid','caid','mid','aid','func','mpfunc','sqlstr','vid','vsource','vids','chdata','js','checked','cnid','cnsource','level','caids','limits','letter','ids',
				'validperiod','thumb','asc','chids','nochids','val','tname','disabled','face','source','pmid','isfunc','isall','type','id','coids','idsource','mode','arid','mp',
				'length','ttl','timeout','fee','color','ellip','vieworder','classid1','classid2','injs',
			);
			foreach(array(0,1,2) as $k){
				self::$UnsetVars[] = 'source'.$k;
				self::$UnsetVars[] = 'ids'.$k;
			}
			$cotypes = cls_cache::Read('cotypes');
			foreach($cotypes as $k => $v){
				self::$UnsetVars[] = 'cosource'.$k;
				self::$UnsetVars[] = 'coinherit'.$k;
				self::$UnsetVars[] = 'ccid'.$k;
				self::$UnsetVars[] = 'ccidson'.$k;
				self::$UnsetVars[] = 'ccids'.$k;
			}
			$grouptypes = cls_cache::Read('grouptypes');
			foreach($grouptypes as $k => $v){
				self::$UnsetVars[] = 'source'.(10+$k);
				self::$UnsetVars[] = 'ids'.(10+$k);
				self::$UnsetVars[] = 'ugid'.$k;
			}
		}
		return self::$UnsetVars;
	}
	
	# 在简化标签配置时，只要值为对应数组内的值就可以清除的Key
	public static function UnsetVars1(){
		$UnsetVars1 = array(
			'val' => array('v',),
			'limits' => array('10',),
		);
		return $UnsetVars1;
	}
	
	# 取得标签所有类型，$isFragment是否碎片需要的标签类型
	public static function TagClass($isFragment = false){
		$ClassArray = array(
			'archives' => '文档列表',
			'catalogs' => '类目列表',
			'members' => '会员列表',
			'commus' => '交互列表',
			'farchives' => '副件列表',
			'pushs' => '推送列表',
			'mccatalogs' => '会员节点列表',
			'outinfos' => '自由调用列表',
			'functions' => '自定函数列表',
			'searchs' => '文档搜索列表',
			'msearchs' => '会员搜索列表',
			'keyword' => '关键词列表',
			'fromids' => '架构资料列表',
			'nownav' => '类目导航',
			'mcatalogs' => '空间类目列表',
			'mnownav' => '空间类目导航',
			'fragment' => '碎片调用',
			'archive' => '单个文档',
			'member' => '单个会员',
			'farchive' => '单个副件',
			'commu' => '单条交互',
			'cnode' => '类目节点',
			'mcnode' => '会员节点',
			'acount' => '文档数量',
			'mcount' => '会员数量',
			'text' => '文本处理',
			'images' => '图集列表',
			'image' => '图片模块',
			'files' => '下载列表',
			'file' => '下载模块',
			'flashs' => 'Flash列表',
			'flash' => 'Flash模块',
			'medias' => '视频列表',
			'media' => '视频模块',
			'texts' => '文本集列表',
			'fromid' => '指定ID资料',
			'date' => '时间日期',
			'field' => '字段标题值',
			'regcode' => '验证码区',
			'freeurl' => '独立页URL',
			'votes' => '投票列表',
			'vote' => '投票选项列表',
		);
		if($isFragment){
			$ClassArray = array('' => '自定模板') + $ClassArray;
			foreach(array('searchs','msearchs','nownav','mcatalogs','mnownav','fragment',) as $Key) unset($ClassArray[$Key]);
		}
		return $ClassArray;
	}
	
	
}
