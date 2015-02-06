Ti.include('watchVid.js','getSpeciesVid.js','training.js');
function selectSpecies(usrID){
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		backgroundColor:'white',
		orientationModes:[Titanium.UI.PORTRAIT]
	});
	var species;
	var loc;
	var ready = 0;
	var grouseBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Grouse Video',
		top:60
	});
	var ternBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Tern Video',
		top:160
	});
	var ploverBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Plover Video',
		top:260
	});
	var grouseLocBeldenBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Belden Location',
		top:140
	});
	var grouseLocBlaisdellBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Blaisdell Location',
		top:240
	});
	var grouseLocLostwoodBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Lostwood Location',
		top:340
	});
	var ternLocMissouriRvrBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Missouri River Location',
		top:240
	});
	var ploverLocMissouriRvrBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Missouri River Location',
		top:240
	});
	var trainingBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Training Videos',
		top:360	
	});
	var openVidBtn = Ti.UI.createButton({
		height:80,width:200,title:'Open Video',
		top:200	
	});
	var backBtn= Ti.UI.createButton({
		bottom: 20,
		height:40,
		backgroundColor:'#8A8A8A',
		title:'Back',
		width:300
	});
	self.add(grouseBtn);
	self.add(ternBtn);
	self.add(ploverBtn);
	self.add(trainingBtn);
	
	grouseBtn.addEventListener('click',function(){
		species = 1;
		self.removeAllChildren();
		self.add(grouseLocBeldenBtn);
		self.add(grouseLocBlaisdellBtn);
		self.add(grouseLocLostwoodBtn);
		self.add(backBtn);
	});
	ternBtn.addEventListener('click',function(){
		species = 2;
		self.removeAllChildren();
		self.add(ternLocMissouriRvrBtn);
		self.add(backBtn);
	});
	ploverBtn.addEventListener('click',function(){
		species = 3;
		self.removeAllChildren();
		self.add(ploverLocMissouriRvrBtn);
		self.add(backBtn);
	});
	grouseLocBeldenBtn.addEventListener('click',function(){
		loc = 1;
		getSpeciesVid(usrID,species,loc);
		self.removeAllChildren();
		self.add(openVidBtn);
		self.add(backBtn);
	});
	grouseLocBlaisdellBtn.addEventListener('click',function(){
		loc = 2;
		getSpeciesVid(usrID,species,loc);
		self.removeAllChildren();
		self.add(openVidBtn);
		self.add(backBtn);
	});
	grouseLocLostwoodBtn.addEventListener('click',function(){
		loc = 3;
		getSpeciesVid(usrID,species,loc);
		self.removeAllChildren();
		self.add(openVidBtn);
		self.add(backBtn);
	});
	ternLocMissouriRvrBtn.addEventListener('click',function(){
		loc = 4;
		getSpeciesVid(usrID,species,loc);
		self.removeAllChildren();
		self.add(openVidBtn);
		self.add(backBtn);
	});
	ploverLocMissouriRvrBtn.addEventListener('click',function(){
		loc = 4;
		getSpeciesVid(usrID,species,loc);
		self.removeAllChildren();
		self.add(openVidBtn);
		self.add(backBtn);
	});
	backBtn.addEventListener('click',function(){
		self.removeAllChildren();
		self.add(grouseBtn);
		self.add(ternBtn);
		self.add(ploverBtn);
		self.add(trainingBtn);
	});
	
//////    Training Videos    /////////////
	trainingBtn.addEventListener('click',function(){
		self.removeAllChildren();
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
			var activeMovie = Titanium.Media.createVideoPlayer({
				url:'http://134.129.182.229/video/wildlife/Website_Training/bird_leaving.wmv',
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
			var activeMovie = Titanium.Media.createVideoPlayer({
				url:'http://134.129.182.229/video/wildlife/Website_Training/bird_returning.wmv',
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
			var activeMovie = Titanium.Media.createVideoPlayer({
				url:'http://134.129.182.229/video/wildlife/Website_Training/defense.wmv',
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
			var activeMovie = Titanium.Media.createVideoPlayer({
	 			url:'http://134.129.182.229/video/wildlife/Website_Training/bird_returning.wmv',
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
			self.removeAllChildren();
			self.add(grouseBtn);
			self.add(ternBtn);
			self.add(ploverBtn);
			self.add(trainingBtn);			
		});
	});
//////    Training Videos    /////////////
	
	var fp,vidID,startTime,animalID;
	Ti.App.addEventListener('vidInfo',function(info){
		if(info.info=='true'){
			//Ti.API.info(info.file+"\n"+info.vidID+"\n"+info.startTime+"\n"+info.animalID+"\n");
			fp=info.file;
			vidID=info.vidID;
			startTime=info.startTime;
			animalID=info.animalID;
		}
	});
	openVidBtn.addEventListener('click',function(){
		var video = watchVid(fp,species,vidID,usrID,loc);
		video.open();
		self.close();
		//self.release();
	});
	return self;
};
//module.exports = selectSpecies;
