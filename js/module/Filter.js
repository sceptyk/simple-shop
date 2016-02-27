var Filter = (function() {
	
	var include = {};
	include.Item = App.module.Item;
	include.Support = App.module.Support;
	include.View = App.view;
	
	var private = {};

	private.categories = [];

	private.filters = {
		state : "off",
		site : 1,
		persite : 20,
		categories : [],
		minprice : 0,
		maxprice : 0,
		tags : [],
		sort : 0
	};
	
	private.
	loadCategories = function(){
		Query.post(
			"get_categories",
			{}, 
			function(data) {
				private.categories = data;
				include.View.displayCategories( private.categories );
		});
	};
	
	var public = {};

	public.
	init = function() {
		private.loadCategories();
	};
	
	public.
	getFilters = function(){
		return private.filters;
	};
	
	public.
	getCategories = function(){
		return private.categories;
	};
	
	public.
	getCategory = function(id){
		var i = private.categories.length;
		while(i--){
			if(private.categories[i].id == id) return private.categories[i];
		}
		return null;
	};
	
	public.
	addCategoryToFilter = function(id){
		private.filters.categories.push( private.getCategory(id) );
	}
	
	public.
	removeCategoryFromFilter = function(id){
		var cats = private.fiters.categories;
		var i = cats.length;
		while(i--){
			if(cats[i].id == id) cats.splice(i, i+1);
		}
	};
	
	public.
	setTags = function(tags){
		private.filters.tags = tags;
	}
	
	public.
	setPrices = function(prices){
		private.filters.minprice = prices.min;
		private.filters.maxprice = prices.max;
	}

	return public;

})();
