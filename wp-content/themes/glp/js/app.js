$(function() {
    $("#nav-featured .participant-thumbnail").click(function(){
        $(".participant-thumbnaill").removeClass("active")
        var a = $(this).attr("data-controls");
        var c = a.replace("content_","#location_"); // create the name of the map image from the data-controls field
        var e = $(c).attr("alt"); // get the lat/long of the individual
        e = e.replace(" ",""); // replace the space from the alt tag with nothing so it can be put in an URL
        //---- add the src of the location image
        $(c).attr({"src":"http://maps.googleapis.com/maps/api/staticmap?center=" + e + "&zoom=6&size=600x400&markers=color:red|" + e + "&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100"})
    })
    $(".overlay, #mapview").hide();
})
/*
  if($("body.page-explore").length){var g=window.location.hash;
("#mapview"===g||"#gridview"===g)&&f(g),$(".btn-mapview").click(function(){f("#mapview")}),$(".btn-gridview").click(function(){f("#gridview")}),$("#nav-explore input, #nav-explore select").change(function(){return $(participants).each(function(){this.filtered=!1,$("select[name=series]").val()&&"All"!==$("select[name=series]").val()&&-1==$.inArray($("select[name=series]").val(),this.series)&&(this.filtered=!0),"All"!==$("select[name=gender]").val()&&this.gender!==$("select[name=gender]").val()&&(this.filtered=!0),"All"!==$("select[name=income]").val()&&this.income!==$("select[name=income]").val()&&(this.filtered=!0),"All"!==$("select[name=age]").val()&&this.age!==$("select[name=age]").val()&&(this.filtered=!0)}),$("input[name=proposed]:checked").val()?($(".proposed").removeClass("hide"),d3.selectAll(".marker.proposed").style("opacity",1)):($(".proposed").addClass("hide"),d3.selectAll(".marker.proposed").style("opacity",0)),h(),!1})}if($("#nav-themes").length){$("#nav-themes li").hover(function(){$(this).siblings().find(".theme-link").hide(),$(this).find(".theme-link").slideDown("slow",function(){$(this).css({"display":"block","bottom":"-22px"})})},
  function(){$(this).children(".theme-link").slideUp()}),$("#nav-themes li").click(function(){var a=$(this).attr("data-term");
$(participants).each(function(){this.filteredByTheme=!1,a&&-1==$.inArray(a,this.themes)&&(this.filteredByTheme=!0)}),h(),$(this).addClass("active").siblings().removeClass("active")}),$("#nav-themes").find(".thumbnails").cycle({timeout:250,speed:250});
var h=function(){$(participants).each(function(){this.filtered===!0||this.filteredByTheme===!0?($("#participant-"+this.id).addClass("filtered"),d3.selectAll("#marker-"+this.id).classed("filtered",!0)):($("#marker-"+this.id+", #participant-"+this.id).removeClass("filtered"),d3.selectAll("#marker-"+this.id).classed("filtered",!1))})}}if($("#mapview").length){var i=function(a){var b=q.select("#marker-"+a),c=[+b.attr("data-x"),+b.attr("data-y")],d=$.grep(participants,function(b){return b.id==a})[0];
$(participants).each(function(){var b=k(d.themes,this.themes);
if(this.id!==a&&b.length>0){var e=this.id,f=q.select("#marker-"+e),g=[+f.attr("data-x"),+f.attr("data-y")];
s.append("path").attr("class","edge").attr("id","edge-"+e).attr("d",function(){return c[0]<g[0]?"M"+c[0]+","+c[1]+"L"+g[0]+","+g[1]+"Z":"M"+g[0]+","+g[1]+"L"+c[0]+","+c[1]+"Z"}).style("stroke","#fff").style("stroke-width",3).style("opacity",.25),s.append("text").attr("class","edge-label").style("fill","#fff").style("text-anchor","middle").attr("dy",3).append("textPath").attr("xlink:href","#edge-"+e).attr("startOffset","25%").text(b)}})},j=function(){q.selectAll(".edge, .edge-label").remove()},k=function(a,b){return a.length>0&&b.length>0?$.grep(a,function(c){return""!==a?$.inArray(c,b)>-1:!1}):!1},l=$("article.participant").attr("data-participant_id");
window.location.hash&&"mapview"===window.location.hash||$("#mapview").hide(),$("#mapview").css("max-height",function(){return $(window).height()-$("#content").offset().top-$("#nav-explore").height()-$("#nav-themes").height()-$(".handle").height()});
var m=$("#mapview").height(),n=2*m,o=d3.geo.mercator().scale(.16*n).translate([n/2,m/1.75]),p=d3.geo.path().projection(o),q=(d3.behavior.zoom().translate(o.translate()).scale(o.scale()).scaleExtent([.15*n,8*m]).on("zoom",function(){o.translate(d3.event.translate).scale(d3.event.scale),r.selectAll("path").attr("d",p),q.selectAll(".marker").attr("transform",function(a){return"translate("+o([+a.longitude,+a.latitude])+")"})}),d3.select("#mapview").append("svg").attr("height",m).attr("width",n)),r=q.append("g").attr("id","countries");
r.append("rect").attr("class","background").attr("height",m).attr("width",n);
var s=q.append("g").attr("id","underlay"),t=(q.append("defs").selectAll("thumbnails").data(participants).enter().append("pattern").attr("id",function(a,b){return"image-"+b}).attr("patternUnits","objectBoundingBox").attr("width",50).attr("height",50).append("image").attr("xlink:href",function(a){return a.thumbnail}).attr("x",0).attr("y",0).attr("width",function(a){return l==a.id?64:48}).attr("height",function(a){return l==a.id?64:48}),q.selectAll(".marker").data(participants).enter().append("g").attr("id",function(a){return"marker-"+a.id}).attr("class",function(a){return"marker "+a.continent+(a.proposed?" proposed":"")}).attr("transform",function(a){return"translate("+o([+a.longitude,+a.latitude])+")"}).attr("data-x",function(a){var b=o([+a.longitude,+a.latitude]);
return Math.round(b[0])}).attr("data-y",function(a){var b=o([+a.longitude,+a.latitude]);
return Math.round(b[1])}).on("click",function(a){window.location=a.permalink}));
t.append("circle").attr("class","pin").attr("r",5),t.append("circle").attr("class",function(a){return l==a.id?"mapthumb single":"mapthumb"}).attr("id",function(a,b){return"mapthumb-"+b}).attr("r",function(a){return l==a.id?32:24}).attr("fill",function(a,b){return"url(#image-"+b+")"});
var u=t.append("text").attr("class","label").attr("dx",-25).attr("dy",function(a){return l==a.id?48:40});
u.append("tspan").attr("class","name").text(function(a){return a.name}),u.append("tspan").attr("class","occupation").text(function(a){return a.occupation}).attr("x",-25).attr("dy",15),u.append("tspan").attr("class","location").text(function(a){return a.location}).attr("x",-25).attr("dy",15),d3.json("/wp-content/themes/glp/js/vendor/countries.json",function(a){r.selectAll("path").data(a.features).enter().append("svg:path").attr("d",p)}),d3.json("/wp-content/themes/glp/js/vendor/countries-hires.json",function(a){r.selectAll("path").remove(),r.selectAll("path").data(a.features).enter().append("svg:path").attr("d",p)}),
  $(".overlay, .mapthumb:not(.single), .label, #popover").hide();

  var v=$("article.participant").attr("data-participant_id");
v?(i(v),$(".marker").hover(function(){$(this).find(".mapthumb, .label").show()},function(){$(this).find(".mapthumb:not(.single), .label").hide()})):$(".marker").hover(function(){$(this).find(".mapthumb, .label").show(),i($(this).attr("id").split("-")[1])},function(){$(this).find(".mapthumb:not(.single), .label").hide(),j()}),$(".background, #popover .close").click(function(){$("#popover, .overlay").hide(),j()}),$(".background").click(function(){$(".mapthumb:not(.single), .label").hide()})}if($(".blog").length){var w=$(".blog .post").first().data("bg");
w&&a(w,{to:"#262626"}),$(".past-posts .post").each(function(){var a=$(this).data("bg");
a&&$(this).css("background-image","url("+a+")")})}if($(".events-list").length&&$(".tribe-events-event").each(function(){var a=$(this).data("bg");
a&&$(this).css("background-image","url("+a+")")}),$(".search-sidebar :checkbox").change(function(){var a=$(this).val();
$(".search-result."+a).slideToggle("",function(){$(".results-found").html($(".search-result:visible").length)})}),$("body.tax-series").length&&($(".carousel").carousel("pause"),$("#series-carousel").bind("slide",function(){$("#series-carousel").css("overflow","hidden")}),$("#series-carousel").bind("slid",function(){$("#series-carousel").css("overflow","visible")}),$("#nav-series .participant-thumbnail").click(function(){$("#home").fadeOut("slow"),b($(this).data("id"))}),$("#nav-series .home-thumbnail").click(function(){$("#stage").fadeOut("slow",function(){$("#home").fadeIn("slow")})}),$(".btn-mapview").click(function(){$("#mapview").slideToggle(500)})),$("body.tax-themes").length&&$("#theme-select").change(function(){window.location="/themes/"+$(this).val()}),$("body.single-participant").length&&($("#nav-themes").hide(),$(".participant-detail-map .handle").click(function(){$("#mapview, #nav-themes").slideToggle(),$(".participant-detail-map .handle .btn span").toggle()}),$(".participant-filter-clips a.filter").click(function(){$(this).toggleClass("active"),$(".participant-clip-listing."+$(this).data("tag")).toggle()})),$("#donate-banner").length){var x=$("#donate-banner");
x.delay(1e3).slideDown(2e3),$(".not-now").click(function(){x.slideUp(2e3)})}}),
$(function(){"use strict";
$(window).bind("setup_players",setup_players),
$(window).bind("setup_players",setup_popover),
$(document).on("click",".controls",function(){
    var a=$(this).closest(".participant-clip").find(".participant-video-embed").attr("id"),
    b=(players[a],$(this).attr("data-control"));
switch(b){case"play":players[a].playVideo();
break;
case"pause":players[a].pauseVideo();
break;
case"fullscreen":var c=document.getElementById(a);
c.requestFullScreen?c.requestFullScreen():c.mozRequestFullScreen?c.mozRequestFullScreen():c.webkitRequestFullScreen&&c.webkitRequestFullScreen();
break;
case"comments":toggle_comments();
break;
case"dimmer":turn_out_the_lights()}}),$(document).on("click",".popover .close",function(){$(this).closest(".popover").prev().popover("hide")}),$(document).on("submit",".popover form",function(){return $(this).find(".error").fadeOut("slow",function(){$(this).remove()}),$.post(glpAjax.ajaxurl,{action:"clip_submit_comment",comment:$(this).find('input[name="comment"]').val(),minutes:$("#taggable-area").data("m"),seconds:$("#taggable-area").data("s"),position:$("#taggable-area").data("p"),post_id:$(".participant-video-embed").attr("id").replace("participant-video-embed-","")},function(a){var b=$.parseJSON(a);
$(".comment-box").prepend(b.message).closest("form").find("input").val(""),b.success&&add_comment_to_marker_box(b.success,b.message),$(".popover").fixPopoverHeight()}),!1}),$(document).on("click","#shadow",function(){turn_out_the_lights()}),$(document).on("mouseenter",".marker",function(){var a=parseInt($(this).attr("id").replace("marker-",""),10);
$("#taggable-area").setupPopover().showPopover(a)}),$(document).on("mouseleave",".marker",function(){}),$(document).on("click",".participant-clip-listing .clip-thumbnail",function(){$("html, body").scrollTop(0);
var a=$(this).data("clip-id");
$(this).parents(".participant-clip-listing").addClass("active").siblings().removeClass("active"),$("#stage").slideUp().load(glpAjax.ajaxurl,{action:"get_participant_clip",clip_id:a},function(){$("#stage").delay(250).slideDown(),$(window).trigger("setup_players")})}),$(document).on("click",".btn-toggle",function(){var a=$(this).data("user-id"),b=$(this).data("clip-id"),c=$(this).data("toggle-type");
return $(this).load(glpAjax.ajaxurl,{action:"toggle_clip",user_id:a,clip_id:b,toggle_type:c},function(a){$("[data-clip-id='"+b+"'][data-toggle-type='"+c+"']").html(a)}),!1}),$(document).on("click",".btn-toggle-all",function(){var a=$(this).data("user-id"),b=$(this).data("list-id");
return $.post(glpAjax.ajaxurl,{action:"toggle_clip_list",user_id:a,post_id:b},function(c){response=$.parseJSON(c),$("[data-list-id='"+b+"'][data-user-id='"+a+"']").html(response.text);
var d=function(a){a=$.parseJSON(a),$("[data-clip-id='"+a.clip_id+"'][data-toggle-type='queue']").html(a.status)};
for(var e in response.toggled)clip_id=response.toggled[e],$.post(glpAjax.ajaxurl,{action:"clip_status",user_id:a,clip_id:clip_id},d(status))}),!1}),$(document).on("click",".btn-play-all",function(){$.each(players,function(){this.playVideo()})})});
var players={},t;
!function(){var a=document.createElement("script");
a.src=("https:"==location.protocol?"https":"http")+"://www.youtube.com/player_api";
var b=document.getElementsByTagName("script")[0];
b.parentNode.insertBefore(a,b)}();
var YT_ready=function(){var a=[],b=!1;
return function(c,d){if(c===!0)for(b=!0;
a.length;
)a.shift()();
else"function"==typeof c&&(b?c():a[d?"unshift":"push"](c))}}();
YT_ready(function(){$(window).trigger("setup_players")}),$.fn.setupPopover=function(){var a=this.next().find(".content").html(),b=this.next().find(".title").html();
return this.popover({html:!0,animation:!1,content:a,title:b}),this},$.fn.showPopover=function(a){var b=this.closest(".participant-clip").find(".participant-video-embed").attr("id"),c=players[b],d=a/this.width(),e=Math.round(c.getDuration()*d*100)/100,
*/