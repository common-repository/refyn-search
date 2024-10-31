var settings = local_vals; //was sent from class-refyn-search.php
let web_url = document.location.origin; //website url
let unlock = settings["key"];
let no_found = settings["settings"]["no_found"];
let ai_settings = settings["settings"]["ai"];
let css_trigger = settings["settings"]["trigger"];
let popup = settings["settings"]["popup"];
let wp_call = null; //admin-ajax.php ajax call
let refyn_call = null; //api.refyn.org ajax call
let input_focus = "#refyn-input";
let overlay = "#Refyn-Overlay";

if (no_found == '')
	no_found = "Sorry, couldn't find anything.";

if (popup == 0){
	css_trigger = "[type='search']";
	input_focus = css_trigger;
	overlay = "#Refyn-Dropdown";
}

(function($){

	$(function(){ //document.ready

		function new_results(results){
			if (results.length == 1 && results[0]["category"] == "Products" && popup == 1) $("#refyn-form").attr("action",web_url + "/shop/");
			results.forEach(function(result){
				let htmlResult = '<div class="refyn-catagory panel panel-default border-bottom pb-3 pt-1">';
				htmlResult += add_catagory(result["category"]);
				if (result["value"][0]["thumbnail"] !== undefined){ //if the category items has pictures
					result["value"].forEach(function(item){
						htmlResult += add_item(item["title"],item["thumbnail"],item["value"],item["permalink"],item["display"]);
					});
				} else { //this is for buttons
					let buttons = '';
					result["value"].forEach(function(item){
						buttons += add_button(item["title"],item["permalink"]);
					});
					htmlResult += add_row(buttons);
				}
				htmlResult += '</div>';
				$("#refyn-results").append(htmlResult);
			});
		}

		function show_loading(){
			$("#refyn-credits").html(
								'<div class="row justify-content-center align-items-middle">\
									<div class="spinner-border" role="status">\
									  <span class="sr-only">Loading...</span>\
									</div>\
								</div>\
							');
		}

		function hide_loading(){
			$("#refyn-credits").html('Powered By <a href="https://www.refyn.org">\
													<img alt="Refyn" src="https://i0.wp.com/refyn.org/wp-content/uploads/2016/06/logo-refyn-210x48.png" style="width:55px">\
												 </a>');
		}
		
		try { $(".site-dialog-search").remove(); } catch (e) {} //remove old search from demo.refyn.org
		
		$(css_trigger).on("click", function(){ 
			$(overlay).slideDown("fast"); 
			if (popup == 0){
				$(overlay).detach().appendTo($(this).parent());
			} else {
				$(input_focus).focus();
			}
		});
		
		$(input_focus).on("focusout",function(){
			setTimeout(() => {
				$("#refyn-results").empty();
				$(overlay).slideUp("fast"); 
			},225); //delay so users can click <a> tag
		});
		
		$(input_focus).keyup( _.debounce(function(e){ //we use debounce just so we can wait for the user to finish typing
			
			if (popup == 1){
				$("#s").val($(input_focus).val()); //update hidden search value
				$("#refyn-form").attr("action",web_url);
			}
			
			if (e.keyCode == 27){ // Esc
				$("#refyn-results").empty();
				$(overlay).slideUp("fast");
				return;
			}
			
			//clear ajax call queue
			
			if (wp_call != null) try { wp_call.abort(); wp_call = null; } catch (e) {}
			if (refyn_call != null) try { refyn_call.abort(); refyn_call=null; } catch (e) {}
			hide_loading();
			
			//must be [0-9 || a-z || A-Z] or backspace
			
			if ( ($(input_focus).val().length >= 3) && ( 
				(e.keyCode>=48 && e.keyCode<=57) || 
				(e.keyCode>=65 && e.keyCode<=90) || 
				(e.keyCode>=97 && e.keyCode<=122) || 
				e.keyCode== 8 || 
				e.key == "Unidentified" ) ){ //Unidentified for mobile
				
				$("#refyn-results").empty();
				
				wp_call = $.ajax({
				type: 'GET',
				url: settings["wp_query"],
				dataType: "json",
				beforeSend: function() {
					show_loading();
				},
				data: { action : 'refyn_search', search : $(input_focus).val() },
				}).done(function(results){
					
					hide_loading();
					
					if (!$("#refyn-results").is(':empty')) return;
					
					if (results.length > 0) {
												
						new_results(results); //we found results
						
					} else { //if no results found we going to use refyn-search
						
						refyn_call = $.ajax({
						type: 'POST',
						url: 'https://api.refyn.org/get_suggestions.php',
						dataType: "json",
						beforeSend: function() {
							show_loading();
						},
						data: { url : web_url, key : unlock, settings : ai_settings, q : $(input_focus).val() },
						}).done(function(out){
							
							hide_loading();
							
							if (!$("#refyn-results").is(':empty')) return;
							if (out.length < 1){
								//no results message
								$("#refyn-results").html(
									'<div class="refyn-catagory panel panel-default border-bottom pb-3 pt-1">' + add_row(add_catagory(no_found)) + '</div>'
								)
							} else if (out.length > 1){
								let htmlResult = '<div class="refyn-catagory panel panel-default border-bottom pb-3 pt-1">';
								htmlResult += add_catagory("Suggestions");
								htmlResult += add_suggestions(out);
								htmlResult += '</div>';
								$("#refyn-results").append(htmlResult);
							} else {
								
								//if its one suggestion we going to just show the results
								
								$("#s").val(out[0]);
								if (wp_call != null) try { wp_call.abort(); wp_call = null; } catch (e) {}

								wp_call = $.ajax({
								type: 'GET',
								url: settings["wp_query"],
								dataType: "json",
								beforeSend: function() {
									show_loading();
								},
								complete: function() {
									hide_loading();
								},
								data: { action : 'refyn_search', search : out[0] },
								}).done(function(results){
									if (!$("#refyn-results").is(':empty')) return;
									
									if (results.length > 0) {
										new_results(results);
									} else {
										//no results message
										$("#refyn-results").html(
											'<div class="refyn-catagory panel panel-default border-bottom pb-3 pt-1">' + add_row(add_catagory(no_found)) + '</div>'
										);
									}
								}).fail(function(error){
									console.log(error);
								});
								
							}
						}).fail(function(error){
							console.log(error);
						});

					}
				
				}).fail(function(error){
					console.log(error);
				});
			
			}
		},500));

	});
	
})( jQuery );

