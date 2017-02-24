<?php
// 关注用户相关
// 随微信规则更新

class cls_wmpUser extends cls_wmpBasic{
	
	protected $user_list = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s'; 
	protected $user_info = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
	protected $user_remark = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=%s';
	protected $user_batchget = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=%s';
	
	protected $group_get = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token=%s';
    protected $group_create = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token=%s';
    protected $group_rename = 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token=%s';
	protected $group_delete = 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token=%s';
    protected $group_getid = 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=%s';
    protected $group_move = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=%s';
	
	function __construct($cfg=array()){
		parent::__construct($cfg); 
	}	
	
	//批量获取用户基本信息
	//$users : string / array()
	function getUserBatch($users){ 
		$url = sprintf($this->user_batchget, $this->actoken);
		if(is_string($users)){
			$users = explode(',',$users);	
		}
		$udata = '';
		foreach($users as $v){
			if(empty($v)) continue;
			$udata .= (empty($udata) ? '' : ',')."{\"openid\":\"$v\",\"lang\":\"zh-CN\"}"; 
		}
		$paras = "{\"user_list\":[$udata]}";
		//$paras = cls_w08Basic::jsonEncode($paras);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 5,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->user_batchget);
	}
	
	// 列表
    function getUserInfoList($next=''){ 
		$url = sprintf($this->user_list, $this->actoken, $next);
		$data = cls_w08Basic::getResource($url."&count=10",5); 
		return cls_w08Basic::jsonDecode($data,$this->user_list); 
    }
	
	// 用户信息
	function getUserInfo($openid){ 
		$url = sprintf($this->user_info, $this->actoken, $openid);	
		$data = cls_w08Basic::getResource($url,3);
		return cls_w08Basic::jsonDecode($data,$this->user_info);
	}

	/**
	 * 设置用户备注名
	 * 可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号。
	 *
	 * @param $openid 用户标识
	 * @param $rename 新的备注名，长度必须小于30字符
	 * @return array 正常时返回Array([errcode] => 0,[errmsg] => ok)
	 */
	function setUserRemark($openid,$rename){ //
		$url = sprintf($this->user_remark, $this->actoken);
		$paras = array(
			'openid' => $openid,
			'remark' => $rename,
		);
		$paras = cls_w08Basic::jsonEncode($paras);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras,
		)); 
		return cls_w08Basic::jsonDecode($data,$this->user_remark);
	}

	function groupList(){  
		$url = sprintf($this->group_get, $this->actoken);	
		$data = cls_w08Basic::getResource($url,3); 
		return cls_w08Basic::jsonDecode($data,$this->group_get); 
	}
	
	function groupCreate($gname){  
		$url = sprintf($this->group_create, $this->actoken);	
		$paras = array('group'=>array('name'=>$gname,));
		$paras = cls_w08Basic::jsonEncode($paras);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras, //'{"group":{"name":"test"}}', //
		));
		return cls_w08Basic::jsonDecode($data,$this->group_create); 
	}
	
	function groupRename($gid,$gname){  
		$url = sprintf($this->group_rename, $this->actoken);	
		$paras = array('group'=>array('id'=>$gid,'name'=>$gname,));
		$paras = cls_w08Basic::jsonEncode($paras);
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras, //'{"group":{"name":"test"}}', //
		));
		return cls_w08Basic::jsonDecode($data,$this->group_rename); 
	}

	function groupDelete($gid){  
		$url = sprintf($this->group_delete, $this->actoken);	
		$paras = '{"group":{"id":"'.$gid.'"}}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras, 
		));
		return cls_w08Basic::jsonDecode($data,$this->group_delete); 
	}
	
	function groupMove($openid,$gid){  
		$url = sprintf($this->group_move, $this->actoken);	
		$paras = '{"openid":"'.$openid.'","to_groupid":'.$gid.'}';
		$data = cls_w08Basic::getResource(array(
			'urls' => $url,
			'timeOut' => 3,
			'method' => 'POST',
			'postData' => $paras, 
		));
		return cls_w08Basic::jsonDecode($data,$this->group_move); 
	}

	
    /**
     * 坐标转换
     * @return array 返回json格式的ip列表
     */
    static function convMap($map='',$type=0){    
		$mar = explode(',',preg_replace('/[^\d|\,|\.]/', '', $map)); 
		$data = file_get_contents("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=$mar[1]&y=$mar[0]");
		// from=0比from=2更准确 : 0表示地球坐标，2表示火星坐标，4表示百度坐标(所以这个原始坐标是地球坐标)
		$data = json_decode($data); //print_r($data);;
		if(empty($data->error)){
			$mapx = base64_decode($data->x);
			$mapy = base64_decode($data->y);
		}else{
			$mapx = $mar[1];
			$mapy = $mar[0];	
		}
		return array("$mapx,$mapy",'x'=>$mapx,'y'=>$mapy);
    }
	
	//static function getUserInfo($openid,$actoken=''){	}

}
