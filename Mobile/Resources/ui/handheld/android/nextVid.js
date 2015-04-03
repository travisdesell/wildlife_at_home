//Ti.include('watchVid.js','getSpeciesVid.js');
function nextVid(usrID,species,loc){
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		backgroundColor:'white'
	});
	var newVidBtn = Ti.UI.createButton({
		height:80,width:200,title:'New Video',
		top:200	
	});
	var openVidBtn = Ti.UI.createButton({
		height:80,width:200,title:'Open Video',backgroundColor:'green',
		top:200	
	});
	self.add(newVidBtn);
	newVidBtn.addEventListener('click',function(){
		getSpeciesVid(usrID,species,loc);
		self.remove(newVidBtn);
		self.add(openVidBtn);
	});
	var fp,vidID,startTime,animalID;
	Ti.App.addEventListener('vidInfo',function(info){
		if(info.info=='true'){
			Ti.API.info(info.file+"\n"+info.vidID+"\n"+info.startTime+"\n"+info.animalID+"\n");
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