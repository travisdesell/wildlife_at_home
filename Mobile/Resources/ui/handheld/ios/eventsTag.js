function eventsTag(data){
	Ti.API.info(data.length);
	var pickerView = Ti.UI.createView({
		height:250,bottom:0
	});
	var cancelBtn = Ti.UI.createButton({
		title:'Cancel',style:Ti.UI.iPhone.SystemButtonStyle.BORDERED
	});
	var doneBtn2 = Ti.UI.createButton({
		title:'Done',style:Ti.UI.iPhone.SystemButtonStyle.DONE
	});
	
	var tbSpace = Ti.UI.createButton({systemButton:Ti.UI.iPhone.SystemButton.FLEXIBLE_SPACE});
	var tb = Ti.UI.createToolbar({
		top:0,items:[cancelBtn,tbSpace,doneBtn2]
	});
	var eventPicker = Titanium.UI.createPicker({
		top: 45,
		width:300
	});
	var eventData = [data.length+1];
	//eventData[0]=Ti.UI.createPickerRow({title:'Select Option'});
	for(var i=0,datalen = data.length+1;i < datalen;i++){
		Ti.API.info(data[i]);
		eventData[i]=Ti.UI.createPickerRow({title:data[i]});
	}
	
	//eventData2[0]=Ti.UI.createPickerRow({title:'Select Type'});
	//eventData2[1]=Ti.UI.createPickerRow({title:'Predator'});
	//eventData2[2]=Ti.UI.createPickerRow({title:'Non-Predator Animal'});
	//eventData2[3]=Ti.UI.createPickerRow({title:'Human'});
	eventPicker.add(eventData);
	eventPicker.selectionIndicator=true;
	pickerView.add(tb);
	pickerView.add(eventPicker);
	doneBtn2.addEventListener('click',function(){
		//object.eventL2=picker2.getSelectedRow(0).title;
		Ti.App.fireEvent('done2',{info:eventPicker.getSelectedRow(0).title});
	});
	cancelBtn.addEventListener('click',function(){Ti.App.fireEvent('cancel2',{info:''});});
	return pickerView;
}
