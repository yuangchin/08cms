<?PHP
!defined('M_COM') && exit('No Permisson');


/* ------------------------------------------------------------------ 
以下函数，已经移动到相关类中，
暂时保留只为暂时兼容旧版本
在201308之后的开发中，将尽量不再使用以下兼容函数，并在下一版核心升级中完全替换，并删除本脚本。
------------------------------------------------------------------ */


# 自动更新静态的暂停时段的分析。暂时保留以兼容旧版本
function static_pause($val = ''){
	return cls_Static::InParsePeriod($val);
}
# 字符串转成标签数组（代码转成非封装标识数据）。暂时保留以兼容旧版本
function _08_code_to_tagarr($string){
	return cls_TagAdmin::CodeToTagArray($string);
}
# 生成(更新)指定会员的静态空间。暂时保留以兼容旧版本
function mspace_static($mid = 0){
	return cls_Mspace::ToStatic($mid);
}
# 独立页面Url。暂时保留以兼容旧版本
function _one_freeurl($fid = 0){
	return cls_FreeInfo::Url($fid);
}
# 传入相对地址图片，生成指定大小的缩略图，返回显示url，暂时保留以兼容旧版本
function thumb($dbstr,$width = 0,$height = 0,$mode = 0){
	return cls_atm::thumb($dbstr,$width,$height,$mode);
}
# 按比例调整图片大小，暂时保留以兼容旧版本
function imagewh($width=0,$height=0,$maxwidth=0,$maxheight=0){
	return cls_atm::ImageSizeKeepScale($width,$height,$maxwidth,$maxheight);
}
# 隐藏会员信息中的未认证字段信息。暂时保留以兼容旧版本
function hidden_uncheck_cert(&$info){
	cls_UserMain::HiddenUncheckCertField($info);
}
# 取得投标字段的相关信息。暂时保留以兼容旧版本
function field_votes($fname,$type,$id,$onlyvote = 1){
	return cls_field::field_votes($fname,$type,$id,$onlyvote);
}
# 获取dynamic/htmlcac中的缓存子路径，如果不存在就创建目录。暂时保留以兼容旧版本
function htmlcac_dir($mode='arc',$spath=''){
	return cls_cache::HtmlcacDir($mode,$spath);
}
# 生成Sitemap静态。暂时保留以兼容旧版本
function sitemap_static($map){
	return cls_SitemapPage::Create(array('map' => $map,'inStatic' => true));
}
# 当前会员是否有权限允许下载文档中的附件//查附件扣值不属此范围。暂时保留以兼容旧版本
function arc_allow_down($item){//当前会员是否有权限允许下载文档中的附件//查附件扣值不属此范围
	return cls_ArcMain::AllowDown($item);
}
# 文档在模板解析中从数据库或类中读出后，需要追加处理的事务。暂时保留以兼容旧版本
function arc_parse(&$item,$inList = false){
	return cls_ArcMain::Parse($item,$inList);
}
# 根据权限方法，返回无权限原因。暂时保留以兼容旧版本
function mem_noPm($info = array(),$pmid=0){
	return cls_Permission::noPmReason($info,$pmid);
}
# 节点页面(含系统首页)生成静态，暂时保留以兼容旧版本
function index_static($cnstr = '',$addno = 0){
	return cls_CnodePage::Create(array('cnstr' => $cnstr,'addno' => $addno,'inStatic' => true));
}
# 会员频道页面生成静态，暂时保留以兼容旧版本
function mindex_static($cnstr = '',$addno = 0){
	return cls_McnodePage::Create(array('cnstr' => $cnstr,'addno' => $addno,'inStatic' => true));
}

