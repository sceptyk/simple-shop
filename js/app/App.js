var App = (function() {
	
	var private = {};
	
	private.queue = [];
	private.queueDone = [];
	
	private.views = {};
	private.view = null;
	private.home = null;
	private.last = "";
	
	private.actions = {};

	/****************/
	/**
	 * Dynamic loading scripts to page
	 * @param {String} src - local path to script 
	 */
	private.
	loadScripts = function( callback ){
		
		//wait for document
		private.queue.push(
			$(document).ready()
		);
		
		private.queue.push(
			$.Deferred(function( deferred ){
		        $( deferred.resolve );
		    })
	    );
	    
	    //remove old styles
		$("link.dynamic").remove();
		
		$.when
		.apply($, private.queue)
		.done(function(){
			private.queue.length = 0;
			
			for(var i=0;length=private.queueDone.length;i<length;i++){
				private.queueDone[i]();
			}
			
			private.queueDone.length = 0;
		});
		
	};
	
	/**
	 * Retrieves hash parameter from url
	 * @return {Object} {"key" : "value"}
	 */
	private.
	getParams = function(){
		var params = {};
		var hash = window.location.href.hash;
		if(hash){
			hash = hash.substring(1);
		}
		var pairs = hash.split("/");
		for(var i=0,length=pairs.length;i<length;i+=2){
			params[pairs[i]] = pairs[i+1];
		}
		
		return params;
	};

	var public = {};
	
	public.module = {};
	
	/**
	 * Loads module to application modules
	 * @param {String} as - name to access module (name of file)
	 * @param {Object} module  
	 */
	public.
	loadModule = function(as, module){
		if( !public.module.hasOwnProperty(as) ){
			private.queue.push( $.getScript(App.getAppPath + "/module/" + as + ".js") );
			public.module[as] = module;
		}
		
	};
	
	/**
	 * Dynamic loading style to page
	 * @param {String} src
	 */
	public.
	loadStyle = function(src){
		private.queue.push(function(){
			$("<link>", {
				rel : "stylesheet",
				type : "text/css",
				class : "dynamic",
				href : Global.path + "/css" + src + ".css"
			})
			.appendTo($("head"));
		});
		
	};
	
	/**
	 * Load html
	 * @param {String} position
	 * @param {String} src 
	 */
	public.
	loadHTML = function( position, src, callback ){
		
		var loader = null;
		src = Global.path + "/html" + src + ".html";
		
		if(position == "header"){
			loader = $("div#header");
					
		}else if(position == "content"){
			loader = $("div#content");
			
		}else if(position == "footer"){
			loader = $("div#footer");
			
		}
		
		private.queue.push(function(){
			loader.load(src);
		});
		private.queueDone.push(function(){
			callback(loader);
		});
	};

	/**
	 * Loads view to application views
	 * @param {String} as - future reference to access
	 * @param {Object} view 
	 */
	public.
	loadView = function(as, view) {
		private.view[as] = view;
		
		if(view.preinit) view.preinit();
	};
	
	/**
	 * Sets view as current and executes initialization 
	 */
	public.
	setHomeView = function(view) {
		private.home = private.views[view];
	};
	
	/**
	 * Returns view 
	 */
	public.
	getView = function(view){
		if(typeof view !== undefined)
			return private.views[view];
		else
			return private.home;
	};
		
	/**
	 * Sets bundle in view as extra for new view
	 * @param bundle 
	 */
	public.
	setBundle = function(view, bundle){
		var view = private.views[view];
		if(view.setBundle) view.setBundle(bundle);
	};
	
	/**
	 * Changes current view, invokes cycle functions 
	 */
	public.
	openView = function(view, bundle){
		//stop current view
		if(private.view.onStop) private.view.onStop();
		
		//update current view
		if(typeof view !== undefined)
			private.view = private.views[view];
		else
			private.view = private.home;
		
		//invoke starting function
		if(private.view.init){
			private.view.init();
			private.queueDone.push(function(){
				if(private.view.onStart){
					private.view.onStart(bundle);
					private.loadScripts();
				}
			}));
			private.loadScripts();
		}
		
	};
	
	/**
	 * Closes current view and sets view to previous one 
	 */
	public.
	closeView = function(){
		if(private.view.onStop) private.view.onStop();
		
		public.openView();
	};
	
	/**
	 * Retrieves module
	 */
	public.
	getModule = function(module){
		return public.module[module];
	};
	
	/**
	 * Bind new action with url tag 
	 */
	public.
	setAction = function(tag, action){
		private.actions[tag] = action;
	};
	
	/**
	 * Invokes bound action 
	 */
	public.
	invokeAction = function(tag, param){
		if( private.actions.hasOwnProperty(tag) ){
			private.actions[tag](param);
		}
	};
	
	/**
	 * Gets app path 
	 */
	public.
	getAppPath = function(){
		return Global.path + "/js";
	};
	
	public.
	getHtmlPath = function(){
		return Global.path + "/html";
	};
	
	/**
	 * Gets server entry 
	 */
	public.
	getServerEntry = function(){
		return Global.path + "/php/post/receiver.php";
	};
	
	/**
	 * Checks for action 
	 */
	public.
	onActionStart = function(){
		
		public.onActionChanged();
		
	};
	
	/**
	 * Starts application 
	 */
	public.
	ready = function(){
		public.onActionStart();
	};
	
	
	/**
	 * Should be triggered when action changed 
	 */
	public.
	onActionChanged = function(){
		var params = private.getParams();
		var isEmpty = true;
		for(var key in params){
			if(params.hasOwnProperty(key)){
				public.invokeAction( key, params[key] );
				isEmpty = false;
			}
		}
		
		if(isEmpty){
			public.openView();
		}
	};
	
	
	return public;
})();

App.loadView( "Shop", ViewShop );
App.loadView( "Admin", ViewAdmin );
App.loadView( "Frame", ViewFrame );

App.setHomeView( "Shop" );

App.ready();
