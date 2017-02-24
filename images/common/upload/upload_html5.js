var _08_uploadHTML5 = {
    config : {},
    targetDocument : null,
    targetWindow : null,
    filesDivObject: null,
    filesInputObject: null,
    _id: 1,
    files_id : [],
    textareaText: '图片标题...',
    /**
     * 开始处理上传文件
     *
     * @params object files 要处理的上传文件对列
     */
    handleFiles : function( files )
    {
        var _this = this, _value = '';
        for(var i = 0; i < files.length; ++i)
        {
            var _name = files[i].name;
            var _type = files[i].name.split('.');
            if ( !this.checkFile(files[i], _type) )
            {
                continue;
            }
            
            var reader = new FileReader();
            reader.onload = function()
            {
                $.post(_this.config.url, {pic1: this.result, file_name: _name}, function(serverData)
                {
                    eval('var serverData = ' + serverData + ';');
                    if ( serverData.error )
                    {
                        _this.showMessage(serverData.error_message);
                        return;
                    }
                    
                    if ( _this.files_id[0] )
                    {
                        var imgObject = $('div[id="' + _this.files_id[0] + '"]', _this.targetDocument).find('.item_box').find('img');
                        imgObject.attr('src', serverData.name);
                        imgObject.attr('href', serverData.name);
                    }
                    
                    if ( _this.config.issingle )
                    {
                        _this.setValue(serverData);
                    }
                    else
                    {
                    	_this.setValue();
                        _this.files_id.splice(0, 1);
                    }
                });
            }
            
            // 加载开始时
            reader.onloadstart = function() {
                var fileid = "SWFUpload_0_" + _this.config.varname + "_0_" + _this._id;
                var loadingFile = CMS_ABS + 'images/common/loading.gif';
                _this.files_id.push(fileid);
                var imgsrc = $('div[id="' + fileid + '"]', _this.targetDocument).find('.item_box').find('img');
                if ( imgsrc.attr('src') && _this.config.issingle )
                {
                    imgsrc.attr('src', loadingFile);
                }
                else
                {
                    var _file = {
                        filestatus: -4,
                        height: 0,
                        width: 0,
                        ufid: '',
                        link: '',
                        id : fileid,
                        name : loadingFile
                    };
                    
                    if ( !_this.config.issingle )
                    {
                        _this._id++;
                    }
                    _this.createThumb(_file);
                }
            }
            
            reader.readAsDataURL(files[i]);
        }
    },
    
    /**
     * 设置INPUT的值
     */
    setValue : function( serverData )
    {
        var _this = this;
        if ( _this.config.issingle ) // 单个文件上传时
        {
            _this._id = 1;
            if ( typeof serverData != 'undefined' )
            {
                _value = serverData.name;
            }
            else
            {
                _this.filesDivObject.html('');
            	_value = '';
            }
        }
        else
        {
            var val = '', id_num = 0;
            _this.filesDivObject.find('.item_box').each(function(){
                id_num++;
                val += $(this).children().attr('src');
                var textareaText = $(this).parent().find('.item_input').find('textarea').val();
                if ( textareaText )
                {
                    val += ('|' + textareaText)
                }
                var textareaLink = $(this).parent().find('.item_link').find('textarea').val();
                if ( textareaLink )
                {
                    val += ('|' + textareaLink)
                }
                val += "\n";
            })
            _this._id = id_num;
            
            if ( typeof serverData != 'undefined' )
            {
                _value = val + serverData.name;
            }
        	else
            {
         	    _value = val;
            }
            
            _value += "\n";
        }
        _this.filesInputObject.val(_value); 
    },
    
    /**
     * 上传前的文件正确性检查
     *
     * @param  object file 要检查的文件对象
     * @return bool        如果检查通过返回TRUE，否则返回FALSE
     */
    checkFile : function (file, _type)
    {
        if ( !_type[_type.length - 1] || !this.config.file_types_limit[_type[_type.length - 1]] )
        {
            this.showMessage("类型错误，允许的文件类型有：" + this.config.file_types);
            return false;
        }
        
        var currentSize = this.config.file_types_limit[_type[1]];
        if ( (file.size < (currentSize[0] * 1024)) || (file.size > (currentSize[1] * 1024)) )
        {
            this.showMessage('文件 ' + file.name + ' 超出大小限制，限制范围为：' + currentSize[0] + ' - ' + currentSize[1] + ' KB');
            return false;
        }
        
        return true;
    },
    
    /**
     * 生成缩略图
     * 
     * @param object file 要生成的文件对象
     */
    createThumb : function ( file )
    {        
        var _this = this;
        $("<div>", {
            class: 'progressWrapper',
            id: file.id
        }).appendTo(this.filesDivObject);
        
        var fileDIVObject = $('div[id="' + file.id + '"]', this.targetDocument);
        var _class = 'progressContainer';
        if ( !this.config.issingle )
        {
            if ( _this.config.imgsFlag.toUpperCase() == 'S' )
            {
                _class += ' h130';
            }
            else
            {
            	_class += ' h110';
            }            
        }
        $("<div>", {
            class: _class,
        }).appendTo(fileDIVObject);        
        
        // 创建删除按钮
        var progressContainer = fileDIVObject.find('.progressContainer');
        $("<a>", {
            class: 'progressCancel',
            href: 'javascript: void(0);',
            id: file.ufid,
            click: function() {
                fileDIVObject.remove();
                _this.setValue();
            }
        }).appendTo(progressContainer);
        $("<span>", {
            class: 'item_box'
        }).appendTo(progressContainer); 
              
        // 创建缩略图
        var resize = this.resizeImage(file), _cssText = '';
        if ( resize.width > resize.height )
        {
            _cssText = 'margin-top:' + parseInt((this.config.thumb_height - resize.height) / 2) + 'px;';
        }
        else
        {
        	_cssText = 'margin-left:' + parseInt((this.config.thumb_width - resize.width) / 2) + 'px;';
        }
        file.name = this.getFileURL(file.name);
        $("<img>", {
            style: _cssText,
            width: resize.width,
            height: resize.height,
            href: file.name,
            title: file.title,
            src: file.name
        }).appendTo(fileDIVObject.find('.item_box'));
        $(fileDIVObject.find('.item_box img')).lightBox();
        
        $("<div>", {
            class: 'progressBarStatus',
            style: 'display: none;'
        }).appendTo(progressContainer);
        
        if ( !this.config.issingle )
        {
            // 绑定拖动排序事件
            this.filesDivObject.sortable({opacity: 0.5, scroll: false, helper: 'clone',
                stop : function(event, ui) {
                    _this.setValue();
                }
            });
            
            $("<div>", {
                class: 'item_input'
            }).appendTo(progressContainer);
            
            $("<i>", {
                class: 'tline hc'
            }).appendTo(fileDIVObject.find('.item_input')); 
            
            $("<textarea>", {
                class: 'c_ccc',
                style: 'resize: none;',
                placeholder: (file.title ? file.title : _this.textareaText),
                blur : function() {                    
                    _this.setValue();
                },
                click: function() { $(this).focus(); }
            }).appendTo(fileDIVObject.find('.item_input'));   
            
            $("<div>", {
                style: (_this.config.imgsFlag.toUpperCase() == 'S' ? "display: block; margin-bottom:5px" : ''),
                class: 'item_link'
            }).appendTo(progressContainer);
            
            $("<i>", {
                class: 't line hc'
            }).appendTo(fileDIVObject.find('.item_link')); 
            $("<textarea>", {
                class: 'c_ccc',
                style: 'resize: none;',
                placeholder: (file.link ? file.link : _this.config.imgsCom),
                blur : function() {                    
                    _this.setValue();
                },
                click: function() { $(this).focus(); }
            }).appendTo(fileDIVObject.find('.item_link'));
        }
    },
    
    /**
     * 获取文件URL
     *
     * @param  string url 原文件URL
     * @return string     如果原文件URL为空或不为图片类型时自动赋于一张图片URL
     */
    getFileURL: function( url )
    {        
        var _path = CMS_ABS + 'images/common/';
        switch(this.config.type)
        {
            case 'image':
            case 'images': {
                if ( !url )
                {
                    url = _path + 'nopic.gif';
                }
            } break;
            case 'flash':
            case 'flashs': {
                url = _path + 'flash.gif';
            } break;
            case 'media':
            case 'medias': {
                url = _path + 'media.gif';
            } break;
            default: {
                url = _path + 'unknowfile.gif';
            } break;
        }
        
        return url;
    },
    
    /**
     * 按等比例计算缩略图大小
     *
     * @param  object _file   要计算的原图片信息
     * @return object newSize 返回按等比例计算缩略图大小
     */
    resizeImage: function( _file )
    {
        var newSize = {width: this.config.thumb_width, height: this.config.thumb_height};
        _file.width = parseInt(_file.width);
        _file.height = parseInt(_file.height);
        
        if ( _file.width == 0 || _file.height == 0 )
        {
            _file.width = newSize.width;
            _file.height = newSize.height;
        }
        
        if ( _file.width > _file.height )
        {
            if ( _file.width < newSize.width )
            {
                newSize.width = _file.width;
            }
            newSize.height = parseInt(_file.height * newSize.width / _file.width);
        }
        else
        {
            if ( _file.height < newSize.height )
            {
                newSize.height = _file.height;
            }
            newSize.width = parseInt(_file.width * newSize.height / _file.height);
        }
        return newSize;
    },
    
    /**
     * 获取上传前的对象URL
     *
     * @param object file 要获取的文件对象
     */
    getObjectURL : function( file )
    {
    	var url = '' ;
    	if ( window.createObjectURL != undefined )
        {
    		url = window.createObjectURL(file);
    	}
        else if ( window.URL!=undefined )
        {
            // mozilla(firefox等)
    		url = window.URL.createObjectURL(file);
    	}
        else if ( window.webkitURL != undefined )
        {
            // webkit 或 chrome
    		url = window.webkitURL.createObjectURL(file);
    	}
        
    	return url ;
    },
    
    showMessage : function (msg)
    {
        var _scrollTop = $(this.targetWindow).scrollTop();
        $('#upload_html5_dialog', this.targetDocument).remove();
        $('#upload_html5_dialog', this.targetDocument).parent().remove();
        $("<div>", {
            title: '提示：',
            id: 'upload_html5_dialog'
        }).appendTo($('body', this.targetDocument));
        var upload_html5_dialog = $('#upload_html5_dialog', this.targetDocument);
        $("<p>", {
            style: 'margin-top: 10px',
            text: msg
        }).appendTo(upload_html5_dialog);
        $( upload_html5_dialog ).dialog({
            close: function() {
                this.remove();
            }
        });

        upload_html5_dialog.parent().animate({left: parseInt(($(this.targetWindow).width() - upload_html5_dialog.parent().width()) / 2) + 'px', top: parseInt(_scrollTop + ($(this.targetWindow).height() - upload_html5_dialog.parent().height()) / 2) + 'px'}, "slow");
        $(this.targetWindow).scrollTop(_scrollTop);
    },
    
    init : function (config)
    {
        this.config = config;
        this.targetDocument = parent.document;
        this.targetWindow = parent.window;
        this.filesDivObject = $('div[id="' + this.config.progressTarget + '"]', this.targetDocument);
        this.filesInputObject = $('input[id="_08_upload_' + this.config.varname + '"]', this.targetDocument);
    }
};