# 下载指定的本地文件，暂时保留以兼容旧版本
function file_down($file, $filename = ''){
	return cls_atm::Down($file, $filename);
}
# 模板意外中断函数，暂时保留以兼容旧版本
function tpl_exit($str = ''){
	return cls_Parse::Message($str);
}
# 模板解析函数，暂时保留以兼容旧版本 xxxxxxxxxxxxxxxxxxxx
function _E($SourceArray,$init=0,$add=array()){
	return cls_Parse::Active($SourceArray,$init);
}
# 模板解析函数，暂时保留以兼容旧版本 xxxxxxxxxxxxxxxxxxxx
function _X(){
	return cls_Parse::ActiveBack();
}
# 模板解析函数，暂时保留以兼容旧版本 xxxxxxxxxxxxxxxxxxxx
function _T($tag=array()){
	return cls_Parse::Tag($tag);
}

# 空间栏目页中补充可用于原始标签调用的资料数组，暂时保留以兼容旧版本
function mcn_parse($info = array(),$ps=array()){
	return cls_Mspace::IndexAddParseInfo($info,$ps);
}

# 获取指定会员的模板方案资料,$Key：setting/arctpls，暂时保留以兼容旧版本
function load_mtconfig($mid=0,$Key='setting'){
	return cls_mtconfig::ConfigByMid($mid,$Key);
}

# 获取指定会员空间资料，暂时保留以兼容旧版本
function load_member($mid = 0,$ttl = 0){
	$re = array();
	if(!($re[0] = cls_Mspace::LoadMember($mid,$ttl))){
		return array();
	}
	$re[1] = cls_Mspace::LoadUclasses($mid,$ttl);
	$re[2] = cls_mtconfig::ConfigByMid($mid,'setting');
	$re[3] = cls_Mspace::LoadMcatalogs($re[0]['mtcid']);
	return $re;
}
# 获取指定会员的个人分类资料，暂时保留以兼容旧版本
function loaduclasses($mid,$ttl = 0){
	return cls_Mspace::LoadUclasses($mid,$ttl);
}

# 删除由 addslashes() 函数添加的反斜杠，支持数组，暂时保留以兼容旧版本
function mstripslashes($s){
	cls_Array::array_stripslashes($s);
	return $s;
}

# 获取区块标签内定义的模板，暂时保留以兼容旧版本
function rtagval($tname,$rt=1){
	return cls_tpl::rtagval($tname,$rt);
}

# 取得所有副件分类 ID=>名称 的列表数组，暂时保留以兼容旧版本
function fcaidsarr($chid = 0){
	return cls_fcatalog::fcaidsarr($chid);
}

# 取得所有推送分类 ID=>名称 的列表数组，暂时保留以兼容旧版本
function ptidsarr(){
	return cls_pushtype::ptidsarr();
}
# 获取交互架构数据数组
function get_commus_info(){
	return cls_commu::InitialInfoArray();
}
# 返回栏目或分类 ID=>名称 的列表数组，来源为栏目或分类资料数组
function caidsarr($SourceArray,$chid = 0,$nospace = 0){
	return cls_catalog::ccidsarrFromArray($SourceArray,$chid,$nospace);
}
# 返回栏目或分类 ID=>名称 的列表数组，来源为指定的类系ID
function ccidsarr($coid,$chid = 0,$nospace = 0){
	return cls_catalog::ccidsarr($coid,$chid,$nospace);
}
//取得常规模板库中不同类型模板的选择数组，暂时保留以兼容旧版本
function mtplsarr($tpclass = 'archive',$chid = 0){
	return cls_mtpl::mtplsarr($tpclass,$chid);
}
# 取得所有副件模型 ID=>名称 的列表数组，暂时保留以兼容旧版本
function fchidsarr(){
	return cls_fchannel::fchidsarr();
}
# 取得所有会员模型 ID=>名称 的列表数组，暂时保留以兼容旧版本
function mchidsarr(){
	return cls_mchannel::mchidsarr();
}
# 取得文档模型 ID=>名称 的列表数组，暂时保留以兼容旧版本
function chidsarr($all = 0,$noViewID = 0){
	return cls_channel::chidsarr($all,$noViewID);
}
//将分类数组$arr中的索引ID根据上下级嵌套的关系(pid)进行重新排序，返回排序后的详细资料数组
function order_arr($arr = array(),$pid = 0){
	return cls_catalog::OrderArrayByPid($arr,$pid);
}

