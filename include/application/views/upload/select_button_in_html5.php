<?php foreach ( array('ftypes', 'otype', 'mconfigs', '_get', 'type', 'maxcount', 'timestamp', 'base_inc_configs', 'accept') as $var ) { $$var = $this->$var; } ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>附件上传 - <?=$mconfigs['hostname']?></title>
<meta name="viewport" content="width=100%, initial-scale=1.0, maximum-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=<?=$base_inc_configs['mcharset']?>" />
<?php
$_get['field'] = empty($_get['field']) ? '' : preg_replace('/[^\w\[\]]/', '', $_get['field']);
if( !empty($mconfigs['cms_top']) && !empty($_get['domain']) )
{
    echo '<script type="text/javascript"> document.domain = "'.$mconfigs['cms_top'].'"; </script>';
}
cls_phpToJavascript::loadJQuery();
?>
<script type="text/javascript">
var CMS_ABS = '<?php echo $mconfigs['cms_abs'];?>', CMS_URL = '<?php echo $mconfigs['cmsurl'];?>', _08_ROUTE_ENTRANCE = '<?php echo _08_ROUTE_ENTRANCE;?>', UPLOAD_URL = CMS_URL + _08_ROUTE_ENTRANCE, parent_wid = <?php echo empty($_get['parent_wid']) ? 0 : (int)$_get['parent_wid']?>, varname = '<?php echo $_get['field'];?>', handlekey = '<?php echo @$_get['handlekey'];?>';
try { var safeVarname = parent._08_uploadData_<?php echo preg_replace('/[^\w]/', '', $_get['field']);?>; } catch(e){}
var swfu, is_ckeditor = <?php echo empty($_get['is_ckeditor']) ? 'false' : 'true';?>;
</script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>include/js/common.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>include/js/floatwin.js"></script>

<link type="text/css" rel="stylesheet" href="<?=$mconfigs['cmsurl']?>images/common/jqueryui/css/custom-theme/jquery-ui-1.10.0.custom.css" />
<link type="text/css" rel="stylesheet" href="<?=$mconfigs['cmsurl']?>images/common/bootstrap/css/bootstrap.min.css" />
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>images/common/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>images/common/jqueryui/js/jquery-ui-1.11.0.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>images/common/jqueryui/js/jquery.ui.touch-punch.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>images/common/lightbox/jquery.lightbox-0.5.js"></script>
<script type="text/javascript" src="<?=$mconfigs['cmsurl']?>images/common/upload/upload_html5.js?t=<?php echo TIMESTAMP;?>"></script>
<script type="text/javascript">
var wid = '"' + Cookie('wid') + '"', wmid = '<?php echo @$this->wmid;?>';
var auto_compression_width = <?php echo @intval($_get['auto_compression_width']);?>;

window.onload = function () {
    try { parent.document.getElementById('loading_' + varname).style.display='none'; } catch (err) {}
    var _08_upload_config = {
        url: CMS_ABS + uri2MVC({'upload': 'post', 'type': '<?=$type?>', 'wmid': wmid, 'auto_compression_width': auto_compression_width}),
        file_upload_limit : <?php echo $maxcount;?>,
        progressTarget: 'imgbox_' + varname,
        issingle: <?php echo (substr($type, strlen($type) - 1) !== 's' ? 1 : 0); ?>,
        delete_url: uri2MVC("upload=delete&ufids"),
        varname: varname,
        type: '<?php echo $type;?>', 
        file_types : "<?=$ftypes?>",
        thumb_width: 80,
        thumb_height: 60,        
        imgsFlag:'<?php echo empty($_get['imgsFlag']) ? '' : $_get['imgsFlag'];?>',
        imgsCom:'<?php echo empty($_get['imgsCom']) ? '' : $_get['imgsCom'];?>', 
        file_types_limit : {<?=$otype?>}  
    }
    console.log(_08_upload_config.url)
    _08_uploadHTML5.init(_08_upload_config);
    
    // 如果是修改状态则以服务端数据生成缩略图
    if ( safeVarname != undefined )
    {
        var file = safeVarname;
        for ( var i = 0; i < file.length; ++i )
        {
            _08_uploadHTML5.createThumb(file[i]);
        }
    }
}
</script>

<?php if(empty($_get['is_ckeditor'])) { ?>
<style type="text/css">
.mr10 {margin-right: 10px;}
a.w_local { background: url('<?php echo $mconfigs['cms_abs'];?>images/common/upload/add.png') no-repeat ; }
input:hover { cursor: pointer; }
a.w_local, a.w_phone, a.w_localn, a.w_cut, a.w_phonen { position: relative; display: inline-block; font: 12px/29px Arial,"宋体"; color: rgb(102, 102, 102) ! important; text-align: right; padding-right: 10px; }
a.w_local { width: 94px; background-position: 0 0; height: 74px; }
</style>
<?php } ?>
</head>
<body style="margin: 0px; padding: 0px;">
<?php if(empty($_get['is_ckeditor'])) { ?>
<a class="mr10 w_local" href="javascript:void(0)" style="position: absolute; z-index: 1;"></a>
<?php } ?>
<input name="<?php echo $_get['field'];?>[]" type="file" id="<?php echo $_get['field'];?>" <?php if ((substr($type, strlen($type) - 1) == 's')) {?> multiple="multiple"<?php } ?> onchange="_08_uploadHTML5.handleFiles(this.files)" style="width: 94px; height: 74px; opacity: 0; position: absolute; z-index: 2;" accept="<?php echo $accept;?>" />
</body>
</html>