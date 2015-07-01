(function ( $ ) {
	$.Biblify = {
		options: {},
		init: function( options ){
			this.options = $.extend( {}, options) ;
			if($("#json_books").length > 0){
				objBooks = $.parseJSON($("#json_books").val());
				var books = {};
				$.each(objBooks, function(i, object) {
					var b = {};
					$.each(object, function(property, value) {
						b[property] = value;
					});		
					books[i] = b;
				});
				this.books = books;
			}			
			$.Biblify.alignVerses();
			return this;
		},
		searchBooks: function( search ){
			var all = this.books;	
			return $.grep(Object.keys(this.books), function(b){
				var book = all[b];
				return (book.name.toLowerCase().indexOf(search.toLowerCase())==0 || b.toLowerCase().indexOf(search.toLowerCase()) ==0);
			});
		},
		alignVerses: function(){
			/*var verses = new Array();
			$(".chapter li").each(function(index){
				if($.inArray($(this).val(),verses) == -1){
					verses.push($(this).val());
				}
			});
			$.each(verses, function(index,vers){
				var offset = 0-$(".bible").scrollTop();
				$(".verse[value='"+vers+"']").each(function(index){
					$(this).css("margin-top","0px");
					if($(this).offset().top > offset){
						offset = $(this).offset().top;
					}
				});
				$(".verse[value='"+vers+"']").each(function(index){
					if($(this).attr("id") == "04020001"){
						console.log(offset);
					}
					if($(this).offset().top < offset){
						$(this).css("margin-top", offset - $(this).offset().top);
					}
				});
			});*/
		},
		call: function(method, options, callback, push){
			if(typeof(options)==='undefined'){
				var options = {};
			}
			
			var data = options.data ? options.data : {};
			data['async'] = '1';
			
			if(method == "update"){
				rmethod = "POST";
				url = this.base_url+method;
			}else if(method == "reload"){
				rmethod = "GET";
				url = this.current_url;
			}else if(method == "load"){
				rmethod = "GET";
				url = this.base_url+options.book+"/"+options.chap;
				$.Biblify.current_url = url;
			}else if(method == "loadurl"){
				rmethod = "GET";
				url = options['url'];
				if(data['setAsCurrent']){
					$.Biblify.current_url = url;					
				}
			}else if(method == "login"){
				rmethod = "POST";
				url = this.base_url+"user/login_check";
			}

			$.ajax({
				method:rmethod,
				url: url,
				data: data,
				complete: function(data){
					if(data.responseText && method != "update"){
						var resp = $.parseJSON(data.responseText);
						if('title' in resp)
							document.title = resp.title;
						
						if('current_url' in resp)
							$.Biblify.current_url = resp.current_url;
												
						if(!(typeof(callback)==='undefined')){
							callback(resp);
						}
						if(push){
							history.pushState(null, document.title, $.Biblify.current_url);
						}
					}else{
						if(!(typeof(callback)==='undefined')){
							callback();
						}
					}
				}
			});
		}
	}
}( jQuery ));