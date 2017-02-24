/**
 * 注：本文件都是自定义的函数，如果升级时请不要覆盖本文件和upload.css,
 * swfupload.js 文件除了35行 this.movieName = "SWFUpload_" + (SWFUpload.movieCount++) + '_' + userSettings.varname; 后面加了个
 *  + '_' + userSettings.varname 外其它没动，其它文件可覆盖
 * 依赖性：JQuery / common.js(如果有编辑器时)
 */



/*
	Queue Plug-in
	
	Features:
		*Adds a cancelQueue() method for cancelling the entire queue.
		*All queued files are uploaded when startUpload() is called.
		*If false is returned from uploadComplete then the queue upload is stopped.
		 If false is not returned (strict comparison) then the queue upload is continued.
		*Adds a QueueComplete event that is fired when all the queued files have finished uploading.
		 Set the event handler with the queue_complete_handler setting.
		
	*/

var SWFUpload;
var showMessage = function( msg, progress )
{    
    if ( is_ckeditor )
    {
        alert(msg);
    }
    else
    {
    	progress.setStatus(msg);
    }
}
if (typeof(SWFUpload) === "function") {
	SWFUpload.queue = {};
	
	SWFUpload.prototype.initSettings = (function (oldInitSettings) {
		return function (userSettings) {
			if (typeof(oldInitSettings) === "function") {
				oldInitSettings.call(this, userSettings);
			}
			
			this.queueSettings = {};
			
			this.queueSettings.queue_cancelled_flag = false;
			this.queueSettings.queue_upload_count = 0;
			
			this.queueSettings.user_upload_complete_handler = this.settings.upload_complete_handler;
			this.queueSettings.user_upload_start_handler = this.settings.upload_start_handler;
			this.settings.upload_complete_handler = SWFUpload.queue.uploadCompleteHandler;
			this.settings.upload_start_handler = SWFUpload.queue.uploadStartHandler;
			
			this.settings.queue_complete_handler = userSettings.queue_complete_handler || null;
		};
	})(SWFUpload.prototype.initSettings);

	SWFUpload.prototype.startUpload = function (fileID) {
		this.queueSettings.queue_cancelled_flag = false;
		this.callFlash("StartUpload", [fileID]);
	};

	SWFUpload.prototype.cancelQueue = function () {
		this.queueSettings.queue_cancelled_flag = true;
		this.stopUpload();
		
		var stats = this.getStats();
		while (stats.files_queued > 0) {
			this.cancelUpload();
			stats = this.getStats();
		}
	};
	
    /**
     * 选择完检查
     */
	SWFUpload.queue.uploadStartHandler = function (file) {
		var returnValue;
		if (typeof(this.queueSettings.user_upload_start_handler) === "function") {
			returnValue = this.queueSettings.user_upload_start_handler.call(this, file);
		}
		// To prevent upload a real "FALSE" value must be returned, otherwise default to a real "TRUE" value.
		returnValue = (returnValue === false) ? false : true;
		this.queueSettings.queue_cancelled_flag = !returnValue;
       
        var currentFile = file, _type, checkFlag = true, minLimit, maxLimit, file_types_limits = this.settings.custom_settings.file_types_limit;
        // 判断文件上传大小
        _type = currentFile.type.substr(1).toLowerCase();
        var progress = new FileProgress(file, this.customSettings.progressTarget); 
        
        try {
            minLimit = file_types_limits[_type][0] * 1024;
            maxLimit = file_types_limits[_type][1] * 1024;
            this.setFileSizeLimit(maxLimit);
        } catch (e) {
            showMessage("类型错误，允许的文件类型有：" + this.settings.file_types, progress);
            checkFlag = false;
        }
        if ( (currentFile.size < minLimit) || (currentFile.size > maxLimit) )
        {
            showMessage('文件超出大小限制，限制范围为：' + file_types_limits[_type][0] + ' - ' + file_types_limits[_type][1] + ' KB', progress);
            checkFlag = false;
        }
        
        // 如果不通过验证则终止上传
        if ( !checkFlag )
        {
            swfu.cancelUpload(file.id, false);
        	setTimeout(function () {
        		progress.disappear();
        	}, 2000);
        }

		return returnValue;
	};
	
    /**
     * 上传完成处理程序
     */
	SWFUpload.queue.uploadCompleteHandler = function (file) {
		var user_upload_complete_handler = this.queueSettings.user_upload_complete_handler;
		var continueUpload;
        
		if (file.filestatus === SWFUpload.FILE_STATUS.COMPLETE) {
			this.queueSettings.queue_upload_count++;
		}

		if (typeof(user_upload_complete_handler) === "function") {
			continueUpload = (user_upload_complete_handler.call(this, file) === false) ? false : true;
		} else if (file.filestatus === SWFUpload.FILE_STATUS.QUEUED) {
			// If the file was stopped and re-queued don't restart the upload
			continueUpload = false;
		} else {
			continueUpload = true;
		}
		
		if (continueUpload) {
			var stats = this.getStats();
			if (stats.files_queued > 0 && this.queueSettings.queue_cancelled_flag === false) {
				this.startUpload();
			} else if (this.queueSettings.queue_cancelled_flag === false) {
				this.queueEvent("queue_complete_handler", [this.queueSettings.queue_upload_count]);
				this.queueSettings.queue_upload_count = 0;
			} else {
				this.queueSettings.queue_cancelled_flag = false;
				this.queueSettings.queue_upload_count = 0;
			}
		}
	};
}




