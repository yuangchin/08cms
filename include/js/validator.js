/************************
 *  表单验证类 注：一个表单中只能有一个submit或image按钮，并且不能有任何表单项使用 name = submit,reset,action
 *					如果不能正常验证表单，请注意是否域名不一致引起的
 * 调用方式
 *		object validator = _08cms.validator(string formname[, object options])
 * 参数
 *		formname	要添加验证的表单名称
 *		options		为扩充参数对象，有以下属性 ajax, rules, message
 *			属性
 *				ajax		形如 {element1 : {cache : 0 || 1, url : 'http://.../ajax.php?element1=%1'}, element2 : {cache : 0 || 1, url : 'http://...'}, ...}
 *					属性名称为要ajax验证的表单项名称，值中 cache 代码验证结果是否缓存，url 为 ajax 请求地址，要求 ajax 处理程序验证成功返回空值或“succeed”(不包括引号)，失败返回提示文本
 *				rules		形如 {'rule1' : {cmd : checkhtmltext, arg : 'vid,must,min,max'}, ...}
 *					属性名称为规则名称，cmd 为验证处理函数，arg 为函数参数列表(注意函数参数的顺序)，可用值见 _08cms.validator 中的 attributes
 *				message
 *
 * 返回值 validator 为控制对象，有以下方法 init, load, submit, attribute
 *		init		添加或修改 options对象里的属性
 *			调用方式
 *				validator.init(string option, string name, mixed content)
 *			参数
 *				option		要设置的项属性名称，见 options 属性
 *				name		要写入的键名
 *				content		要写入的内容
 *		load		表单加载完成要执行的代码，如果表单已经加载完成则立即执行，否则等到表单加载完成时执行
 *			调用方式
 *				validator.init(function callback)
 *			参数
 *				callback	函数名称，可在函数里使用 this 指针代表 form 元素
 *		submit		同 load，只不过是在提交表单时执行，如果返回 false 可以中止表单提交
 *		attribute	设置验证项目的属性
 *			调用方式
 *				validator.attribute(string element, string property, mixed value)
 *			参数
 *				element		表单项目名称
 *				property	设置的属性名称，可设置的属性名见 _08cms.validator 中的 attributes
 *				value		要设置的属性值
 ***********************/

if(typeof _08cms == 'undefined')_08cms = {};

(function(window){
var load, start = true, end, stack = {};
function listen(dom,event,action){
	if(event == 'ready')return ready.add(dom, action);
	if(dom.attachEvent){
		var func=action;action=function(){func.apply(dom,arguments)};
		dom.attachEvent('on'+event,action);
	}else if(dom.addEventListener){
		dom.addEventListener(event,action,false);
	}
}
function ready(){
	var id, dom, has, flag, func, parent;
	for(id in stack){
		if(!(dom = document.getElementById(id))){
			has = true;
			continue;
		}
		if(!load && !dom.nextSibling){
			flag = false;
			parent = dom.parentNode;
			while(parent){
				if(parent.nextSibling){
					flag = true;
					break;
				}
				parent = parent.parentNode;
			}
			if(!flag){
				has = true;
				continue;
			}
		}
		while(func = stack[id].pop())func.apply(dom);
		delete stack[id];
	}
	if(load || end || !has){
		load && !end && (stack = {});
		start = true;
	}else{
		setTimeout(ready, 20);
	}
}
ready.add = function(id, func){
	end = true;
	var timer = setInterval(function(){
		if(!start)return;
		clearInterval(timer);
		start = end = false;
		if(id)stack[id] ? stack[id].push(func) : stack[id] = [func];
		ready();
	}, 20);
};
listen(window, 'load', function(){load = true;ready.add()});
window.listen = listen;
})(this);

