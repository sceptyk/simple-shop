var Query = (function() {

	var include = {};
	include.User = App.module.User;

	var private = {};
	
	private.
	request = function(type, action, data, done) {

		if ( typeof data === undefined)
			data = {};

		data['tag'] = action;
		
		var user = include.User.getUserCredentials();
		data['userid'] = user.userid;
		data['userhash'] = user.userhash;
		
		data['timestamp'] = private.getTimestampTick();
		
		data['session'] = private.getSession();

		$.ajax({
			type : type,
			data : data,
			error : function(msg) {
				console.log(msg);
			},
		}).done(function(data) {

			console.log(data);

			if (data.error == 0) {
				if ( typeof done !== undefined)
					done(data.result);
			} else {
				if (data.action == "logoff") {
					
					include.User.logoff();
					
				}
			}
		});

	};
	
	/**
	 * Returns time tick in seconds 
	 */
	private.
	getTimestampTick = function(){
		return (Date.now()/1000) << 0;
	};
	
	/**
	 * Retrieves session id from cookie 
	 */
	private.
	getSession = function(){
		//TODO if not set ask for one
		return /PHPSESSID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
	};

	var public = {};
	
	public.
	init = function(){
		
		$.ajaxSetup({
			url :  App.getServerEntry(),
			dataType : "json",
			cache : false
		})
		
	};

	/**
	 * Executes post request
	 * @param {String} action - action label
	 * @param {JSON} data - required by action
	 * @param {function} done - callback on successful request
	 */
	public.
	post = function(action, data, done) {

		request("post", action, data, done);

	};

	public.
	get = function(action, data, done) {

		request("get", action, data, done);
	};
	
	/**
	 * Retrieves parameter from URL
	 * @param {String} value - key name
	 * @return {String|null} 
	 */
	public.
	urlParam = function(value){
		
		var params = window.location.search.substr(1).split("&");
		
		for(var i=0;i<params.length;i++){
			var pair = params[i].split("=");
			if(pair[0].toLowerCase() == value) return decodeURIComponent(pair[1]);
		}
		
		return null;
	};
	
	/**
	 * Redirects to url 
	 */
	public.
	redirect = function(url){
		location.window.href = url;
	};
	
	/**
	 * Changes history entry 
	 */
	public.
	changeUrl = function(url){
		//TODO
	};
	
	public.
	loadStaticView = function( target, view, callback ){
		
		target.empty();
		target.load( App.pathview + "/html" + view, callback );
		 
	};
	
	/**
	 * Retrieves cookie value by cookie name
	 * @param {String} name
	 * @return {String} 
	 */
	public.
	getCookie = function(name){
		var regex = new RegExp(name + "=([^;]+)", "i");
		return regex.test(document.cookie) ? return RegExp.$1 : null;
	};
	
	return public;

})();
