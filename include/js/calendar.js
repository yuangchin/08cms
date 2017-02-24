var months = new Array("一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"); 
var days   = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var weeks  = new Array("日","一","二","三","四","五","六");
var today;
var pX;
var pY;

document.writeln("<div id='Calendar' style='position:absolute; z-index:1; visibility: hidden;'></div>");

function getDays(month,year){
    if(1 == month){
        return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29 : 28;
    }else{
        return days[month];
	}
}
function getToday(){
    var date  = new Date();
    this.year = date.getFullYear();
    this.month= date.getMonth();
    this.day  = date.getDate();
}
function getSelectDay(str){
    var str=str.split("-");
    
    var date  = new Date(parseFloat(str[0]),parseFloat(str[1])-1,parseFloat(str[2]));
    this.year = date.getFullYear();
    this.month= date.getMonth();
    this.day  = date.getDate();
}

function ShowDays() {
	var obj_Year =$id('Year');
	var obj_Month=$id('Month');

    var parseYear = parseInt(obj_Year.options[obj_Year.selectedIndex].value);
    var Seldate = new Date(parseYear,obj_Month.selectedIndex,1);
    var day = -1;
    var startDay = Seldate.getDay();
    var daily = 0;
    
    if((today.year == Seldate.getFullYear()) &&(today.month == Seldate.getMonth())){
        day = today.day;
	}
    var tableDay = $id('Day');
    var DaysNum  = getDays(Seldate.getMonth(),Seldate.getFullYear());
    for(var intWeek = 1;intWeek < tableDay.rows.length;intWeek++){
        for(var intDay = 0;intDay < tableDay.rows[intWeek].cells.length;intDay++){
            var cell = tableDay.rows[intWeek].cells[intDay];
            if(intDay == startDay && 0 == daily){
                daily = 1;
			}            
            if(daily > 0 && daily <= DaysNum){				
				cell.style.cssText = 'cursor:pointer;border-right:1px solid #BBBBBB; border-bottom:1px solid #BBBBBB; color:#215DC6; font-family:Verdana; font-size:12px';
				if(day==daily){
					cell.style.background='#6699CC';
					cell.style.color='#FFFFFF';
				} else if(intDay==6){
					cell.style.color='green';
				} else if(intDay==0){
					cell.style.color='red';
				}
				cell.innerHTML = daily;
                daily++;
            } else{
				cell.style.cssText = '';
                cell.innerHTML = '';
			}
        }
	}
}

function GetDate(idname,e){
    var sDate;
	var getElement = e.target || event.srcElement;
    if(getElement.tagName == "TD"){
        if(getElement.innerHTML != ""){
            sDate = $id('Year').value + "-" + $id('Month').value + "-" + getElement.innerHTML;
            $id(idname).value=sDate;
            HiddenCalendar();
        }
	}
} 

function HiddenCalendar(){
    $id('Calendar').style.visibility='hidden';
}

function ShowCalendar(idname){
    var x,y,i,intWeeks,intDays;
    var table;
    var year,month,day;
    var obj=$id(idname);
    var thisyear;
    
    thisyear=new Date();
    thisyear=thisyear.getFullYear();
    
    today = obj.value;
    if(isDate(today)){
        today = new getSelectDay(today);
	}else{
        today = new getToday();
	}
    
    x=obj.offsetLeft;
    y=obj.offsetTop;
    while(obj=obj.offsetParent){
        x+=obj.offsetLeft;
        y+=obj.offsetTop;
		if(obj.id.substr(obj.id.length-8)=='_content'){
    	    x-=obj.scrollLeft;
	        y-=obj.scrollTop;
		}
    }
	var Cal=$id('Calendar');
    with(Cal.style){
		left=x+2+'px';
    	top=y+20+'px';
		zIndex=9999;
    	visibility="visible";
	}
    
    table="<table border='0' cellspacing='0' style='border:1px solid #0066FF; background-color:#FFFFFF'>";
    table+="<tr>";
    table+="<td style='border-bottom:1px solid #0066FF; background-color:#84AACE'>";
    
    table+="<select name='Year' id='Year' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = thisyear - 60;i < (thisyear + 10);i++){ 
        table+="<option value=" + i + " " + (today.year == i ? "Selected" : "") + ">" + i + "</option>"; 
	}
	table+="</select>";

    table+="<select name='Month' id='Month' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = 0;i < months.length;i++){
        table+="<option value= " + (i + 1) + " " + (today.month == i ? "Selected" : "") + ">" + months[i] + "</option>";
	}

	table+="</select>";
    table+="</td>";
    table+="<td style='border-bottom:1px solid #0066FF; background-color:#84AACE;font-family:Verdana;font-size:16px; padding:2px 2px 0 0;color:red; cursor:pointer' align='center' title='关闭' onClick='javascript:HiddenCalendar()'>x</td>";
    table+="</tr>";
    table+="<tr><td align='center' colspan='2'>";
    table+="<table id='Day' border='0' width='100%'>";
    table+="<tr>";

    for(i = 0;i < weeks.length;i++){
        table+="<td align='center' style='font-size:12px;'>" + weeks[i] + "</td>";
	}
	table+="</tr>";

    for(intWeeks = 0;intWeeks < 6;intWeeks++){
        table+="<tr>";
        for (intDays = 0;intDays < weeks.length;intDays++){
            table+="<td onClick='GetDate(\"" + idname + "\",event)' align='center'></td>";
		}
        table+="</tr>";
    }
    table+="</table></td></tr></table>";

    Cal.innerHTML=table;
    ShowDays();
}

function isDate(dateStr){
    var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})$/;
    var matchArray = dateStr.match(datePat);
    if (matchArray == null) return false;
    var month = matchArray[3];
    var day = matchArray[5];
    var year = matchArray[1];
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if ((month==4 || month==6 || month==9 || month==11) && day==31) return false;
    if (month == 2){
        var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
        if (day > 29 || (day==29 && !isleap)) return false;
    }
    return true;
}