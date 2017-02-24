if(typeof _08cms == 'undefined')_08cms = {};
if(!_08cms.stack)_08cms.stack = {};
if(!_08cms.fields)_08cms.fields = {};
/*
 * 联动下拉框
 *		_08cms.fields.linkage(name, data, def, mode, notip, layout);
 * 参数说明
 *		name			要生成的表单项名称
 *		data			列表数组，或Ajax参数。数组格式：[[id, pid, title, flag]]，flag 为真表示本身不可用，还要选择下一级
 *		value			初始值
 *		mode			单/多选，多选最大个数
 *		notip			不要提示
 *		layout			是否竖向排列
 *		vessel			生成到指定ID的元素里面
 * 回调函数
 *		定义方法：form.elements[name]._callback = function(xbox, mode){};
 *			注意：请确保 form.elements[name] 已经存在了（使用Ajax参数的时候会延时，请加定时器判断）
 */
_08cms.fields.linkage = function(name, data, value, mode, notip, layout, vessel, title){//mode可以选几个    title:'品牌,厂商,车型'
	var mbox = [], B = [], Z = [], X = arguments, lnk = {0:{B:B,X:0}}, argv = X[0], done, last, xbox, box, dov, i, l, x,addstr;
	if(typeof argv == 'object'){
		for(x in argv)eval(x + '=argv.' + x);
	}else{
		i = 0;
		argv = '';
		box  = X.callee.toString().match(/\((.+?)\)/)[1].split(/,\s*/);
		while(x = box[i++])argv += ',' + x + ':' + x;
		eval('argv={' + argv.slice(1) + '}');
	}
	function showInfo(){
		var id = name + '_' + xbox.count + '_' + (new Date).getTime();
		box = document.createElement('INPUT');
		box.type = 'checkbox';
		box.id = id;
		// for 按字母排序 (去掉字母)
		var str_text = xbox.text;
		if(str_text.substr(1,1)==' ') str_text = str_text.substr(2,str_text.length-2);
		box.title = str_text;
		box.value = xbox.value;
		box.onclick = function(){
			delete xbox.vals[this.value];
			xbox.count--;
			makeResult();
			dov.removeChild(this.T);
			dov.removeChild(this.L);
			dov.removeChild(this);
		};
		dov.appendChild(box);
		box.checked = true;
		box.L = document.createElement('LABEL');
		box.L.setAttribute('for', box.L.htmlFor = id);
		box.L.title = xbox.text;
		box.L.appendChild(document.createTextNode(str_text));
		dov.appendChild(box.L);
		dov.appendChild(box.T = layout ? document.createElement('BR') : document.createTextNode(' '));
		xbox.vals[xbox.value] = 1;
		xbox.count++;
		makeResult();
	}
	function setItem(B, box){
		var c = value.pop(), x = box.options.length = 1, z = 0;
		for(i = 0, l = B.length; i < l; i++){
			if(c == B[i].A[0])z = i + 1;
			box.options[x] = new Option(B[i].A[2], B[i].A[3] ? '' : B[i].A[0]);
			box.options[x++].B = B[i].B;
		}
		box.selectedIndex = z;
		z && box.onchange();
	}
	function makeResult(){
		var z = [];
		for(x in xbox.vals)z.push(x);
		xbox.box.value = z.length ? ',' + z.join(',') + ',' : '';
	}
	function guid(){
		return '__' + (new Date).getTime() + Math.random().toString().substr(2);
	}
	if(!vessel){
		vessel = guid();
		document.write('<span id="'+vessel+'"></span>');
	}
	if(typeof vessel == 'string')argv.vessel = vessel = document.getElementById(vessel);
	if(!vessel)return;
	if(vessel.xbox){
		xbox = vessel.xbox;
	}else{
		vessel.xbox = xbox = {box : box = document.createElement('INPUT'), vals : {}, count : 1};
		box.type = 'hidden';
		box.name = box.id = name;
		if(argv.callback)box._callback = argv.callback;
		vessel.appendChild(box);
	}
	xbox.select = mbox;
	if(typeof data == 'string'){
	    var _varname = guid();
	   	var __x = document.createElement('SCRIPT');
		__x.type = 'text/javascript';
		data = data.replace('action=','ajax=').replace('action/','ajax/'); 
        __x.src = CMS_URL + uri2MVC(data + (data.substr(data.length - 1) == '/' ? '' : '/') + 'varname=' + _varname);
        if(__x.readyState){//这里有ie9出现的bug,暂时这样解决 (by louis)   
            __x.onreadystatechange = function() {                
                 if(this.readyState == 'loaded' || this.readyState=='complete') {
                        argv.data = window[_varname];
                        X.callee.call(this, argv);
                }
            }
        }else{
             __x.onload = function() {                
    				  argv.data = window[_varname];
    				  X.callee.call(this, argv);                 
            }          
        }          
		document.getElementsByTagName('HEAD')[0].appendChild(__x);
		return xbox.box;
	}
//format data and find default list
	X = 0;
	for(i = 0, l = data.length; i < l; i++){
		if(x = data[i])
			if(x[1] in lnk)
				lnk[x[1]].B.push(lnk[x[0]] = {A:x,B:[],X:lnk[x[1]].X+1});
	}~
	function(B){
		for(var i = 0, l = B.length; i < l; i++){
			B[i].B.length && arguments.callee(B[i].B);
			if(B[i].A[3] && !B[i].B.length){
				delete lnk[B[i].A[0]];
				B.splice(i--, 1);
				l--;
			}
		}
	}(B);
	for(i in lnk)if(X < lnk[i].X)X = lnk[i].X;
	if(!X)return;
	addstr = title ? title.split(',') : [];
	for(i = 0; i < X; i++){
		vessel.appendChild(mbox[i] = document.createElement('SELECT'));
		vessel.appendChild(layout ? document.createElement('BR') : document.createTextNode(' '));
		mbox[i].disabled = true;
        mbox[i].options[0] = new Option(i ? (addstr[i] ? addstr[i] : '下级') : (addstr[i] ? addstr[i] :  '请选择'),'');
		//mbox[i].options[0] = new Option(i ? '下级' : (title ? title : '请选择'), '');
		mbox[i].onchange = function(x){
			return function(){
				i = mbox[x].options[mbox[x].selectedIndex];
				xbox.offset = i.value || x == 0 ? x : (x - 1);
				xbox.value = i.value || x == 0 ? i.value : mbox[x - 1].options[mbox[x - 1].selectedIndex].value;
				xbox.text = i.value ? i.text : !x == 0 ? mbox[x - 1].options[mbox[x - 1].selectedIndex].text : '';
				if(!mode){
					if(empty(xbox.box.value = xbox.value) && (x != 0 || mbox[x].selectedIndex))
						xbox.alert.lastChild || xbox.alert.appendChild(document.createTextNode('您选中的项目还需要下一级，请继续选择...'));
					else
						xbox.alert.lastChild && xbox.alert.removeChild(xbox.alert.lastChild);
				}
				if(box = mbox[x+1]){
					B = i.B;
					for(i = x+1; i < mbox.length; i++){
						mbox[i].disabled = i > x+1 || !this.selectedIndex || !B || !B.length;
						mbox[i].options.length = 1;
						mbox[i].selectedIndex = 0;
					}
					if(B && B.length)setItem(B, box);
				}
				done ? xbox.box._callback && xbox.box._callback(xbox, mode) : last = xbox.box;
			}
		}(i);
	}
	if(mode){
		box = document.createElement('INPUT');
		box.type = 'button';
		box.value = '加入选择';
		box.onclick = function(){
			if(xbox.count > mode)return alert('超出数量限制，最多选择 ' + mode + ' 条');
			if(!xbox.text)xbox.value = '';
			if(!xbox.value)return alert('您选中的项目还需要下一级，请继续选择...');
			if(xbox.value in xbox.vals)
				return alert('选择的项目已存在');
			showInfo();
			xbox.box._callback && xbox.box._callback(xbox);
		};
		vessel.appendChild(box);
		vessel.appendChild(dov = document.createElement('DIV'));
	}
	if(!empty(value)){
		if(mode){
			x = value.toString().split(',');
			value = [];
			for(i = 0, l = x.length; i < l; i++)if(!empty(x[i]) && x[i] in lnk)value.push(x[i]);
			for(i = 0, l = value.length; i < l; i++){
				xbox.value = value[i];
				xbox.text = lnk[value[i]].A[2];
				showInfo();
			}
			value = value.length ? [x = value[l-1]] : [];
		}else{
			value = [x = value];
		}
		if(x in lnk){
			while(x = lnk[x].A[1])value.push(x);
		}else{
			value = [];
		}
	}else{
		value = [];
	}
	if(!mode && !notip){
		xbox.alert = document.createElement('DIV');
		xbox.alert.style.color = 'red';
		vessel.appendChild(xbox.alert);
	}else xbox.alert = {};
	box = mbox[0];
	box.disabled = false;
	setItem(B, box);
	done = true;
	last && last._callback && last._callback();
	return xbox.box;
}