/**
 * 文件上传时进程控制事件
 *
 * @param file     正在上传的文件
 * @param targetID 要设置写入上传状态的DOM ID
                   A simple class for displaying file information and progress
                   Note: This is a demonstration only and not part of SWFUpload.
                   Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
 */
var originalValue = '图片标题...';
function FileProgress(file, targetID, clearValue) {
	this.fileProgressID = file.id;
	this.opacity = 100;
	this.height = 0;
    
    // 如果是编辑器上传时选中文件后打开浮动窗操作
    if ( is_ckeditor )
    {
        showloading('block', file.name + ' 文件上传中，请稍候......');
    }
    var boxID = 'imgbox_' + varname;
        
    // 如果是单文件上传时，再上传的话就删除上一个，只让保留一个文件 
    var testID = _$(boxID).childNodes[0];
    if ( swfu.customSettings.issingle && (testID != null) && !clearValue )
    {
		cancelUpload(testID.id);        
        _$('_08_upload_' + varname).value = '';
    }      
    
	this.fileProgressWrapper = _$(this.fileProgressID);
    var targetDocument = parent.document;
    
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = targetDocument.createElement("div");
		this.fileProgressWrapper.className = "progressWrapper";
        if ( !swfu.customSettings.issingle )
        {
            this.fileProgressWrapper.className += ' h110';
        }
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = targetDocument.createElement("div");
		this.fileProgressElement.className = "progressContainer";
        
        // 创建删除按钮 this.fileProgressElement.childNodes[0]
		var progressCancel = targetDocument.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "javascript: void(0);";
		progressCancel.appendChild(targetDocument.createTextNode(" "));
		this.fileProgressElement.appendChild(progressCancel);
        
        // 创建缩略图 this.fileProgressElement.childNodes[1]
        var progressImgSpan = targetDocument.createElement("span");
		progressImgSpan.className = "item_box";
		this.fileProgressElement.appendChild(progressImgSpan);
        var progressImg = targetDocument.createElement("img");
        progressImg.id = this.fileProgressWrapper.id + '_img';
        progressImg.setAttribute('src', CMS_ABS + 'images/common/loading.gif');
        progressImgSpan.appendChild(progressImg);
        
        // 创建上传进度信息 this.fileProgressElement.childNodes[2]
		var progressFrontText = targetDocument.createElement("div");
		progressFrontText.className = "progressBarStatus";
        this.fileProgressElement.appendChild(progressFrontText);
        
        // 创建封面图标 this.fileProgressElement.childNodes[3]
		var progressFrontCover = targetDocument.createElement("div");
		progressFrontCover.className = "isfenmian";
        if ( is_ckeditor || swfu.customSettings.issingle || (_$(boxID).childNodes.length != 0)  )
        {
            progressFrontCover.style.display = 'none';
        }
        
        this.fileProgressElement.appendChild(progressFrontCover);
        
        // 创建设置封面背景按钮 this.fileProgressElement.childNodes[4]
		var progressFmBG = targetDocument.createElement("div");
		progressFmBG.className = "fm_bg";
        progressFmBG.style.display = 'none';
        this.fileProgressElement.appendChild(progressFmBG);
        
        // 创建设置封面按钮    this.fileProgressElement.childNodes[5]
		var progressFmFM = targetDocument.createElement("div");
		var progressFmFMSet = targetDocument.createElement("div");
        progressFmFMSet.className = 'set';
		progressFmFM.className = "fengmian";
        progressFmFM.style.display = 'none';
        var _fmA0 = targetDocument.createElement("a");
        _fmA0.className = 'scroll_l';
        _fmA0.title = '左移';
        _fmA0.href = "javascript: void(0);";
        var _fmA1 = targetDocument.createElement("a");
        _fmA1.className = 'set_fm';
        _fmA1.href = "javascript: void(0);";
        var _fmA2 = targetDocument.createElement("a");
        _fmA2.className = 'scroll_r';
        _fmA2.title = '右移';
        _fmA2.href = "javascript: void(0);";
        progressFmFMSet.appendChild(_fmA0);
        progressFmFMSet.appendChild(_fmA1);
        progressFmFMSet.appendChild(_fmA2);
        
        // 创建裁剪按钮    this.fileProgressElement.childNodes[5][1]
		var progressFmFMCut = targetDocument.createElement("div");
        progressFmFMCut.className = 'cut';
        var _cutA = targetDocument.createElement("a");
        _cutA.className = 'cut_fm';
        _cutA.title = '裁剪';
        _cutA.href = "javascript: void(0);";
        var _cutA2 = targetDocument.createElement("a");
        _cutA2.className = 'cut_fm2';
        _cutA2.href = "javascript: void(0);";
        
        
        // 创建图片描述框 this.fileProgressElement.childNodes[6]
		var progressInput = targetDocument.createElement("div");
		progressInput.className = "item_input";
        var progressInputI = targetDocument.createElement("i");
		progressInputI.className = "tline hc";
        var progressInputTextarea = targetDocument.createElement("textarea");
		progressInputTextarea.className = "c_ccc";
        progressInputTextarea.style.cssText = 'resize:none';
        
        if ( file.title == null )
        {
            var textareaValue = originalValue;
        }
        else
        {
        	var textareaValue = file.title;
        }

        progressInputTextarea.value = textareaValue;
        progressInputTextarea.onfocus = function() {
            if(this.value == originalValue) this.value = '';
            progressInput.className += ' on';
            progressInput.parentNode.parentNode.style.zIndex = 100;
            if ( progressInput.parentNode.parentNode.nextSibling != undefined )
            {
                progressInput.parentNode.parentNode.nextSibling.style.zIndex = 99;
            }            
            progressInputTextarea.className = 'c_666';
        } 

	   //创建link输入框
		var progressLink = targetDocument.createElement("div");
		progressLink.className = "item_link";		
		var progressLinkI = targetDocument.createElement("i");
		progressLinkI.className = "t line hc";
		var progressLinkTextarea = targetDocument.createElement("textarea");
	
		progressLinkTextarea.className = "c_ccc";
		progressLinkTextarea.style.cssText = 'resize:none'; 


		progressLinkTextarea.value = textareaValue;
        progressLinkTextarea.onfocus = function() {
            if(this.value == swfu.customSettings.imgsCom) this.value = '';
            progressLink.className += ' on';
            progressInput.parentNode.parentNode.style.zIndex = 100;
            if ( progressInput.parentNode.parentNode.nextSibling != undefined )
            {
                progressInput.parentNode.parentNode.nextSibling.style.zIndex = 99;
            }
            progressLinkTextarea.className = 'c_666';
        } 

        if ( swfu.customSettings.type == 'image' || swfu.customSettings.type == 'images' )
        {
            this.fileProgressWrapper.onmouseover = function()
            {
                progressFmFM.style.display = progressFmBG.style.display = 'block';
                if ( _$(boxID).childNodes.length == 0 || this.previousSibling == null )
                {
                    progressFmBG.style.height = '26px';
                    progressFmFMSet.style.display = 'none';
                    progressFmFM.style.top = progressFmBG.style.top = '25px';
                }
            }
            this.fileProgressWrapper.onmouseout = function() {progressFmFM.style.display = progressFmBG.style.display = 'none';}
        
            if( swfu.customSettings.issingle )
            {
                //_$('_08_upload_inputIframe_' + varname).style.cssText = 'float:left; margin-top: 23px;';
            	progressInput.style.display = 'none'; // 单个图片时隐藏描述
				progressLink.style.display = 'none'; // 单个图片时隐藏属性2
                _$('_08_upload_' + varname).onblur = function() {
                    progressImg.setAttribute('src', this.value);
                    _cutA2.setAttribute('href', this.value);
                }
            }
        }
        else
        {
        	progressInput.style.display = 'none';
        }
        
        progressFmFMCut.appendChild(_cutA);
        progressFmFMCut.appendChild(_cutA2);
        progressFmFM.appendChild(progressFmFMSet);
        progressFmFM.appendChild(progressFmFMCut);
        this.fileProgressElement.appendChild(progressFmFM);
		progressInput.appendChild(progressInputI);
		progressInput.appendChild(progressInputTextarea);
		this.fileProgressElement.appendChild(progressInput);
		progressLink.appendChild(progressLinkI);
		progressLink.appendChild(progressLinkTextarea);
		this.fileProgressElement.appendChild(progressLink);
		this.fileProgressWrapper.appendChild(this.fileProgressElement); 
		_$(targetID).appendChild(this.fileProgressWrapper);
		if(swfu.customSettings.type=='images'  && swfu.customSettings.imgsFlag=='S'  ){ _$(file.id).firstChild.lastChild.style.display='block';_$(file.id).className = 'progressWrapper h130';}
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);
}

FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
	this.fileProgressElement.className = "progressContainer";
	this.fileProgressElement.childNodes[0].className = "progressCancel";
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	this.appear();	
};

/**
 * 设置上传完后的显示状态
 */
FileProgress.prototype.setComplete = function () {
};
FileProgress.prototype.setError = function () {
};
FileProgress.prototype.setCancelled = function () {
};
FileProgress.prototype.setStatus = function (status) {
	this.fileProgressElement.childNodes[2].innerHTML = status;
};
FileProgress.prototype.hideStatus = function () {
	this.fileProgressElement.childNodes[2].style.display = 'none';
};

var _$ = function( domID ) {
    var _domObject = parent.document.getElementById(domID);
    
    if ( _domObject == null )
    {
        _domObject = document.getElementById(domID);
    }
    
    return _domObject;
}


/**
 * 交换两个图片的SRC和隐藏域ID
 */
var swapImage = function(swap1, swap2)
{
    var tempSRC = swap1.childNodes[0].childNodes[1].childNodes[0].getAttribute('src');
    swap1.childNodes[0].childNodes[1].childNodes[0].setAttribute('src', swap2.childNodes[0].childNodes[1].childNodes[0].getAttribute('src'));
    swap2.childNodes[0].childNodes[1].childNodes[0].setAttribute('src', tempSRC);
    
    var tempID = swap1.childNodes[0].childNodes[0].id;
    swap1.childNodes[0].childNodes[0].id = swap2.childNodes[0].childNodes[0].id;
    swap2.childNodes[0].childNodes[0].id = tempID;
    
    var tempText = swap1.childNodes[0].lastChild.childNodes[1].value;
    swap1.childNodes[0].lastChild.childNodes[1].value = swap2.childNodes[0].lastChild.childNodes[1].value;
    swap2.childNodes[0].lastChild.childNodes[1].value = tempText;
    
    var tempText = swap1.childNodes[0].lastChild.previousSibling.childNodes[1].value;
    swap1.childNodes[0].lastChild.previousSibling.childNodes[1].value = swap2.childNodes[0].lastChild.previousSibling.childNodes[1].value;
    swap2.childNodes[0].lastChild.previousSibling.childNodes[1].value = tempText;

    
    // 交换查看原图链接
    if (swap1.childNodes[0].childNodes[5].lastChild.lastChild)
    {
        var tempHref = swap1.childNodes[0].childNodes[5].lastChild.lastChild.href;
        swap1.childNodes[0].childNodes[5].lastChild.lastChild.href = swap2.childNodes[0].childNodes[5].lastChild.lastChild.href;
        swap2.childNodes[0].childNodes[5].lastChild.lastChild.href = tempHref;
    }
}

