/**
 * 该文件为内部维护版本
 * 对应的压缩版本是：common_footer.min.js
 * 压缩方法：http://tool.css-js.com/  JSPacker压缩，修改后请使用该方法压缩后再出包
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

jQuery(document).ready(function(){
    var $ = jQuery;
    if ( !window.uri2MVC )
    {
        $.getScript(CMS_ABS + 'include/js/common.js', function(){
            _08_ajaxFunction();
            if(typeof _08_endFunction == 'function') {
                _08_endFunction();
            }
        })
    }
    else
    {
    	_08_ajaxFunction();
        if(typeof _08_endFunction == 'function') {
            _08_endFunction();
        }
    }

    
    function _08_ajaxFunction() {
        var _08_ajax = getConfigs();
        var configs = _08_ajax.configs;
        var ids = _08_ajax.ids;
        var params = _08_ajax.params;
        for ( var _08_key in configs )
        {            
            var _data = '';
            for ( var _08_key2 in configs[_08_key] )
            {
                if ( _08_key2 != 'ajax' )
                {
                    _data += (_08_key2 + '=' + configs[_08_key][_08_key2] + '&');
                }
            }
            
            // 开始发送AJAX请求并返回
            (function(_configs, params, _ids, _data){
                jQuery.getScript(CMS_ABS + uri2MVC('ajax=' + _configs.ajax + '&datatype=js&' + _data + 'params={' + params + '}'), function(){
                     var varname = ('_08_M_Ajax_' + _configs.ajax).toLowerCase();
                     var _id = _configs.ajax_id;
                     var _idSplit = ('_08_' + _id + '_');
                     var _08_SPLIT = '<!--_08_' + _id.toUpperCase() + '_SPILT-->', j = 0;
                     _ids = _ids.split(',');
                     if ( window[varname] != undefined )
                     {
                        try {
                            window[varname] = window[varname].split(_08_SPLIT);
                            for ( var i = 0; i < _ids.length; ++i )
                            {
                                jQuery('#' + _idSplit + _ids[i]).after(window[varname][j]);
                                jQuery('#' + _idSplit + _ids[i]).remove();
                                ++j;
                            }
                        } catch(e) {}
                     }
                });
            })(configs[_08_key], params[_08_key], ids[_08_key], _data);
        }
    }
    
    /**
     * 获取页面初始化的所有要合并的AJAX配置参数
     */
    function getConfigs() {
        var iterationParmas = {};
        var _ids = {};
        var configs = {};
        $("div[id^='_08_']").each(function(){
            eval("var url_params = " + $(this).attr('url-params'));                        
            var _id = this.id.split('_'), id;            
            var _type = _id[2], _key = '';
            //<div id="_08_count_540" url-params="{type: 'a', modid: 51, field: 'stat_1'}"></div>
            
            switch(_type)
            {
                case 'count' : 
                    _key = (_type + url_params['type'] + url_params['modid']);
                    if ( _id.length > 4 )
                    {
                        _key += _id[3];
                    }
                    configs[_key] = getCountConfigs( url_params, _id );
                    break;
                case 'adv' :
                    _key = 'adv';
                    if ( _id.length > 4 )
                    {
                        var adv_ids = '';
                        for(var i = 3; i < _id.length; ++i)
                        {
                            if ( adv_ids )
                            {
                                _id[i] = '_' + _id[i];
                            }
                            adv_ids += _id[i];
                        }
                        _id[_id.length-1] = adv_ids;
                    }
                    
                    configs[_key] = getAdvConfigs();
                    break;
                default : return;
            }
            if ( _ids[_key] === undefined ) // 因为KEY不定，只能在这初始化
            {
                _ids[_key] = '';
            }
            else if (_ids[_key])
            {
            	_ids[_key] += ',';
            }          
            
            _ids[_key] += _id[_id.length-1];
            
            // 组装所有参数
            if ( iterationParmas[_key] )
            {
                iterationParmas[_key] += ',';
            }
            else if (iterationParmas[_key] === undefined)
            {
                iterationParmas[_key] = '';
            }
            
            param = $(this).attr('params');
            if ( param == '' || param == undefined )
            {
                param = '[]';
            }
            
            iterationParmas[_key] += ('"' + _id[_id.length-1] + '"' + ':' + param);
        });
        
        return { configs: configs, ids: _ids, params: iterationParmas };
    }
    
    /**
     * 获取统计数配置
     */
    function getCountConfigs( config, _id )
    {
        var _config = { 'ajax': 'view_count', 'iteration': 'infoid', 'type': config['type'], 'modid': config['modid'],
                        'field': config['field'], 'ajax_id': 'count' };
        if(config['_and'])
        {
            _config['_and'] = config['_and'];
        }
        if(config['_or'])
        {
            _config['_or'] = config['_or'];
        }
        if ( _id.length > 4 )
        {
            _config['ajax_id'] += ('_' + _id[3]);
        }
        return _config;
    }
    
    /**
     * 获取广告配置
     */
    function getAdvConfigs()
    {
        return { 'ajax': 'get_adv', 'iteration': 'fcaid', 'ajax_id': 'adv' };
    }
})