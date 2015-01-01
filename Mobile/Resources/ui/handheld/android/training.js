Ti.include('watchVid.js');
function training(){
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		backgroundColor:'white'		
	});
	var leaveNestBtn = Ti.UI.createButton({
		height:60,width:200,title:'Watch Leave Nest Example',
		top:60
	});
	var returnNestBtn = Ti.UI.createButton({
		height:60,width:200,title:'Watch Return To Nest Example',
		top:140
	});
	var predatorEvtBtn = Ti.UI.createButton({
		height:60,width:200,title:'Watch Predator Example',
		top:220
	});
	var nestDefense  = Ti.UI.createLabel({});
	var nestDefenseBtn = Ti.UI.createButton({
		height:60,width:200,title:'Watch Defense Example',
		top:300
	});
	var hatching = Ti.UI.createLabel({});
	var hatchingBtn = Ti.UI.createButton({
		height:60,width:200,title:'Watch Hatching Example',
		top:380
	});
	var finished = Ti.UI.createButton({
		height:60,width:200,title:'Finished Training Videos',
		top:460
	});
	self.add(leaveNestBtn);
	self.add(returnNestBtn);
	self.add(predatorEvtBtn);
	self.add(nestDefenseBtn);
	self.add(hatchingBtn);
	self.add(finished);
	
	leaveNestBtn.addEventListener('click',function(){
		var activeMovie = Titanium.Media.createVideoPlayer({ 			url:'http://134.129.182.229/video/wildlife/Website_Training/bird_leaving.wmv',
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
        	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
        	fullscreen : true,
        	autoplay : true
			//exitOnClose:true
    	});
    	var closeBtn = Ti.UI.createButton({
        	title : "Done",
        	top : 0,
        	height:60,
        	left :130,
        	width : 100
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
	returnNestBtn.addEventListener('click',function(){
		var activeMovie = Titanium.Media.createVideoPlayer({ 			url:'http://134.129.182.229/video/wildlife/Website_Training/bird_returning.wmv',
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
        	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
        	fullscreen : true,
        	autoplay : true
			//exitOnClose:true
    	});
    	var closeBtn = Ti.UI.createButton({
        	title : "Done",
        	top : 0,
        	height:60,
        	left :130,
        	width : 100
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
	predatorEvtBtn.addEventListener('click',function(){
		var activeMovie = Titanium.Media.createVideoPlayer({ 			url:'http://134.129.182.229/video/wildlife/Website_Training/badger.wmv',
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
        	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
        	fullscreen : true,
        	autoplay : true
			//exitOnClose:true
    	});
    	var closeBtn = Ti.UI.createButton({
        	title : "Done",
        	top : 0,
        	height:60,
        	left :130,
        	width : 100
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
	nestDefenseBtn.addEventListener('click',function(){
		var activeMovie = Titanium.Media.createVideoPlayer({ 			url:'http://134.129.182.229/video/wildlife/Website_Training/defense.wmv',
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
        	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
        	fullscreen : true,
        	autoplay : true
			//exitOnClose:true
    	});
    	var closeBtn = Ti.UI.createButton({
        	title : "Done",
        	top : 0,
        	height:60,
        	left :130,
        	width : 100
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
	hatchingBtn.addEventListener('click',function(){
		var activeMovie = Titanium.Media.createVideoPlayer({ 			url:'http://134.129.182.229/video/wildlife/Website_Training/bird_returning.wmv',
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
        	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
        	fullscreen : true,
        	autoplay : true
			//exitOnClose:true
    	});
    	var closeBtn = Ti.UI.createButton({
        	title : "Done",
        	top : 0,
        	height:60,
        	left :130,
        	width : 100
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
	finished.addEventListener('click',function(){
		var watch = watchVid();
		watch.open();
		self.close();
	});
	return self;
};
