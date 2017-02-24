<?php
@set_time_limit(0);
include_once('../general.inc.php');
function fileUpload($resourceType,$currentFolder){
	$sErrorNumber = '0';
	$sfileName = '';
	$c_upload = cls_upload::OneInstance();
	$c_upload->current_dir = $currentFolder;
	$uploadfile = $c_upload->local_upload('Newfile',$resourceType);
	if($uploadfile['error']){
		$sErrorNumber = '202' ;
	}else{
		$sErrorNumber = '0';
		$sfileName = cls_url::tag2atm($uploadfile['remote']);
	}
	echo '<script type="text/javascript">' ;
	echo 'window.parent.frames["frmUpload"].OnUploadCompleted('.$sErrorNumber.',"'.str_replace('"','\\"',$sfileName).'");';
	echo '</script>' ;
	mexit() ;
}
?>
