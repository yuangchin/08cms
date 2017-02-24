<?php
/**
 * 表格树模板，暂时不做成通用，到时再扩展
 */
      if ( empty($this->tableTree) )
      {
          $catalogs = array();
      }
      else
      {
          $catalogs = $this->tableTree;
      }
      
      echo '<script type="text/javascript">var cata = [';
		foreach($catalogs as $caid => $catalog)echo "[$catalog[level],$caid,'" . str_replace("'","\\'",mhtmlspecialchars($catalog['title'])) . "','$catalog[url]',$catalog[pid],$catalog[level]],";
		empty($this->treesteps) && $this->treesteps = '';
		echo <<<DOT
];
document.write(tableTree({data:cata,ckey:'ckey_0_',step:'{$this->treesteps}'.split(',')[0],html:{
		head: '<td class="txtC" width="40">ID</td>'
			+ '<td class="txtL" width="230"%code%>菜单名称（一级菜单最多4个汉字，二级菜单最多7个汉字） %input%</td>'
			+ '<td class="txtL">链接/KEY （注：如果某一级菜单下有二级菜单则该一级菜单里必须为空，支持变量： {aid}、{mid}、{cms_abs} ）</td>',
		cell:[2,4],
		rows:'<td class="txtC" width="40">%1%</td>'
			+ '<td class="txtL" width="230">%ico%<input name="catalogsnew['
					+ '%1%][title]" value="%2%" size="25" maxlength="7" type="text" /></td>'
			+ '<td class="txtL" width="400"><input name="catalogsnew['
					+ '%1%][url]" value="%3%" id="catalogsnew[%1%][url]" type="text" style="width:400px; margin-top:4px;" />&nbsp;&nbsp;&nbsp;&nbsp;<a class="catalogsnew_url" href="javascript:;"><< 关联系统菜单</a></td>'
			+ '<td class="txtC" width="100">'
					+ '<input name="catalogsnew[%1%][pid]" value="%4%" type="hidden" />'
                    + '<input name="catalogsnew[%1%][level]" value="%5%" type="hidden" /></td>'
		},
	callback : true
}));  
</script>
DOT;

# 菜单列表
if ( !empty($this->menu_list['menus']) )
{
    $menus_string = '';
    if ( is_array($this->menu_list['menus']) )
    {
        foreach ( $this->menu_list['menus'] as $_param => $menu ) 
        {
            if ( $menu )
            {
                if ( is_numeric($_param) )
                {
                    $url = '#';
                }
                else
                {
                	parse_str($_SERVER['QUERY_STRING'], $queryString);
                    $queryString['cache_id'] = $_param;
                    $url = '?' . http_build_query($queryString);
                }
                
                $menus_string .= ('<a href="' . $url . '">' . $menu . '</a>');                
            }
        }
    }
    else
    {
    	$menus_string = (string) $this->menu_list['menus'];
    }
    
    echo <<<DOT
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#opciones').hide();
		jQuery('#settings').click(function(){
		    jQuery('#xubox_layer1').hide();
			jQuery('#opciones').slideToggle();
			jQuery(this).toggleClass("cerrar");
        });
        jQuery('.ui_bubble_closed').click(function(){
            jQuery('#xubox_layer1').hide();
        });
        $('#xubox_layer1').css({position:'fixed', right: '40px'});
DOT;
if ( !empty($this->menu_list['menus_ids']) )
{
    echo <<<DOT
        $('.catalogsnew_url').click(function(){
            var _thisEq = $('.catalogsnew_url').index(this);
            var menus_ids = {$this->menu_list['menus_ids']}, _string = '', _num = 0;
            for(var i in menus_ids)
            {
                if ( (typeof(menus_ids[i].sub_button) == 'undefined') && menus_ids[i].key )
                {
                    _string += ('<a href="javascript:addLinkItem(\'' + menus_ids[i].key + '\', ' + _thisEq + ');">' + (++_num) + '、(' + menus_ids[i].name + ')' + menus_ids[i].name + ' (' + menus_ids[i].key + ') </a>');
                }
                else if ( menus_ids[i].sub_button.length )
                {
                    for(var j in menus_ids[i].sub_button)
                    {
                        if ( menus_ids[i].sub_button[j].key )
                        {
                            _string += ('<a href="javascript:addLinkItem(\'' + menus_ids[i].sub_button[j].key + '\', ' + _thisEq + ');">' + (++_num) + '、(' + menus_ids[i].name + ')' + menus_ids[i].sub_button[j].name + ' (' + menus_ids[i].sub_button[j].key + ') </a>');
                        }
                    }
                }
            }
            if ( _string == '' )
            {
                _string = '<a>暂无可关联的系统菜单</a>';
            }
            
            $.layer({
                type: 1,
                fix: false,
                title: '请选择要关联的系统菜单',
                area: ['400px', '150px'],
                shade: [0],
                offset: [$(this).offset().top - $(window).scrollTop() + 'px', $(this).offset().left - 420 + 'px'],
                page: {
                    html: '<div id="catalogsnew_body">' + _string + '</div>'
                }, success: function(layero){
                    var _index = layero.attr('times');
                    if ( _index > 2 )
                    {
                        layer.close(_index - 1);
                    }
                    layero.children('.xubox_main').children('.xubox_page').css({overflowY: 'auto', width: '100%', height: '114px'});
                }
            });
        });
    });
    
    function addLinkItem(_value, _eq)
    {        
        // 选中后删除节点
        $('div[id^="xubox_layer"]').find('div[id^="xubox_border"]').parent().remove();
        $('.catalogsnew_url').eq(_eq).prev().val(_value);
    }
DOT;
}

    echo <<<DOT
</script>
        <tr><td>
            <div id="settings">Settings</div>
            <div id="opciones">{$menus_string}</div>            
            <style type="text/css">  
                #catalogsnew_body a {display:block; height: 28px; line-height: 28px; text-align: left; padding-left: 15px}
                #catalogsnew_body a:hover{background-color: #DEEFFB}
                #opciones {
                	z-index: 7000; position: fixed; padding: 10px 0px; width: 120px; font: 12px/140% arial, helvetica, sans-serif; background: #F7F7F7; color: #999; top: 0px; right: 0px; border-left: #ccc 1px solid; border-bottom: #ccc 1px solid; overflow: hidden;
                }
                #opciones a { display: block; font-size: 12px; height: 30px; line-height: 30px; text-decoration: none; }
                #opciones .selectd, #opciones a:hover { background: #cde1fc; font-weight: bold; color: #333 }
                #settings {
                	z-index: 8000; position: fixed; text-indent: -99999px; width: 43px; display: block; background: url("{$this->cms_abs}images/default/opciones.gif") no-repeat 0px 0px; height: 43px; top: 0px; cursor: pointer; right: 0px
                }
                #settings:hover {
                	background: url("{$this->cms_abs}images/default/opciones.gif") no-repeat 0px -86px
                }
                .ui_bubble_body { text-align:left; line-height: 20px; }
                .cerrar {
                	background: url("{$this->cms_abs}images/default/opciones.gif") no-repeat 0px -43px!important;
                }
            </style>
        </td></tr>
DOT;

# 提示框
if ( !empty($this->menu_list['tip']) )
{
    echo <<<DOT
        <tr><td>
            <script type="text/javascript">
            layer.tips('{$this->menu_list['tip']}', $('#settings'), {
                style: ['background-color:#134D9D; color:#fff; margin-top:10px;', '#134D9D'],
                maxWidth:185,
                guide: 1,
                time: 0,               
                closeBtn:[0, true]
            });
            </script>
        </td></tr>
DOT;
    }
}
