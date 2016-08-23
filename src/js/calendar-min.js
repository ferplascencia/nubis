"use strict";Date.prototype.getWeek=function(){var t=new Date(this.getFullYear(),0,1);return Math.ceil(((this.getTime()-t.getTime())/864e5+t.getDay()+1)/7)},Date.prototype.getMonthFormatted=function(){var t=this.getMonth()+1;return 10>t?"0"+t:t},Date.prototype.getDateFormatted=function(){var t=this.getDate();return 10>t?"0"+t:t},String.prototype.format||(String.prototype.format=function(){var t=arguments;return this.replace(/{(\d+)}/g,function(e,a){return"undefined"!=typeof t[a]?t[a]:e})}),function(t){function e(t,e){var a,o,n;n=t,a=t.indexOf("?")<0?"?":"&";for(o in e)n+=a+o+"="+encodeURIComponent(e[o]),a="&";return n}function a(e,a){var o=null!=e.options[a]?e.options[a]:null,n=null!=e.locale[a]?e.locale[a]:null;if("holidays"==a&&e.options.merge_holidays){var s={};return t.extend(!0,s,n?n:d.holidays),o&&t.extend(!0,s,o),s}return null!=o?o:null!=n?n:d[a]}function o(e,s){var i=[],l=a(e,"holidays");for(var d in l)i.push(d+":"+l[d]);if(i.push(s),i=i.join("|"),i in o.cache)return o.cache[i];var h=[];return t.each(l,function(e,a){var o=null,i=null,l=!1;if(t.each(e.split(">"),function(t,a){var d,h=null;if(d=/^(\d\d)-(\d\d)$/.exec(a))h=new Date(s,parseInt(d[2],10)-1,parseInt(d[1],10));else if(d=/^(\d\d)-(\d\d)-(\d\d\d\d)$/.exec(a))parseInt(d[3],10)==s&&(h=new Date(s,parseInt(d[2],10)-1,parseInt(d[1],10)));else if(d=/^easter(([+\-])(\d+))?$/.exec(a))h=r(s,d[1]?parseInt(d[1],10):0);else if(d=/^(\d\d)([+\-])([1-5])\*([0-6])$/.exec(a)){var c=parseInt(d[1],10)-1,p=d[2],u=parseInt(d[3]),y=parseInt(d[4]);switch(p){case"+":for(var m=new Date(s,c,-6);m.getDay()!=y;)m=new Date(m.getFullYear(),m.getMonth(),m.getDate()+1);h=new Date(m.getFullYear(),m.getMonth(),m.getDate()+7*u);break;case"-":for(var m=new Date(s,c+1,7);m.getDay()!=y;)m=new Date(m.getFullYear(),m.getMonth(),m.getDate()-1);h=new Date(m.getFullYear(),m.getMonth(),m.getDate()-7*u)}}if(!h)return n("Unknown holiday: "+e),l=!0,!1;switch(t){case 0:o=h;break;case 1:if(h.getTime()<=o.getTime())return n("Unknown holiday: "+e),l=!0,!1;i=h;break;default:return n("Unknown holiday: "+e),l=!0,!1}}),!l){var d=[];if(i)for(var c=new Date(o.getTime());c.getTime()<=i.getTime();c.setDate(c.getDate()+1))d.push(new Date(c.getTime()));else d.push(o);h.push({name:a,days:d})}}),o.cache[i]=h,o.cache[i]}function n(e){"object"==t.type(window.console)&&"function"==t.type(window.console.warn)&&window.console.warn("[Bootstrap-Calendar] "+e)}function s(e,a){return this.options=t.extend(!0,{position:{start:new Date,end:new Date}},l,e),this.setLanguage(this.options.language),this.context=a,a.css("width",this.options.width).addClass("cal-context"),this.view(),this}function i(e,a,o,n){e.stopPropagation();var a=t(a),s=a.closest(".cal-cell"),i=s.closest(".cal-before-eventlist"),r=s.data("cal-row");a.fadeOut("fast"),o.slideUp("fast",function(){var e=t(".events-list",s);o.html(n.options.templates["events-list"]({cal:n,events:n.getEventsBetween(parseInt(e.data("cal-start")),parseInt(e.data("cal-end")))})),i.after(o),n.activecell=t("[data-cal-date]",s).text(),t("#cal-slide-tick").addClass("tick"+r).show(),o.slideDown("fast",function(){t("body").one("click",function(){o.slideUp("fast"),n.activecell=0})})}),t("a.event-item").mouseenter(function(){t('a[data-event-id="'+t(this).data("event-id")+'"]').closest(".cal-cell1").addClass("day-highlight dh-"+t(this).data("event-class"))}),t("a.event-item").mouseleave(function(){t("div.cal-cell1").removeClass("day-highlight dh-"+t(this).data("event-class"))}),n._update_modal()}function r(t,e){var a=t%19,o=Math.floor(t/100),n=t%100,s=Math.floor(o/4),i=o%4,r=Math.floor((o+8)/25),l=Math.floor((o-r+1)/3),d=(19*a+o-s-l+15)%30,h=Math.floor(n/4),c=n%4,p=(32+2*i+2*h-d-c)%7,u=Math.floor((a+11*d+22*p)/451),y=d+p+7*u+114,m=Math.floor(y/31)-1,g=y%31+1;return new Date(t,m,g+(e?e:0),0,0,0)}var l={width:"100%",view:"month",day:"now",events_source:"",tmpl_path:"tmpls/",tmpl_cache:!0,classes:{months:{inmonth:"cal-day-inmonth",outmonth:"cal-day-outmonth",saturday:"cal-day-weekend",sunday:"cal-day-weekend",holidays:"cal-day-holiday",today:"cal-day-today"},week:{workday:"cal-day-workday",saturday:"cal-day-weekend",sunday:"cal-day-weekend",holidays:"cal-day-holiday",today:"cal-day-today"}},modal:null,views:{year:{slide_events:1,enable:1},month:{slide_events:1,enable:1},week:{enable:1},day:{enable:1}},merge_holidays:!1,onAfterEventsLoad:function(t){},onBeforeEventsLoad:function(t){t()},onAfterViewLoad:function(t){},events:[],templates:{year:"",month:"",week:"",day:""},stop_cycling:!1},d={first_day:2,holidays:{"01-01":"New Year's Day","01+3*1":"Birthday of Dr. Martin Luther King, Jr.","02+3*1":"Washington's Birthday","05-1*1":"Memorial Day","04-07":"Independence Day","09+1*1":"Labor Day","10+2*1":"Columbus Day","11-11":"Veterans Day","11+4*4":"Thanksgiving Day","25-12":"Christmas"}},h={error_noview:"Calendar: View {0} not found",error_dateformat:'Calendar: Wrong date format {0}. Should be either "now" or "yyyy-mm-dd"',error_loadurl:"Calendar: Event URL is not set",error_where:'Calendar: Wrong navigation direction {0}. Can be only "next" or "prev" or "today"',no_events_in_day:"No events in this day.",title_year:"{0}",title_month:"{0} {1}",title_week:"week {0} of {1}",title_day:"{0} {1} {2}, {3}",week:"Week",m0:"January",m1:"February",m2:"March",m3:"April",m4:"May",m5:"June",m6:"July",m7:"August",m8:"September",m9:"October",m10:"November",m11:"December",ms0:"Jan",ms1:"Feb",ms2:"Mar",ms3:"Apr",ms4:"May",ms5:"Jun",ms6:"Jul",ms7:"Aug",ms8:"Sep",ms9:"Oct",ms10:"Nov",ms11:"Dec",d0:"Sunday",d1:"Monday",d2:"Tuesday",d3:"Wednesday",d4:"Thursday",d5:"Friday",d6:"Saturday"},c="";try{"object"==t.type(window.jstz)&&"function"==t.type(jstz.determine)&&(c=jstz.determine().name(),"string"!==t.type(c)&&(c=""))}catch(p){}o.cache={},s.prototype.setOptions=function(e){t.extend(this.options,e),"language"in e&&this.setLanguage(e.language),"modal"in e&&this._update_modal()},s.prototype.setLanguage=function(e){window.calendar_languages&&e in window.calendar_languages?(this.locale=t.extend(!0,{},h,calendar_languages[e]),this.options.language=e):(this.locale=h,delete this.options.language)},s.prototype._render=function(){this.context.html(""),this._loadTemplate(this.options.view),this.stop_cycling=!1;var t={};t.cal=this,t.day=1,1==a(this,"first_day")?t.months=[this.locale.d1,this.locale.d2,this.locale.d3,this.locale.d4,this.locale.d5,this.locale.d6,this.locale.d0]:t.months=[this.locale.d0,this.locale.d1,this.locale.d2,this.locale.d3,this.locale.d4,this.locale.d5,this.locale.d6];var e=parseInt(this.options.position.start.getTime()),o=parseInt(this.options.position.end.getTime());switch(t.events=this.getEventsBetween(e,o),this.options.view){case"month":break;case"week":break;case"day":}t.start=new Date(this.options.position.start.getTime()),t.lang=this.locale,this.context.append(this.options.templates[this.options.view](t)),this._update()},s.prototype._week=function(e){this._loadTemplate("week-days");var o={},n=parseInt(this.options.position.start.getTime()),s=parseInt(this.options.position.end.getTime()),i=[],r=this,l=a(this,"first_day");return t.each(this.getEventsBetween(n,s),function(t,e){e.start_day=new Date(parseInt(e.start)).getDay(),1==l&&(e.start_day=(e.start_day+6)%7),e.end-e.start<=864e5?e.days=1:e.days=(e.end-e.start)/864e5,e.start<n&&(e.days=e.days-(n-e.start)/864e5,e.start_day=0),e.days=Math.ceil(e.days),e.start_day+e.days>7&&(e.days=7-e.start_day),i.push(e)}),o.events=i,o.cal=this,r.options.templates["week-days"](o)},s.prototype._month=function(t){this._loadTemplate("year-month");var e={cal:this},a=t+1;e.data_day=this.options.position.start.getFullYear()+"-"+(10>a?"0"+a:a)+"-01",e.month_name=this.locale["m"+t];var o=new Date(this.options.position.start.getFullYear(),t,1,0,0,0);return e.start=parseInt(o.getTime()),e.end=parseInt(new Date(this.options.position.start.getFullYear(),t+1,0,0,0,0).getTime()),e.events=this.getEventsBetween(e.start,e.end),this.options.templates["year-month"](e)},s.prototype._day=function(e,o){this._loadTemplate("month-day");var n={tooltip:"",cal:this},s=this.options.classes.months.outmonth,i=this.options.position.start.getDay();2==a(this,"first_day")?i++:i=0==i?7:i,o=o-i+1;var r=new Date(this.options.position.start.getFullYear(),this.options.position.start.getMonth(),o,0,0,0);o>0&&(s=this.options.classes.months.inmonth);var l=new Date(this.options.position.end.getTime()-1).getDate();if(o+1>l&&(this.stop_cycling=!0),o>l&&(o-=l,s=this.options.classes.months.outmonth),s=t.trim(s+" "+this._getDayClass("months",r)),0>=o){var d=new Date(this.options.position.start.getFullYear(),this.options.position.start.getMonth(),0).getDate();o=d-Math.abs(o),s+=" cal-month-first-row"}var h=this._getHoliday(r);return h!==!1&&(n.tooltip=h),n.data_day=r.getFullYear()+"-"+r.getMonthFormatted()+"-"+(10>o?"0"+o:o),n.cls=s,n.day=o,n.start=parseInt(r.getTime()),n.end=parseInt(n.start+864e5),n.events=this.getEventsBetween(n.start,n.end),this.options.templates["month-day"](n)},s.prototype._getHoliday=function(e){var a=!1;return t.each(o(this,e.getFullYear()),function(){var o=!1;return t.each(this.days,function(){return this.toDateString()==e.toDateString()?(o=!0,!1):void 0}),o?(a=this.name,!1):void 0}),a},s.prototype._getHolidayName=function(t){var e=this._getHoliday(t);return e===!1?"":e},s.prototype._getDayClass=function(t,e){var a=this,o=function(e,o){var n;n=a.options.classes&&t in a.options.classes&&e in a.options.classes[t]?a.options.classes[t][e]:"","string"==typeof n&&n.length&&o.push(n)},n=[];e.toDateString()==(new Date).toDateString()&&o("today",n);var s=this._getHoliday(e);switch(s!==!1&&o("holidays",n),e.getDay()){case 0:o("sunday",n);break;case 6:o("saturday",n)}return n.join(" ")},s.prototype.view=function(t){t&&(this.options.view=t),this._init_position(),this._loadEvents(),this._render(),this.options.onAfterViewLoad.call(this,this.options.view)},s.prototype.navigate=function(e,a){var o=t.extend({},this.options.position);if("next"==e)switch(this.options.view){case"year":o.start.setFullYear(this.options.position.start.getFullYear()+1);break;case"month":o.start.setMonth(this.options.position.start.getMonth()+1);break;case"week":o.start.setDate(this.options.position.start.getDate()+7);break;case"day":o.start.setDate(this.options.position.start.getDate()+1)}else if("prev"==e)switch(this.options.view){case"year":o.start.setFullYear(this.options.position.start.getFullYear()-1);break;case"month":o.start.setMonth(this.options.position.start.getMonth()-1);break;case"week":o.start.setDate(this.options.position.start.getDate()-7);break;case"day":o.start.setDate(this.options.position.start.getDate()-1)}else"today"==e?o.start.setTime((new Date).getTime()):t.error(this.locale.error_where.format(e));this.options.day=o.start.getFullYear()+"-"+o.start.getMonthFormatted()+"-"+o.start.getDateFormatted(),this.view(),_.isFunction(a)&&a()},s.prototype._init_position=function(){var e,o,n;if("now"==this.options.day){var s=new Date;e=s.getFullYear(),o=s.getMonth(),n=s.getDate()}else if(this.options.day.match(/^\d{4}-\d{2}-\d{2}$/g)){var i=this.options.day.split("-");e=parseInt(i[0],10),o=parseInt(i[1],10)-1,n=parseInt(i[2],10)}else t.error(this.locale.error_dateformat.format(this.options.day));switch(this.options.view){case"year":this.options.position.start.setTime(new Date(e,0,1).getTime()),this.options.position.end.setTime(new Date(e+1,0,1).getTime());break;case"month":this.options.position.start.setTime(new Date(e,o,1).getTime()),this.options.position.end.setTime(new Date(e,o+1,1).getTime());break;case"day":this.options.position.start.setTime(new Date(e,o,n).getTime()),this.options.position.end.setTime(new Date(e,o,n+1).getTime());break;case"week":var r,l=new Date(e,o,n);r=1==a(this,"first_day")?l.getDate()-(l.getDay()+6)%7:l.getDate()-l.getDay(),this.options.position.start.setTime(new Date(e,o,r).getTime()),this.options.position.end.setTime(new Date(e,o,r+7).getTime());break;default:t.error(this.locale.error_noview.format(this.options.view))}return this},s.prototype.getTitle=function(){var t=this.options.position.start;switch(this.options.view){case"year":return this.locale.title_year.format(t.getFullYear());case"month":return this.locale.title_month.format(this.locale["m"+t.getMonth()],t.getFullYear());case"week":return this.locale.title_week.format(t.getWeek(),t.getFullYear());case"day":return this.locale.title_day.format(this.locale["d"+t.getDay()],t.getDate(),this.locale["m"+t.getMonth()],t.getFullYear())}},s.prototype.isToday=function(){var t=(new Date).getTime();return t>this.options.position.start&&t<this.options.position.end},s.prototype.getStartDate=function(){return this.options.position.start},s.prototype.getEndDate=function(){return this.options.position.end},s.prototype._loadEvents=function(){var a=this,o=null;"events_source"in this.options&&""!==this.options.events_source?o=this.options.events_source:"events_url"in this.options&&(o=this.options.events_url,n("The events_url option is DEPRECATED and it will be REMOVED in near future. Please use events_source instead."));var s;switch(t.type(o)){case"function":s=function(){return o(a.options.position.start,a.options.position.end,c)};break;case"array":s=function(){return[].concat(o)};break;case"string":o.length&&(s=function(){var n=[],s={from:a.options.position.start.getTime(),to:a.options.position.end.getTime()};return c.length&&(s.browser_timezone=c),t.ajax({url:e(o,s),dataType:"json",type:"GET",async:!1}).done(function(e){e.success||t.error(e.error),e.result&&(n=e.result)}),n})}s||t.error(this.locale.error_loadurl),this.options.onBeforeEventsLoad.call(this,function(){a.options.events=s(),a.options.events.sort(function(t,e){var a;return a=t.start-e.start,0==a&&(a=t.end-e.end),a}),a.options.onAfterEventsLoad.call(a,a.options.events)})},s.prototype._loadTemplate=function(e){if(!this.options.templates[e]){var a=this;t.ajax({url:this.options.tmpl_path+e+".html",dataType:"html",type:"GET",async:!1,cache:this.options.tmpl_cache}).done(function(t){a.options.templates[e]=_.template(t)})}},s.prototype._update=function(){var e=this;t('*[data-toggle="tooltip"]').tooltip({container:"body"}),t("*[data-cal-date]").click(function(){var a=t(this).data("cal-view");e.options.views[a].enable&&(e.options.day=t(this).data("cal-date"),e.view(a))}),t(".cal-cell").dblclick(function(){var a=t("[data-cal-date]",this).data("cal-view");e.options.views[a].enable&&(e.options.day=t("[data-cal-date]",this).data("cal-date"),e.view(a))}),this["_update_"+this.options.view](),this._update_modal()},s.prototype._update_modal=function(){var e=this;if(t("a[data-event-id]",this.context).unbind("click"),e.options.modal){var a=t(e.options.modal);if(a.length){var o=t(document.createElement("iframe")).attr({width:"100%",frameborder:"0"});t("a[data-event-id]",this.context).on("click",function(e){e.preventDefault(),e.stopPropagation();var n=t(this).attr("href");o.attr("src",n),t(".modal-body",a).html(o),a.data("handled.bootstrap-calendar")||a.on("show.bs.modal",function(){var e=t(this).find(".modal-body"),a=e.height()-parseInt(e.css("padding-top"),10)-parseInt(e.css("padding-bottom"),10);t(this).find("iframe").height(Math.max(a,50))}).data("handled.bootstrap-calendar",!0),a.modal("show")})}}},s.prototype._update_day=function(){},s.prototype._update_week=function(){},s.prototype._update_year=function(){this._update_month_year()},s.prototype._update_month=function(){this._update_month_year();var e=t(document.createElement("div")).attr("id","cal-week-box");e.html(this.locale.week);var a=this.options.position.start.getFullYear()+"-"+this.options.position.start.getMonthFormatted()+"-";t(".cal-month-box .cal-row-fluid").on("mouseenter",function(){var o=t(".cal-cell1:first-child .cal-month-day",this),n=o.hasClass("cal-month-first-row")?1:t("[data-cal-date]",o).text();n=10>n?"0"+n:n,e.attr("data-cal-week",a+n).show().appendTo(o)}).on("mouseleave",function(){e.hide()});var o=this;e.click(function(){o.options.day=t(this).data("cal-week"),o.view("week")}),t("a.event").mouseenter(function(){t('a[data-event-id="'+t(this).data("event-id")+'"]').closest(".cal-cell1").addClass("day-highlight dh-"+t(this).data("event-class"))}),t("a.event").mouseleave(function(){t("div.cal-cell1").removeClass("day-highlight dh-"+t(this).data("event-class"))})},s.prototype._update_month_year=function(){if(this.options.views[this.options.view].slide_events){var e=this,a=t(document.createElement("div")).attr("id","cal-day-box").html('<i class="icon-chevron-down glyphicon glyphicon-chevron-down"></i>');t(".cal-month-day, .cal-year-box .span3").on("mouseenter",function(){0!=t(".events-list",this).length&&t(this).children("[data-cal-date]").text()!=e.activecell&&a.show().appendTo(this)}).on("mouseleave",function(){a.hide()}).on("click",function(n){0!=t(".events-list",this).length&&t(this).children("[data-cal-date]").text()!=e.activecell&&i(n,a,o,e)});var o=t(document.createElement("div")).attr("id","cal-slide-box");o.hide().click(function(t){t.stopPropagation()}),this._loadTemplate("events-list"),a.click(function(a){i(a,t(this),o,e)})}},s.prototype.getEventsBetween=function(e,a){var o=[];return t.each(this.options.events,function(){(parseInt(this.start)<a||null==this.start)&&(parseInt(this.end)>=e||null==this.end)&&o.push(this)}),o},t.fn.calendar=function(t){return new s(t,this)}}(jQuery);