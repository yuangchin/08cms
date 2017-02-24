<?PHP
/*
** 管理后台脚本，兼容了文档添加与详情编辑，如果拆分两者脚本，可在详情脚本中去除添加专用部分的代码
** 如通过url传入$chid，可基本兼容不同模型的文档操作
*/
/* 参数初始化代码 */
# $chid = 5;//指定chid
#-----------------

aheader();
$pid = empty($pid)?cls_message::show('请指定楼盘ID'):max(1,intval($pid));

//要指定合辑id变量名$pidkey、合辑项目$arid
$_arc = new cls_arcedit; //商业地产-合辑兼容
$_arc->set_aid($pid,array('au'=>0,'ch'=>0)); 
$_chid = $_arc->archive['chid'];
$_arid = $_chid==4 ? 1 : 35;//指定合辑项目id
$_abtab = $_chid==4 ? "aalbums" : "aalbums_arcs";//指定合辑项目id

if(!submitcheck('bsubmit')){
    $ex_url = "<a href=\"?entry=extend&extend=loudong_pid&chid=111&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\">添加/修改楼栋</a>----<a href=\"?entry=extend&extend=sppicarchive&chid=$_chid&aid=$pid&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\">添加/修改沙盘图片</a>";
    $_sql = "SELECT a.aid,a.subject,a.shapan,a.xszt FROM {$tblprefix}".atbl(111)." a INNER JOIN {$tblprefix}$_abtab b WHERE a.aid =b.inid AND b.pid='$pid' AND a.checked = 1 ";
    $lp_hj_loudong = $db->query("$_sql AND a.shapan != ''"); 
    $_loudong_str = '';//被选中的楼栋的数据
    $_ld_valid_ids = '';//被选中的楼栋的AID
    while($rows = $db->fetch_array($lp_hj_loudong)){
        $_loudong_str .= "'".$rows['aid']."^".$rows['subject'].":".$rows['shapan']."',"; 
        $_ld_valid_ids .= $rows['aid'].",";
    }       
    $_loudong_str = empty($_loudong_str)?'':"[".substr($_loudong_str,0,-1)."]";
    $_ld_valid_ids = empty($_ld_valid_ids)?'':substr($_ld_valid_ids,0,-1);
    
    
    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"".$cms_abs."images/common/sha.css\" />";
    tabheader("沙盘管理----$ex_url",'newform',"?entry=extend$extend_str&pid=$pid",2,1,1);
        
    $_str = '';
    $_str .= "<tr><td class=\"txt txtright fB\" width=\"10%\">楼栋</td><td class=\"txt txtleft\">";
    $_sql = $db->query($_sql);  
    $i = 1;     
    
    if(!($_loudong_num = $db->num_rows($_sql))){
        $_str .= "暂无楼栋<a href=\"?entry=extend&extend=ldarchive&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\"><font color=\"#03F\">>>点击添加楼栋</font></a>";
    }else{
        $_loudong_ids = '';
        while($r = $db->fetch_array($_sql)){
            $_str .= "<input type='checkbox' id='cb_".$r['aid']."' name='cb_".$r['aid']."' value='".(empty($r['shapan'])?'':$r['shapan'])."' data-xszt='".$r['xszt']."' data-ox=\"$r[aid],$r[subject],$i\">$r[subject] &nbsp;&nbsp;";
            $i ++;
            $i%6==0 && $_str .= "<br/>";
            $_loudong_ids .= ','.$r['aid'];
        }
    }
    
    $_str .= "<input type=\"hidden\" id=\"fmdata[loudongs]\" name=\"fmdata[loudongs]\" value=\"".(empty($_loudong_ids)?'':$_loudong_ids)."\"></td></tr>";
    $_str .= "<tr><td class=\"txt txtright fB\" width=\"10%\">沙盘图</td><td class=\"txt txtleft\">";
    
    if(!($_pic = $db->result_one("SELECT stpic FROM {$tblprefix}archives_$_chid WHERE aid = '$pid'"))){
        $_str .= "暂无沙盘图片<a href=\"?entry=extend&extend=sppicarchive&chid=$_chid&aid=$pid&pid=$pid\" onclick=\"return floatwin('open_arcexit',this)\"><font color=\"#03F\">>>点击添加沙盘图片</font></a>";
    }else{
        $_str .= "<div id='shapan' style=\"overflow:auto; width:640px; height:480px;position:relative;border:2px solid #CAE4F7\"><div id='shapan-i'><img id='MapImages' src='".(empty($ftp_enabled)?$cms_abs:$ftp_url).$_pic."'/>";
        $_str .= "</div></div>";
    }
    $_str .= "</td></tr>";
    echo $_str;
    tabfooter('bsubmit');
    a_guide('sandtable');
    echo cls_phpToJavascript::loadJQuery();
    echo "<script type='text/javascript' src='".$cms_abs."template/".$templatedir."/js/jqmodal.js?20140915'></script>"; 
    ?>
    <script type="text/javascript">
    !function($) {
        var dots_data = <?php echo empty($_loudong_str)?"''":$_loudong_str;?>;
        var movingNow,dotMoved=0;
        
        var ld_checked = '<?php echo $_ld_valid_ids;?>';
        var ld_checked_aids = ld_checked.split(',');
        for(var i = 0; i < ld_checked_aids.length; i ++){
            var checkbox_id = document.getElementById('cb_'+ ld_checked_aids[i]);
            if(checkbox_id){                    
                checkbox_id.checked = true;
            }
        }   
        sha_init(); 
        $('[data-ox]').click(function(event) {
            var ox = $(this).data('ox').split(',');
            sha_add(ox[0], ox[1], ox[2]);
        });
        // 初始化-所有点
        function sha_init(id){
            for(var i=0;i<dots_data.length;i++){
                var iarr = dots_data[i].split(':');
                var imsg = iarr[0].split('^');
                var ipos = iarr[1].split(','); 
                sha_idot(imsg[0],imsg[1],parseInt(ipos[0]),parseInt(ipos[1]));
            }
            /*$('#shapan-i').jqDrag({
                attachment:'#shapan'
            })*/

        }

        // 增加一个点
        function sha_add(id,msg,offset){
            var check_box = document.getElementById('cb_' + id);
            if(check_box.checked == true){
                sha_idot(id,msg,10,10);
            }else{  
                sha_del(id);
                return;
            }
        }
        // 删除一个点
        function sha_del(id){
            $('#dot_'+id).remove();
            $('#cb_'+id).prop({checked:false,disabled:false});
            //清理表单项目
        }

        // 显示一个点
        //id:楼栋ID
        //ldmc:楼栋名称
        function sha_idot(id,ldmc,left,top){
            $('<a>',{
                id: 'dot_'+id,
                'class': 'sha-dot sha-dot-'+$('#cb_'+id).attr('data-xszt'),
                html: ldmc+'<i></i><b title="close"></b>'
            })
            .css({
                left: left,
                top: top
            })
            .jqDrag({
                attachment:'#shapan-i'
            })
            .on('dragEnd', function (el, l, t) {
                $('#' + this.id.replace('dot','cb')).val(l+','+t);
            })
            .appendTo('#shapan-i')
            .find('b').click(function(){ sha_del(id); })

        }
        
    }($)
    </script>     

    <?php
            
}else{
    // die();
    $_loudong_arr = array_filter(explode(',',$fmdata['loudongs']));
    if(empty($_loudong_arr)) cls_message::show('没有楼栋ID,请先添加楼栋。',"?entry=extend$extend_str&pid=$pid");
    foreach($_loudong_arr as $k){
        $_loudong_name = 'cb_'.$k;
        $_loudong_zuopbiao = isset($$_loudong_name) ? $$_loudong_name : '';         
        $db->query("UPDATE {$tblprefix}".atbl(111)." SET shapan='".$_loudong_zuopbiao."' WHERE aid = '$k'");
    }
    cls_message::show('沙盘编辑完成',"?entry=extend$extend_str&pid=$pid");
}

?>
