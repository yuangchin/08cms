rhb=new Array(440.104,301.103,231.7,190.136,163.753,144.08,129.379,117.991,108.923,101.542,95.425,90.282,85.902,82.133,78.861,75.997,73.473,71.236,69.241,67.455,65.848,64.397,63.082,61.887,60.798,59.802,58.890,58.052,57.282)
yhz=new Array(1.978,2.9344,3.8699,4.7847,5.6794,6.5544,7.4102,8.2472,9.0657,9.8662,10.6491,11.4148,12.1636,12.8959,13.6121,14.3126,14.9977,15.6677,16.3229,16.9637,17.5904,18.2034,18.8028,19.389,19.9624,20.5231,21.0715,21.6078,22.1323)
function chk01()
{
if (parseFloat(document.myform.rg01.value)<4.7)
	alert("--您确定是"+parseFloat(document.myform.rg01.value)+"万元?--"+"\n\n"+"那么您目前尚不具备购房能力，"+"\n\n"+"建议积攒积蓄或能筹集更多的资金。")
if (parseFloat(document.myform.rg01.value)>10000)
	alert("您确定拥有超过一亿元的购房资金？");

}
function chk02()
{
if (parseFloat(document.myform.rg03.value)>parseFloat(document.myform.rg02.value)*0.7)
	{
	alert("您预计家庭每月可用于购房支出已超过家庭月收入的70%，"+"\n\n"+"是否确定不会影响您的正常生活消费？"+"\n\n"+"建议在40%（"+parseFloat(document.myform.rg02.value)*0.4+"元）左右")
	}
}
function chk03()
{
if (document.myform.rg01.value=="")
	alert("请填写现可用于购房的资金")
else
	if (document.myform.rg02.value=="")
			alert("请填写现家庭月收入")
	else	
		if (document.myform.rg03.value=="")
			alert("请填写预计家庭每月可用于购房支出")
	else	
		if (document.myform.rg06.value=="")
			alert("请填写您计划购买房屋的面积")
	else
		chk04()

}
function chk04()
{
js00=parseFloat(document.myform.rg01.value)*10000
js01=parseFloat(document.myform.rg03.value)
js02=Math.round(js01/rhb[parseInt(document.myform.rg04.options[document.myform.rg04.selectedIndex].value)/12-2])*10000
js03=parseFloat(document.myform.rg06.value)

if (js02>js00*3.2)
	js02=js00*3.2
document.myform.rg07.value=Math.round((js02+0.8*js00)*100)/100
document.myform.rg08.value=Math.round(parseFloat(document.myform.rg07.value)/js03*100)/100

if(js03<90){
	document.myform.rg10.value=Math.round(parseFloat(document.myform.rg07.value)*1)/100
}else if(js03>=90 && js03<144){
	document.myform.rg10.value=Math.round(parseFloat(document.myform.rg07.value)*1.5)/100
}else{
	document.myform.rg10.value=Math.round(parseFloat(document.myform.rg07.value)*3)/100
}


document.myform.rg11.value=Math.round(parseFloat(document.myform.rg06.value)*70)
document.myform.rg111.value=Math.round(parseFloat(document.myform.rg06.value)*50)

document.myform.rg12.value=Math.round(parseFloat(document.myform.rg07.value)*30)/100
document.myform.rg13.value=Math.round(Math.round(parseFloat(document.myform.rg07.value)*0.05)/100*yhz[parseInt(document.myform.rg04.options[document.myform.rg04.selectedIndex].value)/12-2]*100)/100
document.myform.rg14.value=Math.round(parseFloat(document.myform.rg07.value)*0.3)/100
document.myform.rg15.value="200~500"
}