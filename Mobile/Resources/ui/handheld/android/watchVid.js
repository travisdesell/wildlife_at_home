//Ti.include('selectSpecies.js','eventList.js','connect.js','eventsTag.js','timePicker.js','picker1.js','addDataModal.js');
//ANDROID
Ti.include('addDataModal.js','eventList.js','dataModal.js','endVid.js');
function watchVid(vidPath,species,vidID,userID,loc) {
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		backgroundColor:'white',
		orientation: Ti.UI.PORTRAIT
	});
	var watchVidButton = Ti.UI.createButton({
		height:50,
		width:200,
		title:"Watch Video",
		top:20
	});
	var addEventButton = Ti.UI.createButton({
		height:50,
		width:200,
		title:"Add Event",
		top:90
	});
	self.add(watchVidButton);

	var evtSt;
	var evtEnd;
	var ui = userID;
	Ti.API.info("USER IS "+ui);
	var vidurl = 'http://134.129.182.229' + vidPath+'.mp4';
	Ti.API.info(vidurl);
////////      Video Player      ////////
	watchVidButton.addEventListener('click', function() {
		var activeMovie = Titanium.Media.createVideoPlayer({
        	url : vidurl,
        	mediaControlStyle : Titanium.Media.VIDEO_CONTROL_EMBEDDED,
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
 		var eventStartButton = Ti.UI.createButton({
 			title:'Event Start',
 			top:0,height:60,width:100,left:20
 		});
 		var eventEndButton = Ti.UI.createButton({
 			title:'Event End',
 			top:0,height:60,width:100,right:20
 		});
    	eventStartButton.addEventListener('click', function() {
        	evtStart = activeMovie.getCurrentPlaybackTime();
    	});
    	eventEndButton.addEventListener('click', function() {
        	evtEnd = activeMovie.getCurrentPlaybackTime();
        	addDataModal(evtStart,evtEnd,species);
			//dataModal(evtStart,evtEnd,species);
    	});
    	closeBtn.addEventListener('click',function(){
    		activeMovie.hide();
        	activeMovie.release();
        	activeMovie = null;
    	});
    	activeMovie.add(eventStartButton);
    	activeMovie.add(eventEndButton);
    	activeMovie.add(closeBtn);
    	activeMovie.play();
	});
////////      Video Player      ////////
	
	var eventWindow;
	var list;
	var eventData = '';
	var newData = "";
	var rand = '';
	var birdEvent;
	addEventButton.addEventListener('click', function() {
		Ti.API.info("DATA");
		//Ti.API.info(videoLen);
		Ti.API.info(evtSt);
		Ti.API.info(evtEnd);
		addDataModal();
	});

////////      Events List      ////////
	Ti.App.addEventListener('modal:passEventData', function(data){
		//Ti.API.info(data.info);
		eventData += data.info + ',';
		//self.remove(list);
		//Ti.API.info("eventData");
		if(list!=null){
			self.remove(list);
			list = eventList(eventData);
			self.add(list);
		}
		else{
			list = eventList(eventData);
			self.add(list);
		}
		
	});
	Ti.App.addEventListener('passPostDelData', function(data){
		//Ti.API.info("data after delete:  " + data.info);
		if(data.info == null){
			eventData=data.info+',';
			self.remove(list);
		}
		else{
			eventData = data.info;
			self.remove(list);
			list = eventList(eventData);
			self.add(list);
		}
	});
////////      Events List      ////////
	var finishedBtn = Ti.UI.createButton({
 		title:'Finished with Video',
		bottom:20,height:50,width:320, color:'red'
 	});
	var randVidBtn = Ti.UI.createButton({
 		title:'Random Video',
		top:100,height:80,width:200
 	});
	var nextVidBtn = Ti.UI.createButton({
 		title:'Next Video',
		bottom:100,height:80,width:200
 	});
	self.add(finishedBtn);
	finishedBtn.addEventListener('click', function(){
		self.removeAllChildren();
		self.add(randVidBtn);
		self.add(nextVidBtn);
		Ti.API.info(eventData);
		var finalData='';
		if(eventData.indexOf(',')>-1){
			tempData = eventData.split(',');
			var size = tempData.length;
			var j = 0;
			while(j<size){
				var tempDataArray = tempData[j].split('//');
				Ti.API.info(tempDataArray[4]);
				//Ti.API.info(tempDataArray[1].toString());
				if(tempDataArray[0]==''){break;}
				if(tempDataArray[3]==''){
					tempDataArray[3]=' ';
				}
				finalData += tempDataArray[1]+'//'+tempDataArray[2]+'//'+tempDataArray[3]+'//'+tempDataArray[4]+',';
				j++;
			}
			//Pass data back to eventList to create new list
		}
		Ti.API.info(finalData);
		randVidBtn.addEventListener('click',function(){
			rand = 'true';
			endVid(finalData,vidID,ui,loc,species,rand);
			var newVid = nextVid(ui,species,loc);
			newVid.open();
			self.close();
		});
		nextVidBtn.addEventListener('click',function(){
			rand = 'false';
			endVid(finalData,vidID,ui,loc,species,rand);
			var newVid = nextVid(ui,species,loc);
			newVid.open();
			self.close();
		});
	});
	return self;
};
//module.exports = watchVid;
