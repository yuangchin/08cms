function compute(){
	if(document.getElementById("totalPrice").value==""){
		alert("请输入总金额");
		return false;
	}
	if(document.getElementById("buildArea").value==""){
		alert("请输入建筑面积");
		return false;
	}
	if(document.getElementById("evaluationPrice").value==""){
		alert("房屋评估金额");
		return false;
	}
		
	employeeBuyer();
	employeeSaler();
	loanDan();
	loanPing();
	taxYin();
	taxGet();

	taxQI();
	taxArea();
	feeAA();
	feeBB();
	areaTurn();
	cost();
	caTotal();
}
function computeLoan(form){
	sv('daikuan_total000',v('buyerPay'));
	sv('total_sy',v('buyerPay'));
	sv('total_gjj',0);
	ext_total(form);
}
//总价修改后相应的改动内容
function countPrice(){
	countAverage(0);
	employeeBuyer();
	employeeSaler();
	loanDan();
	loanPing();
	taxYin();
	taxGet();

	taxQI();
	taxArea();
	c();
	
}

//房屋 类别 更改后相应的改动内容
function onChangeHouseType(type){
	
	//taxArea2(type);
	
	//taxGetType(type);
	taxGet();
	taxTradeType(type);
	cost();
	//compute()
	
}

//房屋评估金额改动后相应的改动内容
function onChangeAboutPrice(price){
	
	loanDan();

	loanPing();
	taxYin()
	taxGet();
	taxQI();
	taxTrade();
	//compute()
}



//面积更新后要计算的内容
function areaChange(area){
	countAverage();	
	cost();
	c();
}

//买方所需房贷更新后要计算的内容
function buyerLoanChange(){
	if(dev('buyerPay')>dev('totalPrice')){sv('buyerPay',dev('totalPrice'));return true;}
	
	sv('daikuan_total000',v("buyerPay"));
	daikuanYin();
	loanDan();
	feeCC();
	taxTrade();
	
	//compute()
}

//交易费
function cost(){
	var hType=v("houseType");
	var area=v("buildArea");
	if(hType!=4)
		sv("jiao_yi",(area*6).toFixed(2));
	else 
		sv("jiao_yi",(area*12).toFixed(2));
		
	c();
}

//贷款印花税
function daikuanYin(price){
	daikuanYin();
	c();
}

function daikuanYin(){
	var price=dev("daikuan_total000");
	//if(price>=1000)
	price = price*0.00005;
	document.getElementById("daikuan_yinhua").value=price.toFixed(2);
}


//土地出让金
function areaTurn(area){
areaTurn();
}

function areaTurn(){
	var area=document.getElementById("buildArea").value;
	var buildarea = document.getElementById("buildArea").value;
	var ckdj=v("ckdj");
	var tddj=document.getElementById("tddj").value;
	var price = (tddj*buildarea)+(ckdj*area);	
	document.getElementById("area_turn").value=price;
	c();
}

//个人所得税

function taxGet(){
/*	
5年内普通住宅(<144平米)：1%(全额)或20%(差额)；
5年外普通住宅(<144平米)：1%(全额)或20%(差额),唯一住房免征
5年内非普通住宅(>144平米)：1.5%(全额)或20%(差额)；
5年外非普通住宅(>144平米)：1.5%(全额)或20%(差额),唯一住房免征。
商用房：1%(全额)或20%(差额)；
2009年12月31日前，面积小于144普通住宅个税返还32％

<option value="1" selected>普通住宅(面积小于90平方)</option>
          <option value="2" >普通住宅(面积大于90平方，小于144平方)</option>
          <option value="3">非普通住宅(面积大于144平方)</option>
          <option value="4">商用房（非住宅）</option>
          <option value="5">其它</option>
		  
		  <option value="1" selected>2年以下</option>
          <option value="2" >2年－5年</option>
          <option value="3">5年以上</option>
*/
	var totalPrice = getPrice("totalPrice");
	if(dev('evaluationPrice')>totalPrice)totalPrice=dev('evaluationPrice');
	var saler_price = getPrice("saler_price");
	var price="";
	//var p=v("grsdcq");
	var f=v("wyzf");
	
	var year=v("houseYear");
	var type=getSelect("houseType");
	if(year<=2 && type<=2){//5年内普通住宅(<144平米)：1%(全额)或20%(差额)；
			price=totalPrice*0.01;
	}else if(year>=3 && type<=2){//5年外普通住宅(<144平米)：1%(全额)或20%(差额),唯一住房免征
			price=totalPrice*0.01;
		if(f==1)price=0;
	}else if(year<=2 && type==3){//5年内非普通住宅(>144平米)：1.5%(全额)或20%(差额)；
			price=totalPrice*0.015;
	}else if(year>=3 && type==3){//5年外非普通住宅(>144平米)：1.5%(全额)或20%(差额),唯一住房免征。
			price=totalPrice*0.015;
		if(f==1)price=0;
	}else if(type==4){//商用房：1%(全额)或20%(差额)；
			price=totalPrice*0.01;
	}
	//if(type<=2)//2009年12月31日前，面积小于144普通住宅个税返还32％
		//price=price*0.68
	sv("tax_get",price.toFixed(2));
	
	c();
}