_08cms.validator = function(){
	var key, exists = [],
	attributes = [
		'vid',		// 表单项关联ID
		'min',		// 最小数值或字符串长度
		'max',
		'rev',		// 表单项显示名称，给默认提示信息用的
		'must',		// 必填/选的表单项
		'mode',		// 字符串格式用于 checklimit 函数检查，如 'int,number,email,...' mode => mode
		'regx',		// 正则表达式规则
		'exts',		// 扩展参数
		'ajax',		// Aajx提示字串
		'init',		// 初始化提示字串。对比规则中如果原表单项为空显示这个
		'comp',		// 对比规则专用。如果原表单项填写了，显示这个
		'wait',		// Ajax等待时提示字串
		'warn',		// 警告错误提示字串
		'pass'		// 验证正确提示字串
	],
	rules = {
		// 只作提示用
		'void' : {},
		// 必选填并不能为空值
		'must'	:{
			cmd : checkempty,
			arg : 'vid,form,mode,regx'
		},
		// 整数检查
		'int'	: {
			cmd : checkint,
			arg : 'item,must,regx,min,max'
		},
		// 小数检查
		'float' : {
			cmd : checkfloat,
			arg : 'item,must,regx,min,max'
		},
		// 时间日期检查
		'date'	: {
			cmd : checkdate,
			arg : 'item,must,min,max'
		},
		// 下拉框/单/复选框 是否有选
		'check'	: {
			cmd : checkcheck,
			arg : 'vid,form'
		},
		// 数字字符串
		'number'	: {
			cmd : function(){
				arguments[2] = 'number';
				return checktext.apply(null, arguments);
			},
			arg : 'item,must,mode,regx,min,max'
		},
		// 字母字符串
		'letter'	: {
			cmd : function(){
				arguments[2] = 'letter';
				return checktext.apply(null, arguments);
			},
			arg : 'item,must,mode,regx,min,max'
		},
		// 字母与数字字符串
		'numberletter'	: {
			cmd : function(){
				arguments[2] = 'numberletter';
				return checktext.apply(null, arguments);
			},
			arg : 'item,must,mode,regx,min,max'
		},
		// 字符串
		'text'	: {
			cmd : checktext,
			arg : 'item,must,mode,regx,min,max'
		},
		// FCK编辑器HTML串
		'html'	: {
			cmd : checkhtmltext,
			arg : 'vid,must,min,max'
		},
		//比较
		'comp'	: [
			{//初始化提示
				cmd : function(vid, rev){
					var comp = this.form.elements[vid];
					if(!comp)return;
					if(!comp.value){
						return this._08check.init || (rev ? '\u8bf7\u5148\u8f93\u5165' + rev + ' \u9879' : '\u8bf7\u5148\u8f93\u5165\u8981\u6bd4\u8f83\u7684\u9879');
					}else{
						return this._08check.comp || '\u8bf7\u8f93\u5165\u5bf9\u6bd4\u5185\u5bb9';
					}
				},
				arg : 'vid,rev',
				use	: true
			},
			{//验证提示
				cmd : function(vid, must, rev){
					var comp = this.form.elements[vid];
					if(!comp)return;
					if(must && !comp.value)
						return this._08check.init || (rev ? '\u8bf7\u5148\u8f93\u5165' + rev + ' \u9879' : '\u8bf7\u5148\u8f93\u5165\u8981\u6bd4\u8f83\u7684\u9879');
					if(must && !this.value)
						return this._08check.comp || '\u8bf7\u8f93\u5165\u5bf9\u6bd4\u5185\u5bb9';
					if(comp.value && comp.value != this.value)
						return this._08check.warn || '\u4e24\u6b21\u8f93\u5165\u4e0d\u4e00\u81f4\uff01';
				},
				arg : 'vid,must,rev',
				use	: true
			}
		],
		// 文本集
		'texts'	: {
			cmd : checktexts,
			arg : 'vid,must,exts,min,max'//id, notnull, fields, min, max
		},
		// 单个附件
		'adj'	: {
			cmd : checksimple,
			arg : 'item,must,exts'
		},
		// 多个附件
		'adjs'	: {
			cmd : checkmultiple,
			arg : 'item,must,exts,min,max'
		},
		// 分组，未处理
		'group'	: {
			cmd : checkgroup,
			arg : 'vid,min,max'
		},
		// E-mail
		'email'	: {
			cmd : function(){
				arguments[2] = 'email';
				return checktext.apply(null, arguments);
			},
			arg : 'item,must,mode,regx,min,max'
		},
		// IP
		'ip'	: function(ip){
			var match = /^\s*(\d+)\.(\d+)\.(\d+)\.(\d+)\s*$/.exec(ip);
			if(!match)return false;
			match[0] = parseInt(match[0]);
			if(isNaN(match[0]) || match[0] < 1 || match[0] > 254)return false;
			match[1] = parseInt(match[1]);
			if(isNaN(match[1]) || match[1] < 0 || match[1] > 254)return false;
			match[2] = parseInt(match[2]);
			if(isNaN(match[2]) || match[2] < 0 || match[2] > 254)return false;
			match[3] = parseInt(match[3]);
			if(isNaN(match[3]) || match[3] < 1 || match[3] > 254)return false;
			return true;
		},
		// URL地址
		'url'	: /^\s*https?:\/\/(?:[\w\-]+\.)+[a-z]{2,4}(?:\:\d{1,5})?(?:\/.*)?\s*$/,
		// 电话
		'tel'	: /^\s*(\+[1-9]\d+-?)?(?:[48]00-?\d{3}-?\d{4}|(?:00?[1-9]\d{1,2}-?)?[2-8]\d{6,7})\s*$/,
		'mobile': /^\s*(\+[1-9]\d+-?)?1[358]\d{9}\s*$/,
		'phone'	: /^\s*(\+[1-9]\d+-?)?(?:[48]00-?\d{3}-?\d{4}|(?:00?[1-9]\d{1,2}-?)?[2-8]\d{6,7}|1[358]\d{9})\s*$/
	};
	function _(tagName){
		return document.createElement(tagName);
	}
	function T(text){
		return document.createTextNode(text);
	}
	function each(object, callback){
		var key, value, length = object.length;
		if(length === undefined || object instanceof Function){
			for(key in object)if(callback.call(value = object[key], key, value) === false)break;
		}else{
			var i = -1;
			while(object[++i] && callback.call(value = object[i], i, value) !== false);
		}
	}
	function message(mode, className, message, use, options){
		var msgbox = this._08check.message, txt = (use ? message : (this._08check[mode] || options.message[mode] || message)) || '';
		if(!options.configs.notips){
			msgbox.style.display = txt || (this._08check.must && className == 'pass') ? '' : 'none';
			msgbox.className = 'validator_message ' + className;
            
            // 如果input的状态不让输入则自动隐藏提示
            if ( this.disabled )
            {
                msgbox.style.display = 'none';
            }
            
			msgbox.textNode && msgbox.removeChild(msgbox.textNode);
			msgbox.appendChild(msgbox.textNode = T(txt || 'OK'));// IE6 bug, txt为空不会显示
		}
        
		return mode != 'pass' ? txt : '';
	}
	function check(item, options){
		var onblur = item.onblur;
		item.onblur = function(e, callback){
			//下一句解决IE的执行先后造成的BUG
			if(item.form._08cms_noblur || (item._valid.ajax && item._valid.timer && item._valid.callback))return;
			clearTimeout(item._valid.timer);
			onblur && onblur.call(this, e);
			var	ajax, args, flag, info, mode, clss, rule = options.rules[this._08check.rule];
			if(rule.length)rule = rule[1] || rule[0] || rule;
			if(rule.cmd){
				args = rule.arg.split(/\s*,\s*/g);
				for(var key = 0; args[key]; key++)args[key] = this._08check[args[key]];
			}else{
				if(rule instanceof RegExp || rule instanceof Function){
					args = [this._08check.vid, this._08check.must, rule, this._08check.min, this._08check.max];
					rule = {cmd : checkbyregx};
				}else{
					callback && callback.call(item);
					return;
				}
			}
			info = rule.cmd.apply(this, args);
			if(options.ajax[item.name] && !info){
				flag = true;
				item._valid.ajax || (item._valid.ajax = {});
				if(options.ajax[item.name].cache && item.value in item._valid.ajax){
					mode = 'ajax';
					clss = (info = item._valid.ajax[item.value]) ? 'warn' : 'pass';
					if(!info){
						//如果是提交表单，并没有Ajax失败(包括没有执行Ajax的)的不显示提示
						clss = 'ajax';
					}
//					info = info ? (item._08check.rev || '') + info : '';		// Ajax不要加那个说明
					callback && callback.call(item, !info);
				}else{
					item._valid._ajax = true;
					item._valid._value = item.value;
                    var eleScript= document.createElement("script");
                    eleScript.type = "text/javascript";
					var _url = options.ajax[item.name].url.replace(/%1/g, encodeURIComponent(item.value)) + '/datatype/json/callback/callback_vajax';
					// 扩展其它动态参数(类似的,可以扩充n多参数,但有必要吗？)
					// Demo : (1)表单域加上属性:ajaxpara2="fmdata[lxdh]",表示把另一个id为fmdata[lxdh]的表单域值加上去ajax认证; 
					//        (2)初始化.init("ajax","msgcode",{ url: '.../isjs/1/code/%1/tel/%2' });
					if(_url.indexOf('%2')>0 && item.getAttribute("ajaxpara2")){ //
						var _par2id = item.getAttribute("ajaxpara2");
						var _par2e = document.getElementById(_par2id); 
						if(_par2e.value){
							_url = _url.replace(/%2/g, encodeURIComponent(_par2e.value)); //fmdata[lxdh]
						}
					}
                    eleScript.src = _url;
					
                    //之前函数名为js_callback改为callback_vajax；避免与floatwin.js中的js_callback同名
					callback_vajax = function(info) {
                        if(info == 'succeed')info = '';
						item._valid.ajax[item.value] = info;
						if(item._valid._ajax){
							mode = 'ajax';
							clss = info ? 'warn' : 'pass';
//								info = info ? (item._08check.rev || '') + info : '';		// Ajax不要加那个说明
							message.call(item, mode, clss, info, undefined, options);
						}
						callback && callback.call(item, !info);
                    }
                    document.getElementsByTagName("HEAD")[0].appendChild(eleScript);
                    
					mode = clss = 'wait';
					info = '\u67e5\u8be2\u4e2d...';
				}
			}else{
				mode = clss = info ? 'warn' : 'pass';
				info = info ? (this._08check.rev || '') + info : '';
			}
			info = message.call(this, mode, clss, info, rule.use, options);
			//ajax之前就出错或不是ajax的元素
			(!options.ajax[item.name] || !flag) && callback && callback.call(item, !info);
			return info;
		}
	}
	function showinfo(item, options){
		var onfocus = item.onfocus;
		function show(e, init){
			item._valid._ajax = false;
//			item.form._08cms_submit = false;
			!init && onfocus && onfocus.call(item, e);
			var info, rule = options.rules[item._08check.rule];
			if(rule.length && rule[0]){
				rule = rule[0];
				if(rule.cmd){
					args = rule.arg.split(/\s*,\s*/g);
					for(var key = 0; args[key]; key++)args[key] = item._08check[args[key]];
				}else{
					if(rule instanceof RegExp || rule instanceof Function){
						args = [item._08check.vid, item._08check.must, rule, item._08check.min, item._08check.max];
						rule = {cmd : checkbyregx};
					}else{
						return;
					}
				}
				info = rule.cmd.apply(item, args);
			}
			message.call(item, 'init', init ? 'init' : 'focus', info, rule.use, options);
		}
		show.call(item, {}, true);
		if(item._08check.rule != 'void')item.onfocus = show;
	}
	function attribute(item, key, val, options){
		var item = this.elements[item];
		if(item && item._08check)item._08check[key] = val;
		key == 'init' && message.call(item, 'init', 'init', undefined, undefined, options || {message:{}});
	}
	function loadinit(){
		var key, item, rule, parent, offset, i = 0, object = validator.config[this.id].object, elem = object.form.elements, options = object.options;
		while(item = elem[i++]){
			if(!item._08check && (rule = item.getAttribute('rule'))){
				item._valid = {};
				item._08check = {
					'item' : item,
					'rule' : rule.toLowerCase()
				};
				each(['offset'].concat(attributes), function(){item._08check[this] = item.getAttribute(this)});
				item._08check.must = !empty(item._08check.must);
				if(!options.configs.notips){
					if(item._08check.offset && (parent = document.getElementById(item._08check.offset))){
						parent.appendChild(item._08check.message = _('DIV'));
					}else{
						parent = item.parentNode;
						offset = item;
						if(item._08check.offset = parseInt(item._08check.offset)){
							key = item._08check.offset > 0 ? 'nextSibling' : 'previousSibling';
							while(item._08check.offset && offset[key]){
								offset = offset[key];
								offset.nodeType == 1 && (item._08check.offset > 0 ? item._08check.offset-- : item._08check.offset++);
							}
						}
                        
                        // 解决PHP端加的注释节点与本提示节点顺序影响样式问题
                        if ( offset.className == 'tips1' )
                        {
                            offset = offset.previousSibling;
                        }
                        
						parent.replaceChild(item._08check.message = _('DIV'), offset);
						parent.insertBefore(offset, item._08check.message);
					}
				}
				if(item._08check.rule != 'void'){
					check(item, options);
				}
				showinfo(item, options);
			}
		}
	}
	function validator(){
		var id = ++validator.insIndex,
			config = validator.config[id] = {
				stack : {},
				object : {}
			},
			object = {
				id : id,
				instance : new function(){
					var instance = this;
					each(validator.prototype, function(name, method){
						config.stack[name] = [];
						instance[name] = function(method){
							return function(){
								if(config.object.form){
									method.apply(object, arguments);		//179行
								}else{
									config.stack[name].push(arguments);
								}
								return instance;
							};
						}(method);
					});
				}
			};
		return object;
	}
	validator.insIndex = 0;
	validator.config = {};
	validator.loaded = function(form, object){
		var config = validator.config[object.id];
		config.object = {
			options : object.options,
			form : form
		}
		each(validator.prototype, function(name, method){
			setTimeout(function(){
				each(config.stack[name], function(){
					method.apply(object, this);						//159行
				});
				config[name] = [];
			}, 7);
		});

	};
    // 验证配置初始化
	validator.prototype = {
		init : function(option, name, content){
			var options = validator.config[this.id].object.options;
			if(options[option]){
				options[option][name] = content;
			}
		},
		load : function(func){
			var form = validator.config[this.id].object.form;
			func.apply(form, arguments);
		},
		reload : loadinit,
		submit : function(func){
			var form = validator.config[this.id].object.form,
				submit = form.onsubmit;
			form.onsubmit = function(){
				submit.apply(form, arguments);
				func.apply(form, arguments);
			};
		},
		attribute : function(){
			var object = validator.config[this.id].object;
			arguments[3] = object.options;
			arguments.length = 4;
			attribute.apply(object.form, arguments);
		}
	};
	return function(formid, options){
		if(!exists[formid]){
			exists[formid] = true;//初始化
			var __validator = validator();
			__validator.options = options || (options = {});
			each(['ajax', 'rules', 'configs', 'message'], function(index, property){options[property] || (options[property] = {})});
			each(rules, function(rule){options.rules[rule] = this});
			listen(formid, 'ready', function(){
				var elem = this.elements, oldsubmit = this.onsubmit;
				this.className += ' validator';
				this.onsubmit = function(e){
					var sleep, interval = 30, timestamp = (new Date).getTime();
					var i = 0, num = 0, ajax = 0, form = this, first, item, info, doSubmit, message = '';
					if(form._08cms_submit && (typeof(_08_resubmit) == 'undefined')){
						sleep = interval - Math.floor(((new Date).getTime() - form._08cms_submit) / 1000);
						if(sleep > 0){
							form._08cms_noblur = true;
							alert('\u6b63\u5728\u63d0\u4ea4\u8868\u5355\uff0c\u8bf7\u4e0d\u8981\u91cd\u590d\u63d0\u4ea4\uff01\n(\u5982\u679c ' + sleep + ' \u79d2\u5185\u8fd8\u672a\u63d0\u4ea4\u6210\u529f\uff0c\u53ef\u5c1d\u8bd5\u518d\u6b21\u63d0\u4ea4)');
							return form._08cms_noblur = false;
						}
					}
					form._08cms_submit = timestamp;
					while(item = elem[i++]){
//						info = item.onblur ? item.onblur(e, true) : '';		// IE8会报错
						if(options.ajax[item.name]){// && (!item._valid.ajax || !(item.value in item._valid.ajax) || item._valid._value != item.value || item._valid.ajax[item.value])
							//Ajax##并且没有验证过或，正在验证或修改了，验证失败
							ajax++;
							doSubmit = true;
							if(info = item.onblur.call(item, e, function(flag){
								if(doSubmit && !(doSubmit = flag))first = this;
								setTimeout(function(){
									if(!--ajax && !message){
										//最后一个Ajax通知
										if(doSubmit && form._08cms_submit == timestamp){
											//Ajax验证成功，并表单正确，状态也没有改变
											var i = 0; j = 0, item;
											while(item = elem[j++]){
												if('submit,image'.indexOf(item.type.toLowerCase()) >= 0 && item.name){
													//JS提交不会提交submit的值，
													i = document.createElement('input');
													i.type = 'hidden';
													i.name = item.name;
													i.value = item.value;
													form.appendChild(i);
													break;
												}
											}
											form.submit();
											try{ //捕获IE6,7,8的一个错误! [Error Object]
												i && form.removeChild(i);
											}catch(ex){}
										}else{
											try{ //修正:在与自定义js认证函数下出错。
											form._08cms_noblur = true;
											form._08cms_submit = false;
											form._08cms_noblur = false;
											first.focus();
											alert('\u8868\u5355\u586b\u5199\u6709\u8bef\u6216\u8d85\u65f6\uff0c\u8bf7\u68c0\u67e5\u3002\u786e\u5b9a\u6ca1\u9519\u8bf7\u91cd\u65b0\u63d0\u4ea4\uff01');
											}catch(ex){}
										}
									}else if(!flag){
										form._08cms_submit = false;
									}
								}, 13)
							})){
								if(info != '\u67e5\u8be2\u4e2d...'){
									if(!first)first = item;
									message += '\n' + (++num) + '. ' + info;
								}
							}
						}else{
							if(info = item.onblur ? item.onblur.call(item, e) : ''){
								if(!first)first = item;
								message += '\n' + (++num) + '. ' + info;
							}
						}
					}
					if(message){
						form._08cms_noblur = true;
						alert('\u63d0\u4ea4\u5931\u8d25\uff0c\u8bf7\u6309\u4ee5\u4e0b\u63d0\u793a\u68c0\u67e5\u60a8\u7684\u8f93\u5165\uff1a\n' + message);
						form._08cms_noblur = false;
						try{
							first.focus();
						}catch(x){}
						return form._08cms_submit = false;
					}
					if(oldsubmit && oldsubmit.call(form, e) === false){
						return form._08cms_submit = false;
					}
					return !ajax;
				};
//				options.load && options.load.call(this, function(){attribute.apply(this, arguments)});
				validator.loaded(this, __validator);
				__validator.instance.reload();
			});
		}
		try {return __validator.instance;} catch (err) {}
	}

function $id(d){return typeof d == 'string' ? document.getElementById(d) : d}

function trim(str) {
	return str?str.replace(/^\s+|\s+$/,''):'';
}
function empty(val){
	var i,ret = !val;
	if(!ret){
		if(typeof val == 'string')
			ret =/^[\s|0]*$/.test(val);
		else if(val instanceof Array)
			ret = !val.length;
		else if(val instanceof Object){
			ret = true;
			for(i in val){ret = false;break}
		}
	}
	return ret;
}

function checkempty(name, form, limit, regular, min, max){
	if(!name)name = this;if(!form)form = this.form;
	var field = typeof name != 'string' ? name : form ? form.elements[name] : $id(name), dom = field.length ? field[0] : field, tag = dom.tagName.toLowerCase(), ret = '\u4e3a\u5fc5\u9009/\u586b\u9879\u76ee', i, val;
	if(tag == 'option'){
		for(i = 0; i < field.length; i++)if(field[i].selected && !empty(field[i].value)){ret = '';val = field[i].value;break}
	}else if(tag == 'textarea'){
		val = dom.value;
		if(!empty(val))ret = '';
	}else if(dom.type){
		switch(dom.type.toLowerCase()){
		case 'text':
		case 'password':
		case 'hidden':
		case 'file':
		case 'button':
		case 'image':
			val = dom.value;
			if(!empty(dom.value))ret = '';
			break;
		case 'radio':
		case 'checkbox':
			field.length || (field = [field]);
			for(i = 0; i < field.length; i++)if(field[i].checked && !empty(field[i].value)){ret = '';val = field[i].value;break}
			break;
		default:
			ret = '';//unknow
		}
	}else{
		ret = '';//unknow
	}
	!ret && val && limit && (ret = checklimit(val, limit));
	!ret && val && regular && (ret = checkregular(regular, val));
	return ret;
}

function checkcheck(name, form){
	if(!name)name = this;if(!form)form = this.form;
	var field = typeof name != 'string' ? name : form ? form.elements[name] : $id(name), dom = field.length ? field[0] : field, tag = dom.tagName.toLowerCase(), ret = '\u4e3a\u5fc5\u9009/\u586b\u9879\u76ee', i, val;
	if(tag == 'option'){
		for(i = 0; i < field.length; i++)if(field[i].selected){ret = '';break}
	}if(dom.type){
		switch(dom.type.toLowerCase()){
		case 'radio':
		case 'checkbox':
			field.length || (field = [field]);
			for(i = 0; i < field.length; i++)if(field[i].checked){ret = '';break}
			break;
		default:
			ret = '';//unknow
		}
	}else{
		ret = '';//unknow
	}
	return ret;
}

function checkstring_minmax(len, min, max){
	min = parseInt(min);
	max = parseInt(max);
	if(!min && !max)return;
	if((min && len < min) || (max && len > max)){
		return '\u957f\u5ea6\u4e0d\u7b26\u5408\uff0c' + (min == max ? '\u5fc5\u9700\u4e3a ' + min + ' \u4e2a\u5b57\u8282\uff01'
			 : min && max ? '\u5fc5\u9700\u5728 ' + min + ' - ' + max + ' \u4e2a\u5b57\u8282\u4e4b\u95f4\uff01'
			 : min ? '\u81f3\u5c11 ' + min + ' \u4e2a\u5b57\u8282\uff01' : ('\u6700\u591a ' + max + ' \u4e2a\u5b57\u8282\uff01'));
	}
}

function checknumber_minmax(val, min, max){
	min = empty(min) && min != '0' ? null : parseFloat(min);
	max = empty(max) && max != '0' ? null : parseFloat(max);
	if(min === null && max === null)return;
	val = parseFloat(val);
	if((min !== null && val < min) || (max !== null && val > max)){
		return '\u6570\u503c\u65e0\u6548\uff0c' + (min === max ? '\u5fc5\u9700\u662f ' + min
			 : min != null && max != null ? '\u5fc5\u9700\u5927\u4e8e\u7b49\u4e8e ' + min + ' \u5e76\u4e14\u5c0f\u4e8e\u7b49\u4e8e ' + max
			 : min != null ? '\u5fc5\u9700\u5927\u4e8e\u7b49\u4e8e ' + min : ('\u5fc5\u9700\u5c0f\u4e8e\u7b49\u4e8e ' + max)) + ' \uff01';
	}
}

function checktext(id, notnull, limit, regular, min, max){
	var dom=$id(id),val=trim(dom.value),len,ret;
	if(val.length==0&&empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	if(limit && (ret=checklimit(val,limit)))return ret;
	if(regular && (ret = checkregular(regular, val)))return ret;
	len=strlen(val);
	return checkstring_minmax(len, min, max);
}

function checktexts(id, notnull, fields, min, max){
	var i, l, temp, tline, length = 0, slen = id.length, inputs = document.getElementsByTagName('INPUT');
	fields = fields.split('\n');
	for(i = 0, l = fields.length; i < l; i++){
		temp = fields[i].split('|');
		fields[i] = [temp[0], parseInt(temp[1]) || 0, parseInt(temp[2]) || 0];
	}
	for(i = 0, l = inputs.length; i < l; i++){
		if(inputs[i].name.slice(0, slen) == id && (temp = inputs[i].name.slice(slen).match(/^\[(\d+)\]\[(\d+)\]$/))){
			if(tline != temp[1]){
				tline = temp[1];
				length++;
			}
			if(fields[temp[2]] && ((fields[temp[2]][1] && fields[temp[2]][1] > strlen(inputs[i].value)) || (fields[temp[2]][2] && fields[temp[2]][2] < strlen(inputs[i].value))))return fields[temp[2]][1] && fields[temp[2]][2] ? ('\u7b2c ' + length + ' \u884c\u7b2c ' + (parseInt(temp[2]) + 1) + ' \u5217\u957f\u5ea6\u9700\u5728 ' + fields[temp[2]][1] + ' \u5230 ' + fields[temp[2]][2] + ' \u4e2a\u5b57\u8282\u4e4b\u95f4') : fields[temp[2]][1] ? ('\u7b2c ' + length + ' \u884c\u7b2c ' + (parseInt(temp[2]) + 1) + ' \u5217\u957f\u5ea6\u9700\u5927\u4e8e ' + fields[temp[2]][1] + ' \u4e2a\u5b57\u8282') : ('\u7b2c ' + length + ' \u884c\u7b2c ' + (parseInt(temp[2]) + 1) + ' \u5217\u957f\u5ea6\u9700\u5728 ' + fields[temp[2]][2] + ' \u4e2a\u5b57\u8282\u4ee5\u5185');
		}
	}
	if(length < min || (max && length > max))return min && max ? ('\u9879\u6570\u5e94\u5728 ' + min + ' \u5230 ' + max + ' \u9879\u4e4b\u95f4') : min ? ('\u9879\u6570\u5e94\u5927\u4e8e ' + min + ' \u9879') : ('\u9879\u6570\u5e94\u5c0f\u4e8e ' + max + ' \u9879');
}

function checkhtmltext(id, notnull, min, max){
	var val=trim(CKEDITOR && CKEDITOR.instances[id] ? CKEDITOR.instances[id].getData() : $id(id).value),len,ret;
	if(empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	len=strlen(val);
	return checkstring_minmax(len, min, max);
}

function checksimple(id, notnull, exts){
	var e=','+exts+',',dom=$id(id),val=trim(dom.value),ext;
	if(empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	ext=trim(val.substr(val.lastIndexOf('.')+1)).toLowerCase();
	ext=ext.split('|'),ext=empty(ext[0]) ? '' : ext[0];
	if(!ext||e.indexOf(','+ext+',')<0)return '\u4e0d\u652f\u6301\u6b64\u4e0a\u4f20\u7c7b\u578b\u6269\u5c55\u540d\u7684\u9644\u4ef6\uff01';
}

function checkmultiple(id, notnull, exts, min, max){
	var dom=$id(id),val=trim(dom.value),len;
	if(empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	val=val.split('\n');len=val.length;
	min=parseInt(min);
	max=parseInt(max);
	if((min && len < min) || (max && len > max))return '\u6570\u91cf\u8d85\u51fa\u6709\u6548\u8303\u56f4 ' + min + ' - ' + max + ' \uff01';
	var i,v,e=','+exts+',',ext;
	for(i=0;i<len;i++){
		v=val[i].split('|');
		if(empty(v[0]) || v.length>3 || (v.length==3 && !isint(trim(v[2]))))return (i+1)+'\u884c\u4e0d\u662f\u6709\u6548\u6570\u636e\u683c\u5f0f\uff01';
		ext=trim(v[0].substr(v[0].lastIndexOf('.')+1)).toLowerCase();
		if(!ext||e.indexOf(','+ext+',')<0)return (i+1)+'\u884c\u4e0d\u652f\u6301\u6b64\u4e0a\u4f20\u7c7b\u578b\u6269\u5c55\u540d\u7684\u9644\u4ef6\uff01';
	}
}

function checkdate(id, notnull, min, max){
	var dom=$id(id),val=trim(dom.value),e=/^(\d{4})-(\d{1,2})-(\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?$/,ret;
	if(empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	if(ret=checklimit(val,'date'))return ret;
	if(!min&&!max)return;
	if(min && (ret=min.match(e)))min=new Date(ret[1],ret[2],ret[3],ret[4]||0,ret[5]||0,ret[6]||0);else min=0;
	if(max && (ret=max.match(e)))max=new Date(ret[1],ret[2],ret[3],ret[4]||0,ret[5]||0,ret[6]||0);else max=0;
	ret=val.match(e);val=new Date(ret[1],ret[2],ret[3],ret[4]||0,ret[5]||0,ret[6]||0);
	if((min && val < min) || (max && val > max))return '\u65f6\u95f4\u8d85\u51fa\u6709\u6548\u8303\u56f4 ' + min + ' - ' + max + ' \uff01';
}

function checkint(id, notnull, regular, min, max){
	var dom=$id(id),val=trim(dom.value),ret;
	if(val.length==0&&empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	if(ret=checklimit(val,'int'))return ret;
	if(regular && (ret = checkregular(regular, val)))return ret;
	return checknumber_minmax(val, min, max);
}

function checkfloat(id, notnull, regular, min, max){
	var dom=$id(id),val=trim(dom.value),ret;
	if(empty(val))return empty(notnull) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	if(ret=checklimit(val,'number'))return ret;
	if(regular && (ret = checkregular(regular, val)))return ret;
	return checknumber_minmax(val, min, max);
}

function checkgroup(vid, min, max){

}

function checkbyregx(id, must, regx, min, max){
	var len, val, dom = $id(id);
	dom = dom || this;
	val = trim(dom.value);
	if(empty(val))return empty(must) ? '' : '\u4e0d\u80fd\u4e3a\u7a7a';
	if(regx instanceof RegExp ? !regx.test(val) : !regx(val))return '\u8f93\u5165\u4e0d\u7b26\u5408\u89c4\u5219';
	len=strlen(val);
	return checkstring_minmax(len, min, max);
}

function checklimit(val,limit){
	var ret;
	switch(limit){
    // 不是一个整数
	case'int':
		if(!isint(val))ret = '\u4e0d\u662f\u4e00\u4e2a\u6574\u6570';
		break;
    // 不是一个数字
	case'number':
		if(!isnumber(val))ret = '\u4e0d\u662f\u4e00\u4e2a\u6570\u5b57';
		break;
    // 不是有效字母串
	case'letter':
		if(!isletter(val))ret = '\u4e0d\u662f\u6709\u6548\u5b57\u6bcd\u4e32';
		break;
    // 不是有效字符串
	case'numberletter':
		if(!isnumberletter(val))ret = '\u4e0d\u662f\u6709\u6548\u5b57\u7b26\u4e32';
		break;
    // 请用字母开始的字符串
	case'tagtype':
		if(!istagtype(val))ret = '\u8bf7\u7528\u5b57\u6bcd\u5f00\u59cb\u7684\u5b57\u7b26\u4e32';
		break;
    // 不是有效日期格式
	case'date':
		if(!isdate(val))ret = '\u4e0d\u662f\u6709\u6548\u65e5\u671f\u683c\u5f0f';
		break;
    // 不是有效电子邮箱地址格式
	case'email':
		if(!isemail(val))ret = '\u4e0d\u662f\u6709\u6548\u7535\u5b50\u90ae\u7bb1\u5730\u5740\u683c\u5f0f';
		break;/*
	default:
		ret='未知检测格式';*/
	}
	return ret;
}

function checkregular(regular, val){
	try{
		if(regular){
			eval('var e=' + regular);
			if(!e.test(val))return '\u8f93\u5165\u4e0d\u7b26\u5408\u89c4\u5219';
		}
	}catch(e){
		return '\u6b63\u5219\u68c0\u67e5\u5b57\u4e32\u6709\u8bef\uff0c\u8bf7\u8054\u7cfb\u7ba1\u7406\u5458';
	}
}

function strlen(str){
	//var tmp =window.charset == 'utf-8' ? '***' : '**';
	//return str.replace(/[^\x00-\xff]/g, tmp).length;
	return str.replace(/[^\x00-\xff]/g, "**").length; //与dz相同判断
	// 不管[中文GBK/中文utf-8/英文]编码,中文算两个字节,英文算一个
}

function isdate(str){
	var ret = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?$/);
	if(ret == null) return false;
	ret[2] --;
	var d = new Date(ret[1],ret[2],ret[3],ret[4]||0,ret[5]||0,ret[6]||0);
	return d.getFullYear() == ret[1] && d.getMonth() == ret[2] && d.getDate() == ret[3] &&
		(!ret[4] || d.getHours() == ret[4]) && (!ret[5] || d.getMinutes() == ret[5]) && (!ret[6] || d.getSeconds() == ret[6])
}

function isnumber(str){
	var reg = /^[+-]?\d+(?:\.\d+)?$/;
	return reg.test(str);
}

function isnumberletter(str){
	var reg = /^\w+$/;
	return reg.test(str);
}

function istagtype(str){
	var reg = /^[a-zA-Z]+\w*$/;
	return reg.test(str);
}

function isletter(str){
	var reg = /^[a-zA-Z]+$/;
	return reg.test(str);
}

function isint(str){
	var reg = /^-?\d+$/;
	return reg.test(str);
}

function isemail(str){
	var reg = /^[\w\.\+]+@(?:[\-A-Z0-9]+\.)+[A-Z]{2,4}$/i;
	return reg.test(str);
}

}();






/**
 * 验证码处理对象
 */
var _08_Regcode = {
    id : '_08code_',
    isIE : /MSIE/.test(navigator.userAgent),
    
    /**
     * 展示验证码
     * 
     * @param object obj       在该节点后插入验证码
     * @param string code_name 验证码名称
     * @param int    _timer    验证码时间
     */
    show : function( obj, code_name, e, _timer )
    {
        document.getElementById(code_name).style.display = '';
    },

    /**
     * 隐藏一个验证码
     *
     * @param string code_name 验证码名称
     */
    hide : function( obj, code_name, flag )
    {
        _this = this;
        document.onclick = function(e){
            e = e || window.event;
            _target = e.target || e.srcElement;
            if ( ((_target.id != code_name) && (_target.id != obj.id)) || flag )
            {
                _this.setHide(code_name);
            }
        }
    },
    
    setHide: function( code_name )
    {
        document.getElementById(code_name).style.display = 'none';
    },
    
    /**
     * 当表单输入完input的maxlength个字符时自动隐藏验证码
     
     * @param object obj       input对象
     * @param string code_name 验证码名称
     */
    keyUpHide : function( obj, code_name, form_name )
    {
        if ( obj.value.length >= obj.maxLength )
        {
            this.setHide(code_name);
            obj.blur();
            
            // 保持输入完验证码后能按回车提交
            document.onkeydown = function(e) {
                if( !e ) e = window.event;//火狐中是 window.event
                if( (e.keyCode || e.which) == 13 )
                {
                    var form = document.getElementById(form_name);
                    if ( !form )
                    {
                        if ( isIE )
                        {
                            var es = document.getElementsByTagName("form");
                            for( var i = 0; i < es.length; i++ )
                            {
                                if( es[i].getAttribute("name") == form_name )
                                {
                                    form = es[i];
                                    break;
                                }
                            }
                        }
                        else
                        {
                        	form = document.getElementsByName(form_name)[0];
                        }                        
                    }
                    form && !form.getAttribute('onsubmit') && form.submit();
                }
            }
        }
    },
    
    /**
     * 获取从当前节点到浏览器左部的距离
     *
     * @param object element 当前节点对象
     */
    getElementLeft : function (element)
    {
        var actualLeft = element.offsetLeft;
        var current = element.offsetParent;
        var position;

        while (current !== null)
        {
            if ( this.isIE )
            {
                position = current.currentStyle.position;
            }
            else
            {
            	position = document.defaultView.getComputedStyle(current, null)['position'];
            }
            
            // 遇到相对定位时终止计算
            if ( position == 'relative' )
            {
                break;
            }
            
            actualLeft += current.offsetLeft;            
            current = current.offsetParent;
        }

        return actualLeft;
    }
};