/**
 * 删除上传图片
 */
var cancelUpload = function(id)
{
    var _dom = _$(id);
    _dom && _dom.parentNode.removeChild(_dom);
    var imgBox = _$('imgbox_' + swfu.customSettings.varname);
    if ( imgBox.childNodes[0] != undefined )
    {
        if ( !swfu.customSettings.issingle ) {
            imgBox.childNodes[0].childNodes[0].childNodes[3].style.display = '';
        }
        
        imgBox.childNodes[0].onmouseover = function(){}
    }    
}

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressElement.childNodes[0].onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			cancelUpload(fileID);
			return false;
		};
	}
};

FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
	
	if (this.fileProgressWrapper.filters) {/*
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}*/
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}
		
	this.fileProgressWrapper.style.height = "";
	
	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";
	
};

/**
 * 淡出和剪辑掉FileProgress框
 */
FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
		this.fileProgressWrapper.style.display = "none";
		this.setTimer(null);
        cancelUpload(this.fileProgressWrapper.id);
	}
};







/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function preLoad() {
	if (!this.support.loading) {
	    if ( confirm("使用 上传附件 功能需要安装 Flash Player 9.028 或以上版本，是否现去下载？") )
        {
            top.location.href = 'http://get.adobe.com/cn/flashplayer/';
        };
		return false;
	}
}
function loadFailed() {
	alert("当你载入 上传附件 出错时，如果这是一个正确的应用程序，我们会进行清理，并且会给你一个替换");
}

