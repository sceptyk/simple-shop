App.view.Frame = (function(){
	
	var include = {};
	
	var private = {};
	
	private.bundle;
	
	private.frame = $("div.frame");
	private.content = $("div.frame div.frame-content");
	
	private.
	loadEvents = function(){
		
		/**
		 * Handles updating action 
		 */
		private.frame.on('click', 'button#update-missing', function() {

			var frame = private.content;
	
			var missing = {};
	
			var name = frame.find("input#name");
			if (name.length > 0) {
				missing.name = name.val().toString();
			}
	
			var address = frame.find("input#street1");
			if (address.length > 0) {
				address = encodeURIComponent(address.val().toString());
				address += ";";
				address += encodeURIComponent(frame.find("input#street2").val().toString());
				address += ";";
				address += encodeURIComponent(frame.find("input#code").val().toString());
				address += ";";
				address += encodeURIComponent(frame.find("input#city").val().toString());
				address += ";";
				address += encodeURIComponent(frame.find("input#country").val().toString());
	
				missing.address = address;
			}
	
			var email = frame.find("input#email");
			if (email.length > 0) {
				missing.email = email.val().toString();
			}
	
			var phone = frame.find("input#phone");
			if (phone.length > 0) {
				missing.phone = phone.val().toString();
			}
	
			include.Cart.checkout(missing);
	
		});
		
		/**
		 * Handles confirming order 
		 */
		private.frame.on('click', 'button#confirm', function() {

			include.Cart.confirm();
	
		});
		
		private.frame.on('click', 'button#login', function(){
			
			var username = private.frame.find("input.login-name").val();
			var userpass = private.frame.find("input.login-pass").val();
			
			App.getModule("User").login(username, userpass);
			
		});
		
	};
	
	private.
	precheckout = function(miss){
		var div = "<div class='user-details'>" + "<h2>We need just some of your details</h2>" + "<div class='form'>";

					console.log(miss);
					//array
					if (miss.name) {
						div += "<div class='form-field'>" + "<label for='name'>Receiver name: </label>" + "<input type='text' id='name' name='name'>" + "</div>";

						anything_missing = true;
					}
					if (miss.email) {
						div += "<div class='form-field'>" + "<div class='note'>This is the email on which we will send your digital goods after a purchase and contact with you</div>" + "<label for='email'>Email: </label>" + "<input type='email' id='email' name='email'>" + "</div>";

						anything_missing = true;
					}
					if (miss.address) {
						div += "<div class='form-field'>" + "<div class='note'>Your shipping details</div>" + "<label for='street1'>Street: </label>" + "<input type='text' id='street1' name='street1'>" + "<label for='street2'>Street: </label>" + "<input type='text' id='street2' name='street2'>" + "<label for='code'>Code: </label>" + "<input type='text' id='code' name='code'>" + "<label for='city'>City: </label>" + "<input type='text' id='city' name='city'>" + "<label for='country'>Country: </label>" + "<input type='text' id='country' name='country'>" + "</div>";

						anything_missing = true;
					}
					if (miss.phone) {
						div += "<div class='form-field'>" + "<div class='note'>How could we contact you if something went wrong?</div>" + "<label for='phone'>Phone: </label>" + "<input type='text' id='phone' name='phone'>" + "</div>";

						anything_missing = true;
					}

					div += "</div>" + "<div class='form-field'>" + "<button id='update-missing'>Proceed</button>" + "</div>" + "</div>";
			return div;
	};	
	
	private.
	login = function(){
		var div = "<div class='form login-form'>"
			+ "<div class='header'>"
				+ "<h2>Login</h2>"
			+ "</div>"
			+ "<div class='fields'>"
				+ "<div class='field'>"
					+ "<label for='name'>Name:</label>"
					+ "<input type='text' name='name' class='frame-form login-name' />"
				+ "</div>"
				+ "<div class='field'>"
					+ "<label for='pass'>Password:</label>"
					+ "<input type='password' name='pass' class='frame-form login-pass' />"
				+ "</div>"
				+ "<div class='field'>"
					+ "<label for='remember'>Remember me:</label>"
					+ "<input type='checkbox' name='remember' class='frame-form login-remember' />"
				+ "</div>"
			+ "</div>" 
			+ "<div class='buttons'>"
				+ "<button class='frame-form' id='login'>Login</button>"
			+ "</div>"
		+ "</div>";

		return div;
	};
	
	private.
	loginevents = function(){
		
		private.frame.on('click', "button.frame-form#login", function(){
			
			var name = private.frame.find("input.login-name").val().toString();
			var pass = private.frame.find("input.login-pass").val().toString();
			var remember = private.frame.find("input.login-remember").is(":checked");
			
			App.getModule("User").login(name, pass, remember);
			
		});
	};
	
	private.
	register = function(){
		var div = "<div class='form register-form'>"
			+ "<div class='header'>"
				+ "<h2>Register</h2>"
			+ "</div>"
			+ "<div class='fields'>"
				+ "<div class='field'>"
					+ "<label for='name'>Name:</label>"
					+ "<input type='text' name='name' class='frame-form' />"
				+ "</div>"
				+ "<div class='field'>"
					+ "<label for='email'>Email:</label>"
					+ "<input type='text' name='email' class='frame-form' />"
				+ "</div>"
				+ "<div class='field'>"
					+ "<label for='pass'>Password:</label>"
					+ "<input type='password' name='pass' class='frame-form' />"
				+ "</div>"
			+ "</div>"
			+ "<div class='buttons'>"
				+ "<button class='frame-form'>Register</button>"
				+ "<button class='frame-form'>Skip</button>"
			+ "</div>"
		+ "</div>";
		
		return div;
	};
	
	private.
	confirmorder = function(extra){
		var items = extra.items;
		var details = extra.orderdetails;
		
		var div = "<div id='confirm'>"
		+ "<h2>Confirm your order</h2>"
		+ "<div class='order'>"
			+ "<div class='column'>" 
				+ "<h3>Your order</h3>"
					+ "<div class='order-list'>";
	
		for (var i = 0, length = items.length; i < length; i++) {
			var item = items[i];
			div += "<div class='order-item'>"
					+ "<div class='item-detail title'>" + item.name + "</div>"
					+ "<div class='item-detail price'>" + "&euro;" + item.price + "</div>"
				+ "</div>";
		}

		div += "</div>" + "</div>"
		+ "<div class='column'>"
		+ "<h3>Your details</h3>"
		+ "<div class='order-details'>"
			+ "<div class='client client-name'>"+ details.name + "</div>";

		if (details.email != null)
			div += "<div class='client client-email'>" + details.email + "</div>";

		if (details.address != null)
			div += "<div class='client client-address'>" + str_replace(";", "<br>", decodeURIComponent( details.address )) + "</div>";
		
		if (details.phone != null)
			div += "<div class='client client-phone'>" + details.phone + "</div>";

		div += "</div>" + "</div>" + "</div>"
			+ "<div class='clear'></div>"
			+ "<div class='button-wrapper'>"
				+ "<button id='confirm'>"+ "Confirm and pay" + "</button>"
			+ "</div>"
		+ "</div>";
		
		return div;
	};
	
	private.
	itemdetails = function(){
		var getImageItemSrc = include.Item.getImagePath;
		
		var div = "<div class='photo-container'>";
		div += "<div class='photo-big'>";

		var bigImage = item.images[0];
		div += "<img src='" + getItemImageSrc(bigImage) + "'>";

		div += "</div>";
		div += "<div class='photo-preview'>";
		div += "<div class='photo-preview-wrapper'>";

		var images = item.images;
		for (var i = 0; i < images.length; i++) {
			var image = images[i];
			if (i == 0) {
				div += "<img class='marked' src='" + getItemImageSrc(image) + "'>";
			} else {
				div += "<img src='" + getItemImageSrc(image) + "'>";
			}
		}

		div += "</div>"+ "</div>" + "</div>"
			+ "<div class='text-container'>"
				+ "<div class='title'>" + item.name + "</div>"
				+ "<div class='price'>" + "&euro;" + item.price + "</div>"
				+ "<div class='buy'>" + "Add to cart" + "</div>"
				+ "<div class='description'>" + item.description + "</div>"
			+ "</div>"
			+ "<div class='clear'></div>"
			+ "<div class='close'></div>"
		+ "</div>";

	};
	
	private.
	closeHandler = function(){
		private.frame.click(function(event) {
			if ($(event.target).is("div.frame"))
				App.closeView();
		});
	};
	
	var public = {};
	
	public.
	init = function(){
		private.loadEvents();
		private.closeHandler();
		
		include.Item = App.module.Item;
		include.Cart = App.module.Cart;
	};
	
	public.
	onStart = function(bundle){
		var action = private[bundle.action.toLowerCase()];
		var extra = bundle.extra;
		private.content.empty().append(action(extra));
		private.frame.fadeIn("slow");
	};
	
	public.
	onStop = function(content){
		private.frame.fadeOut();
	};
	
	public.
	setBundle = function(bundle){
		private.bundle = bundle;
	}
	
	public.
	getContainer = function(){
		return {
			frame : private.frame,
			content : private.content
		}
	};
	
	return public;
})();