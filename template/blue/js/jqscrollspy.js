/**
* author : ahuing
* date   : 2015-8-7
* name   : jqscrollspy v1.0
* modify : 2015-8-12 15:17:32
 */
!function(a){function c(c){return this.each(function(){var d=a(this),e=d.data("jqScrollspy"),f="object"==typeof c&&c;e||(d.data("jqScrollspy",e=new b(this,f)),e.init())})}var d,b=function(c,d){this.o=a.extend({},b.defaults,d),this.$cell=a(c).find(this.o.obj)};b.defaults={offset:10,obj:"a"},b.prototype={init:function(){var b=this,c=a(window),d=c.height(),e=a("body").height(),f=b.$cell.eq(0).hasClass("act"),g=function(){var h,a=c.scrollTop()+b.o.offset,g=f?0:void 0;if(e-d>a)for(h=0;h<b.aTop.length;h++)a>=b.aTop[h]&&b.aTop[h]>0&&(g=h);else g=-1;b.$cell.removeClass("act").eq(g).addClass("act")};b.aTop=[],b.$cell.each(function(c,d){var e=a(a(d).attr("href"));b.aTop.push(e.length?e.offset().top:null)}).on("click",function(){var c=b.aTop[b.$cell.index(this)];return null!=c&&a("body,html").animate({scrollTop:c-b.o.offset}),!1}),b.aTop.length<2||(g(),c.on("scroll",g))}},d=a.fn.jqScrollspy,a.fn.jqScrollspy=c,a.fn.jqScrollspy.Constructor=b,a.fn.jqScrollspy.noConflict=function(){return a.fn.jqScrollspy=d,this},a(window).on("load",function(){a(".jqScrollspy").each(function(){var b=a(this);c.call(b,b.data())})})}(jQuery);