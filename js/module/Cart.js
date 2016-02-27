var Cart = (function(){
	
	//TODO move transaction from server $_SESSION to localSession (current contetn of cart)
		
	var include = {};
	include.Query = App.module.Query;
	include.View = App.view;
	
	var private = {};
	private.items = [];
	
	private.
	getCurrentOrder = function(){
		
		include.Query.post(
			"get_current_order", {}, 
			function(data) {

				var items = data.items;
				var amount = data.amount;
		
				include.View.displayCartSum(amount);
		
				for (var i = 0, length = items.length; i < length; i++) {
					var item = items[i];
					include.View.displayCartItem(item);
				}
		});
		
	}
	
	private.
	getOrderConfirmation = function(){
		include.Query.post(
			"get_confirm", {},
			function(data) {

				App.openView( "Frame", {
					action : "confirmorder",
					extra : data
				});
			}
		);
	};
	
	private.
	handleCofirm = function(){
		
		var container = App.getView("Frame").getContainer;
		container.
		
	};
	
	var public = {};
	
	public.
	init = function(){
		private.getCurrentOrder();
		private.getOrderConfirmation();
		
		private.handleSwitch();
		private.handleSwitchOff();
		private.handleRemove();
		private.handleCheckout();
		private.handleUpdateData();
		private.handleConfirm();
	};
	
	public.
	add = function(item){
		include.View.displayCartItem(item);
		include.Query.post(
			"add_to_order",
			{
				id : item.id
			},
			function(data){
				include.View.displayCartSum(data);
			}
		);
	};
	
	public.
	remove = function(itemid){
		include.Query.post(
			"delete_from_order",
			{
				id : itemid
			},
			function(data){
				include.View.displayCartSum(data);
				include.View.removeCartItem(itemid);
			}
		)
	};
	
	public.
	checkout = function(missing){
		include.Query.post(
			"checkout", {
				missing : missing
			}, 
			function(data) {
				
				var miss = data.missing;
				if (miss) {
					App.openView( "Frame", {
						action : "precheckout",
						extra : miss
					});
				} else {
					include.Query.redirect(data.url);
					include.View.header.off('click', 'div#trolley-checkout');
				}

		});
	};
	
	public.
	confirm = function(){
		
		var transaction = include.Query.urlParam("paypal");
		var token = include.Query.urlParam("token");
		var payerid = include.Query.urlParam("payerid");
	
		include.Query.post("pay", {
			paypal : {
				transaction : transaction,
				token : token,
				payerid : payerid
			}
		}, function(data) {

				//TODO remember me for next purchase, register me, finish
	
			if (data.request) {
				registerForm();
			}
	
		});
		
	};
	
	return public;
})();
