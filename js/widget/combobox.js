$(document).ready(function() {

	var input = $("div.autocomplete input");
	var select = $("div.autocomplete div.select");
	var options = select.children(".option");

	var inputWidth = input.width();
	select.width(inputWidth);

	input.change(function() {
		console.log("onChange");
		//FIXIT if(!input.is(":focus")) select.hide();
	});

	input.keydown(function(e) {
		console.log("onKeydown");
		console.log(e.which);

		if (e.which == 40 || e.which == 38) {
			
			var highlight = -1;
			options.each(function(index){
				if($(this).hasClass("highlight")){
					highlight = index;
				}
			});
			
			if(highlight != -1){
				
				var opt = options.eq(highlight);
				opt.removeClass("highlight");
				
				if(e.which == 38){
					
					console.log("keydown");
					
					do{
						highlight--;	
					}while(highlight >= 0 && opt.eq(highlight).is(":visible"));
					
					if(highlight >= 0){
						opt = options.eq(highlight);
						opt.addClass("highlight");
						input.val(opt.attr("value").toString());
					}else{
						input.val(input.attr("primary").toString());
					}
					
				}else{
					
					console.log("keyup");
					
					do{
						highlight++;	
					}while(highlight < options.size() && opt.eq(highlight).is(":visible"));
					
					if(highlight >= options.size()){
						highlight = options.size() - 1;
					}
					opt = options.eq(highlight);
					opt.addClass("highlight");
					input.val(opt.attr("value").toString());
				}
				
				
			}else{
				if(e.which == 40){
					
					console.log("keydown from input");
					
					input.attr("primary", input.val());
					highlight = highlight + 1;
					if(highlight >= options.size()){
						highlight = options.size() - 1;
					}
					opt = options.eq(highlight);
					opt.addClass("highlight");
					input.val(opt.attr("value").toString());
				}
			}
		}

	});

	input.keyup(function() {

		var text = $(this).val();
		console.log(text + " " + select.is(":visible"));
		if (text != "" && !select.is(":visible")) {

			select.show();
			console.log("open");

		} else if (text == "" && select.is(":visible")) {

			select.hide();
			console.log("close");

		}
		
		options.each(function() {
			var val = $(this).attr("value").toString().toLowerCase();
			console.log(text + " " + val);
			if (val.indexOf(text) < 0 || val == text){
				$(this).addClass("hidden");
			}else{
				$(this).removeClass("hidden");
			}
		});

	});
	
	select.on("click", ".option", function(){
		var value = $(this).attr("value").toString().toLowerCase();
		console.log("combobox" + value);
		input.val(value);
		select.hide();
	});
	
	//TODO input loses focus options hides
	//TODO arrows down up

});
