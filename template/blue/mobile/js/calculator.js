// 购房能力评估
var rg7 = document.getElementById("rg7");
var rg8 = document.getElementById("rg8");
var rg10 = document.getElementById("rg10");
var rg11 = document.getElementById("rg11");
var rg12 = document.getElementById("rg12");
var rg13 = document.getElementById("rg13");
var rg14 = document.getElementById("rg14");
var rg15 = document.getElementById("rg15");
var nlpg = document.nlpg;

rhb = new Array(440.104, 301.103, 231.7, 190.136, 163.753, 144.08, 129.379, 117.991, 108.923, 101.542, 95.425, 90.282, 85.902, 82.133, 78.861, 75.997, 73.473, 71.236, 69.241, 67.455, 65.848, 64.397, 63.082, 61.887, 60.798, 59.802, 58.890, 58.052, 57.282)
yhz = new Array(1.978, 2.9344, 3.8699, 4.7847, 5.6794, 6.5544, 7.4102, 8.2472, 9.0657, 9.8662, 10.6491, 11.4148, 12.1636, 12.8959, 13.6121, 14.3126, 14.9977, 15.6677, 16.3229, 16.9637, 17.5904, 18.2034, 18.8028, 19.389, 19.9624, 20.5231, 21.0715, 21.6078, 22.1323)

function chk01() {
    if (parseFloat(nlpg.rg01.value) < 4.7)
        alert("--您确定是" + parseFloat(nlpg.rg01.value) + "万元?--" + "\n\n" + "那么您目前尚不具备购房能力，" + "\n\n" + "建议积攒积蓄或能筹集更多的资金。")
    if (parseFloat(nlpg.rg01.value) > 10000)
        alert("您确定拥有超过一亿元的购房资金？");
}

function chk02() {
    if (parseFloat(nlpg.rg03.value) > parseFloat(nlpg.rg02.value) * 0.7) {
        alert("您预计家庭每月可用于购房支出已超过家庭月收入的70%，" + "\n\n" + "是否确定不会影响您的正常生活消费？" + "\n\n" + "建议在40%（" + parseFloat(nlpg.rg02.value) * 0.4 + "元）左右")
    }
}

function chk03() {
    if (nlpg.rg01.value == "")
        alert("请填写现可用于购房的资金")
    else
    if (nlpg.rg02.value == "")
        alert("请填写现家庭月收入")
    else
    if (nlpg.rg03.value == "")
        alert("请填写预计家庭每月可用于购房支出")
    else
    if (nlpg.rg06.value == "")
        alert("请填写您计划购买房屋的面积")
    else
        return chk04()
}

function chk04() {
    js00 = parseFloat(nlpg.rg01.value) * 10000
    js01 = parseFloat(nlpg.rg03.value)
    js02 = Math.round(js01 / rhb[parseInt(nlpg.rg04.options[nlpg.rg04.selectedIndex].value) / 12 - 2]) * 10000
    js03 = parseFloat(nlpg.rg06.value)

    if (js02 > js00 * 3.2)
        js02 = js00 * 3.2
    rg07.innerHTML = Math.round((js02 + 0.8 * js00) * 100) / 100
    rg08.innerHTML = Math.round(parseFloat(rg07.innerHTML) / js03 * 100) / 100
    if (js03 < 120)
        rg10.innerHTML = Math.round(parseFloat(rg07.innerHTML) * 2) / 100
    else
        rg10.innerHTML = Math.round((parseFloat(rg07.innerHTML) - parseFloat(rg08.innerHTML) * 120) * 4 + parseFloat(rg08.innerHTML) * 120 * 2) / 100
    rg11.innerHTML = Math.round(parseFloat(rg07.innerHTML) * 2) / 100
    rg12.innerHTML = Math.round(parseFloat(rg07.innerHTML) * 20) / 100
    rg13.innerHTML = Math.round(Math.round(parseFloat(rg07.innerHTML) * 0.05) / 100 * yhz[parseInt(nlpg.rg04.options[nlpg.rg04.selectedIndex].value) / 12 - 2] * 100) / 100
    rg14.innerHTML = Math.round(parseFloat(rg07.innerHTML) * 0.3) / 100
    rg15.innerHTML = "200~500"
    return true;
}
$("#nlpg").on("submit",function(){
    chk03();
    //if(chk03()===true) $('html,body').scrollTop($("#result").offset().top)
    return false;
})

// 贷款计算器

