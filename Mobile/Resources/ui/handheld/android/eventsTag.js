function eventsTag(data){
	Ti.API.info(data.length);
	var eventPicker = Titanium.UI.createPicker({
		top: 140,
		width:300
	});
	var eventData = [data.length+1];
	eventData[0]=Ti.UI.createPickerRow({title:'Select Option'});
	for(var i=1,datalen = data.length+1;i < datalen;i++){
		Ti.API.info(data[i]);
		eventData[i]=Ti.UI.createPickerRow({title:data[i-1]});
	}
	
	//eventData2[0]=Ti.UI.createPickerRow({title:'Select Type'});
	//eventData2[1]=Ti.UI.createPickerRow({title:'Predator'});
	//eventData2[2]=Ti.UI.createPickerRow({title:'Non-Predator Animal'});
	//eventData2[3]=Ti.UI.createPickerRow({title:'Human'});
	eventPicker.add(eventData);
	eventPicker.selectionIndicator=true;
	return eventPicker;
}
