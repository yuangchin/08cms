<?php
defined('M_COM') || exit('No Permission');
$players = array (
  1 => 
  array (
    'plid' => '1',
    'cname' => 'RealPlayer',
    'ptype' => 'media',
    'issystem' => '1',
    'available' => '1',
    'vieworder' => '1',
    'exts' => 'rm,rmvb',
    'template' => '<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="{$height}" width="{$width}">
<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" height="100%" id=RP1 name=RP1 width="100%">
  <param name="AUTOSTART" value="-1">
  <param name="SHUFFLE" value="0">
  <param name="PREFETCH" value="0">
  <param name="NOLABELS" value="0">
  <param name="CONTROLS" value="Imagewindow">
  <param name="CONSOLE" value="clip1">
  <param name="LOOP" value="0">
  <param name="NUMLOOP" value="0">
  <param name="CENTER" value="0">
  <param name="MAINTAINASPECT" value="1">
  <param name="BACKGROUNDCOLOR" value="#000000">
</object>
<OBJECT classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA  height=30 id=RP2 name=RP2 width="100%">
<PARAM NAME="_ExtentX" VALUE="4657">
<PARAM NAME="_ExtentY" VALUE="794">
<PARAM NAME="AUTOSTART" VALUE="-1">
<PARAM NAME="SRC" VALUE="{$url}">
<PARAM NAME="SHUFFLE" VALUE="0">
<PARAM NAME="PREFETCH" VALUE="0">
<PARAM NAME="NOLABELS" VALUE="-1">
<PARAM NAME="CONTROLS" VALUE="ControlPanel">
<PARAM NAME="CONSOLE" VALUE="clip1">
<PARAM NAME="LOOP" VALUE="0">
<PARAM NAME="NUMLOOP" VALUE="0">
<PARAM NAME="CENTER" VALUE="0">
<PARAM NAME="MAINTAINASPECT" VALUE="1">
<PARAM NAME="BACKGROUNDCOLOR" VALUE="#000000">
</OBJECT>
<object classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA height=30 id=RP3 name=RP3 width="100%">
  <param name="_ExtentX" value="4657">
  <param name="_ExtentY" value="794">
  <param name="AUTOSTART" value="-1">
  <param name="SHUFFLE" value="0">
  <param name="PREFETCH" value="0">
  <param name="NOLABELS" value="-1">
  <param name="CONTROLS" value="StatusBar">
  <param name="CONSOLE" value="clip1">
  <param name="LOOP" value="0">
  <param name="NUMLOOP" value="0">
  <param name="CENTER" value="0">
  <param name="MAINTAINASPECT" value="1">
  <param name="BACKGROUNDCOLOR" value="#000000">
</object>
    
    </td>
  </tr>
</table>
',
  ),
  2 => 
  array (
    'plid' => '2',
    'cname' => '微软wmPlayer',
    'ptype' => 'media',
    'issystem' => '1',
    'available' => '1',
    'vieworder' => '2',
    'exts' => 'mepg,avi',
    'template' => '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" id="MediaPlayer1" width="{$width}" height="{$height}">
<param name="AudioStream" value="-1">
<param name="AutoSize" value="-1">
<param name="AutoStart" value="-1">
<param name="AnimationAtStart" value="-1">
<param name="AllowScan" value="-1">
<param name="AllowChangeDisplaySize" value="-1">
<param name="AutoRewind" value="0">
<param name="Balance" value="0">
<param name="BaseURL" value>
<param name="BufferingTime" value="15">
<param name="CaptioningID" value>
<param name="ClickToPlay" value="-1">
<param name="CursorType" value="0">
<param name="CurrentPosition" value="0">
<param name="CurrentMarker" value="0">
<param name="DefaultFrame" value>
<param name="DisplayBackColor" value="0">
<param name="DisplayForeColor" value="16777215">
<param name="DisplayMode" value="0">
<param name="DisplaySize" value="0">
<param name="Enabled" value="-1">
<param name="EnableContextMenu" value="-1">
<param name="EnablePositionControls" value="-1">
<param name="EnableFullScreenControls" value="-1">
<param name="EnableTracker" value="-1">
<param name="Filename" value="{$url}" valuetype="ref">
<param name="InvokeURLs" value="-1">
<param name="Language" value="-1">
<param name="Mute" value="0">
<param name="PlayCount" value="1">
<param name="PreviewMode" value="-1">
<param name="Rate" value="1">
<param name="SAMIStyle" value="1">
<param name="SAMILang" value>
<param name="SAMIFilename" value>
<param name="SelectionStart" value="-1">
<param name="SelectionEnd" value="-1">
<param name="SendOpenStateChangeEvents" value="-1">
<param name="SendWarningEvents" value="-1">
<param name="SendErrorEvents" value="-1">
<param name="SendKeyboardEvents" value="0">
<param name="SendMouseClickEvents" value="0">
<param name="SendMousemovieeEvents" value="0">
<param name="SendPlayStateChangeEvents" value="-1">
<param name="ShowCaptioning" value="0">
<param name="ShowControls" value="-1">
<param name="ShowAudioControls" value="-1">
<param name="ShowDisplay" value="0">
<param name="ShowGotoBar" value="0">
<param name="ShowPositionControls" value="-1">
<param name="ShowStatusBar" value="-1">
<param name="ShowTracker" value="-1">
<param name="TransparentAtStart" value="-1">
<param name="VideoBorderWidth" value="0">
<param name="VideoBorderColor" value="0">
<param name="VideoBorder3D" value="0">
<param name="Volume" value="0">
<param name="WindowlessVideo" value="0">
</object>',
  ),
  11 => 
  array (
    'plid' => '11',
    'cname' => 'ckplayer播放器',
    'ptype' => 'media',
    'issystem' => '0',
    'available' => '1',
    'vieworder' => '3',
    'exts' => 'mp4,f4v,m3u8',
    'template' => '<script type="text/javascript" src="{$tplurl}images/ckplayer/ckplayer.js" charset="utf-8"></script>
                       <script type="text/javascript">
                          var flashvars={
                              f:\'{$url}\'
                              ,c:0
                              ,b:1
                              // ,s:\'3\'
                              // ,p:1
                              };
                          var params={bgcolor:\'#FFF\',allowFullScreen:true,wmode:\'transparent\',allowScriptAccess:\'always\'};
                          CKobject.embedSWF(\'{$tplurl}images/ckplayer/ckplayer.swf\',\'video\',\'ckplayer_a1\',\'100%\',\'{$height}\',flashvars,params);
                      </script>',
  ),
  4 => 
  array (
    'plid' => '4',
    'cname' => 'Flash模块',
    'ptype' => 'flash',
    'issystem' => '1',
    'available' => '1',
    'vieworder' => '4',
    'exts' => 'swf',
    'template' => '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="{$width}" height="{$height}">
<param name="movie" value="{$url}">
<param name="quality" value="high">
<param name="wmode" value="transparent">
<embed wmode="transparent" src="{$url}" quality="high" type="application/x-shockwave-flash" 
   pluginspage="http://www.macromedia.com/go/getflashplayer" width="{$width}" height="{$height}"></embed>
</object>',
  ),
  5 => 
  array (
    'plid' => '5',
    'cname' => 'Flv播放器',
    'ptype' => 'flash',
    'issystem' => '1',
    'available' => '1',
    'vieworder' => '5',
    'exts' => 'flv',
    'template' => '<object type="application/x-shockwave-flash" data="{$tplurl}images/vcastr3.swf" width="{$width}" height="{$height}" id="vcastr3">
    <param name="movie" value="{$tplurl}images/vcastr3.swf"/>
    <param name="allowFullScreen" value="true" />
<param name="wmode" value="transparent">
    <param name="FlashVars" value="xml=
                {vcastr}
                    {channel}
                        {item}
                            {source}{$url}{/source}
                            {duration}{/duration}
                            {title}{/title}
                        {/item}
                    {/channel}
                    {config}
{isLoadBegin}false{/isLoadBegin}
                    {/config}
                {/vcastr}"/>
</object>',
  ),
) ;