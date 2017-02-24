
function settmenu(){
	for(i=1;i<30;i++){
		var tid=Cookie('menubox'+i);
		if(tid && $id('menubox'+i)){
			$id('menubox'+i).className= "cor_box close_box";	
		}
	}
}

function changeBoxState(obj){
	var pobj= obj.parentNode;
	var id=pobj.id;

	for(var i=1;i<30;i++){
		if($id('menubox'+i)){
			$id('menubox'+i).className= "cor_box close_box";	
		}
	}
	var cls = pobj.className;
    for(var i=1;i<30;i++){
        var ids = 'menubox'+i;
        if($id(ids)){
        Cookie(ids,ids,'-1Y');
        }
    }
    pobj.className = cls=="cor_box"?"cor_box close_box":"cor_box";
    Cookie(id,id,'1Y');
    setMenuClass();
}

function setMenuClass(){     
    for(var i = 1;i < 30 ;i++){
        if(document.getElementById('menubox'+i)&&getcookie('menubox'+i)){           
          document.getElementById('menubox'+i).className = 'cor_box';  
        }else if(document.getElementById('menubox'+i)&&!getcookie('menubox'+i)){
          document.getElementById('menubox'+i).className = 'cor_box close_box'; 
        }
    }     
}

listen(window, 'load', setMenuClass);

//设置选中项的样式
function SetCookie(k){
	Cookie('uc_menu',k,'1Y');
}
function SetClass(){
	var k =Cookie('uc_menu');
  for(i=1;i<50;i++){
    var tagid="menu"+i.toString();
	var obj=$id(tagid.toString());
	if(obj){
	    if(k==i){
	    	obj.className = "cur";
		}else{
	    	obj.className = "";
		}
	 }
  }
}
listen(window, 'load', SetClass);
//listen(window, 'load', settmenu);

function MenuInit() {
    var flag = true;
    for(var i = 1;i < 30;i++){
        if(document.getElementById('menubox'+i)&&getcookie('menubox'+i)){        
           flag = false;
        }     
    }
    for(var i = 1;i < 30;i++){
        if(flag&&document.getElementById('menubox'+i)){        
           document.getElementById('menubox'+i).className = 'cor_box';
           Cookie('menubox'+i,'menubox'+i,'1Y');
           break;
        }     
    }
  
}
listen(window,'load',MenuInit);

// 会员中心提示
function ftip_inti(ckid){
	var ckstr = getcookie(tipm_ckkey);
	var hcnt = 0; //隐藏个数
	if(ckstr.indexOf(ckid+')')>0){
		ftip_close(ckid);
	}else{
		ftip_open(ckid);
	}

}
function ftip_open(ckid){
	$id('tipm_ptop_msg_'+ckid).style.display = ''; 
	$id('tipm_ptop_lamp_'+ckid).style.display = 'none';
	var ckstr = getcookie(tipm_ckkey);
	if(ckstr.indexOf(ckid+')')>0){
		setcookie(tipm_ckkey,ckstr.replace('('+ckid+')',''),10321000123);
	}

}
function ftip_close(ckid){
	$id('tipm_ptop_msg_'+ckid).style.display = 'none'; 
	$id('tipm_ptop_lamp_'+ckid).style.display = '';
	var ckstr = getcookie(tipm_ckkey);
	if(ckstr.indexOf(ckid+')')<0){
		setcookie(tipm_ckkey,ckstr+'('+ckid+')',10321000123);
	}
}

/**
 * 提交表但前，再次确认表单元素
 * @param  string fmid 输入 表单元素ID；
 * @param  string fmname 输入 表单元素名称；
 * @param  string msg 输入 提示信息；
 * demo : tabfooter("bsubmit\" onclick=\"return sendReCheck('sms_count','充值条数','确认执行充值?');\"");

 * @return boll true/false
 */
function sendReCheck(fmid,fmname,msg){
	fmname = fmname ? fmname : fmid;
	msg = msg ? msg : '确认执行此操作?';
	val = $id(fmid).value;
	msg += "\n" + fmname +" : "+ val;
	if(confirm(msg)){ 
		return true;
	}else{ 
		return false;
	} 
}
