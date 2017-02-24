/**
 * @class _08cms.multiStore
 * @author Peace@08cms.com
 * @参考: http://www.cnblogs.com/zjcn/archive/2012/07/03/2575026.html#comboWrap
 * Demo: 
	_08cms.locStore.set('aa1','bb1');
	var tt1 = _08cms.locStore.get('aa1');
	console.log(tt1);
	_08cms.sesStore.set('aa2','bb2');
	var tt2 = _08cms.sesStore.get('aa2');
	console.log(tt2);
 */
 
// localStorage/sessionStorage存储公共方法属性 =================================================

function multiStore(flag){ // local,session
	this.parFlag = flag=='session' ? 'sessionStorage' : 'localStorage';
	this.parStore = flag=='session' ? window.sessionStorage : window.localStorage;
	// 是否支持localStorage/sessionStorage
	this.ready = function(){ 
		return (this.parFlag in window) && (window[this.parFlag] !== null); 
	};
	// 扩展 : 最多设置保存mnum个key(如最近浏览历史记录)
	// demo: _08cms.locStore|sesStore.setGroup('{$ckpre}chid{$chid}','{aid}',10); // ('_auto_dev52_chid2','542350',10); 
	// ??? 一条记录存储更多信息? 这里没有处理, 统一规范? 目前要扩展, 如类似信息【id|529026;time|14-07-2110:50】
	this.setGroup = function(keyid,nowkey,mnum){
		if(nowkey.length==0) return;
		if(!mnum) mnum = 10;
		var oldkeys = this.get(keyid); 
		if(!oldkeys){ 
			var keystr = nowkey;
		}else{ 
			var oldarr = oldkeys.split(','); 
			var keystr = nowkey; unum = 1;
			for(var i=0;i<oldarr.length;i++){ 
				if(oldarr[i]==nowkey || oldarr[i].length==0) continue;
				if(unum<mnum){
					keystr += ','+oldarr[i];	
					unum++;
				}else{
					break;	
				}
			}
		}
		keystr = keystr.replace(/[^0-9A-Za-z_\.\-\:\,\|\;]/g,''); // setGroup内容字符限制 \=\)\(\]\[  善用ascii码
		this.set(keyid,keystr);
	};
	// 扩展 : 初始化信息(不支持localStorage/sessionStorage)
	// demo: _08cms.locStore|sesStore.initMessage('itemList','<li class="none">不支持localStorage/sessionStorage(本地存储)</li>')
	this.initMessage = function(id,msg){
		var canFlag = this.ready();
		if(!canFlag) document.getElementById(id).innerHTML = msg;
	};
	// 设置值
	this.set = function(key, value){
		//在iPhone/iPad上有时设置setItem()时会出现诡异的QUOTA_EXCEEDED_ERR错误；这时一般在setItem之前，先removeItem()就ok了
		if( this.get(key) !== null )
			this.remove(key);
		this.parStore.setItem(key, value);
	};
	// 获取值 查询不存在的key时，有的浏览器返回undefined，这里统一返回null
	this.get = function(key){
		var v = this.parStore.getItem(key);
		return v === undefined ? null : v;
	};
	this.each = function(fn){
		var n = this.parStore.length, i = 0, fn = fn || function(){}, key;
		for(; i<n; i++){
			key = this.parStore.key(i);
			if( fn.call(this, key, this.get(key)) === false )
				break;
			//如果内容被删除，则总长度和索引都同步减少
			if( this.parStore.length < n ){
				n --;
				i --;
			}
		}
	};
	this.remove = function(key){
		this.parStore.removeItem(key);
	}
	this.clear = function(){
		this.parStore.clear();
	};
	
}

// localStorage/sessionStorage放进_08cms对象中 =================================================

_08cms.locStore = new multiStore('local');
_08cms.sesStore = new multiStore('session');

// 扩展 =================================================

//function exStore(p1){
//	this.dosth = function (){ };
//}
//exStore.prototype = new mulStore('local');
//_08cms.exStore = new exStore('p');