//Helper Functions

function add_row(innerHTML){
	return '\
		<div class="refyn-item">\
			<div class="panel panel-default pt-2 pb-2 pl-3 pr-3">\
				<div class="panel panel-default">\
					<div class = "row">\
						<div class="col product-info">' + innerHTML + '</div>\
					</div>\
				</div>\
			</div>\
		</div>\
	';
}

function add_suggestions(suggestions){
	let buttons = '';
	suggestions.forEach(function(suggest){
		buttons += add_button(suggest,web_url +'?s=' + suggest);
	});
	return add_row(buttons)
}

function add_item(title,img,value,link,display){
	return '\
		<div class="refyn-item refyn-row">\
			<div class="panel panel-default pt-2 pb-2 pl-3 pr-3">\
				<a href="' + link +'" class="text-dark">\
					<div class="panel panel-default">\
						<div class = "row">\
							<div class = "col-3">\
								' + img +'\
							</div>\
							<div class="col product-info">\
								<span class="row font-weight-bold">' + title + '</span>\
								<span class="row pt-2 pb-2">' + display["content"] + '</span>\
								<span class="row text-primary">' + value +'</span>' + display["html"] +'\
							</div>\
						</div>\
					</div>\
				</a>\
			</div>\
		</div>\
	';
}

function add_catagory(cat){
	return '<span class="font-weight-bold pl-3 pr-3">' + cat + '</span>';
}

function add_button(text,link){
	return '<a href="' + link + '"><button type="button" class="btn btn-dark m-1"> ' + text + ' </button></a>';
}