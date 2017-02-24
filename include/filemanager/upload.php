<?php
@set_time_limit(0);
include_once('../general.inc.php');
$c_upload = cls_upload::OneInstance();
$uploadfile = $c_upload->local_upload('Newfile',$_GET['type']);
if($uploadfile['error']){
	SendResults( '202' ) ;
}else{
	$sErrorNumber = '0';
	SendResults('0',cls_url::tag2atm($uploadfile['remote'])) ;
}
function SendResults($errorNumber,$fileUrl='',$fileName='',$customMsg='')
{
	echo '<script type="text/javascript">' ;
	echo 'window.parent.OnUploadCompleted('.$errorNumber.',"'.str_replace('"','\\"',$fileUrl ).'","'.str_replace('"','\\"',$fileName).'","'.str_replace('"','\\"',$customMsg).'");';
	echo '</script>' ;
	mexit() ;
}

?>
