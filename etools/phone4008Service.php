<?php
/**
 * phoneService4008元素类
 * @author    freelyworld <freelyworld@163.com>
 * @copyright Copyright (C) 2016 - 2018 , Inc. All rights reserved.*/

defined('M_COM') || exit('No Permission');

class _08_phone4008Service{

const APPVER                = 1;//版本
const APIURL                = "http://api400.web4008.com/jyadwebapi/api/Interface/";//API接口前缀
//const ADDCUSTOMER         = self::APIURL.'AddCustomer';  //新建商户及分机
const ADDCUSTOMER           = 'http://api400.web4008.com/jyadwebapi/api/Interface/AddCustomer';  //新建商户及分机
const ADDCUSTOMERONE        = 'http://api400.web4008.com/jyadwebapi/api/Interface/AddCustomerone'; //新建商户（多大号模式）
const ADDWORKGROUP          = 'http://api400.web4008.com/jyadwebapi/api/Interface/AddWorkGroup'; //新建分机（多小号模式）
const EDITWORKGROUP         = 'http://api400.web4008.com/jyadwebapi/api/Interface/EditWorkGroup'; //修改分机信息及呼叫转移
const DELWORKGROUP          = 'http://api400.web4008.com/jyadwebapi/api/Interface/DelWorkGroup'; //删除分机
const CALLBACK              = 'http://api400.web4008.com/jyadwebapi/api/Interface/CallBack'; //回拨
const GETCALLDETAIL         = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetCallDetail'; //获取话单（分页）
const GETCALLDETAILBYLASTID = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetCallDetailByLastID'; //获取话单（实时）
const GETRECORDER           = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetRecorder'; //获取录音
const GETAGENTBIGCODEINFO   = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetAgentBigCodeInfo'; //获取代理商400号码信息
const GETCUSTBIGCODEINFO    = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetCustBigCodeInfo'; //获取商户400号码信息
const AddSEATUSER           = 'http://api400.web4008.com/jyadwebapi/api/Interface/AddSeatUser'; //添加坐席
const EDITSEATUSER          = 'http://api400.web4008.com/jyadwebapi/api/Interface/EditSeatUser'; //修改坐席
const DELSEATUSER           = 'http://api400.web4008.com/jyadwebapi/api/Interface/DelSeatUser'; //删除坐席
const EDITUSERBIND          = 'http://api400.web4008.com/jyadwebapi/api/Interface/EditUserBind'; //修改分机号和坐席的绑定关系
const GETWORKGROUPINFO      = 'http://api400.web4008.com/jyadwebapi/api/Interface/GetWorkGroupInfo'; //根据400号码和分机号查询分机号信息

private $user;//登录用户名
private $pwd;//登录密码 
private $pageSize;//分页条数
private $pageNow;//页码
private $account;//400账号
    
//初始化
function __construct(
	$config   =  array(
	'user'    => 'jk4008190996',
	'pwd'     => 'Jan8190cs',
	'account' => '4008190996',
	'pageSize'=> 100,
	'pageNow' => 1)
){
	$this->user        = $config['user'];//用户名
	$this->pwd         = $config['pwd'];//密码
	$this->pageSize    = $config['pageSize'];//分页数量
	$this->pageNow     = $config['pageNow'];//分页页码
	$this->account     = $config['account'];//400电话
}

/*AddCustomer 新建商户及分机
示例$VM =  array('loginname'=>'CD000583','loginpwd'=>'385000DC','extcode'=>'8001','tellist'=>'02988767968','custname'=>'test','workgroupname'=>'test','content'=>'你好，欢迎致电创典房产，请拨分机号','addtion'=>'1,0.13,2,0',);
返回json示例：{"result":"1","code":"111","message":"新建成功"} code成功为企业ID，失败为0
*/
function AddCustomer($data){
	$post              = array();
	$post['bigcode400']      = $this->account;//400号码 必填
	$post['loginname']       = $data['loginname'];//登录名 必填isset ( $data['loginname'] ) ? $data['loginname'] : ''
	$post['loginpwd']        = $data['loginpwd'];//登录密码 必填
	$post['extcode']         = $data['extcode'] ;//分机号码 必填
	$post['tellist']         = $data['tellist'];//电话列表(多个号码以英文,号隔开)必填
	$post['custname']        = $data['custname'];//商户名称
	if (isset ( $data['callfee'] )) {//通话资费 单位是元
		$post['callfee']       = $data['callfee'];
	}
	if (isset ( $data['msgfee'] )) {//短信资费 单位是元
		$post['msgfee']       = $data['msgfee'];
	}
	if (isset ( $data['adfee'] )) {//推广费 单位是元
		$post['adfee']       = $data['adfee'];
	}
	$post['workgroupname']   = $data['workgroupname'];//分机号名称
	if (isset ( $data['content'] )) {  //分机号提示音 (0：空，1：TTS，2：语音文件必须是wav格式)
		$post['content']       = $data['content'];
	}

	if (isset ( $data['acdtype'] )) {  //轮询模式 默认为一号通模式
		$post['acdtype']       = $data['acdtype'];
	}
	if (isset ( $data['msgtel'] )) {  //短信通知号码
		$post['msgtel']       = $data['msgtel'];
	}
	if (isset ( $data['timeout'] )) {  //超时时间默认为25
		$post['timeout']       = $data['timeout'];
	}
	$post['addtion']         = $data['addtion'];//增值业务信息1,0.5,1,1|2,0.3,2,0每一项增值业务以|分割，增值业务的具体参数以英文,号分割，顺序为增值业务ID,资费,是否包月(1是2否),是否开启(1开0关)
	$sendData          = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::ADDCUSTOMER,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*新建商户（多大号模式）
返回json示例：{"result":"1","code":"111","message":"新建成功"} code成功为企业ID，失败为0
*/
function AddCustomerone($data){
	$post              = array();
	$post['bigcode400']      = $this->account;//400号码 必填
	$post['loginname']       = $data['loginname'];//登录名 必填
	$post['loginpwd']        = $data['loginpwd'];//登录密码 必填
	$post['custname']        = $data['custname'];//商户名称 必填
	$post['callfee']         = $data['callfee'];//通话资费 单位是元 必填
	$post['msgfee']          = $data['msgfee'];//短信资费 必填
	$post['adfee']           = $data['adfee'];//推广费 必填
	$post['workgroupname']   = $data['workgroupname'];//分机号名称
	$post['addlist']         = $data['addlist'];//[{"addtionid": "1","addtionfee": "0.2","custtype": "2","opertype": "1"},……]addtionid 增值业务ID addtionfee 增值业务费用 custtype 是否包月( 1包月，2按条) opertype 是否开启( 1启用，0停用)
	$sendData          = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::ADDCUSTOMERONE,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*新建分机（多小号模式）
返回json示例：{"result":"1","code":"1","message":"新建成功"} code可忽略
*/
function AddWorkGroup($data){
	$post              = array();
	$post['bigcode400']      = $this->account;//400号码 必填
	$post['custid']          = $data['custid'];//商户ID
	$post['extinfo']         = $data['extinfo'];//[{"extcode": "1","workgroupname": "0.2","content": "2","acdtype": "1","msgtel": "1","timeout": "1","tellist": "1"},……] extcode：分机号（必填）  workgroupname：分机名称  content：提示音 acdtype：轮询模式 msgtel：短信号码 timeout：超时时间 tellist：接线员号码（必填多个号码以英文逗号隔开）
	$sendData          = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::ADDCUSTOMER,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*修改分机信息及呼叫转移
返回json示例：{"result":"1","code":"1","message":"新建成功"} code可忽略
*/
function EditWorkGroup($data){
	$post                    = array();
	$post['bigcode400']      = $this->account;//400号码 必填
	$post['extcode']         = $data['extcode'];//分机号码 必填
	$post['newextcode']      = $data['newextcode'];//新分机号号码 必填

	if (isset ( $data['workgroupname'] )) {  //分机号名称
		$post['workgroupname'] = $data['workgroupname'];
	}
	if (isset ( $data['content'] )) {  //分机号提示音 (0：空，1：TTS，2：语音文件必须是wav格式)
		$post['content']       = $data['content'];
	}
	if (isset ( $data['tellist'] )) {  //接线员号码（多个以英文,分割）
		$post['tellist']       = $data['tellist'];
	}
	if (isset ( $data['acdtype'] )) {  //轮询模式
		$post['acdtype']       = $data['acdtype'];
	}
	if (isset ( $data['startdate'] )) {  //开始日期（年月日，格式（20141212））
		$post['startdate']     = $data['startdate'];
	}
	if (isset ( $data['enddate'] )) {  //结束日期（年月日，格式（20151212））
		$post['enddate']       = $data['enddate'];
	}
	if (isset ( $data['starttime'] )) {  //开始时间（时分秒，格式（180000））
		$post['starttime']     = $data['starttime'];
	}
	if (isset ( $data['endtime'] )) {  //结束时间（时分秒，格式（080000））
		$post['endtime']       = $data['endtime'];
	}
	if (isset ( $data['recallday'] )) {  //开关（格式（0,0,0,0,0,1,1），周一至周五关闭，周末开启）
		$post['recallday']     = $data['recallday'];
	}
	if (isset ( $data['phonenum'] )) {  //呼叫转移处理内容 （对应optiontype设置）
		$post['phonenum']      = $data['phonenum'];
	}
	if (isset ( $data['optiontype'] )) {  //呼叫转移处理类型
		$post['optiontype']    = $data['optiontype'];
	}
	if (isset ( $data['calldaymax'] )) {  //同一个号码每天的最大呼叫次数 全局
		$post['calldaymax']    = $data['calldaymax'];
	}
	if (isset ( $data['calledsound'] )) {  //商户被叫提示音 全局
		$post['calledsound']   = $data['calledsound'];
	}
	if (isset ( $data['updateall'] )) {  //是否修改所有(calldaymax和calledsound是针对于商户的属性，一旦修改则影响到商户下所有大号和分机号，1为修改所有，0则忽略)
		$post['updateall']     = $data['updateall'];
	}
	$sendData          = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::EDITWORKGROUP,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*删除分机
返回json示例：{"result":"1","code":"1","message":"新建成功"} code可忽略
*/
function DelWorkGroup($data){
	$post                    = array();
	$post['bigcode400']      = $this->account;//400号码 为空则guid不能为空
	$post['extcode']         = $data['extcode'];//分机号码 为空则guid不能为空
	$post['guid']            = $data['guid'];//分机号guid 为空则bigcode400和extcode不能为空
	//$sendData          = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::DELWORKGROUP,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}


/*回拨 暂时不能使用
返回json示例：{"result":"1","code":"1","message":"新建成功"} code可忽略
*/
function CallBack($data){
	$post                      = array();
	$post['bigcode']           = $this->account;//400号码 必填
	$post['extcode']           = $data['extcode'];//分机号码 必填
	$post['direction']         = $data['direction'];//呼叫方向 必填（1先呼客户，0先呼坐席）
	$post['custnum']           = $data['custnum'];//坐席号码 必填（多个以|分割）
	$post['callingnum']        = $data['callingnum'];//客户号码 必填（只能为一个号码）
	$post['returnstr']         = isset ( $data['returnstr'] ) ? $data['returnstr'] : 'freely';//验证码（只能为数字或者字母）
	if (isset ( $data['crmuserid'] )) {//Crm坐席ID
		$post['crmuserid']     = $data['crmuserid'];
	}
	if (isset ( $data['crmusername'] )) {//Crm坐席名称
		$post['crmusername']   = $data['crmusername'];
	}
	if (isset ( $data['userloginname'] )) {//坐席登录名
		$post['userloginname'] = $data['userloginname'];
	}
	$sendData  = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::CALLBACK,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*根据400号码和分机号查询分机号信息
返回json示例：Array ( [id] => 19126491 [custid] => 14493661 [seatuserid] => 18931112 )  code可忽略
*/
function GetWorkGroupInfo($data,$type=true){
	$post                      = array();
	$res                      = array();
	$post['bigcode']           = $this->account;//400号码 必填
	$post['extcode']           = $data['extcode'];//分机号码 必填
	$sendData  = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::GETWORKGROUPINFO,json_encode($post)) ,true);
	if($type && $result['status'] == '1'){
		$res['id'] = $result['workgroup']['id'];//分机ID
		$res['custid'] = $result['workgroup']['seatuser'][0]['custid'];//商户ID
		$res['seatuserid'] = $result['workgroup']['seatuser'][0]['id'];//坐席ID
		return $res;
	}
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*根据400号码和分机号查询分机号信息
返回json示例：Array ( [id] => 19126491 [custid] => 14493661 [seatuserid] => 18931112 )  code可忽略
*/
function GetCallDetail($data,$type=true){
	$post                      = array();
	$res                       = array();
	$post['date']              = $data['date'];//日期 必填 （格式 20130507000000）
	$post['number']            = $this->pageSize;//每页条数 默认100
	$post['pagenum']           = $this->pageNow;//页数
	if( $type ){
		$post['column']            = $data['column'];//页数
	}else{
		$post['column']            = 'id,userid,ani,dni,startdate,enddate,callfee,recorderwav,callresult,calltype,callertime,calledtime,custid,bigcode,extcode,callid,proname,cityname,servicelevel,callresultname,username,workgroupname,custname,dnicityname,dniproname,customstring1,transcode';//页数
	}
	
	$sendData  = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::GETCALLDETAIL,json_encode($post)) ,true);
	return array('queryResult',$result,'send'=>$sendData);//请求借口 返回操作执行信息
}

/*根据400号码和分机号查询分机号信息
返回json示例：Array ( [id] => 19126491 [custid] => 14493661 [seatuserid] => 18931112 )  code可忽略
*/
function GetCallDetailByLastID($data,$type=true){
	$post                      = array();
	$res                       = array();
	$post['date']              = $data['date'];//日期 必填 （格式 20130507000000）
	$post['lastid']            = $data['lastid'];//第一次请求传1，后面请求为前一次lastid+1
	if( $type ){
		$post['column']            = $data['column'];//页数
	}else{
		$post['column']            = 'id,userid,ani,dni,startdate,enddate,callfee,recorderwav,callresult,calltype,callertime,calledtime,custid,bigcode,extcode,callid,proname,cityname,servicelevel,callresultname,username,workgroupname,custname,dnicityname,dniproname,customstring1,transcode';//页数
	}
	
	$sendData  = $this->ToUrlParams($post);//转码为form 对象
	$result = json_decode( $this->vpost(self::GETCALLDETAILBYLASTID,json_encode($post)) ,true);
	return $result;//请求借口 返回操作执行信息
}

//post前
private function ToUrlParams( $params ){
	$string = '';
	if( !empty($params) ){
		$array = array();
		foreach( $params as $key => $value ){
			$array[] = $key.'='.$value;
		}
		$string = implode("&",$array);
	}
	return $string;
}

//模拟post请求
private function vpost($url,$data){ // 模拟提交数据函数
	set_time_limit(0);
	$header = array(
			 "Content-Type: application/json", 	
			 "loginname:$this->user",
			 "Pwd:$this->pwd",
			 "roleid:3",				
	);
	//print_r($data);
	$curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS,$data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
	$tmpInfo = curl_exec($curl); // 执行操作

	if (curl_errno($curl)) {
		echo 'Errno'.curl_error($curl);//捕抓异常
	}
	curl_close($curl); // 关闭CURL会话
	return $tmpInfo; // 返回数据
}

}