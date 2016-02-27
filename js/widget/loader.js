function Loader(wrap){
	this.wrap = wrap;
	
	var _this = this;
	var holder = null;
	var step = 30; //in milisecs
	
	var loaderWrap = null;
	var circleOuter = null;
	var circleMiddle = null;
	var circleInner = null;
	
	var maxSize = 75; //in pix
	var stepSize = 1; //in pix
	
	this.start = function(){
		
		loaderWrap = $('<div>', {
			class : "loader-wrap"
		});
		
		circleOuter = $('<div>', {
			class : "circle-outer"
		}).appendTo(loaderWrap);
		
		circleMiddle = $('<div>', {
			class : "circle-middle"
		}).appendTo(loaderWrap);
		
		circleInner = $("<div>", {
			class : "circle-inner"
		}).appendTo(loaderWrap);
		
		
		wrap.prepend(loaderWrap);
		
		animate();
	}
	
	var animate = function(){
		
		console.log("animate");
		
		var width;
		var top;
		
		//Circle outer
		width = circleOuter.width();
		
		width += stepSize;
		if(width > maxSize){
			width = stepSize;
		}
		
		height = width;
		left = top = (maxSize - width) / 2;
		
		circleOuter.css({
			top: top,
			left: left,
			width: width,
			height, height
		});
		
		//Circle middle
		width = circleMiddle.width();
		
		width += stepSize;
		if(width > maxSize){
			width = stepSize;
		}
		
		height = width;
		left = top = (maxSize - width) / 2;
		
		circleMiddle.css({
			top: top,
			left: left,
			width: width,
			height, height
		});
		
		//Circle inner
		width = circleInner.width();
		
		width += stepSize;
		if(width > maxSize){
			width = stepSize;
		}
		
		height = width;
		left = top = (maxSize - width) / 2;
		
		circleInner.css({
			top: top,
			left: left,
			width: width,
			height, height
		});
		
		//set order the smaller the higher
		if(circleOuter.width() < circleMiddle.width()){
			if(circleOuter.width() < circleInner.width()){
				circleOuter.css({"z-index" : "3"});
				
				if(circleInner.width() < circleMiddle.width()){
					circleInner.css({"z-index" : "2"});
					circleMiddle.css({"z-index" : "1"});
				}else{
					circleInner.css({"z-index" : "1"});
					circleMiddle.css({"z-index" : "2"});
				}
			}else{
				circleInner.css({"z-index" : "3"});
				circleMiddle.css({"z-index" : "1"});
				circleOuter.css({"z-index" : "2"});
			}
		}else{
			if(circleMiddle.width() < circleInner.width()){
				circleMiddle.css({"z-index" : "3"});
				
				if(circleInner.width() < circleOuter.width()){
					circleInner.css({"z-index" : "2"});
					circleouter.css({"z-index" : "1"});
				}else{
					circleInner.css({"z-index" : "1"});
					circleOuter.css({"z-index" : "2"});
				}
			}else{
				circleInner.css({"z-index" : "3"});
				circleMiddle.css({"z-index" : "2"});
				circleOuter.css({"z-index" : "1"});
			}
		}
		
		holder = setTimeout(animate, step);	
	}
	
	this.stop = function(){
		clearTimeout(holder);
		_this.wrap.find(".loader-wrap").remove();
	}
	
}