_08cms.fields.texts = function(name, fields, def, limit){
	var i, k, id, dox, doxid, btn, fit, cel, row, count = 1, index = 0;
	function add(e, def){
		if(limit && count >= limit){
			def || alert('已达到最大项数限制。');
			return;
		}
		count++;
		index++;

		// Clone row
		row = row.cloneNode(true);
		fit = row.getElementsByTagName('INPUT');
		for(k = 0; k < fit.length; k++){
			fit[k].name  = fit[k].name.replace(/\d+(\]\[\d+\])$/, index + '$1');
			fit[k].value = def ? htmlEncode(def[k]) : '';
		}

		dox.appendChild(row);
		row.lastChild.lastChild.onclick = del;
		texts_chknull();
	}

	// 解决原先有选项,删除所有选项提交后,原有选项还在(删除不起作用)
	function texts_chknull(){
		//alert(count);
		var disflag = count>0 ? true : false; //.disabled=true;
		document.getElementById('null_hid_'+doxid+'').disabled=disflag;
	}

	function del(){
		var row = this;
		if(!confirm('确定要删除选定的项目吗？'))return;
		count--;
		while(row && row.tagName != 'TR')row = row.parentNode;
		if(row)row.parentNode.removeChild(row);
		texts_chknull();
	}

	function guid(){
		return '__' + (new Date).getTime() + Math.random().toString().substr(2);
	}

	function htmlEncode(txt){
		return (txt || '').toString().replace(/[&"<>]/g, htmlReplace);
	}

	function htmlReplace(c){
		return c == '&' ? '&amp;' : c == '"' ? '&quot;' : c == '<' ? '&lt;' : '&gt;';
	}

	doxid = guid();
	document.write('<input id="null_hid_'+doxid+'" name="'+name+'" hidden="" value="" disabled><table id="'+doxid+'" class="textArray"></table>');
	dox = document.getElementById(doxid);

	//Create an original line
	row = dox.insertRow(0);
	fields = fields.split('|');
	for(i = 0; i < fields.length; i++){
		cel = row.insertCell(i);
		cel.className = 'td' + i;
		cel.innerHTML = '<label class="label' + i + '">' + fields[i] + ' <input type="text" name="'
					  + name + '[' + index + '][' + i + ']" class="field' + i + '"'
					  + (def && def[0] && def[0][i] ? ' value="' + htmlEncode(def[0][i]) + '"' : '') + '/></lable>';
	}

	//Only one line without any button
	if(limit != 1){
		//Creating the delete button
		cel = row.insertCell(i);
		cel.className = 'td' + i;
		btn = document.createElement('A');
		btn.href = 'javascript://';
		btn.onclick = del;
		btn.appendChild(document.createTextNode('删除'));
		cel.appendChild(btn);

		//Creating the Add button
		itm = dox.createTFoot();
		itm.className = 'foot';
		itm = itm.insertRow(0);
		itm = itm.insertCell(0);
		itm.colSpan = i + 1;
		btn = document.createElement('INPUT');
		btn.type = 'button';
		btn.value = '添加';
		btn.className = 'button';
		btn.onclick = add;
		itm.appendChild(btn);

		//Compatible IE table
		dox = row.parentNode;
	}

	//Adding the old values
	if(def){
		for(i = 1; def[i]; i++)add(null, def[i]);
	}
}