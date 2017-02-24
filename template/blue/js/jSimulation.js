/**
*author:ahuing
*date:2014-10-29
*jSimulation v1.0
*modify:2014-10-29
 */
(function($) {
		// 参数选项设置
    var selectConfig = {
		event       : 'click'//事件，默认点击，'mouseover'
		,linkActive : 0 //点击下拉框里项（链接）时，是否跳转
		,vertical   : 1 //下拉框里的项排列方向
		,dropWidth  : 0 //下拉框的宽度，0表示和select宽度相同
		,clickFun   : null //点击时触发的代码，例如可以做联运效果
	};
	$('<link rel="stylesheet">').appendTo('head').attr('href',(typeof(tplurl)!='undefined'?tplurl:'')+'css/jSimulation.css');
	$.fn.jSimulation = function(opt){
		return this.each(function() {
			var that = $(this);
			if(that.is('select')){
				var o = $.extend({},selectConfig, opt || {})
				, $option = that.find('option')
				, $select = $('<div id="j-'+this.name+'" class="j-select"><span class="j-txt"></span><i></i></div>')
				, $selectTxt = $select.find('.j-txt').html(that.find('option:selected').html());

				var sHtml = '<span class="j-droplist">';
				$option.each(function(a, b) {
					sHtml += '<a class="'+(!$(b).prop('selected')||'j-selected')+'" style="float:'+(!o.vertical?'left':'none')+';" href="'+b.value+'">'+b.innerHTML+'</a>';
				});
				sHtml += '</span>';

				var $selectDrop = $(sHtml).width(o.dropWidth||'100%');
				$select.insertAfter(that).css({
					width   : that.outerWidth()
					,zIndex : that.css('z-index')
					,float  : that.css('float')
				}).append($selectDrop)
				[o.event](function() {
					$selectDrop.css('display', 'block');
				}).mouseleave(function() {
					$selectDrop.css('display', 'none');
				}).on('click','a',function() {
					var $this = $(this);
					$selectDrop.css('display', 'none');
					$option.removeAttr('selected').eq($this.index()).prop('selected','selected');
					$this.addClass('j-selected').siblings().removeClass('class j-selected');
					$selectTxt.html($this.html());
					o.clickFun&&o.clickFun.call();
					if(!o.linkActive) return false;
				})
			}else{
				var $label = $('label[for="'+this.id+'"]');

				that.prop('checked')&&$label.addClass('checked');
				$label.addClass('j-'+that[0].type+' j-'+that[0].name)
				.on('click',function() {
					that.triggerHandler('click');
					if (that.is(':checkbox')) {
						$label.toggleClass('checked');
					}else{
						$('input[name="'+that[0].name+'"]').each(function() {
							$('label[for="'+this.id+'"]').removeClass('checked');
						});
						$label.addClass('checked');
					};
				})
			}
			//隐藏原对象
			that.css('display', 'none');
		});
	}
})(jQuery);