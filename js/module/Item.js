var Item = (function() {

	var include = {};
	include.Query = App.module.Query;
	include.Filter = App.module.Filter;
	include.Cart = App.module.Cart;
	include.View = App.getView("Admin");

	var private = {};

	private.items = [];
	
	var public = {};
	
	public.
	init = function(){
		
	};
	
	/**
	 * Opens item details in frame
	 * @requires "ViewFrame"
	 */
	public.
	details = function(id){
		var item = public.getItem(id);
		App.openView( "Frame", {
			action : "itemdetails",
			extra : item
		});
	};
	
	public.
	staticLoadItems = function(){
		
		if(private.items.length == 0){
			public.loadItems();
		}else{
			includ.View.displayItems( private.items );
		}
		
	};
	
	public.
	loadItems = function() {

		Query.post(
			"get_products", {
			filters : include.Filter.getFilters(),
			},
			function(data) {
				private.items = data;
				include.View.displayItems( private.items );
			}
		);

	};
	
	/**
	 * Returns items
	 */
	public.
	getItems = function() {
		return private.items;
	};

	/**
	 * Sets items
	 */
	public.
	setItems = function(items) {
		private.items = items;
	};

	/**
	 * Adds new item
	 */
	public.
	addLocal = function(item){
		private.items.push(item);
	}
	
	public.
	addItem = function(item){
		
		include.Query.post({
			"add_product",
			item,
			function(result) {
				
				include.View.cleanForm();
			});
		});
	};


	/**
	 * Retrieves item
	 */
	public.
	getItem = function(id) {

		var i = private.
		items.length
		while (i--) {
			if(private.items[i].id == id) return private.
			items[i];
		}
		return null;
	};
	
	public.
	updateLocal = function(item){
		
		var i = private.items.length;
		while(i--){
			if(private.items[i].id == item.id) private.items[i] = item;
		}
		
	};
	
	public.
	updateItem = function(item){
		
		public.updateLocal(item);
		
		include.Query.post("update_product", {
			product : {
				id : item.id,
				name : item.name,
				price : item.price,
				description : item.description,
				highlight : item.highlight
			}
		});
		
	};
	
	public.
	deleteLocal = function(id){
		
		var i = private.items.length;
		while(i--){
			if(private.items[i].id == id) private.items.slice(i, i+1);
		}
		
	};
	
	public.
	deleteItem = function(id, callback){
		
		deleteLocal(id);
		
		include.Query.post("delete_product", {
			id : id
		},
		function(data) {
			if(typeof callback !== undefined)
				callback();
		});
		
	}
	
	public.
	getImagePath = function( src ){
		return "data/" + img;
	};
	
	public.
	getShortDescription = function(desc, length) {

		if (desc.length < length)
			return desc;

		//crop
		var desc = desc.substr(0, length);

		//find last word
		var LAST = false;
		for (var i = desc.length - 1; i >= 0 && !LAST; i--) {
			if (desc[i] == " ")
				LAST = true;
		}

		//append more
		desc += "...";

		return desc;
	};

	return public;
})();