// 数组$arr中，所有pid下的分类id(含子id)，暂时保留以兼容旧版本
function son_ids($arr = array(),$pid = 0){
	return cls_catalog::OrderArrayByPid($arr,$pid,1);
}

// 数组$arr中，所有id下的子id，暂时保留以兼容旧版本
function cnsonids($id,$arr){
	return cls_catalog::cnsonids($id,$arr);
}

// 取得指定类目的所有下级id(仅下级)，暂时保留以兼容旧版本
function son_ccids($nowid,$coid = 0){
	return cls_catalog::son_ccids($nowid,$coid);
}

// 取得某个类目的指定级(level)的上级类目，暂时保留以兼容旧版本
function p_ccid($nowid,$coid = 0,$level = 0){
	return cls_catalog::p_ccid($nowid,$coid,$level);
}

// 说明：，暂时保留以兼容旧版本
function cn_upid($id,&$arr,$level=0){
	return cls_catalog::cn_upid($id,$arr,$level);
}

// 获取符合条件的类系/栏目数组，暂时保留以兼容旧版本
function uccidsarr($coid,$chid = 0,$framein = 0,$nospace = 0,$viewp = 0,$id=0){
	return cls_catalog::uccidsarr($coid,$chid,$framein,$nospace,$viewp,$id);
}

// 获取类系名称或图标，暂时保留以兼容旧版本
function cnstitle($id,$mode,$sarr,$num=0,$showmode=0){
	return cls_catalog::cnstitle($id,$mode,$sarr,$num,$showmode);
}

// 说明：，暂时保留以兼容旧版本
function mcn_format($cnstr = '',$addno = 0){//含{$page}的节点文件(相对系统根目录)
	return cls_node::mcn_format($cnstr,$addno);
}

// 列出节点所有 列栏/类系 项目相关信息，暂时保留以兼容旧版本
function cn_parse($cnstr,$listby=-1){
	return cls_node::cn_parse($cnstr,$listby);
}

// 列出会员节点所有 列栏/类系/组系 项目相关信息，暂时保留以兼容旧版本
function m_cnparse($cnstr){
	return cls_node::m_cnparse($cnstr);
}

// 说明：，暂时保留以兼容旧版本
function re_cnode(&$item,&$cnstr,&$cnode){
	cls_node::re_cnode($item,$cnstr,$cnode);
}

// 根据节点$cnstr，返回节点名称，暂时保留以兼容旧版本
function cnode_cname($cnstr){
	return cls_node::cnode_cname($cnstr);
}

// 根据节点字串，返回会员节点信息，暂时保留以兼容旧版本
function read_mcnode($cnstr){
	return cls_node::read_mcnode($cnstr);
}

// 根据会员节点信息，得到节点字串，暂时保留以兼容旧版本
function mcnstr($temparr){
	return cls_node::mcnstr($temparr);
}

// 根据节点字串，返回节点信息，暂时保留以兼容旧版本
function cnodearr($cnstr,$NodeMode = 0){
	return cls_node::cnodearr($cnstr,$NodeMode);
}

// 根据会员节点字串，返回节点信息，暂时保留以兼容旧版本
function mcnodearr($cnstr,$noauto=0){
	return cls_node::mcnodearr($cnstr,$noauto);
}

// 根据节点字串，返回节点信息，暂时保留以兼容旧版本
function read_cnode($cnstr,$NodeMode = 0){
	return cls_node::read_cnode($cnstr,$NodeMode);
}

// 说明：，暂时保留以兼容旧版本
function cn_format($cnstr,$addno,&$cnode){//含{$page}的节点文件格式(相对系统根目录)
	return cls_node::cn_format($cnstr,$addno,$cnode);
}

// 根据会员节点字串，得到节点名称(不包含自定义节点)，暂时保留以兼容旧版本
function mcnode_cname($cnstr){
	return cls_node::mcnode_cname($cnstr);
}

