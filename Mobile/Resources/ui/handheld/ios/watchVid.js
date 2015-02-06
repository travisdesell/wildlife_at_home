//Ti.include('selectSpecies.js','eventList.js','connect.js','eventsTag.js','timePicker.js','picker1.js','addDataModal.js');
//ios
Ti.include('addDataModal.js','eventList.js','evtReviewPicker.js');
function watchVid() {
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		backgroundColor:'black'		
	});
	var watchVidButton = Ti.UI.createButton({
		height:50,
		width:200,
		title:"Watch Video",
		top:20
	});
	var videoPlayer = Titanium.Media.createVideoPlayer({
    	top : 20,
    	autoplay : true,
    	backgroundColor : 'blue',
    	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
    	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FIT
	});
	var addEventButton = Ti.UI.createButton({
		height:50,
		width:200,
		title:"Add Event",
		top:90
	});
	self.add(watchVidButton);
	self.add(addEventButton);
	//change to watch a video
	videoPlayerUrl = '192.168.1.129/videos/test.mp4';
	//var videoLen;
	var evtSt;
	var evtEnd;
	watchVidButton.addEventListener('click', function() {
		self.add(iosMovie);
    	iosMovie.add(closeBtn);
		iosMovie.add(eventStartButton);
		iosMovie.add(eventEndButton);
    	//activeMovie.add(eventStartButton);
    	//activeMovie.add(eventEndButton);
    	//activeMovie.add(closeBtn);
    	//activeMovie.play();
	});
	
	//*************  START VIDEO PAYER  **************
	var iosMovie = Ti.Media.createVideoPlayer({
    	url : 'http://134.129.182.229/video/wildlife/testing/Lek_Clip1.wmv',
    	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_DEFAULT,
    	scalingMode : Titanium.Media.VIDEO_SCALING_ASPECT_FILL,
    	fullscreen : true,
		autoplay : true
	});
	var closeBtn = Ti.UI.createButton({
    	title : "Done",
    	top : 0,
    	height:60,
    	left :130,
    	width : 100
	});
	var eventStartButton = Ti.UI.createButton({
		title:'Event Start',
		top:0,height:60,width:100,left:20
	});
	var eventEndButton = Ti.UI.createButton({
		title:'Event End',
		top:0,height:60,width:100,right:20
	});
	eventStartButton.addEventListener('click', function() {
    	evtSt = iosMovie.getCurrentPlaybackTime();
	});
	eventEndButton.addEventListener('click', function() {
    	evtEnd = iosMovie.getCurrentPlaybackTime();
   		addDataModal(evtSt,evtEnd);
	});
	closeBtn.addEventListener('click',function(){
		iosMovie.hide();
       	iosMovie.release();
        iosMovie = null;
		self.add(viewEvts);
	});
	
	//*************  END VIDEO PAYER  **************
	//Ti.API.info(videoLen);
	var eventWindow;
	var list;
	var eventData = '';
	var newData = "";
	var location = 0;
	var birdEvent;
	addEventButton.addEventListener('click', function() {
		Ti.API.info("DATA");
		//Ti.API.info(videoLen);
		Ti.API.info(evtSt);
		Ti.API.info(evtEnd);
		//new addDataModal();
	});
	var viewEvts = Ti.UI.createButton({
		title:'Review Events',
		height:50,
		width:200,
		bottom:90
	});
	var vidDoneBtn = Ti.UI.createButton({
		title:'Done with Video',
		height:50,
		width:200,
		bottom:20,
		color:'red'
	});
	self.add(vidDoneBtn);
	var picker;
	viewEvts.addEventListener('click',function(){
		/*var listView = Ti.UI.createView({
			height:300,bottom:0,
			backgroundColor:'white'
		});
		var finishedBtn = Ti.UI.createButton({
			title:'Done',style:Ti.UI.iPhone.SystemButtonStyle.DONE
		});
		var viewEvtBtn = Ti.UI.createButton({
			title:'View Event',style:Ti.UI.iPhone.SystemButtonStyle.DONE
		});
		var tbSpace = Ti.UI.createButton({systemButton:Ti.UI.iPhone.SystemButton.FLEXIBLE_SPACE});
		var tb = Ti.UI.createToolbar({
			top:0,items:[finishedBtn,tbSpace,viewEvtBtn]
		});
		var listView = Ti.UI.createView({
			height:300,bottom:0,
			backgroundColor:'white'
		});
			//************ CREATES TOOLBAR AND ADDS TO VIEW *************
		var finishedBtn = Ti.UI.createButton({
			title:'Done',style:Ti.UI.iPhone.SystemButtonStyle.DONE
		});
		var viewEvtBtn = Ti.UI.createButton({
			title:'View Event',style:Ti.UI.iPhone.SystemButtonStyle.DONE
		});
		var tbSpace = Ti.UI.createButton({systemButton:Ti.UI.iPhone.SystemButton.FLEXIBLE_SPACE});
		var tb = Ti.UI.createToolbar({
			top:0,items:[finishedBtn,tbSpace,viewEvtBtn]
		});
		listView.add(tb);*/
		picker = evtReviewPicker(eventData);
		//listView.add(picker);
		Ti.API.info("View Events");
		self.add(picker);
		//list = eventList(eventData);
		//ist = viewEvts();
		//test = evtReviewPicker(eventData);
		//listView.add(tb);
		//listView.add(list);
		//self.add(test);
		Ti.API.info(list);
		Ti.API.info("ADDED");
	});
	Ti.App.addEventListener('deleteData', function(data){
		Ti.API.info("after delete data is: "+data.info);
		//self.remove(test);
		//eventData = data.info;
		//test = evtReviewPicker(eventData);
		//self.add(test);
	});
	Ti.App.addEventListener('modal:passEventData', function(data){
		//Ti.API.info(data.info);
		eventData += data.info + '*';
		//self.remove(list);
		/*if(list!=null){
			self.remove(list);
			list = eventList(eventData);
			self.add(list);
		}
		else{
			list = eventList(eventData);
			self.add(list);
		}*/
	});
	Ti.App.addEventListener('reviewDone',function(data){
		self.remove(picker);
	});
	return self;
};
//module.exports = watchVid;
