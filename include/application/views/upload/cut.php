<?php foreach ( array('mconfigs', 'imgurl', 'timestamp', 'base_inc_configs', '_get') as $var ) { $$var = $this->$var; } 
$cms_top = cls_env::mconfig('cms_top');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$base_inc_configs['mcharset']?>" />
<title>上传图片裁剪</title>
<meta name="Keywords" content="08CMS 图片剪裁" />
<meta name="Description" content="08CMS 图片剪裁，支持php，asp，jsp，asp.net 调用 头像剪裁，预览组件" />
<script type="text/javascript">document.domain = "<?=$cms_top?>" || document.domain;</script>
<style type="text/css">
    body {
        color: #333;
        background: white;
        margin: 0px;
        padding: 0px;
        text-align: center;
        font-size: 12px;
    }
    *, body {
        font-family: Arial,Helvetica,sans-serif;
        line-height:30px;font-family:verdana;
        color:#333;
        border:0px;
        padding:0px;
    }
    input {height: 18px; line-height: 18px;}
    a {text-decoration: none;}
    h1,h3{margin:15px 0 5px 0;padding:0;font-size:17px;font-family:microsoft yahei;font-weight:normal;border-bottom: 1px solid #CCC;}
    span{color:#f30;margin:0 4px;}
    .mr5 {margin-right: 5px;}
    #set_cut_size {width: 655px; margin: 0 auto;}
    .input_css {
        background: none repeat scroll 0 0 #F9F9F9;
        border-color: #666666 #CCCCCC #CCCCCC #666666;
        border-image: none;
        border-style: solid;
        border-width: 1px;
        color: #333333;
        font-family: "Lucida Grande",Verdana,Lucida,Helvetica,Arial,sans-serif;
        padding: 2px 0;
        width: 50px;
    }
    a.w_local { background: url('<?php echo $mconfigs['cms_abs'];?>images/common/upload/image_upload.png') no-repeat; width: 85px; background-position: 0 0; height: 29px; float: left; margin-left: 10px; }   
    a.w_local:hover { background-position: 0 -30px; color: rgb(0, 0, 0) ! important; text-decoration: none; }
</style>
<!--放入项目中 start -->
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>include/js/common.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>include/js/floatwin.js"></script>
<script type="text/javascript">
var issingle = <?php echo empty($_get['issingle']) ? 0 : (int)$_get['issingle'];?>;
var varname = '<?php echo empty($_get['varname']) ? '' : $_get['varname'];?>';
function uploadevent(status,serverData){
    eval('var serverData = ' + serverData + ';');
    var wid = '<?php echo empty($_get['handlekey']) ? 'main' : "_08winid_{$_get['handlekey']}";?>';
    try {
        var parentDocument = parent.document.getElementById(wid);
        var parentDOM = parentDocument.contentDocument.getElementById('<?php echo @$_get['img_id'];?>');
    } catch (e) {
        var parentDocument = parent.document;
        var parentDOM = parentDocument.getElementById('<?php echo @$_get['img_id'];?>');
        
        // IE
        if ( parentDOM == null )
        {
            var parentDocument = parent.document.getElementById(wid);
            var parentDOM = parentDocument.contentWindow.document.getElementById('<?php echo @$_get['img_id'];?>');             
        }
    }
    
    switch(status)
    {
        case '1':
        case 1:
            parentDOM.setAttribute('src', serverData.name);
            if ( issingle )
            {
                try {
                    var _valueDom = parentDocument.contentDocument.getElementById('_08_upload_' + varname);
                } catch (e) {
                    _valueDom = parentDocument.getElementById('_08_upload_' + varname);
                }
                _valueDom.value = serverData.name;
            }
            else
            {
                var _node = parentDOM.parentNode.parentNode.lastChild.previousSibling;
                if ( _node == null )
                {
                    _node = parentDOM.parentNode.parentNode.lastChild;
                }
                // 让隐藏域再重新设置值
                _node.childNodes[1].focus();
                _node.childNodes[1].blur();
            }
            winclose();
        break;
                
        default:
            window.location.reload(); 
        break;
    } 
}

function winclose(){
    var win_id = document.CWindow_wid;
    try {
        window.close();
        var w=window.parent;
        
        if ( document.getElementById('mymoviename') != null )
        {
            document.body.removeChild(document.getElementById('mymoviename'));
        }
        
        w.floatwin('close_' + win_id,0,0,0,0,0,1);
    } catch(e){}
}

function saveSetCutSize()
{
    var cut_width_value = document.getElementById('cut_width').value;
    var cut_height_value = document.getElementById('cut_height').value;
    if ( /cut_width[\/|=]/.test(document.URL) || /cut_height[\/|=]/.test(document.URL) )
    {
        var url = document.URL.replace(/cut_width([\/|=])\d+/, 'cut_width$1' + cut_width_value).replace(/cut_height([\/|=])\d+/, 'cut_height$1' + cut_height_value);
        location.href = url;
    }
    else
    {
    	location.href = (document.URL + uri2MVC('&cut_width=' + cut_width_value + '&cut_height=' + cut_height_value, false));
    }    
}
</script>
<!--放入项目中 start -->
</head>

<body>
<?php
if ( isset($_get['cut_width']) )
{
    $_get['cut_width'] = (int) $_get['cut_width'];
}
else
{
	$_get['cut_width'] = 162;
}
if ( isset($_get['cut_height']) )
{
    $_get['cut_height'] = (int) $_get['cut_height'];
}
else
{
	$_get['cut_height'] = 162;
}

$pCut = "{$_get['cut_width']}|{$_get['cut_height']}";
@$flashvar = "imgUrl={$imgurl}&uploadUrl={$mconfigs['cmsurl']}" . _08_ROUTE_ENTRANCE . "upload/post/mode/swf/type/image/wmid/{$_get['wmid']}/&uploadSrc=false&showCame=true&pCut={$pCut}&pSize={$pCut}";
?>
<!--放入项目中 end -->
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="650" height="450" id="mymoviename">
    <param name="movie" value="<?php echo $mconfigs['cmsurl'];?>images/common/upload/avatar.swf?t=<?php echo $timestamp;?>">
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <param name="flashvars" value="<?php echo $flashvar;?>" />
    <embed src="<?php echo $mconfigs['cmsurl'];?>images/common/upload/avatar.swf?t=<?php echo $timestamp;?>" quality="high" bgcolor="#ffffff" width="650" height="450" wmode="transparent" flashvars="<?php echo $flashvar;?>" name="mymoviename" align="" type="application/x-shockwave-flash" allowscriptaccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>
</object>
<!--放入项目中 end -->

<!--
<div id="set_cut_size">
    <div style="float: left; margin-left: 100px;">
        调整裁剪框宽度：<input type="text" name="cut_width" id="cut_width" class="input_css mr5" value="<?php echo $_get['cut_width'];?>" />
        调整裁剪框高度：<input type="text" name="cut_height" id="cut_height" class="input_css mr5"  value="<?php echo $_get['cut_height'];?>" />
    </div>
    <a class="w_local" href="javascript:void(0)" onclick="saveSetCutSize();" >保存</a>
</div>
-->
</body>
</html>