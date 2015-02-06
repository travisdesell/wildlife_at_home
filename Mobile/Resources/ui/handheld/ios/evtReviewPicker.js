Ti.include('deleteEvt.js','evtPickerTitles.js')
function evtReviewPicker(data){
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
	listView.add(tb);
	//************ CREATES TOOLBAR AND ADDS TO VIEW *************
	//************ PARSES DATA ************
	Ti.API.info(data);
	var newPick = 0;
	var evtArray = [];
	var evtData = [];
	var evtTypes = [];
	var pickerRow;
	var pickTitle;
	if(newPick ==1){
		if (data.indexOf('*')>-1){
			evtArray = data.split("*");
			var dataLen = evtArray.length;
			pickTitle = [dataLen];
			var i=0;
			var j=0;
			while(i<dataLen){
				evtData=evtArray[i].split('//');
				evtTypes = evtData[0].split('-');
				pickTitle[i] = evtTypes[0];
				i++;
			}
		}
	}
	if (data.indexOf('*')>-1){
		evtArray = data.split("*");
		var dataLen = evtArray.length;
		pickTitle = [dataLen];
		var i=0;
		var j=0;
		while(i<dataLen){
			evtData=evtArray[i].split('//');
			evtTypes = evtData[0].split('-');
			pickTitle[i] = evtTypes[0];
			i++;
		}
	}
	/*var evtReviewPicker = Ti.UI.createPicker({
		top:40,width:300
	});
	var datalen;
	pickerRow = [pickTitle.length];
	for(var k=0,datalen = pickTitle.length+1;k < datalen;k++){
		//Ti.API.info(pickTitle[k]);
		pickerRow[k]=Ti.UI.createPickerRow({title:pickTitle[k]});
	}
	evtReviewPicker.add(pickerRow);
	evtReviewPicker.selectionIndicator=true;
	//Ti.API.info(pickTitle.toString());*/
	
	var datalen = pickTitle.length+1;
	var picker = evtPickerTitles(pickTitle);
	listView.add(picker);
	viewEvtBtn.addEventListener('click',function(){
		var getInfo =  picker.getSelectedRow(0).title;
		var arrayLoc;
		for(var l = 0;l<datalen;l++){
			if(getInfo==pickTitle[l]){
				Ti.API.info("Found\t"+ l);
				Ti.API.info(evtArray[l]);
				listItemDetail(evtArray[l]);
			}
		}
		//Ti.App.fireEvent('view',{info:evtReviewPicker.getSelectedRow(0).title});
		//Ti.API.info(evtReviewPicker.getSelectedRow(0));
		//Ti.API.info(evtReviewPicker.getSelectedRow(0).title);
	});
	finishedBtn.addEventListener('click',function(){
		Ti.App.fireEvent('reviewDone',{info:''});		
	});
	Ti.App.addEventListener('modal:delete',function(deleteData){
		var newData = deleteEvt(data,deleteData.info);
		
		//data = newData;
		//Ti.App.fireEvent('deleteData',{info:newData});
		//evtReviewPicker.reloadColumn(0);
		Ti.API.info("new data is: "+newData);
		evtReviewPicker(newData);
	});
//	evtArray = data.split("");
	return listView;
}
