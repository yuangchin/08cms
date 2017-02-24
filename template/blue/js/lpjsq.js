if ($('#bs_calarea').length) {
    var $pri = $('#pri');
  $.getScript(tplurl+'js/ll.js');
  var calPrice = parseFloat($pri.html());

  function calTotalPriceInit(){
  	var optionSelected = $("#bs_calarea option:selected");
     var area = optionSelected.val();
     var totalPrice = (area*calPrice)/10000+"万元 <span class='fz14 fcg'>(均价"+calPrice+"元/m&sup2;)</span>";
     $("#cal_total").html(totalPrice);
  }
  calTotalPriceInit();
  $("#bs_calarea").change(function(){
      calTotalPriceInit();
  });

  //本息还款的月还款额(参数: 年利率/贷款总额/贷款总月份)
  function getMonthMoney1(lilv, total, month){
  	var lilv_month = lilv / 12;//月利率
  	return total * lilv_month * Math.pow(1 + lilv_month, month) / ( Math.pow(1 + lilv_month, month) -1 );
  }
  function myround(v, e){
      var t = 1;
      e = Math.round(e);
      for(; e > 0; t *= 10, e--);
      for(; e < 0; t /= 10, e++);
      return Math.round(v * t) / t;
  }
  function ShowLilvNew(month, lt){
          var indexNumSd = getArrayIndexFromYear(month, 1); // 商贷
         $("#singlelv")[0].value = myround(lilv_array[lt][1][indexNumSd] * 100, 2);
  	   ext_total(document.calc1);
  }
  function getArrayIndexFromYear(year,dkType){
     var indexNum = 0;
     if(dkType == 1){
        if(year == 1) {
           indexNum = 1;
        } else if(year > 1 && year <= 3) {
           indexNum = 3;
        } else if(year > 3 && year <= 5) {
           indexNum = 5;
        } else {
           indexNum = 10;
        }
     } else if(dkType == 2) {
        if(year > 5) {
           indexNum = 10;
        } else {
           indexNum = 5;
        }
     }
     return indexNum;
  }
  function ext_total(fmobj){
  	//var fmobj = document.calc1;
  	$("#all_total1").html('');
  	$("#month_money1").html('');
  	var years = fmobj.calyears_s.value;
  	var month = fmobj.calyears_s.value * 12;
  	var lilv = $('#singlelv')[0].value / 100;//得到利率
  	//var lilv = getlilv(fmobj.callilv_s.value, 1, fmobj.calyears_s.value);//得到利率
  	//房款总额
  	var fangkuan_total = calPrice * fmobj.calarea_s.value;
  	//贷款总额
  	var daikuan_total = fangkuan_total * (fmobj.calanjie_s.value / 10);
  	//首期付款
  	var money_first = fangkuan_total - daikuan_total;
  //2.本息还款
  	//月均还款
  	var month_money1 = getMonthMoney1(lilv, daikuan_total, month);//调用函数计算
  	//fmobj.month_money1.innerHTML = Math.round(month_money1 * 100) / 100 + "(元)";
  	$("#shoufu").html(Math.round(money_first) / 10000 + "万("+(10-fmobj.calanjie_s.value)+"成)");
    $("#dkje").html(Math.round(daikuan_total) / 10000 + "万("+(fmobj.calanjie_s.value)+"成)");
    $("#month_money1").html(Math.round(month_money1 * 100) / 100 + "元");
    //还款总额
    var all_total1 = month_money1 * month;
    var all_total1 = Math.round(all_total1) / 10000;
    all_total1 = Math.round(all_total1*100) / 100;
    $("#all_total1").html(all_total1 + "万");
  	$("#zflx").html((all_total1 - Math.round(daikuan_total) / 10000).toFixed(2) + '万');
  }
  ext_total(document.calc1);

}
