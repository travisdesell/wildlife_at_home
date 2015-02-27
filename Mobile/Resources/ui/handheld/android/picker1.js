function picker1(){
	//Ti.API.info(data.length);
	var eventPicker = Titanium.UI.createPicker({
		top: 20,
		width:300
	});
	var data = ['Select Event','Camera Interaction','Chick Behavior',
		'Error','Miscellaneous','Parent Behavior','Parent Care','Territorial'];
	var eventData = [data.length];
	for(var i=0,datalen = data.length;i < datalen;i++){
		Ti.API.info(data[i]);
		eventData[i]=Ti.UI.createPickerRow({title:data[i]});
	}
	
	//eventData2[0]=Ti.UI.createPickerRow({title:'Select Type'});
	//eventData2[1]=Ti.UI.createPickerRow({title:'Predator'});
	//eventData2[2]=Ti.UI.createPickerRow({title:'Non-Predator Animal'});
	//eventData2[3]=Ti.UI.createPickerRow({title:'Human'});
	eventPicker.add(eventData);
	eventPicker.selectionIndicator=true;
	return eventPicker;
}
