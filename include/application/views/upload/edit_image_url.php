<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>远程附件地址</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->base_inc_configs['mcharset']; ?>" />
<script type="text/javascript">var parent_wid = <?php echo empty($this->_get['parent_wid']) ? 0 : (int)$this->_get['parent_wid']; ?>, varname = '<?php echo $this->_get['field'];?>', handlekey = '<?php echo @(int)$this->_get['handlekey']; ?>';
</script>
<script type="text/javascript">
var originDomain = originDomain || document.domain;
document.domain = '<?php echo $this->mconfigs['cms_top'];?>' || document.domain;
</script>
<script type="text/javascript" src="<?php echo $this->mconfigs['cmsurl']; ?>images/common/base64.js"></script>
<style type="text/css">
.btn {
    background: none repeat scroll 0 0 #DDDDDD;
    border-color: #DDDDDD #666666 #666666 #DDDDDD;
    color: #000000;
    cursor: pointer;
    margin-top: 10px;
    width: 100%;
    height: 36px;
    vertical-align: middle;
}
</style>
</head>
<body style="margin: 0px; padding: 0px; text-align: center;">
<div><textarea name="textarea_value" id="textarea_value" placeholder="在此填写远程附件地址，一个图片一行（格式：图片地址|图片属性）" onfocus="javascript:if(this.value == '在此填写远程附件地址，一个图片一行（格式：图片地址|图片属性）'){this.value = ''}" onblur="if(this.value.length == 0){this.value = '在此填写远程附件地址，一个图片一行（格式：图片地址|图片属性）';}" style="width: 99%; border: 1px #ccc solid; height: 200px;color:#aaa;">在此填写远程附件地址，一个图片一行（格式：图片地址|图片属性）</textarea></div>
<div><input name="save" id="save" type="submit" class="btn" value="添 加" onclick="saveAction()" /></div>
<script type="text/javascript">
    var parentDocument = parent.document.getElementById('_08winid_' + handlekey);
    if ( parentDocument == null )
    {
        parentDocument = parent.document.getElementById('main');
		if(parentDocument == null)
		{
			parentDocument = parent.document;
		}
		else
		{
			var _parentDocument = parentDocument.contentDocument;
            if ( _parentDocument == undefined )
            {
                parentDocument = parentDocument.contentWindow.document;
            }
            else
            {
            	parentDocument = _parentDocument;
            }
		}
    }
    else
    {
    	parentDocument = parentDocument.contentDocument;
    }
    var parentDOM = parentDocument.getElementById('_08_upload_' + varname);
    var parentIframeDOM = parentDocument.getElementById('iframe_' + varname);
    var parentImgBoxDOM = parentDocument.getElementById('imgbox_' + varname);
    var textareaDOM = document.getElementById('textarea_value');
//    function loadData() {
//        textareaDOM.value = parentDOM.value;
//    }
    
    function saveAction() {
        if ( !/^(https?|ftp):\/\/.*?\.[a-z]/i.test(textareaDOM.value) )
        {
            alert('远程附件地址不合法。');
            return false;
        }
        var serverData, _value, _startIndex = parentImgBoxDOM.childNodes.length;
        var newValue = textareaDOM.value.split('\n');
        for(var i = 0; i < newValue.length; ++i)
        {
            serverData = {};
            if ( !newValue[i] )
            {
                continue;
            }
            ++_startIndex;
            serverData.id = 'SWFUpload_0_' + varname + '_0_' + _startIndex;
            _value = newValue[i].split('|');
            serverData.name = _value[0];
            serverData.index = _startIndex;
            if ( _value[1] != null )
            {
                serverData.title = _value[1];
            }
            else
            {
            	serverData.title = '';
            }
			if ( _value[2] != null )
            {
                serverData.link = _value[2];
            }
            else
            {
            	serverData.link = '';
            }
            serverData.ufid = BASE64.encoder(_value[0] + '#' + varname + '#' + _startIndex);
            serverData.isUpload = 0;
            serverData.filestatus = -4;
            
            parentIframeDOM.contentWindow._init(serverData);
        }
        
        winclose();
    }

    function winclose(){
        var win_id = document.CWindow_wid;
        try {
            window.close();
            var w=window.parent;            
            w.floatwin('close_' + win_id,0,0,0,0,0,1);
        } catch(e){}
    }
</script>
</body>
</html>