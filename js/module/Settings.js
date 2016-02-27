var Settings = (function(){
	
	var include = {};
	include.Query = App.module.Query;
	
	var private = {};
	
	/**
	 * @var settingsMask - binding view ids with db column names 
	 */
	private.settingsMask = {
		"shopname" : "es_name",
		"cemail" : "es_customer_email",
		"memail" : "es_main_email",
		"logo" : "es_logoimg",
		"payuser" : "paypal_user",
		"paypwd" : "paypal_pwd",
		"paysign" : "paypal_signature",
		"payver" : "paypal_version",
		"paycur" : "paypal_currency"
	};
	
	private.setSettingsObject = function( fields ){
		
		var data = {};
		for(var i=0, length=fields.length; i<length; i++){
			
			var field = fields[i];
			var id = field.attr("id");
			var val = fields.val();
			
			if( private.settingsMask.hasOwnProperty(id) ){
				
				data[ private.settingsMask[id] ] = val;
			}
			
		}
		
		return data;
	};
	
	var public = {};
	
	public.saveSettings = function( fields ){
		
		var data = private.setSettingsObject(fields);
		
		include.Query.post(
			"set_settings",
			data
		);
	};
	
	public.get_settings = function(){
		
		include.Query.post(
			"get_settings",
			{
				
			},
			function(data){
				App.getView("Admin");
			}
		)
		
	};
	
	return public;
})();
