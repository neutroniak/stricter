function StricterUi() {
	this.loginUrl = '/login';
}
var MOBILE_WIDTH=480;
//========
StricterUi.prototype.loading = function(st) {
    /*
	if(st==false) {
        $(".st-icon-loading").css('visibility', 'hidden');
        $(".st-icon-loading").css('z-index', '0');
        $(".st-icon-loading").css('height', '0px');
        $(".st-icon-loading").hide();
    } else {
        $(".st-icon-loading").css('visibility', 'visible');
        $(".st-icon-loading").css('z-index', '4440');
        $(".st-icon-loading").css('height', $('body').css('height'));
        $(".st-icon-loading").show();
    }
	*/
}
//========
StricterUi.prototype.getContent = function(nurl){
	$.ajax({
		url:nurl+"/?ajax=1",
		dataType:"text",
		method:"GET",
		beforeSend: function(res){ 
			$("#st-content").html("");
			// TODO clear timeouts
		}
	}).success(function(res){
		$("#st-content").html(res);
	}).fail(function(res){
		if(res.status==401 || res.status==403)
			window.location.replace(this.loginUrl);
	}).complete(function(){
		stricterui.ajaxReload();
	});
}
//========
StricterUi.prototype.postContent = function(nurl, formid){
	if(formid) refform="#"+formid; else refform='.st-class';
	$.ajax({
		url:nurl+"/?ajax=1",
		data:$(refform).serialize(),
		dataType:"text",
		method:"POST",
		contentType:'application/x-www-form-urlencoded'
	}).success(function(res){
		$("#st-content").html(res);
	}).fail(function(res){
		if(res.status==401 || res.status==403)
			window.location.replace(this.loginUrl);
	}).complete(function(){
		stricterui.ajaxReload();
	});
}
StricterUi.prototype.ajaxReload = function() {
	$('.st-ajaxlink').unbind('click');
	$('.st-menulink').unbind('click');
	$('.st-form').unbind('submit');
	$('.st-ajaxlink').click(function(e) {
		stricterui.loading(true);
		href = $(this).attr("href");
		stricterui.getContent(href);
		history.pushState({}, href, href);
		e.preventDefault();
	});
	$('.st-menulink').click(function(e) {
		stricterui.loading(true);
		href = $(this).attr("href");
		stricterui.getContent(href);
		history.pushState({}, href, href);
		e.preventDefault();
	});
	$(".st-form").submit(function( e ) {
		href = $(this).attr("action");
		stricterui.postContent(href, $(this).attr("id"));
		history.replaceState('', href, href);
		e.preventDefault();
	});
	stricterui.loading(false);
}
//========
StricterUi.prototype.setMobile = function(tf){
/*
logosrc=$('#logoimg').attr('src');
	if(tf==false){
		newlogo = logosrc.replace('logo_small.svg','logo.svg');
		$('#logoimg').attr('src',newlogo);
		$('#logoimg').css('width','188px');
		$('#logoimg').css('height','34px');
		$('#logoimg').css('margin-left','0px');
		$('.responsive').removeClass('nonmobile');
		$('#home .buttons').css('width','90%');
		$('#home .buttons .button').css('margin','4px');
	} else {
		newlogo = logosrc.replace('logo.svg','logo_small.svg');
		$('#logoimg').attr('src',newlogo);
		$('#logoimg').css('width','34px');
		$('#logoimg').css('height','34px');
		$('#logoimg').css('margin-left','50px');
		$('.responsive').addClass('nonmobile');
		$('#home .buttons').css('width','80%');
		$('#home .buttons .button').css('margin','4px');
	}
*/
}
StricterUi.prototype.undo = function(formid){
	if(fid = document.getElementById(formid))
		fid.reset();
}
StricterUi.prototype.refresh = function(){
	window.location.reload();
}
StricterUi.prototype.del = function(s,p){
	var c = confirm('Remover este registro?');
	if(c==true)
		window.location.href=webpath+'/'+p+'/delete/'+id;
}
$(".st-form").submit(function( e ) {
	href = $(this).attr("action");
	stricterui.postContent(href);
	history.replaceState('', href, href);
	e.preventDefault();
});
$(window).resize(function(){
	if($(window).width()<MOBILE_WIDTH)
		stricterui.setMobile(true);
	else
		stricterui.setMobile(false);
});
$(window).load(function() {
	$(".dropmenu").click(function(e){
		if($("#themenu").css("display")=="none") {
			$("#themenu").show(300);
		} else {
			$("#themenu").hide(300);
		}
	});

	if($(window).width()<MOBILE_WIDTH)
		stricterui.setMobile(true);
	else
		stricterui.setMobile(false);

	$('#header .user').click(function(e){
		if($(".userbox").css("display")=="none"){
			$('.userbox').slideDown(300);
		} else {
			$('.userbox').slideUp(300);
		}
	});
});
$(window).ready(function() {
	//if(isAjax==false) {
		$('.st-menulink').click(function(e) {
			stricterui.loading(true);
			href = $(this).attr("href");
			stricterui.getContent(href);
			history.pushState({}, href, href);
			e.preventDefault();
		});
//	}	
	$('.st-ajaxlink').click(function(e) {
		stricterui.loading(true);
		href = $(this).attr("href");
		stricterui.getContent(href);
		history.pushState({}, href, href);
		e.preventDefault();
	});
	if($(window).width()<MOBILE_WIDTH)
		stricterui.setMobile(true);
	else
		stricterui.setMobile(false);
});

stricterui = new StricterUi();