// 对url，绑定域名，暂时保留以兼容旧版本
function domain_bind($url){
	return cls_url::domain_bind($url);
}

// 处理：系统设置[隐藏]的url，暂时保留以兼容旧版本
function remove_index($url){
	return cls_url::remove_index($url);
}

// 保存html字段时，处理里面的url，暂时保留以兼容旧版本
function html_atm2tag(&$str){
	cls_url::html_atm2tag($str);
}

// 根据数据库保存路径，判断是否为 ftp附件还是本地附件,只能分析单个附件，暂时保留以兼容旧版本
function is_remote_atm($str){
	return cls_url::is_remote_atm($str);
}

// 根据url，判断是否为本地文件(附件)，暂时保留以兼容旧版本
function islocal($url,$isatm=0){
	return cls_url::islocal($url,$isatm);
}

// 把 原始保存的url字符 转化为 可以浏览的url，暂时保留以兼容旧版本
function tag2atm($str,$ishtml=0){
	//ishtml:如果是1的话，传入的html文本，要处理的是内嵌的附件，暂时保留以兼容旧版本
	return cls_url::tag2atm($str,$ishtml);
}

// 说明：，暂时保留以兼容旧版本
function arr_tag2atm(&$item,$fmode=''){
	cls_url::arr_tag2atm($item,$fmode);
}

// ，暂时保留以兼容旧版本
function local_file($url){
	return cls_url::local_file($url);
}

// 根据url得到本地路径//incftp同时处理ftp的url//如果是第三方附件则返回原url，暂时保留以兼容旧版本
function local_atm($url,$incftp=0){
	return cls_url::local_atm($url,$incftp);
}

// 根据本地路径，得到缩略图的本地路径。，暂时保留以兼容旧版本
function thumb_local($local,$width,$height){//根据本地路径，得到缩略图的本地路径。
	return cls_url::thumb_local($local,$width,$height);
}

// 说明：，暂时保留以兼容旧版本
function fetch_arc_tpl($chid,$caid = 0){
	return cls_tpl::arc_tpl($chid, $caid);
}

// 加载模版，暂时保留以兼容旧版本
function load_tpl($tplname,$rt=1){
	return cls_tpl::load($tplname, $rt);
}

// 获取模版名称，暂时保留以兼容旧版本
function tplname($type,$id,$name){
	return cls_tpl::CommonTplname($type, $id, $name);
}

// 获取类目节点模版名称，暂时保留以兼容旧版本
function cn_tplname($cnstr,&$cnode,$addno=0,$tn=''){
	return cls_tpl::cn_tplname($cnstr, $cnode, $addno, $tn);
}

// 获取会员节点模版名称，暂时保留以兼容旧版本
function mcn_tplname($cnstr,$addno=0){
	return cls_tpl::mcn_tplname($cnstr, $addno);
}

// 处理虚拟静态地址，暂时保留以兼容旧版本
function en_virtual($str,$suffix=0,$novu=0){
	return cls_url::en_virtual($str,$novu);
}

// 针对单个附件url得到保存到数据库中的格式，暂时保留以兼容旧版本
function save_atmurl($url){
	return cls_url::save_atmurl($url);
}

//参考tag2atm(用 tag2atm 代替 ??? )，暂时保留以兼容旧版本
function view_atmurl($url=''){
	return cls_url::view_atmurl($url);
}

// 获取附件url，暂时保留以兼容旧版本
function view_farcurl($id,$url=''){
	return cls_url::view_farcurl($id,$url);
}


//根据节点字串，更新节点相关连接，暂时保留以兼容旧版本
function view_cnurl($cnstr,&$cnode){
	cls_url::view_cnurl($cnstr,$cnode);
}

//对url格式化显示，处理 绑定域名,系统设置[隐藏]的url，暂时保留以兼容旧版本
function view_url($url){
	return cls_url::view_url($url);
}

