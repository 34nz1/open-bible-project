$(document).ready(function(){  
	window['handler'] = [];
	$.fn.inlineBlockToggle=function () {
		/* Show/Hide method that toggles an item's display between inline-block and none */
	    if (this.css('display')==='inline-block') {
	        this.css('display', 'none');
	    } else {
	        this.css('display', 'inline-block');
	    }
	};
	
	$(document).on("focus", "#search", function(e){		
		$(this).animate({'width':'50%'});
	});
	
	$(document).on("focusout", "#search", function(e){
		if($(this).val().length)
			var width = ($(this).val().length*12)+27;
		else
			var width = 207;
			
		$(this).animate({'width':width+"px"});
	});
	
	$(document).on("keyup", "#search", function(e){		
		var elm = $(this);
		if(e.keyCode == 13){
			$(".ajax-loader").show();
			var val = $(elm).val();
			$.Biblify.call("loadurl", {"url":$.Biblify.base_url+"search/"+val,"setAsCurrent":true}, function(data){
				$(".bible").html($(data.html).filter(".bible").html());
				$(".ajax-loader").hide();
				$.Biblify.alignVerses();
			},true);
		}
	});
	
	$("#toggle_header").change(function(){
		$("header").slideToggle();
		$(".bible,.sidebar").toggleClass("spacier");
		$('#sidebar').toggle().toggle();
		$(".sidebar").getNiceScroll().resize();
	});
	
	$(window).resize(function(){
		$(".bible,.sidebar").toggleClass("spacier");
		$(".bible,.sidebar").toggleClass("spacier");
		$(".sidebar").getNiceScroll().resize();
	});
	
	$(".sidebar_toggle").click(function(){
		var id = $(this).attr("id");

		if(!$(".sidebar-left").is(":visible") || $("section#"+id).is(":visible")){
			$(".sidebar-left").toggle();
			$(".bible").toggleClass("space-left");
		}		
		
		$(".sidebar-left section.active,#sidebar_tabs li.sidebar_toggle.active").removeClass("active");
		$("section#"+id).addClass("active");

		var visible = $("section#"+id).is(":visible");
		if(visible){
			if($(this).prop("tagName") == "li"){
				$(this).addClass("active");			
			}else{
				$(this).closest("li").addClass("active");
			}	
		}
		$('#sidebar-left').toggle().toggle();
		$(".sidebar-left").getNiceScroll().resize();
	});

	$("#account_toggler").click(function(){
		var is_active = $(this).hasClass("active");
		$("#options_toggler,#account_toggler, section#account, section#options").removeClass("active");
		if(is_active){
			$(".sidebar-right").hide();
			$(this).removeClass('active');			
		}else{
			$(".sidebar-right").show();
			$(this).addClass('active');			
			$("section#account").addClass("active");
		}		
	});
	
	$("#options_toggler").click(function(){
		var is_active = $(this).hasClass("active");
		$("#options_toggler,#account_toggler, section#account, section#options").removeClass("active");
		if(is_active){
			$(".sidebar-right").hide();
			$(this).removeClass('active');			
		}else{
			$(".sidebar-right").show();
			$(this).addClass('active');			
			$("section#options").addClass("active");
		}	
	});
	
	$(".login-form").submit(function(e){
		e.preventDefault();
		var data = {
			'_username': $(this).find("#username").val(),
			'_password': $(this).find("#password").val()
		};
		$.Biblify.call("login", {"data":data}, function(data){
			/*$.Biblify.call("reload", {},function(data){
				$("html").html($(data.html).html());
				$(".ajax-loader").hide();
				$.Biblify.alignVerses();
			},true);*/
		},true);
	});
	
	$("#side_books .item.book").click(function(e){
		if($(e.target).hasClass("chapter"))
			return false;
		
		var active = $(this).hasClass('active');
		
		$(".chapters").hide();
		$(".item.book").removeClass('active');
		if(!active){
			$(this).find(".chapters").inlineBlockToggle();
			$(this).addClass("active");
		}
	});	
	
	$("#side_books .item.book .chapter").click(function(){
		var book = $.parseJSON($(this).closest(".book").find("input").val());
		var search = window.location.search ? window.location.search :"";
		$(".chapter").removeClass("active");
		$(this).addClass("active");
		$(".ajax-loader").show();
		$.Biblify.call("load", {"book":book.Names.dutch, "chap":$(this).text()}, function(data){
			$(".bible").html($(data.html).filter(".bible").html());
			$(".ajax-loader").hide();
			$.Biblify.alignVerses();
		},true);
	});
	
	$("#side_versions .item").not("input[type='checkbox']").click(function(e){
		if($(e.target).prop("tagName") == "SPAN"){
			$(e.target).closest("li").find("input[type='checkbox']").click();			
		}		
	});
	
	$("#side_versions .item.version input[type='checkbox']").change(function(e){		
		
		var cur = this;
		var ids = [];

		var curId = $(cur).attr("id").replace("version_", "").replace(/^(00)/,"");
		
		$("#side_versions input:checked").each(function(index){
			ids.push($(this).attr("id").replace("version_", "").replace(/^(00)/,""));
		});

		id = ids.filter(function(itm,i,a){
		    return i==a.indexOf(itm);
		}).join();

		$(".ajax-loader").show();
		$.Biblify.call("update", {"data":{"versions":ids}}, function(data){
			if(!window.handler['versions']){
				window.handler['versions'] = true;
				$.Biblify.call("reload", {},function(data){
					$(".bible").html($(data.html).filter(".bible").html());
					$(".ajax-loader").hide();
					$.Biblify.alignVerses();
					window.handler['versions'] = false;
				},true);
			}			
		},true);		
	});
	
	$('#sidebar').niceScroll({smoothscroll:false});
	//$('.bible').niceScroll({smoothscroll:false});
	$.Biblify.init();
});