function taxGetType(type){
 taxGet();
}

//计算均价
function countAverage(buildArea){

	var totalPrice = getPrice("totalPrice");
	
	var buildArea2 = document.getElementById("buildArea").value;
	
	document.getElementById("averagePrice").value=(totalPrice/buildArea2).toFixed(2);
	c();
		
}

//计算定金余额
function payforDeposit(){
	
	var totalPrice = getPrice("totalPrice");
	
	var ag_paySum = getPrice("ag_paySum");
	
	document.getElementById("ag_payBalance").value=(totalPrice-ag_paySum).toFixed(2);
		
	payforFirst();
}

//计算首付后余额
function payforFirst(){
	
	var totalPrice = getPrice("totalPrice");
	
	var payfor_first = getPrice("payfor_first");
	
	var ag_payBalance = getPrice("ag_payBalance");
	
	if(ag_payBalance.length>0 && payfor_first.length>0){
		
		document.getElementById("surplus_first").value=(ag_payBalance-payfor_first).toFixed(2);
	
	}else if(totalPrice.length>0 && payfor_first.length>0){
		
		document.getElementById("surplus_first").value=(totalPrice-payfor_first).toFixed(2);
	} 
}

//支付余额
function payfor(id) {
	
	var totalPrice = getPrice("totalPrice");

	var payfor_first = getPrice("payfor_first");
	
	var ag_paySum = getPrice("ag_paySum");
	
	var price = totalPrice-payfor_first-ag_paySum;
	
	if(id>0){
		for (i=1;i<=id;i++) {    
			var payfor = document.getElementById("payfor"+i).value;
			
			payfor = payfor.replaceAll(",","");
			
			price = price-payfor;

		}
	}
	
	document.getElementById("surplus"+id).value=price.toFixed(2);
}



//计算买家佣金
function employeeBuyer(){
	
	var size = getSelect("buyerProportion");
	
	var totalPrice = getPrice("totalPrice");
	
	var price = totalPrice*size;
		
	document.getElementById("buyerBrokerage").value=price.toFixed(2);
	c();
	
}

//计算卖家佣金
function employeeSaler(){
	
	var size = getSelect("sellerProportion");
	
	var totalPrice = getPrice("totalPrice");
	
	var price = totalPrice*size;
		
	document.getElementById("sellerBrokerage").value=price.toFixed(2);
	c();
	
}

//贷款担保费
function loanDan(){
	
	/*2010年政策，删除该项
	var size = getSelect("guaranteeProportion");
	
	var buyerPayPrice = getPrice("buyerPay");
	
	var price = buyerPayPrice*size;
		
	document.getElementById("loanGuarantee").value=price.toFixed(2);
	
	c();*/
	
}

//贷款评估费
function loanPing(){
	
	var size = getSelect("evaluationProportion");
	
	var evaluationPrice = getPrice("evaluationPrice");
	
	var price = evaluationPrice*size;
		
	document.getElementById("loanEvaluation").value=price.toFixed(2);
	
	c();
	
}

