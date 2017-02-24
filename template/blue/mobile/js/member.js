var getLSI = function(a) {
	var _chid = $(a).attr('data-chid')
	return {
		aids : localStorage.getItem('chid_'+_chid)
		, chid : _chid
	};
}

var browserNum = 0;
var $browserList = $('#browser').find('article').each(function() {
	var sAids = getLSI(this).aids;
	browserNum += sAids?sAids.split(',').length:0
});

$('a[href="#browser"]').next().html(browserNum);

$('#browser').one('pageshow',function() {
	// browser
	$(this).find('article').one('articleshow',function() {
		var getdb = getLSI(this)
		, $this = $(this);
		// 浏览历史
		var browserOpt = {
			_param : {
		        'aj_model'    : 'a,'+getdb.chid+',1' , //模型信息(a-文档/m-会员/cu-交互/co-类目,3,1-模型表; 如:a,3,1)
		        'aj_check'    : 1 ,     //是否审核(0/1或不设置)
		        'aj_pagesize' : 10 ,
		        'aj_ids'      : getdb.aids || null,
		        'aj_pagenum'  : 1 , //当前分页(数字,默认2)
		        'datatype'    : 'json',
		        'ordermode'   : 0
		    } ,
			'wrap'     : '#'+this.id ,
			'dataWrap' : $this.find('ul') ,
			'ajax'     : 'pageload' ,
			'template' : function() {
		    	return '<li>'
		            + '     <i class="icon icon-f054"></i><a href="'+this.arcurl+'"><strong>'+this.subject+'</strong></a>'
		            + '</li>';
		    }
		}

		pullRefresh(browserOpt);

		 $this.find('.button').click(function() {
	        J.confirm('提示','你确定要删除此浏览历史吗？',function(){
				localStorage.setItem('chid_'+getdb.chid, '');
				 $this.find('ul').html('');
	    		J.showToast('删除成功！','success top');
	        },function(){
	        	return false;
	        });
		});
	});
})

// favorite
$('#favorite').one('pageshow',function() {
	if (!loginfo.user_info.mid){
		noLogInfo(this);
		return false;
	}
	$(this).find('article').one('articleshow',function() {
		var getdb = getLSI(this);
// 收藏夹
		var favoriteOpt = {
			_param : {
				'aj_model'     : 'a,'+(getdb.chid)+',1', //模型信息(a-文档/m-会员/cu-交互/co-类目,3,1-模型表; 如:a,3,1)
				'aj_check'     : 1 ,     //是否审核(0/1或不设置)
				'aj_pagesize'  : 10 ,
				'chid'         : getdb.chid ,
				'aj_pagenum'   : 1 , //当前分页(数字,默认2)
				'datatype'     : 'json',
				'ordermode'    : 0
		    } ,
			'wrap'     : '#'+this.id ,
			'dataWrap' : $(this).find('ul') ,
			'ajax'     : 'pageload_mcu' ,
			'template' : function() {
		    	return '<li>'
		            + '     <i class="icon icon-f054"></i><a href="'+this.arcurl+'"><strong>'+this.subject+'</strong></a>'
		            + '</li>';
		    }
		}

		pullRefresh(favoriteOpt);
	});
})

$('#esfsection').one('pageshow',function() {
	if (!loginfo.user_info.mid){
		noLogInfo(this);
		return false;
	}
	esfOpt._param.aj_whrfields = 'mid,=,'+loginfo.user_info.mid;
	pullRefresh(esfOpt);
})

$('#czsection').one('pageshow',function() {
	if (!loginfo.user_info.mid){
		noLogInfo(this);
		return false;
	}
	czOpt._param.aj_whrfields = 'mid,=,'+loginfo.user_info.mid;
	pullRefresh(czOpt);
})