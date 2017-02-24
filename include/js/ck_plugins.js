/* ckeditor插件 产品行情 */
if(typeof($) == 'undefined') function $(id){return document.getElementById(id);}
// 添加选中项
function addSelect(forms){
    var len = forms.elements.length;
    var e, ids = getcookie('ids'), arcstr = getcookie('arcstr');
    for(var i = 0; i < len; ++i) {
        e = forms.elements[i];
        if(e.checked && e.name.indexOf('selectid') >= 0){
            if(ids.indexOf(e.value) < 0) {
                ids += ',' + e.value;
                arcstr += encodeURIComponent(',' + $('arc'+e.value).innerHTML);
    			$('show_select').innerHTML +=
                    '<span id="ss">&nbsp;&nbsp;<input type="checkbox" value="' + e.value +
                    '" name="checkeds[]" checked="checked" onclick="closed(this);" id="checkeds'+
                    e.value+'" title="'+$('arc'+e.value).innerHTML+'"/><label for="checkeds'+e.value+'" title="点击关闭选择">' + $('arc'+e.value).innerHTML +
                    '</label>';
            }
		}
    }
    setcookie('ids', ids);
    setcookie('arcstr', arcstr);
    return false;
}

/**
 * 关闭选中的节点
 * @param object obj input对象
 */
function closed(obj) {
    var ids = getcookie('ids'), arcstr = getcookie('arcstr');
    ids = ids.replace(','+obj.value, '');
    arcstr = arcstr.replace(','+obj.title, '');
    obj.parentNode.removeChild(obj.nextSibling);
    obj.parentNode.removeChild(obj);
    setcookie('ids', ids);
    setcookie('arcstr', arcstr);
}

// 初始化选择项
function init() {
    setcookie("ids", "");
    setcookie("arcstr", "");
}
/* ckeditor插件 产品行情 end */