// 说明：，暂时保留以兼容旧版本
function m_parseurl($u,$s = array()){
	return cls_url::m_parseurl($u,$s);
}

//获取文档url所需要的字段，暂时保留以兼容旧版本
function view_arcurl(&$archive,$addno = 0){
	return cls_ArcMain::Url($archive,$addno);
}

//获取文档url，暂时保留以兼容旧版本
function view_mspcnurl(&$info,$params = array(),$dforce = 0){//$dforce强制动态
	return cls_Mspace::IndexUrl($info, $params, $dforce);
}

//获取会员空间url所需要的字段，暂时保留以兼容旧版本
function view_mcnurl(&$cnstr,&$cnode){
	return cls_url::view_mcnurl($cnstr,$cnode);
}


//该函数有待删除，暂时保留以兼容旧版本
function str_js_src($val){
    return cls_phpToJavascript::str_js_src($val);
}

//安全字串，暂时保留以兼容旧版本
function safestr($string){
	return cls_string::SafeStr($string);
}

//按长度剪裁文本，暂时保留以兼容旧版本
function cutstr($string, $length, $dot = ' ...') {
	return cls_string::CutStr($string, $length, $dot);
}

//按中文字来统计字数，暂时保留以兼容旧版本
function ccstrlen($str){
	return cls_string::WordCount($str);
}

//清理html文本中的样式、js等，暂时保留以兼容旧版本
function html2text($str){
	return cls_string::HtmlClear($str);
}

//编码转换，暂时保留以兼容旧版本
function convert_encoding($from,$to,$source){
	return cls_string::iconv($from,$to,$source);
}

//隐藏电话,手机,邮件,qq,ip的中间一部分,暂时保留以兼容旧版本
function sub_replace($str,$char=''){
	return cls_string::SubReplace($str,$char);
}

//切割处理关键词，暂时保留以兼容旧版本
function keywords($nstr, $ostr=''){
	return cls_string::keywords($nstr, $ostr);
}

//字数计算，暂时保留以兼容旧版本
function wordcount($string, $flag = false){
	return cls_string::WordCount($string, $flag);
}

//是否email，暂时保留以兼容旧版本
function isemail($email){
	return cls_string::isEmail($email);
}

//是否日期格式，暂时保留以兼容旧版本
function isdate($date, $mode = 0) {
	return cls_string::isDate($date, $mode);
}

# 暂时保留以兼容旧版本
function sys_cache2file($carr,$cname,$cacdir=''){
	return cls_CacheFile::cacSave($carr,$cname,$cacdir);
}

# 暂时保留以兼容旧版本(cecore)
function sys_cache($cname,$cacdir='',$noex = 0){
	return cls_cache::cacRead($cname,$cacdir,$noex);
}

//旧方法(需传pname)，暂时保留以兼容旧版本，请使用 mem_pmbypmid
function mem_permission($info = array(),$pname = '',$pmid=0){
	return _mem_noPm($info,$pmid) ? false : true;
}

# 生成随机字串，暂时保留以兼容旧版本
function random($length, $onlynum = 0) {
	return cls_string::Random($length, $onlynum);
}

# 按指定键值对数组进行排序，暂时保留以兼容旧版本
function m_array_multisort(&$array,$orderkey = 'vieworder',$keepkey = false){
	cls_Array::_array_multisort($array,$orderkey,$keepkey);
}

# 追溯指定分类的所有上级id，暂时保留以兼容旧版本
function pccidsarr($ccid = 0,$coid = 0,$self = 0){
	return cls_catalog::Pccids($ccid,$coid,$self);
}

# 从数组中取出一段，暂时保留以兼容旧版本
function marray_slice($arr,$offset = 0,$length = 0){
	return array_slice($arr,$offset,$length,true);
}

# 暂时保留以兼容旧版本
function mmicrotime(){
	return microtime(TRUE);
}

