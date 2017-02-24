function ex_form_check(form){
	var val = form.elements['extractnew[total]'].value, v = parseFloat(val), x;
	if(isNaN(v) || v < extract_mincount){
		alert(extract_langs.min_tip.replace('%v', extract_mincount));
		return false;
	}else{
		x = Math.round(v * ex_val_dis.ex) / 100;
		return confirm(extract_langs.confirm.replace('%i', val).replace('%v', x.toString(10)));
	}
}

function ex_item_check(item){
	var v, x, X = item.value, T, e = /^(\d\d*\.?\d{0,2})/,
		a = item.name.substring(11, item.name.length - 1),
		b = $id('currency_tip_' + a),
		z = a != 'total',
		k = z ? a : 'ex';
		if(ex_val_cnt._$_ex === undefined)ex_val_cnt._$_ex = ex_val_cnt.ex;
	T = setInterval(
		function(){
			if(item.value == X)return;
			if(X = e.exec(item.value)){
				X = X[1];
			}else{
				X = item.value = b.innerHTML = '';
				if(z){
					ex_val_cvt[k] = 0;
					ex_con_cvt();
				}
				return;
			}
			v = parseFloat(X);
			if(v > ex_val_cnt[k])v = ex_val_cnt[k];
			x = Math.round(v * ex_val_dis[k]) / 100;
			if(z){
				ex_val_cvt[k] = x;
				ex_con_cvt();
			}
			if(v == ex_val_cnt[k] && v.toString(10) != X)X = v.toString(10);
			if(X != item.value)item.value = X;
			b.innerHTML = extract_langs[z ? 'other_tip' : 'total_tip'].replace('%v', x);
		},
		10
	);
	item.onblur = function(){
		clearInterval(T)
	}
}

function ex_con_cvt(){
	ex_val_cnt.ex = ex_val_cnt._$_ex;
	for(var i in ex_val_cvt)ex_val_cnt.ex += ex_val_cvt[i];
	ex_val_cnt.ex = Math.round(ex_val_cnt.ex * 100) / 100;
	$id('extract_total').innerHTML = ex_val_cnt.ex != ex_val_cnt._$_ex ? extract_langs.total.replace('%v', ex_val_cnt.ex) : '';
	
	var z = ex_val_cnt.ex < extract_mincount;
//	$id('extract_input').style.display = z ? 'none' : '';
	$id('extract_button').style.display = z ? 'none' : '';
	$id('extract_message').style.display = z ? '' : 'none';
}