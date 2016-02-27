var ViewShop = (function(){
	
	var include = {};
	
	var private = {};
	
	private.settings = {
		columns : 3,
		smallScreen : 992,
		
		button : {
			enlarge : "Details",
			buy : "Add to cart"
		}
	};
	
	private.
	filtersEvents = function(){
		
		/**
		 * Handles switch filter box 
		 */
		public.header.on('click', 'div#open-filter-panel', function() {

			public.toggle($("div#filter-panel-content"));
		});
		
		/**
		 * Handles search action 
		 */
		public.header.on('click', "button#search", function() {

			var phrase = public.header.find("input#search").val().toString();
	
			//retrieve tags (remove special characters and split string by space)
			var tags = phrase.replace(/[\.,-\/#!$%\^&\*;:{}=\-_`~()]/g, "").split(" ");
	
			include.Filter.setTags( tags );
			include.Item.loadItems();
		});
		
		/**
		 * Handles category action 
		 */
		public.header.on('click', "div.category", function() {

			$(this).toggleClass("highlight");
			var id = $(this).attr("id").toString();
	
			if (!$(this).hasClass("highlight")) {
				include.Filter.removeCategoryFromFilter(id);
			} else {
				include.Filter.addCategoryToFilter(id);
			}
	
			private.filters.categories = cats;
			include.Item.loadItems();
		});
		
		/**
		 * Handle price filtering 
		 */
		public.header.on('click', "button.price", function() {

			var min = parseInt(include.View.header.find("input#minprice").val());
			var max = parseInt(incldue.View.header.find("input#maxprice").val());
			
			var prices = {
				min : min,
				max : max
			}
			include.Filter.setPrices(prices);
			include.Item.loadItems();
		});
		
		/**
		 * Handles sort filtering 
		 */
		public.header.on('click', "button.sort-button", function() {

			var sort = include.Support.getSortValue($(this).attr("id"), $(this).hasClass("desc"));
	
			include.View.header.find("button.sort-button").removeClass("clicked");
			$(this).addClass("clicked");
			$(this).toggleClass("desc");
	
			private.filters.sort = sort;
			include.Item.loadItems();
		});
	};
	
	private.
	cartEvents = function(){
		
		/**
		 * Handle switching cart 
		 */
		public.header.on('click', 'div#trolley img', function() {

			$("div#trolley-content").toggleClass("hidden");
	
		});
		
		/**
		 * Handles switch off by clicking outside of cart box 
		 */
		$(window).click(function(event) {

			if (!$(event.target).is("div#trolley img")) {
				$("div#trolley-content").hide();
			}
	
		});
		
		/**
		 * Handles removing item from cart 
		 */
		public.header.on('click', 'div.trolley-item .close', function() {
			var id = $(this).parent().attr("id").toString();
			include.Cart.remove(id);
		});
		
		/**
		 * Handles checkout action 
		 */
		public.header.on('click', 'div#trolley-checkout', function() {

			include.Cart.checkout();
	
		});
		
	};
	
	private.
	itemsEvents = function(){
		
		public.stage.on('click', 'span.shop-item-enlarge', function() {
			var id = $(this).parent().attr("id").toString();
			include.Item.details(id);
		});
		
		public.stage.on('click', 'span.shop-item-buy', function() {
			public.clickButton($(this));
			
			var id = $(this).parent().attr("id").toString();
			include.Cart.addItem(include.Item.getItem(id));
	
		});
	};
	
	private.
	toggle = function(element) {

		if (element.hasClass("hidden")) {
			element.hide(function() {
				element.toggleClass("hidden");
				element.show();
			});

		} else {
			element.hide(function() {
				element.toggleClass("hidden");
			});
		}
	};
	
	private.
	createGridView = function(){
		
		//check for small devices
		var ww = $(window).width();
		if(ww < private.settings.smallScreen) private.settings.columns = 1;
		
		var cols = private.settings.columns;
		var columnWidth = Math.floor(100 / cols);
		var itemWidth = Math.floor(columnWidth * 0.9);
		var margin = Math.floor((columnWidth - itemWidth) / 2);

		//hide scrollbar
		private.stage.css("height", "auto");

		//END style
		
		//clean stage for loading new view
		private.stage.empty();

		var column = {};
		var columns = [];
		for (var i = 0; i < cols; i++) {
			column = $("<div>", {
				class : "shop-column"
			}).css({
				width : itemWidth + "%",
				padding : margin + "%"
			});
			columns.push(column);
		}

		for (var i = 0; i < columns.length; i++) {
			private.stage.append(columns[i]);
			//console.log("add column");
		}

		var items = private.items;
		//console.log(private);

		var addItem = function(i) {

			if (i >= items.length)
				return;

			//find the shortest column
			var shortest = 0;
			for (var j = 1; j < columns.length; j++) {
				//console.log("Shortest column: " + columns[j].height());
				if (columns[j].height() < columns[shortest].height()) {
					shortest = j;
				}
			}

			var item = items[i];
			//console.log(item);

			var newitem = $('<div>', {
				id : item.id,
				class : "shop-item" + (item.highlight == 1 ? " shop-item-highlight" : "")
			}).hide();

			newitem.attr("index", i);

			var img = $('<img>', {
				src : getItemImageSrc(item.images[0])
			}).load(function() {
				//console.log("Image height: " + $(this).height());
				
				columns[shortest].append(newitem);
				newitem.fadeIn("fast");
				//console.log("shortest col: " + columns[shortest].height());

				//console.log("new item " + i);
				//console.log(columns[shortest]);

				addItem(++i);
			}).appendTo(newitem);
			
			var oldprice = item.discount;
			var iteminfo = "<div class='shop-item-info'>"
					+ "<span class='shop-item-title'>" + item.name + "</span>"
					+ "<span class='shop-item-price" + (oldprice > 0 ? "shop-item-discount" : "") + "'>"
					+ (oldprice > 0 ? "<span class='shop-item-oldprice'>" + oldprice + "</span>" : "")
					+ "<span class='shop-item-description'>" + include.Item.getShortDescription(item.description, 150) + "</span>"
					+ "<span class='shop-item-enlarge shop-item-button'>" + private.settings.button.enlarge + "</span>"
					+ "<span class='shop-item-buy shop-item-button'>" + private.settings.button.buy + "</span>"
				+ "</div>"
				+ "<div class='clear'></div>";
				
			newitem.append(iteminfo);

		}
		addItem(0);

		private.stage.append("<div class='clear'></div>");
		
	};
	
	var public = {};
	
	public.shop = $("div#shop-content");
	public.stage = public.shop.children("#shop-view").eq(0);
	public.header = public.shop.children("#shop-header").eq(0);
	public.cart = public.header.children("#trolley-content").eq(0);
	
	public.
	preinit = function(){
		App.action( "home", function(){
			App.openView();
		});
	};
	
	public.
	init = function(){
		App.loadModule( "Query", Query );
		App.loadModule( "Filter", Filter );
		App.loadModule( "Support", Support );
		App.loadModule( "Item", Item );
		App.loadModule( "Cart", Cart );
		App.loadModule( "User", User );
		
		App.loadStyle( "/shop/style" );
		
		
	};
	
	public.
	onStart = function(){
		
		include.Filter = App.module.Filter;
		include.Item = App.module.Item;
		include.Support = App.module.Support;
		include.Cart = App.module.Cart;
		
		App.loadHTML( "content", "/shop/content" );
		App.loadHTML( "header", "/shop/header" );
		
	};
	
	public.
	onReady = function(){
		
		public.shop = $("div#shop-content");
		public.stage = public.shop.children("#shop-view").eq(0);
		public.header = public.shop.children("#shop-header").eq(0);
		public.cart = public.header.children("#trolley-content").eq(0);
		
		private.filtersEvents();
		private.cartEvents();
		private.itemsEvents();
		
		include.Item.loadItems();
		
	};
	
	public.
	displayCategories = function(categories){
		var container = public.header.find("div.categories");
			
		for (var i = 0, var length = categories.length; i < length; i++) {
			var category = categories[i];
	
			var div = $("<div>", {
				id : category.id,
				class : "category"
			}).text(category.name).appendTo( container );
		}
	};
	
	public.
	displayItems = function(items){
		private.createGridView();
	};
	
	public.
	displayCartItem = function(item){
		var trolleyitem = "<div class='trolley-item' id='" + item.id + "'>"
			+ "<div class='photo-thumb'>"
				+ "<img src='" + include.Item.getImagePath(item.images[0]) + "'>"
			+ "</div>"
			+ "<div class='title'>" + item.name + "</div>"
			+ "<div class='price'>" + "&euro;" + item.price + "</div>"
			+ "<div class='close'></div>"
			+ "</div>";

		public.header.children("#trolley-content").append(trolleyitem);
		
	};
	
	public.
	displayCartSum = function(sum){
		$("div#trolley-amount").html("&euro;" + sum);
	};
	
	public.
	removeCartItem = function(itemid){
		private.cart.find(".trolley-item#" + itemid).remove();
	};
	
	public.
	clickButton = function(button){
		button.css("opacity", 0.5).fadeTo("slow", 1);
	};
	
	return public;
})();
