function _08_uploadHTML5(config){
	$.extend(this.config,config);
}

_08_uploadHTML5.prototype = {
  config:{
	  url:  CMS_ABS + uri2MVC({'upload': 'post'}),//上传url
	  num:3,//上传数量
	  isSingle:0,
	  size:8000,//上传大小,单位kb
	  type:'image/jpeg,image/gif,image/png',//上传类型
	  },
  target:null,//input表单
  state:0,//上传状态
  init:function(target){
	   var me = this;
	 $('._08_upload_action input').on('change',function(event){
		 var target = event.target;
		 me.target = target;
		 me.handleFiles(target.files);
	});
	 
	 //绑定删除事件
	 $('._08_upload_list li a').live('click',function(event){
		 var target = event.target;
		 $(target).parent().remove();
		 me.setValue();
		 me.numSub();
	 }); 
},
handleFiles:function(files){
	var me = this;
	for(var i=0,len=files.length;i<len;i++){	
		var file = files[i];		
		if(me.checkSize(file.size)) return;//检查图片大小
		var filename = file.name;
		var reader = new FileReader();	
		reader.readAsDataURL(file);		
		reader.onloadstart = function(){
			//me.loading();
		};
		reader.onprogress = function(){
			
		};
		reader.onload = function(){
			if(me.checkNum()) return;//检查图片数量
			$.ajax({
				type:'POST',
				url:me.config.url,
				async: false,
				data: {pic1: this.result, file_name: filename},
				success:function(serverData){ 
						var serverData = JSON.parse(serverData);
						//服务返回错误
						if(parseInt(serverData.error)!=0){
							me.message(serverData.error_message);
							me.state = 0;
							return;
						}
						me.state = 1;
						me.showThumb(serverData);						
				}			
				});
						
			

		};
		reader.onloadend = function(){
			//me.loadover();
		};
	}

},

loading:function(){
	var me = this;
	var target = $(me.target);	
	//target.parent().find('img').attr('src','http://192.168.1.54/house/images/common/upload/fenmian.png');
},
loadover:function(){
	var me = this;
	var target = $(me.target);
	//target.parent().find('img').attr('src','http://192.168.1.54/house/images/common/upload/add.png');
},
onloadstart:function(t){
	
},

onload:function(){
	
},

onprogress:function(){
	
},
onload:function(){
	
},
onloadend:function(){
	
},
showThumb:function(img){
	var me = this;
	var target = $(me.target);
	var lihtml = '<li class="_08_upload_itembox" data-value="'+img.remote+'"><img src="'+img.remote+'" style="width:100%;height:100%;"/><a herf="javascript:void(0);" title="删除"></a></li>';
	$(lihtml,{style:'background:url('+img.remote+')  50% 50% / cover;'}).insertBefore('._08_upload_action');
	me.setValue();
	me.numAdd();
},
setValue:function(){
	var me = this;
	var target= me.target;
	var value = '';
	$(target).parent().parent().find('._08_upload_itembox').each(function(i,d){
		 value += $(d).attr('data-value') + '\n';
	});	
	$(target).parent().find('input:hidden').val(value);
},
checkFile:function(){
	
},
numAdd:function(){
	var me = this;
	me.num += 1;
},
numSub:function(){
	var me = this;
	me.num  -= 1;
},
checkNum:function(){
	var me = this;
	var imgli = $(me.target).parent().parent().find('._08_upload_itembox');
	if(!me.config.isSingle){
		if( imgli.length>=me.config.num){
			me.message('最多上传'+me.config.num+'张图片!');return true;
		}	
	}else{
		me.removeAll();		
	}
},
removeAll:function(){
	var me = this;
	var target = me.target;
	$(target).parent().parent().find('._08_upload_itembox').remove();
	 me.setValue();
},
checkSize:function(size){
	var me = this;
	if(size/1000>me.config.size){
		me.message('上传大小超过'+me.config.size+'kb!');
		return true;	
	}
},
message:function(msg){
	alert(msg);
}

};

// rFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

