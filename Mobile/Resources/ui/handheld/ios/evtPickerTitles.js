function evtPickerTitles(data){
	//************ PARSES DATA ************
	var pickerRow;
	var evtReviewPicker = Ti.UI.createPicker({
		top:40,width:300
	});
	var datalen;
	pickerRow = [data.length];
	for(var k=0,datalen = data.length+1;k < datalen;k++){
		//Ti.API.info(pickTitle[k]);
		pickerRow[k]=Ti.UI.createPickerRow({title:data[k]});
	}
	evtReviewPicker.add(pickerRow);
	evtReviewPicker.selectionIndicator=true;
	return evtReviewPicker;
}