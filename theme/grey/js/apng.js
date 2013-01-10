Apng = {
	
	width 		: 30,
	height 		: 30,
	png 		: 'loader.png',
	frames		: 0,
	speed		: 50,
	target		: '#loader',
	_active		: false,
	_theLoop	: '',

	// Load the image paths into an array
	loadImage : function(){
		var img = document.createElement("img");
		img.src = this.png;
	},

	// Cycle the image through the different versions
	run : function(){
		var i = pos = 0;			
		
		if(!this.target) return false;
		
		$(this.target).css({
			width: Apng.width,
			height: Apng.height,
			display: 'block',
			position: 'absolute',
			left: '50%',
			top: '50%',
			marginLeft: -(Apng.width / 2),
			marginTop: -(Apng.height / 2)
		});
		
		Apng.loadImage();

		this._active = true;

		Apng._theLoop = setInterval(function(){
			if(i < Apng.frames-1){
				i++;
				pos -= 30;
				Apng.target.css({
					background : 'url('+Apng.png+') no-repeat '+pos+'px 0'
				});
			} else {
				i=0;
				pos = 0;
				Apng.target.css({
					background : 'url('+Apng.png+') no-repeat '+pos+'px 0'
				});
			}
		}, this.speed);
		
	},

	stop : function(){
		if(this.target){
			Apng.target.css('background', 'none' );
			clearInterval(Apng._theLoop);
			Apng._active = false;
		}
	}
}