/**
 * 文件队列
 */
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("等待上传...");
		progress.toggleCancel(true, this);
	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert((message === '0' ? "已经达到了上传总数的限制" : "您尝试上传的文件太多.\n您还只可以选择 " + message + " 个文件."));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            showMessage("上传文件太大", progress);
			this.debug("错误代码: 文件太大, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            showMessage("无法上传空文件", progress);
			this.debug("错误代码: 零字节文件, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            showMessage("无效的文件类型", progress);
			this.debug("错误代码: 无效的文件类型, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		default:
			if (file !== null) {
                showMessage("未处理的错误", progress);
			}
			this.debug("错误代码: " + errorCode + ", 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

/**
 * 文件选择窗口关闭时触发事件，默认方法
 */
function fileDialogComplete (numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
		//	document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		/* I want auto start the upload and I can do that here */
        
		this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		showMessage("正在上传...", progress);
		progress.toggleCancel(true, this);
	} catch (ex) {}
	
	return true;
}

/**
 * 文件上传过程中触发事件，默认方法
 */
function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		progress.setStatus(percent + '%');
	} catch (ex) {
		this.debug(ex);
	}
}

/**
 * 上传成功
 *
 * @param file       当前上传的文件句柄
 * @param serverData 服务端上传后返回的数据
 */
function uploadSuccess(file, serverData, clearValue) {
    var _this = this, flag = 0;
	var progress = new FileProgress(file, this.customSettings.progressTarget, clearValue);
    var uploadID = '_08_upload_' + this.customSettings.varname;
    var boxID = 'imgbox_' + this.customSettings.varname;
	progress.setComplete();
	progress.toggleCancel(true, this);
    if ( !serverData )
    {
        serverData = file;
    }
    
    if ( typeof serverData == 'string' )
    {
        serverData = eval('(' + serverData + ')');
    }

    try {    
        if ( serverData.error )
        {
            showMessage(serverData.error_message);        
        	setTimeout(function () {
        		progress.disappear();
        	}, 2000);
            return;
        }
        
        // 如果在上传时才显示。有file.key代表是在编辑状态
        if ( typeof file.isUpload == 'undefined' && !is_ckeditor )
        {
            showMessage("上传完成.", progress);
        }
    } catch(e) {
		showMessage("服务端返回错误，上传失败", progress);
        return;
    }
    
    switch(this.customSettings.type)
    {
        case 'image':
        case 'images': break;
        case 'flash':
        case 'flashs': {
            serverData.value = serverData.name;
            serverData.name = CMS_ABS + 'images/common/flash.gif';
        } break;
        case 'media':
        case 'medias': {
            serverData.value = serverData.name;
            serverData.name = CMS_ABS + 'images/common/media.gif';
        } break;
        default: {
            serverData.value = serverData.name;
            serverData.name = CMS_ABS + 'images/common/unknowfile.gif';
        } break;
    }
    
    var _value = serverData.value ? serverData.value : serverData.name;
    
    if ( this.customSettings.issingle )
    {
        if ( _$(uploadID).getAttribute('value') != _value )
        {
            setValue(uploadID, _value);
        }
    }
    else
    {
    	var varValue = getValue(uploadID);
        if ( file.title == '' || file.title == null )
        {
            var textareaValue = (typeof(serverData.original)=='undefined') ? originalValue : serverData.original;
        }
        else
        {
        	var textareaValue = file.title;
        }
        
       if ( file.link == '' || file.link == null )
        {
            var textareaLink = swfu.customSettings.imgsCom;
        }
        else
        {
        	var textareaLink = file.link;
        }
        
        var _title = ((textareaValue && textareaValue!=originalValue) ? '|' + textareaValue : '|');
        var _link = ((textareaLink && textareaLink!=swfu.customSettings.imgsCom) ? '|' + textareaLink : '' );
        var replaceFlag = true;
        var splits_n = varValue.split('\n');
        for(var i in splits_n )
        {
            var splits_l = splits_n[i].split('|');
            for ( var j in splits_l )
            {
                if ( splits_l[j] == _value )
                {
                    replaceFlag = false;
                    break;
                }
            }
            if ( !replaceFlag )
            {
                break;
            }
        }
        
            
        if ( replaceFlag )
        {
            setValue(uploadID, varValue + ((varValue ? '\n' : '') + (_value + _title + _link)));  
        }
                  
        // 设置描述框状态
		
        _$(file.id).childNodes[0].lastChild.previousSibling.childNodes[1].value = textareaValue;
        _$(file.id).childNodes[0].lastChild.previousSibling.childNodes[1].onblur = function() {
            this.parentNode.className = 'item_input';
            this.className = 'c_ccc';
            refreshValue(uploadID, boxID);
            if(this.value == '') this.value = originalValue;
        }

		_$(file.id).childNodes[0].lastChild.childNodes[1].value = textareaLink;
        _$(file.id).childNodes[0].lastChild.childNodes[1].onblur = function() {
            this.parentNode.className = 'item_link';
            this.className = 'c_ccc';
            refreshValue(uploadID, boxID);
            if(this.value == '') this.value = swfu.customSettings.imgsCom;
        }
    }
    

    setTimeout(function(){progress.hideStatus();}, 500);
    
    // 交换封面
    _$(file.id).childNodes[0].childNodes[5].childNodes[0].childNodes[0].onclick =
    _$(file.id).childNodes[0].childNodes[5].childNodes[0].childNodes[2].onclick =
    _$(file.id).childNodes[0].childNodes[5].childNodes[0].childNodes[1].onclick = function(){
        var swapObject;
        
        switch(this.className)
        {
            // 如果点击的是与上一个图片交换时
            case 'scroll_l':
                swapObject = this.parentNode.parentNode.parentNode.parentNode.previousSibling; break;
            
            // 如果点击的是与下一个图片交换时
            case 'scroll_r':
                swapObject = this.parentNode.parentNode.parentNode.parentNode.nextSibling; break;
            
            // 如果点击交换封面时
            default: swapObject = _$(boxID).childNodes[0]; break;
        }
        
        if ( swapObject == undefined )
        {
            return;
        }
        
        swapImage(swapObject, this.parentNode.parentNode.parentNode.parentNode);
        
        refreshValue(uploadID, boxID);
    }
    
    _$(file.id).childNodes[0].childNodes[0].id = serverData.ufid;
    // 删除图片
    _$(file.id).childNodes[0].childNodes[0].onclick = function() {        
//            if ( !isNaN(this.id) )
//            {
//                // 该方法为JQuery方法，所以请在使用前引入JQuery库
//                $.post(_this.customSettings.delete_url, {ufid: this.id}, function(data){});     
//            }
                    
        cancelUpload(file.id);
        refreshValue(uploadID, boxID);
    }
    setImageDom(_$(file.id + '_img'), serverData);
    
    // 如果类型不是图片时不作以下操作
    if ( (this.customSettings.type != 'image') && (this.customSettings.type != 'images') )
    {
        return false;
    }
    // 裁剪
    _$(file.id).childNodes[0].childNodes[5].childNodes[1].childNodes[0].onclick = function() {
        var ufid = this.parentNode.parentNode.parentNode.childNodes[0].id;
        var imgID = this.parentNode.parentNode.parentNode.childNodes[1].childNodes[0].id;
        var imgSRC = this.parentNode.parentNode.parentNode.childNodes[1].childNodes[0].getAttribute('src');
        imgSRC = BASE64.encoder(imgSRC);
        var url = uri2MVC({'upload': 'cut', 'imgsrc': imgSRC, 'img_id': imgID, 'wmid': wmid, 'varname': varname, 'handlekey': handlekey, 'issingle': _this.customSettings.issingle});
        uploadwin('images', function(images){}, 0, 0, 0, 0, 'undefined', 655, 460, url, 0);
    }
    
    // 查看原图
    var srcImage = _$(file.id).childNodes[0].childNodes[5].childNodes[1].childNodes[1];
    srcImage.setAttribute('href', serverData.name);
    srcImage.setAttribute('title', '查看原图');
    $(srcImage).lightBox();	
}

/**
 * 设置图片节点属性
 */
function setImageDom(_object, _serverData)
{
    var _width = 0, _height = 0;
    _object.setAttribute('src', _serverData.name);
    _object.setAttribute('title', _serverData.name);
    _serverData.width = parseInt(_serverData.width);
    _serverData.height = parseInt(_serverData.height);
    if (_serverData.extension == 'image' || _serverData.extension == 'image')
    {
        if ( _serverData.width > _serverData.height )
        {
            _width = 80;
            _height = _serverData.height * _width / _serverData.width;
            _object.style.marginTop = parseInt((60 - _height) / 2) + 'px';
        }
        else if( _serverData.width < _serverData.height )
        {
        	_height = 60;
            _width = _serverData.width * _height / _serverData.height;
            _object.style.marginLeft = parseInt((80 - _width) / 2) + 'px';
        }
        else
        {
        	_width = _height = 60;
            _object.style.marginLeft = parseInt((80 - _width) / 2) + 'px';
        }
    } 
    else
    {
    	_width = 80;
        _height = 60;
    }
    
    _object.setAttribute('width', parseInt(_width));
    _object.setAttribute('height', parseInt(_height));
    if ( _serverData.value )
    {
        _object.setAttribute('value', _serverData.value);        
    }  
}

/**
 * 检查当前允许上传的文件后缀
 * 
 * @param  string ext 要检查的类型
 * @return bool       如果该类型正确返回FALSE，否则返回TRUE
 */
function checkExt(ext)
{
    var exts = swfu.settings.file_types.split(';'), ext2;
    for(var i = 0; i < exts.length; ++i)
    {
        ext2 = exts[i].split('.');
        if ( ext2[1].toLowerCase() == ext.toLowerCase() )
        {
            return false;
        }
    }
    return true;
}

/**
 * 刷新所有图片列表值(包含图片描述)
 */
function refreshValue(uploadID, boxID)
{
    var currentBox, _value = '', _src, textareaValue,textareaLink;
    for(var i = 0; i < _$(boxID).childNodes.length; ++i)
    {
        currentBox = _$(boxID).childNodes[i].childNodes[0];
        if ( currentBox != undefined )
        {
            textareaValue = currentBox.lastChild.previousSibling.childNodes[1].value;
            textareaLink = currentBox.lastChild.childNodes[1].value;          
            if( _value ){ _value += '\n'; }
			var itm_box = currentBox.getElementsByTagName('img')[0]; //用getElementsByTagName可避免中间添加元素后查找失败
			_src = itm_box.getAttribute('src') ? itm_box.getAttribute('src') : itm_box.getAttribute('value');
            /*_src = currentBox.childNodes[1].childNodes[0].getAttribute('value');
            if ( !_src )
            {
                _src = currentBox.childNodes[1].childNodes[0].getAttribute('src'); originalValue
            }*/
			_sValue = (textareaValue && textareaValue != originalValue) ? ('|' + textareaValue) : '';
			_sLink = (textareaLink && textareaLink != swfu.customSettings.imgsCom) ? ('|' + textareaLink) : '';
			_sValue = (!_sValue && _sLink) ? '|' : _sValue; //如果_sValue为空,_sLink不为空,则还是要个|分开
            _value += _src + _sValue + _sLink;           
        }
    }
    setValue(uploadID, _value);
}
function getValue(id) { return _$(id).value;}
function setValue(id, value) { _$(id).value = decodeURIComponent(value); }

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
		    showMessage("上传错误: " + message, progress);
			this.debug("错误代码: HTTP 错误, 文件名: " + file.name + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
		    showMessage("上传失败.", progress);
			this.debug("错误代码: 上传失败, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
		    showMessage("服务器（ IO ）错误", progress);
			this.debug("错误代码: IO 错误, 文件名: " + file.name + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
		    showMessage("安全错误", progress);
			this.debug("错误代码: 安全错误, 文件名: " + file.name + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
		    showMessage("超出上传限制", progress);
			this.debug("错误代码: 超出上传限制, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
		    showMessage("未通过验证。上传跳过", progress);
			this.debug("错误代码: 文件验证失败, 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
			//	document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
		    showMessage("取消上传", progress);
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
		    showMessage("停止上传", progress);
			break;
		default:
		    showMessage("未处理的错误: " + errorCode, progress);
			this.debug("错误代码: " + errorCode + ", 文件名: " + file.name + ", 文件大小: " + file.size + ", 信息: " + message);
			break;
		}
        
    	setTimeout(function () {
    		progress.disappear();
            cancelUpload(file.id);
    	}, 2000);
	} catch (ex) {
        this.debug(ex);
    }
}

/**
 * 上传完后触发的事件
 */
function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
        if ( is_ckeditor )
        {
            showloading('block', '上传完成.');
            setTimeout(function(){
                var testMenu = top.document.getElementById('testMenu');
                while((testMenu != null) && (testMenu.style.display != 'none'))
                {
                    showloading('none');
                }                    
            }, 300);
        }
	//	document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

/**
 * 队列完成执行事件
 */
function queueComplete(numFilesUploaded) {
	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
}

/**
 * 打开文件选择窗口时触发的事件
 */
function fileDialogStart()
{
    if ( _$('wmid_' + varname) != null )
    {
        // 关闭水印
        if ( _$('wmid_' + varname).checked == false )
        {
            this.setUploadURL(this.settings.upload_url.replace(/wmid\/\d+/, 'wmid/0'));
        }
        else // 打开水印
        {
            this.setUploadURL(this.settings.upload_url.replace(/wmid\/0/, 'wmid/' + wmid));    	
        }
    }
}

/**
 * Flash按钮被加载好的时候执行的操作
 */
function swfUploadLoaded()
{
}