<?php
!defined('M_COM') && exit('No Permisson');
$timestamp = time();
$errmsg = '';
@include dirname(dirname(__FILE__)) . '/base.inc.php';
$dberror = str_replace(array($tblprefix, $dbname), array('#__', substr($dbname, 0, 2) . '***'), $this->error());
$dberrno = $this->errno();

if($dberrno == 1114){
?>
<html>
<head>
<title>Max Onlines Reached</title>
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 9px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB">
        <br /><b style="font-size: 10px">Onlines reached the upper limit</b>
        <br /><br /><br />Sorry, the number of online visitors has reached the upper limit.
        <br />Please wait for someone else going offline or visit us in idle hours.
        <br /><br />
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
<?
	exit();
}else{
	if(defined('_08_INSTALL_EXEC')) $phpviewerror = 3; //安装下,显示错误详情
	if($phpviewerror == 3){
		$error = _08_Profiler::getInstance();        
        $error_filestr = str_replace(
            array($dbuser, $dbpw, $dbname), 
            array(substr($dbuser, 0, 2) . '****', substr($dbpw, 0, 2) . '****', substr($dbname, 0, 2) . '****'),
            (string) $error->getDebugBacktraceMessage($sql)
        );
	} else $error_filestr = 'Hidden (Please Set $phpviewerror = 3 In base.inc.php)';
	
    
	$message && $errmsg .= "<b>08CMS Info</b>: $message\n\n";
    
    # 有模板管理权限才提示错误
    if(is_object($curuser) && $curuser->NoBackFunc('tpl') && $phpviewerror != 3)
    {
        $errmsg .= 'Hidden (Please log in the administrator to check)';
    }
    else
    {    	
        $error_filestr = htmlspecialchars($error_filestr);
        $dberror = htmlspecialchars($dberror);
    	$errmsg .= "<b>Time</b>: ".date("Y-n-j H:i", $timestamp)."\n";
    	$sql && $errmsg .= "<b>SQL</b>: ".htmlspecialchars(str_replace(' '.$tblprefix,' #__',$sql))."\n";
    	$errmsg .= "<b>File</b>:  {$error_filestr}\n";
    	$errmsg .= "<b>Error</b>:  $dberror\n";
    	$errmsg .= "<b>Errno.</b>:  $dberrno";
    }
    
	echo "</table></table></table></table></table>\n";
	echo "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #FFFFFF;margin:10px 10px;padding:10px 10px;border:#eeeeee solid 1px; text-align:left;\">";
	echo nl2br($errmsg);
	echo '</p>';
	exit();
}

?>