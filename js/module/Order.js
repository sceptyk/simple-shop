var Order = (function(){
	
	var include = {};
	include.Query = App.module.Query;
	include.View = App.getView();
	
	var private = {};
	
	private.orders = [];
	
	var public = {};
	
	public.
	staticLoadOrders = function(){
		
		if( private.orders.length == 0 ){
			public.loadOrders();
		}else{
			include.View.displayOrders( private.orders );
		}
		
	};
	
	public.
	loadOrders = function(){
		include.Query.post("get_orders", {},
		function(data) {
			private.orders = data;
			include.View.displayOrders( private.orders );
		});
	};
	
	return public;
})();