var fangkuan_total1=document.getElementById("fangkuan_total1");
var daikuan_total1=document.getElementById("daikuan_total1");
var _all_total1=document.getElementById("all_total1");
var accrual1=document.getElementById("accrual1");
var money_first1=document.getElementById("money_first1");
var month1=document.getElementById("month1");
var _month_money1=document.getElementById("month_money1");
var daikuan_total2=document.getElementById("daikuan_total2");
var _all_total2=document.getElementById("all_total2");
var fangkuan_total2=document.getElementById("fangkuan_total2");
var accrual2=document.getElementById("accrual2");
var money_first2=document.getElementById("money_first2");
var month2=document.getElementById("month2");
var _month_money2=document.getElementById("month_money2");
$("#dkjs").on("submit",function(){
    ext_total(this);
    // if(ext_total(document.calc2)===true) $('html,body').scrollTop($("#result").offset().top)
    // $("#month_money2").height($("#month_money2")[0].scrollHeight+10)
    return false;
})
$("#hdfs1,#hdfs2").on("click",function(){
    if($("#hdfs1").prop("checked"))
        $("#divr1").show().next().hide()
    else
        $("#divr1").hide().next().show()
})
$("#calc2_radio1,#calc2_radio2").on("click",function(){
    if($("#calc2_radio1").prop("checked")){
        $(".dk2").hide()
        $(".dk1").show()
    }else{
        $(".dk1").hide()
        $(".dk2").show()
    }
})
$("#dktype").on("change",function(){
    if (this.value==3){
        $(".zh1").hide()
        $(".zh2").show()
    }else{
        $(".zh2").hide()
        $(".zh1").show()
        $("#calc2_radio1").trigger("click")
    }
})
// 公积金还贷
var l1_5 = 0.0405;
var l6_30 = 0.0459;
var ze22=document.getElementById("ze22");
var lx2=document.getElementById("lx2");
var sfk2=document.getElementById("sfk2");
var lx3=document.getElementById("lx3");
var sfksan=document.getElementById("sfksan");
var lx4=document.getElementById("lx4");
var lx5=document.getElementById("lx5");
var lx6=document.getElementById("lx6");

$("#gjjhd").on("submit",function(){
    gjjloan2(this)
    return false;
})
// 提前还款

var ykhke=document.getElementById("ykhke");
var gyyihke=document.getElementById("gyyihke");
var yzhhkq=document.getElementById("yzhhkq");
var xyqyhke=document.getElementById("xyqyhke");
var yhkze=document.getElementById("yhkze");
var jslxzc=document.getElementById("jslxzc");
var yhlxe=document.getElementById("yhlxe");
var xdzhhkq=document.getElementById("xdzhhkq");
var jsjgts=document.getElementById("jsjgts");
$("#tqhd").on("submit",function(){
    play(this);
    return false;
})
$("#tqhkfs1,#tqhkfs").on("click",function(){
    if($("#tqhkfs1").prop("checked"))
        $(".clfs").show()
    else
        $(".clfs").hide()
})
// 税费

var _fkz3 = document.getElementById("fkz3");
var _q = document.getElementById("q");
var _yh = document.getElementById("yh");
var _gzh = document.getElementById("gzh");
var _fw = document.getElementById("fw");
var _wt = document.getElementById("wt");

function runjs3(obj) {
    var dj3 = parseFloat(obj.dj3.value);
    var mj3 = parseFloat(obj.mj3.value);
    var fkz3 = dj3 * mj3;
    var yh = fkz3 * 0.0005;
    if (dj3 <= 9432) {
        var q = fkz3 * 0.015;
    } else if (dj3 > 9432) {
        var q = fkz3 * 0.03;
    }
    if (mj3 <= 120) {
        var fw = 500;
    } else if (120 < mj3 && mj3 <= 5000) {
      var fw = 1500;
    }
    if (mj3 > 5000) {
        var fw = 5000;
    }
    var gzh = fkz3 * 0.003;
    _yh.innerHTML = Math.round(yh * 100, 5) / 100;
    _fkz3.innerHTML = Math.round(fkz3 * 100, 5) / 100;
    _gzh.innerHTML = Math.round(gzh * 100, 5) / 100;
    _wt.innerHTML = Math.round(gzh * 100, 5) / 100;
    _fw.innerHTML = Math.round(fw * 100, 5) / 100;
    _q.innerHTML = Math.round(q * 100, 5) / 100;
    return true;
}
$("#sfjs").on("submit",function(){
    runjs3(this);
    return false;
})