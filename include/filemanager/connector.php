<?php
ob_start();
global $Config ;
include_once('upload.fun.php');
$Config['Enabled'] = true ;
!$curuser->isadmin() && SendError(1,'Need Administrator Permission!');
// Path to user files relative to the document root.
$Config['UserfilesPath'] = $cms_rel.$dir_userfile.'/' ;
//$Config['UserfilesPath'] = 'userfiles/' ;

$Config['UserfilesAbsolutePath'] = '' ;

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

$Config['AllowedExtensions']['file']	= array() ;
$Config['DeniedExtensions']['file']		= array('html','htm','php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','com','dll','vbs','js','reg','cgi','htaccess','asis') ;

$Config['AllowedExtensions']['image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['image']	= array() ;

$Config['AllowedExtensions']['flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['flash']	= array() ;

$Config['AllowedExtensions']['media']	= array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['media']	= array() ;

function RemoveFromStart( $sourceString, $charToRemove )
{
	$sPattern = '|^' . $charToRemove . '+|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function RemoveFromEnd( $sourceString, $charToRemove )
{
	$sPattern = '|' . $charToRemove . '+$|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function ConvertToXmlAttribute( $value )
{
	return utf8_encode( htmlspecialchars( $value ) ) ;
}

function GetUrlFromPath( $resourceType, $folderPath )
{
	$url=$GLOBALS['cms_abs'].$GLOBALS['dir_userfile'];
	if ( $resourceType == '' )
		return $url . $folderPath ;
	else
		return $url .'/' . strtolower( $resourceType ) . $folderPath ;
}

function RemoveExtension( $fileName )
{
	return substr( $fileName, 0, strrpos( $fileName, '.' ) ) ;
}

function ServerMapFolder( $resourceType, $folderPath )
{
	// Get the resource type directory.
	$sResourceTypePath = $GLOBALS["UserfilesDirectory"] . strtolower( $resourceType ) . '/' ;

	// Ensure that the directory exists.
	CreateServerFolder( $sResourceTypePath ) ;

	// Return the resource type directory combined with the required path.
	return $sResourceTypePath . RemoveFromStart( $folderPath, '/' ) ;
}

function GetParentFolder( $folderPath )
{
	$sPattern = "-[/\\\\][^/\\\\]+[/\\\\]?$-" ;
	return preg_replace( $sPattern, '', $folderPath ) ;
}

function CreateServerFolder( $folderPath )
{
	$sParent = GetParentFolder( $folderPath ) ;

	// Check if the parent exists, or create it.
	if ( !file_exists( $sParent ) )
	{
		$sErrorMsg = CreateServerFolder( $sParent ) ;
		if ( $sErrorMsg != '' )
			return $sErrorMsg ;
	}

	if ( !file_exists( $folderPath ) )
	{
		// Turn off all error reporting.
		error_reporting( 0 ) ;
		// Enable error tracking to catch the error.
		ini_set( 'track_errors', '1' ) ;

		// To create the folder with 0777 permissions, we need to set umask to zero.
		$oldumask = umask(0) ;
		mkdir( $folderPath, 0777 ) ;
		umask( $oldumask ) ;
		@touch($folderPath.'/index.htm');
		@touch($folderPath.'/index.html');

		$sErrorMsg = $php_errormsg ;
		// Restore the configurations.
		ini_restore( 'track_errors' ) ;
		ini_restore( 'error_reporting' ) ;

		return $sErrorMsg ;
	}
	else
		return '' ;
}

function GetRootPath()
{
	$sRealPath = realpath( './' ) ;

	$sSelfPath = $_SERVER['PHP_SELF'] ;
	$sSelfPath = substr( $sSelfPath, 0, strrpos( $sSelfPath, '/' ) ) ;

	return substr( $sRealPath, 0, strlen( $sRealPath ) - strlen( $sSelfPath ) ) ;
}

function SetXmlHeaders()
{
	ob_end_clean() ;

	// Prevent the browser from caching the result.
	// Date in the past
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
	// always modified
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
	// HTTP/1.1
	header('Cache-Control: no-store, no-cache, must-revalidate') ;
	header('Cache-Control: post-check=0, pre-check=0', false) ;
	// HTTP/1.0
	header('Pragma: no-cache') ;

	// Set the response format.
	header( 'Content-Type:text/xml; charset=utf-8' ) ;
}

function CreateXmlHeader( $command, $resourceType, $currentFolder )
{
	SetXmlHeaders() ;

	// Create the XML document header.
	echo '<?xml version="1.0" encoding="utf-8" ?>' ;

	// Create the main "Connector" node.
	echo '<Connector command="' . $command . '" resourceType="' . $resourceType . '">' ;

	// Add the current folder node.
	echo '<CurrentFolder path="' . ConvertToXmlAttribute( $currentFolder ) . '" url="' . ConvertToXmlAttribute( GetUrlFromPath( $resourceType, $currentFolder ) ) . '" />' ;
}

function CreateXmlFooter()
{
	echo '</Connector>' ;
}

function SendError( $number, $text )
{
	SetXmlHeaders() ;

	// Create the XML document header
	echo '<?xml version="1.0" encoding="utf-8" ?>' ;

	echo '<Connector><Error number="' . $number . '" text="' . htmlspecialchars( $text ) . '" /></Connector>' ;

	mexit() ;
}

function GetFolders( $resourceType, $currentFolder )
{
	// Map the virtual path to the local server path.
	$sServerDir = ServerMapFolder( $resourceType, $currentFolder ) ;

	// Array that will hold the folders names.
	$aFolders	= array() ;

	$oCurrentFolder = opendir( $sServerDir ) ;

	while ( $sfile = readdir( $oCurrentFolder ) )
	{
		if ( $sfile != '.' && $sfile != '..' && is_dir( $sServerDir . $sfile ) )
			$aFolders[] = '<Folder name="' . ConvertToXmlAttribute( $sfile ) . '" />' ;
	}

	closedir( $oCurrentFolder ) ;

	// Open the "Folders" node.
	echo "<Folders>" ;

	natcasesort( $aFolders ) ;
	foreach ( $aFolders as $sFolder )
		echo $sFolder ;

	// Close the "Folders" node.
	echo "</Folders>" ;
}

function GetFoldersAndfiles( $resourceType, $currentFolder )
{
	// Map the virtual path to the local server path.
	$sServerDir = ServerMapFolder( $resourceType, $currentFolder ) ;

	// Arrays that will hold the folders and files names.
	$aFolders	= array() ;
	$afiles		= array() ;

	$oCurrentFolder = opendir( $sServerDir ) ;

	while ( $sfile = readdir( $oCurrentFolder ) )
	{
		if ( $sfile != '.' && $sfile != '..' )
		{
			if ( is_dir( $sServerDir . $sfile ) )
				$aFolders[] = '<Folder name="' . ConvertToXmlAttribute( $sfile ) . '" />' ;
			else
			{
				$ifileDate = date ("Y-m-d", filemtime( $sServerDir . $sfile ));
				$ifileSize = filesize( $sServerDir . $sfile ) ;
				if ( $ifileSize > 0 )
				{
					$ifileSize = round( $ifileSize / 1024 ) ;
					if ( $ifileSize < 1 ) $ifileSize = 1 ;
				}

				//$afiles[] = '<file name="' . ConvertToXmlAttribute( $sfile ) . '" size="' . $ifileSize . '" />' ;
				$afiles[] = '<file name="' . ConvertToXmlAttribute( $sfile ) . '" date="' . $ifileDate . '" size="' . $ifileSize . '" />' ;
			}
		}
	}

	// Send the folders
	natcasesort( $aFolders ) ;
	echo '<Folders>' ;

	foreach ( $aFolders as $sFolder )
		echo $sFolder ;

	echo '</Folders>' ;

	// Send the files
	natcasesort( $afiles ) ;
	echo '<files>' ;

	foreach ( $afiles as $sfiles )
		echo $sfiles ;

	echo '</files>' ;
}

function CreateFolder( $resourceType, $currentFolder )
{
	$sErrorNumber	= '0' ;
	$sErrorMsg		= '' ;

	if ( isset( $_GET['NewFolderName'] ) )
	{
		$sNewFolderName = $_GET['NewFolderName'] ;

		if ( strpos( $sNewFolderName, '..' ) !== FALSE )
			$sErrorNumber = '102' ;		// Invalid folder name.
		else
		{
			// Map the virtual path to the local server path of the current folder.
			$sServerDir = ServerMapFolder( $resourceType, $currentFolder ) ;

			if ( is_writable( $sServerDir ) )
			{
				$sServerDir .= $sNewFolderName ;

				$sErrorMsg = CreateServerFolder( $sServerDir ) ;

				switch ( $sErrorMsg )
				{
					case '' :
						$sErrorNumber = '0' ;
						break ;
					case 'Invalid argument' :
					case 'No such file or directory' :
						$sErrorNumber = '102' ;		// Path too long.
						break ;
					default :
						$sErrorNumber = '110' ;
						break ;
				}
			}
			else
				$sErrorNumber = '103' ;
		}
	}
	else
		$sErrorNumber = '102' ;

	// Create the "Error" node.
	echo '<Error number="' . $sErrorNumber . '" originalDescription="' . ConvertToXmlAttribute( $sErrorMsg ) . '" />' ;
}


if ( !$Config['Enabled'] )
	SendError( 1, 'This connector is disabled. Please check the "editor/filemanager/browser/default/connectors/php/config.php" file' ) ;

// Get the "Userfiles" path.
$GLOBALS["UserfilesPath"] = '' ;

if ( isset( $Config['UserfilesPath'] ) )
	$GLOBALS["UserfilesPath"] = $Config['UserfilesPath'] ;
else if ( isset( $_GET['ServerPath'] ) )
	$GLOBALS["UserfilesPath"] = $_GET['ServerPath'] ;
else
	$GLOBALS["UserfilesPath"] = $cms_rel.$dir_userfile.'/' ;

if ( ! preg_match( '#/$#', $GLOBALS["UserfilesPath"] ) )
	$GLOBALS["UserfilesPath"] .= '/' ;

if ( strlen( $Config['UserfilesAbsolutePath'] ) > 0 )
{
	$GLOBALS["UserfilesDirectory"] = $Config['UserfilesAbsolutePath'] ;

	if ( ! preg_match( '#/$#', $GLOBALS["UserfilesDirectory"] ) )
		$GLOBALS["UserfilesDirectory"] .= '/' ;
}
else
{
	// Map the "Userfiles" path to a local directory.
	$GLOBALS["UserfilesDirectory"] = GetRootPath() . $GLOBALS["UserfilesPath"] ;
}

DoResponse() ;

function DoResponse()
{
	if ( !isset( $_GET['Command'] ) || !isset( $_GET['Type'] ) || !isset( $_GET['CurrentFolder'] ) )
		return ;

	// Get the main request informaiton.
	$sCommand		= $_GET['Command'] ;
	$sResourceType	= $_GET['Type'] ;
	$sCurrentFolder	= $_GET['CurrentFolder'] ;

	// Check if it is an allowed type.
	if ( !in_array( $sResourceType, array('file','image','flash','media') ) )
		return ;

	// Check the current folder syntax (must begin and start with a slash).
	if ( ! preg_match( '#/$#', $sCurrentFolder ) ) $sCurrentFolder .= '/' ;
	if ( strpos( $sCurrentFolder, '/' ) !== 0 ) $sCurrentFolder = '/' . $sCurrentFolder ;

	// Check for invalid folder paths (..)
	if ( strpos( $sCurrentFolder, '..' ) )
		SendError( 102, "" ) ;

	// file Upload doesn't have to Return XML, so it must be intercepted before anything.
	if ( $sCommand == 'fileUpload' )
	{
		fileUpload( $sResourceType, $sCurrentFolder ) ;
		return ;
	}

	CreateXmlHeader( $sCommand, $sResourceType, $sCurrentFolder ) ;

	// Execute the required command.
	switch ( $sCommand )
	{
		case 'GetFolders' :
			GetFolders( $sResourceType, $sCurrentFolder ) ;
			break ;
		case 'GetFoldersAndfiles' :
			GetFoldersAndfiles( $sResourceType, $sCurrentFolder ) ;
			break ;
		case 'CreateFolder' :
			CreateFolder( $sResourceType, $sCurrentFolder ) ;
			break ;
	}

	CreateXmlFooter() ;

	mexit() ;
}
?>
