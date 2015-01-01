function listItemDetail(data){
	var self = Titanium.UI.createWindow({
		title:'Event Detail',
		//exitOnClose:true,
		modal:true
		//fullscreen:false
	});
	Ti.API.info(data.properties.title);
	Ti.API.info(data.properties.typeL2);
	Ti.API.info(data.properties.startTime);
	
	var eventTypeLabel = Ti.UI.createLabel({
		top: 20,
		text:'Event Type:',
		left:10,
		font: {fontSize:20},
		color:'white'		
	});
	var eventTypeData = Ti.UI.createLabel({
		top:25,left:120,color:'white',text:data.properties.title+' '+data.properties.typeL2+' '+data.properties.typeL3
	});
	var eventStartTimeLabel = Ti.UI.createLabel({
		top:80,left:10,color:'white',text:'Start Time:',font:{fontSize:20}
	});
	var eventStartTime = Ti.UI.createLabel({
		top:85,left:120,color:'white',text:data.properties.startTime
	});
	var eventEndTimeLabel = Ti.UI.createLabel({
		top:140,left:10,color:'white',text:'End Time:',font:{fontSize:20}
	});
	var eventEndTime = Ti.UI.createLabel({
		top:145,left:120,color:'white',text:data.properties.endTime
	});
	var descriptionLabel = Ti.UI.createLabel({
		top:200,left:10,color:'white',text:'Description:',font:{fontSize:20}
	});
	var descriptionInfo = Ti.UI.createLabel({
		top:205,left:120,color:'white',text:data.properties.description
	});
	var doneButton = Ti.UI.createButton({
		bottom: 20,
		height:40,
		backgroundColor:'#8A8A8A',
		title:'Done',
		width:300
	});
	var deleteButton = Ti.UI.createButton({
		bottom: 80,
		height:40,
		backgroundColor:'#FF0000',
		title:'Delete',
		width:300
	});
	deleteButton.addEventListener('click',function(){
		Ti.App.fireEvent('modal:delete',{info: data});
		self.close();
	});
	doneButton.addEventListener('click',function(){
		self.close();
	});
	self.add(doneButton);
	self.add(deleteButton);
	self.add(eventTypeLabel);
	self.add(eventTypeData);
	self.add(eventStartTime);
	self.add(eventStartTimeLabel);
	self.add(eventEndTime);
	self.add(eventEndTimeLabel);
	self.add(descriptionLabel);
	self.add(descriptionInfo);
	self.open({modal:true});
}
