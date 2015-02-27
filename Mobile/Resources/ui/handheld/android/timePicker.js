function timePicker(){
	var time = Ti.UI.createTextField({
		top: 220,left:20,width:80,
		keyboardType:Ti.UI.KEYBOARD_NUMBER_PAD
	});
	time.addEventListner('keypressed',function(e){
		if(e.keyCode != 8 && this.value.length == 2||e.keyCode != 8 && this.value.length == 5) {
        	this.value += ":";
    	}
	});
	/*var time = Ti.UI.createPicker({
		top:180,
		useSpinner:true
	});
	time.selectionIndicator = true;
	var column1 = Ti.UI.createPickerColumn({width:40,title:'Hours'});
	for(var i=0,datalen = 0;i < 5;i++){
		var row = Ti.UI.createPickerRow({title:i});
		column1.addRow(row);
	}
	var column2 = Ti.UI.createPickerColumn({width:40,title:'Minutes'});
	for(var i=0,datalen = 0;i < 60;i++){
		var row = Ti.UI.createPickerRow({title:i});
		column2.addRow(row);
	}
	var column3 = Ti.UI.createPickerColumn({width:40,title:'Seconds'});
	for(var i=0,datalen = 0;i < 60;i++){
		var row = Ti.UI.createPickerRow({title:i});
		column3.addRow(row);
	}
	time.add([column1,column2,column3]);
	//view.add(time);
	time.addEventListener('change',function(evt){
		this.parent.label.value = (this.value);
		this.parent.fireEvent('timechange',{src:this.parent});
	});
	return time;*/
}
