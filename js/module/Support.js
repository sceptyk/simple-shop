var Support = (function(){
	
	var public = {};
	
	
	public.
	getOrderState = function(state){
		
		var text = "";
		state = parseInt(state);
		
		if(state == 0){
			text = "New order";
		}else if(state == 1){
			text == "Choosing items";
		}else if(state == 2){
			text = "Checkout request";
		}else if(state == 3){
			text = "In paying";
		}else if(state == 4){
			text = "Paid";
		}else if(state == 5){
			text = "Sent";
		}else if(state == 6){
			text = "Delivered";
		}
		
		return text;
	};
	
	public.
	getSortValue = function(name, desc){
		
		var sort = 0;
		
		if(name == "sort-price"){
			sort = 1;
			if(desc) sort++;
		}else if(name == "sort-name"){
			sort = 3;
			if(desc) sort++;
		}else if(name == "sort-date"){
			sort = 7;
			if(desc) sort++;
		}
		
		return sort;
	}
	
	return public;
})();
