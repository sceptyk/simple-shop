var Product = (function(){
	
	var include = {};
	include.View = App.view;
	
	var private = {};
	
	private.
	handleImageUpload = function(){	
		include.View.container.on('change', "input#load-file" function() {
			private.uploadImage();
		});
	};
	
	private.
	uploadImage = function(){
		var files = include.View.container.find('input#load-file').get(0).files;
		var reader = new FileReader();
		var i = 0;
		reader.onload = function() {

			var canThu = $("<canvas class='thumb'></canvas>");
			//canvas thumb
			var canOri = $("<canvas class='original hidden'></canvas>");
			//canvas original photo
			
			//TODO put some limit for large photos
			canThu = canThu[0];
			canOri = canOri[0];
			var ctx = canThu.getContext('2d');
			var ctxO = canOri.getContext('2d');
			
			include.View.addPhotoPreview(canThu, canOri);
			var image = new Image();
			image.src = this.result;

			image.onload = function() {

				var maxWidth = 100,
					maxHeight = 100,
					imageWidth = image.width,
					imageHeight = image.height;

				canOri.width = imageWidth;
				canOri.height = imageHeight;
				ctxO.drawImage(this, 0, 0);

				if (imageWidth > imageHeight) {
					if (imageWidth > maxWidth) {
						imageHeight *= maxWidth / imageWidth;
						imageWidth = maxWidth;
					}
				} else {
					if (imageHeight > maxHeight) {
						imageWidth *= maxHeight / imageHeight;
						imageHeight = maxHeight;
					}
				}
				canThu.width = imageWidth;
				canThu.height = imageHeight;

				ctx.drawImage(this, 0, 0, imageWidth, imageHeight);
				
				// The resized file ready for upload
				//var finalFile = canvas.toDataURL(fileType);
			};
		};
		reader.onloadend = function(){
			i++;
			if(i < files.length){
				reader.readAsDataURL(files[i]);
			}
		};
		
		reader.readAsDataURL(files[i]);
	};
	
	private.
	uploadProduct = function(){
		var files = $(this).get(0).files;
		var reader = new FileReader();
			
			reader.onloadstart = function(){
				include.View.notifyUploading("Loading");
			};
			
			reader.onloadend = function(){
				include.View.notifyUploading("Loaded");
				//TODO 
				container.find("div#file-product-container").text(this.result);
			};
			
		reader.readAsDataURL(files[0]);
	};
	
	private.
	handleProductUpload = function(){
		$("input#load-file-product").on("change", function(){
			private.uploadProduct();
		});
	};
	
	var public = {};
	
	public.
	init = function(){
		
	};
	
	public.
	loadForm = function(scope, name){
		var view = include.Query.loadStaticView( "/" + scope.toLowerCase() + "/form" + name + ".html");
		App.getView(scope).displayView(view);
	};
	
	public.
	loadMenu = function(scope, name){
		var view = include.Query.loadStaticView( "/" + scope.toLowerCase() + "/menu" + name + ".html");
		App.getView(scope).displayMenu(view);
	}
	
	return public;
})();