//读取通用缓存，兼容旧版本(因为使用很广，需要长期保留??)
function read_cache($CacheName,$BigClass = '',$SmallClass = '',$noExCache = 0){
	return cls_cache::Read($CacheName,$BigClass,$SmallClass,$noExCache);
}

//按全局变量方式加载缓存，兼容旧版本(因为使用很广，需要长期保留??)
function load_cache($Keys = ''){
	return cls_cache::Load($Keys);
}

//通用缓存完整路径，暂时保留以兼容旧版本
function cache_dir($CacheName=''){
	return cls_cache::CacheDir($CacheName);
}

//生成缓存键值(缓存文件名)，暂时保留以兼容旧版本
function cache_name($CacheName,$BigClass = '',$SmallClass = ''){
	return cls_cache::CacheKey($CacheName,$BigClass,$SmallClass);
}

//优先读取扩展系统中的开发配置缓存，暂时保留以兼容旧版本
function extend_cache($cname,$noex = 0){
	return cls_cache::exRead($cname,$noex);
}

//强制从缓存文件重新载入缓存，暂时保留以兼容旧版本
function reload_cache($CacheName,$BigClass = '',$SmallClass = ''){
	return cls_cache::ReLoad($CacheName,$BigClass,$SmallClass);
}

//读取单个模板标识缓存，暂时保留以兼容旧版本
function read_tag($TagType,$TagName){
	return cls_cache::ReadTag($TagType,$TagName);
}

//把数据数组保存到通用缓存文件，暂时保留以兼容旧版本
function cache2file($carr,$cname,$ctype='',$noex = 0){
	return cls_CacheFile::Save($carr,$cname,$ctype,$noex);
}

//删除某个通用缓存对应的缓存文件，暂时保留以兼容旧版本
function del_cache($CacheName,$BigClass=''){
	return cls_CacheFile::Del($CacheName,$BigClass);
}

//通过自动条件类系 组sql子句，暂时保留以兼容旧版本
function self_sqlstr($coid,$ccids,$pre = ''){
	return cls_catalog::SelfClassSql($coid,$ccids,$pre);
}

//检测站点及手机版是否关闭，暂时保留以兼容旧版本
function if_siteclosed($noout = 0){
	cls_env::CheckSiteClosed($noout);
}

if ( !function_exists('sp_tplname') )
{//取得功能页面的模板，暂时保留以兼容旧版本
    function sp_tplname($name,$NodeMode = 0){
		return cls_tpl::SpecialTplname($name,$NodeMode);
    }
}
function mapsql($x,$y,$diff = 0,$mode = 1,$fname){
	cls_dbother::MapSql($x,$y,$diff,$mode,$fname);
}

####################以下函数从/include/common.fun.php移植过来##################
if ( !function_exists('message') )
{
    function message($str,$url = '', $mtime = 1250) {
        cls_message::show( $str, $url, $mtime );
    }
}

if ( !function_exists('ajax_info') )
{
    function ajax_info($str) {
    	cls_message::ajax_info($str);
    } 
}

if ( !function_exists('cumessage') )
{
    function cumessage($msg = '',$url=''){
    	cls_message::show($msg, $url);
    }
}
if ( !function_exists('template') )
{
   function template($spname='',$_da=array(),$NodeMode = 0){
	   return cls_tpl::SpecialHtml($spname,$_da,$NodeMode);
    } 
}

/**
 * 加密密码
 *
 * 统一对注册、登录等密码进行加密，当更换加密算法时可减少维护成本
 *
 * @param  string $passwrod 传递加密前的密码
 * @return string $passwrod 返回加密后的密码
 */
function encryptionPass($password) {
    return _08_Encryption::password($password);
}

#-------------------- 从include/admina.fun.php 移植过来
function amessage($str='', $url = '', $mtime = 1250) {
	cls_message::show($str, $url, $mtime);
}

#-------------------- 从adminm/func/main.php 移植过来
function mcmessage($str='', $url = '', $mtime = 1250){
	cls_message::show($str, $url, $mtime);
}
##############################################################################