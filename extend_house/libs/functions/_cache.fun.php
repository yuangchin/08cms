<?php
//暂时保留以兼容旧版本

!defined('M_COM') && exit('No Permission');

//批量更新系统架构缓存，暂时保留以兼容旧版本
function rebuild_cache($except = ''){
	return cls_CacheFile::ReBuild($except);
}

//生成或更新指定的系统架构缓存，暂时保留以兼容旧版本
function updatecache($CacheName,$BigClass = ''){
	return cls_CacheFile::Update($CacheName,$BigClass);
}

//通过数据表得到生成缓存所需要的原始数据数组，暂时保留以兼容旧版本
function cache_array($cachecfg = array()){
	return cls_DbOther::CacheArray($cachecfg);
}

//得到指定表的字段名数组，暂时保留以兼容旧版本
function mfetch_fields($tbls = ''){
	return cls_DbOther::ColumnNames($tbls);
}

//重写类目表的tureorder排序字段，暂时保留以兼容旧版本
function cn_dborder($coid=0){
	return cls_catalog::DbTrueOrder($coid);
}

//指定id的所有上级id（通过传入的原始数组获取），暂时保留以兼容旧版本
function cn_pids($ccid,$cnArray = array()){
	return cls_catalog::PccidsByAarry($ccid,$cnArray);
}

//取得顶级域名，暂时保留以兼容旧版本
function top_domain($url){
	return cls_env::TopDomain($url);
}

//取得栏目及各类系类目所占用的dirname(静态路径)数组，暂时保留以兼容旧版本
function cn_dirname_arr(){
	return cls_catalog::DirnameArray();
}
