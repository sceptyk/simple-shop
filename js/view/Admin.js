var ViewAdmin = (function(){
	
	var include = {};
	
	var private = {};
	
	private.
	formItemsEvents = function(){
		
		/**
		 * Save new item to databse 
		 */
		public.container.on('click', "button#submit", function() {
				
			var imgs = Array();
			public.container.find("div#added-photos").children("div.photo-thumb").each(function() {
				var img = $(this).children("canvas.original")[0].toDataURL();
				imgs.push(img);
	
				$(this).remove();
			});
	
			var tags = Array();
			public.container.find("div#added-tags").children("div.tag").each(function() {
				var tag = $(this).children("span").html().toString();
				tags.push(tag);
	
				$(this).remove();
			});
			
			var title = public.container.find("input#title");
			var price = public.container.find("input#price");
			var description = public.container.find("textarea#description");
			var category = public.container.find("input#category");
			var digitalPop = public.container.find("input#digital")
			var digital = digitalPop.is(":checked") ? 1 : 0;
			var product = "";
			if(digital == 1){
				product = public.container.find("div#file-product-container").text().toString();
			}
			var productNote = public.container.find("span#file-product-note");
			
			var item =  {
				title : title.val().toString(),
				price : price.val().toString(),
				description : description.val().toString(),
				category : category.val().toString(),
				digital : digital,
				product : product,
				imgs : imgs,
				tags : tags
			}
			
			public.formLoaderStart();	
			include.Item.addItem(item);
		});
		
		
		/**
		 * Handles adding tags 
		 */
		public.container.on('keydown', "input#add-tag", function(e) {
			if (e.which == 13 || e.which == 9) {
				var text = $(this).val();
				text = text.replace(/\s+/g, '');
				var div = $("<div class='tag'></div>");
				div.empty().append("#");
				div.append("<span>" + text + "</span>");
				div.append("<div class='close'></div>");
				$("div#added-tags").prepend(div);
				$(this).val("");
			}
		});
		
		/**
		 * Handles removing image and tag thumbs 
		 */
		public.container.on('click', 'div.close', function() {
			$(this).parent().remove();
		});
		
		/**
		 * Handles invoking photo picker 
		 */
		public.container.on('click', "button#invoke-file", function() {
			public.container.find("input#load-file").click();
		});
		
		/**
		 * Handles invoking file (product) picker 
		 */
		public.container.on('click', "button#invoke-file-product", function(){
			public.container.find("input#load-file-product").click();
		});
		
		/**
		 * Handles choosing type of product (digital)
		 */
		public.container.on('change', "input#digital", function(){
			include.View.hideDigitalProduct($(this).is(":checked"));
		});
		
		
	};
	
	/**
	 * 
	 */
	private.
	previewItemsEvents = function(){
		
		/**
		 * Handles update of product 
		 */
		public.container.on('click', "button.update", function(){
		
			var element = $(this).parents("div.item-row");
			
			var item = {
				id : element.attr("id").toString(),
				name : element.find("div.name").text().toString();
				price : element.find("div.price").text().toString();
				description : element.find("div.description").text().toString();
				highlight : element.find("input.highlight").is(":checked") ? 1 : 0;
			}
			
			include.Item.updateItem(item);
		});
		
		/**
		 * Handles deleting element 
		 */
		public.container.on('click', "button.delete", function(){
		
			var element = $(this).parents("div.item-row");
			var itemid = element.attr("id").toString();
			include.Item.deleteItem(id, function(){
				element.remove();
			});
		});
		
	};
	
	/**
	 * 
	 */
	private.
	previewOrdersEvents = function(){
		
	};
	
	/**
	 * 
	 */
	private.
	formSettingsEvents = function(){
		
		public.container.on('click', 'div#shop-settings button.save-settings', function(){
			
			var fields = [
				public.container.find("input#shopname"),
				public.container.find("input#cemail"),
				public.container.find("input#memail")
			];
			
			include.Settings.saveSettings( fields );
		});
		
		public.container.on('click', 'div#paypal-settings button.save-settings', function(){
			
			var fields = [
				public.container.find("input#payuser"),
				public.container.find("input#paypwd"),
				public.container.find("input#paysign"),
				public.container.find("input#payver")
			];
			
			include.Settings.saveSettings( fields );
			
		});
		
		public.container.on('click', 'div#user-settings button.save-settings', function(){
			
			var user = {
				name : $("input#user-name").val().toString(),
				pass : $("input#user-pass").val().toString(),
				email : $("input#user-email").val().toString()
			};
			
			include.User.updateUser( user );
		});
		
		public.container.on('click', 'div#newuser-settings button.save-settings', function(){
			
			var user = {
				name : $("input#new-user-name").val().toString(),
				pass : $("input#new-user-pass").val().toString(),
				email : $("input#new-user-email").val().toString(),
				level : $("select#new-user-level").val().toString()
			};
			
			include.User.addNewUser( user );
			
		});
	};
	
	private.
	menuActions = function(href){
		if(href == "product-add"){
			include.Form.loadForm("Admin", "Product");
			
		}else if(href == "product-manage"){
			include.Item.staticLoadItems();
			
		}else if(href == "order"){
			include.Order.staticLoadOrders();
			
		}else if(href == "settings-general"){
			include.Form.loadForm("Admin", "SettingsShop");
			
		}else if(href == "settings-paypal"){
			include.Form.loadForm("Admin", "SettingsPaypal");
			
		}else if(href == "settings-user"){
			include.Form.loadForm("Admin", "SettingsUser");
			
		}else if(href == "settings-new-user"){
			include.Form.loadForm("Admin", "SettingsNewUser");
			
		}
	};
	
	private.
	menuEvents = function(){
		
		public.container.on('click', 'a', function(){
			var hash = $(this).attr("href").substring(1); //delete #
			hash = hash.split("/")[1];
			
			private.menuActions(hash)
		});
		
	};
	
	var public = {};
	
	public.container = $("div#content .ajax-content");
	public.menuContainer = $("div#content .menu-content");
	
	public.
	preinit = function(){
		
		App.setAction( "admin", function( extra ){
			
			if( typeof extra === undefined){
				App.openView("Admin");
				
			}else{
				private.menuActions( extra );
				
			}
			
		});
		
	};
	
	public.
	init = function(){
		
		App.loadModule( "Query", Query );
		App.loadModule( "Support", Support );
		App.loadModule( "Filter", Filter );
		App.loadModule( "Item", Item );
		App.loadModule( "User", User );
		App.loadModule( "Panel", Order );
		App.loadModule( "Settings", Settings );
		
		App.loadStyle( "/admin/style" );
		
	};
	
	public.
	onStart = function(){
		
		include.User = App.module.User;
		include.Support = App.module.Support;
		include.Filter = App.module.Filter;
		include.User = App.module.User;
		include.Panel = App.module.Panel;
		include.Settings = App.module.Settings;
		
		if( !include.User.checkUser() ){
			App.openView("Frame", {
		 		action : "login"
		 	});
		}else{
			App.loadHTML( "content", "/admin/content", public.onReady);
		}
	};
	
	public.
	onReady = function(loader){
		
		public.container = $("ajax-content");
		public.menuContainer = $("menu-content");
		
		private.previewOrdersEvents();
		private.previewItemsEvents();
		private.formSettingsEvents();
		private.formItemsEvents();
		private.menuEvents();
		
	}
	
	public.
	displayMenu = function(menu){
		public.menuContainer.empty().append(menu);
	};
	
	public.
	displayView = function(view){
		
		public.container.empty().append(view);
	};
	
	public.
	displayPanel = function(){
		
		include.Form.loadMenu("Admin", "Main")
		include.Form.loadForm("Admin", "Product");
	};
	
	public.
	formLoaderStart = function(){
		var loader = new Loader($("#loader"));
		loader.start();
		public.container.find("form").hide();
	};
	
	public.
	formLoaderStop = function(){
		//TODO loader.stop(); 
		public.container.find("form").show();
	};
	
	public.
	cleanForm = function(){
		var form = public.container.find("form");
		form.children("input").each(function(){
			$(this).val("");
		});
		form.children("textarea").each(function(){
			$(this).val("");
		});
		
		form.children(".note").each(function(){
			$(this).text("");
		});
	};
	
	public.
	displayCategories = function(categories){
		
		for(var i=0; length = categories.length;i<length;i++){
			var name = categories[i].name;
			var div = "<div class='option' value='" + name + "'>" + name +"</div>";
			public.container.find("div#category-hints.select").append(div);
		}
	};
	
	public.
	hideDigitalProduct = function(checked){
		var container = public.container.find("div#digital-product");
		if(checked){
			container.fadeIn("fast");	
		}else{
			container.fadeOut("fast");
		}
	};
	
	public.
	notifyUploading = function(text){
		var note = public.container.find("span#file-product-note");
		note.text(text);
		note.stop().fadeIn("fast").delay(1000).fadeOut("fast");
	};
	
	public.
	addPhotoPreview = function(canvasThumb, canvasOriginal){
		
		var div = $("<div class='photo-thumb'>");
			div.append(canvasThumb);
			div.append(canvasOriginal);
			div.append("<div class='close'></div>");
		
		public.container.find("div#added-photos").prepend(div);
	};
	
	public.
	displayItems = function(items){
		
		public.displayView("");
		var src = App.getHtmlPath() + "/admin/item.html";
		$.get(src, function(div){
			for(var i=0, length=items.length; i<length; i++){
				var item = items[i]
				var divItem = div;
					
				divItem.find(".item-row").attr("id", item.id);
				divItem.find(".name").text(item.name);
				divItem.find(".price").text(item.price);
				parseInt(item.highlight) == 1 ? divItem.find(".highlight").prop();
				divItem.find(".amount").text(item.amount);
				divItem.find(".description").text(item.description);	
				parseInt(item.active) == 1 ? divItem.find(".activate").addClass("active");
					
				var tags = item.tags;
				var tagsDiv = itemDiv.find(".tags");
				for(var j=0,length=tags.length;j<length;j++){
					tagsDiv.append("<div class='tag'>"
						+ "<span>"
							+ tags[j]
						+ "</span>"
					+ "</div>");
				}
				
				var images = item.images;
				var photosDiv = divItem.find(".photos");
				for(var j=0,length=images.length;j<length;j++){
					photosDiv.append("<div class='photo'>"
					+ "<img src='" + include.Item.getImagePath(images[j]) + "'>"
					+ "</div>");	
				}
				photosDiv.append("<div class='clear'></div>");
				public.container.append(divItem);
			}
			public.container.append("<div class='clear'></div>");
		});
		
	};
	
	public.
	displayOrdersView = function(orders){
		var divItem = "<div class='order-row'>"
					+  "<div class='row'>"
						+  "<div class='column'>"
							+  "<h4>Name</h4>"
						+  "</div>"
						+  "<div class='column'>"
							+  "<h4>Email</h4>"
						+  "</div>"
						+  "<div class='column'>"
							+  "<h4>Order state</h4>"
						+  "</div>"
					+  "</div>"
				+ "</div>";
			 
			 for(var i=0;i<orders.length;i++){
				var order = orders[i];
				
				divItem += "<div class='order-row'>"
					+  "<div class='basic row'>"
					+  "<div class='column'>"
					+  "<div class='user-data name'>"
					+  order.name
					+  "</div>"
					+  "</div>"
					+  "<div class='column'>"
					+  "<div class='user-data email'>"
					+  "<a href='mailto:"
					+  order.email
					+  "'>Send a message</a>"
					+  "</div>"
					+  "</div>"
					+  "<div class='column'>"
					+  "<div class='user-data state'>"
					+  Util.getOrderState(order.state)
					+  "</div>"
					+  "</div>"
					+  "</div>"
					+  "<div class='extended'>"
					+  "<div class='items'>";
					
					var items = order.items;
					for(var j=0;j<items.length;j++){
						var item = items[j];
						console.log(item);
						divItem += "<div class='item'>"
						+  "<img src='../data/"+ item.url + "'>"
						+  "<span>"
						+  item.name
						+  "</span>"
						+  "</div>";
					}
					
				divItem +=  "</div>"
					+  "</div>"
					+ "</div>";
			}
			
			public.displayView(divItem);
	};
	
	return public;
})();
