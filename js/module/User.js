var User = (function(){
	
	var include = {};
	include.Query = App.module.Query;
	
	var private = {};
	
	private.
	getUserCredentials = function(){
		
		var user = null;
		
		if(window.localStorage){
			
			var userid = window.localStorage.getItem("userid");
			var userhash = window.localStorage.getItem("userhash");
			
			if( !(userid && userhash) ){
				return null;
			}
			
			user = {
				userid : userid,
				userhash : userhash
			};
		}
		
		if( user == null ){
			if(window.sessionStorage){
				var userid = window.sessionStorage.getItem("userid");
				var userhash = window.sessionStorage.getItem("userhash");
				
				if( !(userid && userhash) ){
					return null;
				}
				
				user = {
					userid : userid,
					userhash : userhash
				};
			}
		}
		
		return user;
	};
	
	private.
	setUserCredentials = function( userid, userhash, remember ){
		
		if(remember){
			if(window.localStorage){
				
				window.localStorage.setItem("userid", userid);
				window.localStorage.setItem("userhash", userhash);
			
			}else{
				return false;
			}
		}else{
			if(window.sessionStorage){
				
				window.sessionStorage.setItem("userid", userid);
				window.sessionStorage.setItem("userhash", userhash);
			
			}else{
				return false;
			}
		}
		
		return false;
	};
	
	var public = {};
	
	public.
	login = function( username, usernpass, remember, $level ){
		
		userpass = btoa(encodeURIComponent(userpass));
		remember = !!remember;
		
		
		include.Query.post(
			"log_user",
			{
				username : username,
				userpass : userpass,
				remember : remember
			},
			function(data){
				
				private.setUserCredentials(data.userid, data.userhash);
				
			}
		);
		
	};
	
	public.
	logoff = function(){
		
		private.setUserCredentials(null, null);
		App.openView("Frame", {
	 		action : "login"
	 	});
		
	};
	
	public.
	checkUser = function(){
		
		 var credentials = private.getUserCredentials();
		 if( !credentials ){
		 	return false;
		 }else{
		 	return true;
		 }
		
	};
	
	public.
	updateUser = function( user ){
		
		include.Query.post(
			"update_user",
			{
				name : user.name,
				pass : user.pass
				email : user.email
			},
			function(){
				//TODO print success
			}
		);
		
	};
	
	public.
	addNewUser = function( user ){
		
		include.Query.post(
			"add_new_user",
			{
				name : user.name,
				pass : btoa(user.pass),
				email : user.email,
				userlevel : user.level
			},
			function(){
				//TODO print success
			}
		)
		
	};
	
	return public;
})();