//计算契税
function taxQI(){
/*
普通住宅(<90平米，首次购房)：1%；
普通住宅(<90平米，非首次购房)：1.5%；
普通住宅(90－144平米)：1.5%； 
非普通住宅(>144平米)：3%；
商用房：3%。
<option value="1" selected>普通住宅(面积小于90平方)</option>
          <option value="2" >普通住宅(面积大于90平方，小于144平方)</option>
          <option value="3">非普通住宅(面积大于144平方)</option>
          <option value="4">商用房（非住宅）</option>
          <option value="5">其它</option>
		  
		  <option value="1" selected>2年以下</option>
          <option value="2" >2年－5年</option>
          <option value="3">5年以上</option>
*/
	
	var totalPrice = getPrice("totalPrice");
	if(dev('evaluationPrice')>totalPrice)totalPrice=dev('evaluationPrice');
	var type=getSelect("houseType");
	var year=getSelect("houseYear");		
	var f = v("scgf");		
	var price="";
	if(type<=1){
		if(f==1)
			price=totalPrice*0.01;
		else 
			price=totalPrice*0.015;
	}else if(type==2){
		price=totalPrice*0.015;
	}else if(type==3){
		price=totalPrice*0.03;
	}else if(type==4){
		price=totalPrice*0.03;
	}
	sv("tax_qi",price.toFixed(2));
	c();
	
}

//土地增值税
function taxArea(){
	
	var totalPrice = getPrice("totalPrice");
	
	var tdzjbl = document.getElementById("tdzjbl").value;

	var price = totalPrice*tdzjbl;

	document.getElementById("tax_area").value=price.toFixed(2);
	c();
	
}
//所有权工本费
function feeAA(){
	var price= document.getElementById("syqgbj").value*10;	
	document.getElementById("feeA").value=price.toFixed(2);
	c();
}
//土地证工本费
function feeBB(){
	var price= document.getElementById("tdzsyj").value*20;	
	document.getElementById("feeB").value=price.toFixed(2);
	c();
}

//他项权证费用（有贷款）
function feeCC(){
	if(getPrice("buyerPay")>0)
		sv("feeC",80);
	else
		sv("feeC",0);
	
	c();
}
//营业税
function taxTrade(){
/*
2年内普通住宅(<144平米)：5.6%(差额)；
2年外普通住宅(<144平米)：无；
2年内非普通住宅(>144平米)：5.6%(全额)；
2年外非普通住宅(>144平米)：5.6%(差额)；
商用房：5.6%(差额)。
2009年12月31日前，面积小于144㎡普通住宅营业税返还80％。

<option value="1" selected>普通住宅(面积小于90平方)</option>
          <option value="2" >普通住宅(面积大于90平方，小于144平方)</option>
          <option value="3">非普通住宅(面积大于144平方)</option>
          <option value="4">商用房（非住宅）</option>
          <option value="5">其它</option>
		  
          <option value="2" >5年及以下</option>
          <option value="3">5年以上</option>
*/

	var totalPrice = getPrice("totalPrice");	
	if(dev('evaluationPrice')>totalPrice)totalPrice=dev('evaluationPrice');
	var saler_price = getPrice("saler_price");	
	var type = getSelect("houseType");
	var year=getSelect("houseYear");	
	var price = "";
	if(year<=2 && type<=2)//5年内普通住宅(<144平米)：5.6%(差额)；
		price=(totalPrice-saler_price)*0.056;
	else if(year>2 && type<=2)//5年外普通住宅(<144平米)：无；
		price=0;
	else if(year<=2 && type==3)//5年内非普通住宅(>144平米)：5.6%(全额)；
		price=totalPrice*0.056;
	else if(year>2 && type==3)//5年外非普通住宅(>144平米)：5.6%(差额)；
		price=(totalPrice-saler_price)*0.056;
	else if(type==4)//商用房：5.6%(差额)。
		price=(totalPrice-saler_price)*0.056;
	
	
	//if(type<=2)//2009年12月31日前，面积小于144㎡普通住宅营业税返还80％。
		//price=price*0.2;	
	
	sv("tax_trade",price.toFixed(2));
	
	c();

}
//印花税
function taxYin(){
	var totalPrice = getPrice("totalPrice");
	if(dev('evaluationPrice')>totalPrice)totalPrice=dev('evaluationPrice');
	var price = totalPrice*0.001;	
	document.getElementById("tax_yin").value=price.toFixed(2);
	c();
}

function taxTradeType(type){
		taxTrade();	
}

function taxArea2(size){
	
	var totalPrice = getPrice("totalPrice");
	
	if(size==1 || size==2){

		document.getElementById("tax_area").value="0";
	}else{
		var price = totalPrice*0.01;

		document.getElementById("tax_area").value=price.toFixed(2);
	}
}

