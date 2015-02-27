var pickerView = Ti.UI.createView({
	height:200,bottom:0
});
var cancelBtn = Ti.UI.createButton({
	title:'Cancel',style:Ti.UI.iPhone.SystemButtonStyle.BORDERED
});
var doneBtn = Ti.UI.createButton({
	title:'Done',style:Ti.UI.iPhone.SystemButtonStyle.DONE
});
var tbSpace = Ti.UI.createButton({systemButton:Ti.UI.iPhone.SystemButton.FLEXIBLE_SPACE});
var tb = Ti.UI.createToolbar({
	top:0,items:[cancelBtn,tbSpace,doneBtn]
});
var eventPicker1 = Titanium.UI.createPicker({
	top: 20,
	width:300
});
	//var eventPicker3;
var eventData1 = [];
//eventData1[0]=Ti.UI.createPickerRow({title:'Select Event'});
eventData1[0]=Ti.UI.createPickerRow({title:'Camera Interaction'});
eventData1[1]=Ti.UI.createPickerRow({title:'Chick Behavior'});
eventData1[2]=Ti.UI.createPickerRow({title:'Error'});
eventData1[3]=Ti.UI.createPickerRow({title:'Miscellaneous'});
eventData1[4]=Ti.UI.createPickerRow({title:'Parent Behavior'});
eventData1[5]=Ti.UI.createPickerRow({title:'Parent Care'});
eventData1[6]=Ti.UI.createPickerRow({title:'Territorial'});
eventPicker1.add(eventData1);
eventPicker1.selectionIndicator=true;
pickerView.add(tb);
pickerView.add(eventPicker1);