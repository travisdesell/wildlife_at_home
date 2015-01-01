Ti.include('eventsTag.js','picker1.js','events.js');
//'eventList.js','connect.js','timePicker.js'
function addDataModal(stTime,eTime){
	var self = Titanium.UI.createWindow({
		title:'Add Event',
		backgroundColor:'white'
		//exitOnClose:true,
		//modal:true
		//fullscreen:false
	});
	var startTime = getTime(stTime);
	var endTime = getTime(eTime);
	var object = {
		eventL1: '',
		eventL2: '',
		eventL3: ''
	};
	
	var selectEvt1Btn = Ti.UI.createButton({
		title:'Select Event',
		top:20,
		width:300
	});
	var selectEvt2Btn = Ti.UI.createButton({
		title:'Select Event',
		top:60,
		width:300
	});
	var selectEvt3Btn = Ti.UI.createButton({
		title:'Select Event',
		top:100,
		width:300
	});
	
	self.add(selectEvt1Btn);
//********    Creates picker view 1 *******************	
	var pickerView = Ti.UI.createView({
		height:250,bottom:0
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
		top: 35,
		width:300
	});
	var eventData1 = [];
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
	
//********    Creates picker view 1 *******************	
	
	selectEvt1Btn.addEventListener('click',function(){
		self.add(pickerView);
		doneBtn.addEventListener('click',function(){
			selectEvt1Btn.title = eventPicker1.getSelectedRow(0).title;
			object.eventL1=eventPicker1.getSelectedRow(0).title;
			self.remove(pickerView);
			if(object.eventL1=='Territorial'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataTerritorial);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
						if(object.eventL2=='Predator'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								//Ti.App.removeEventListener('done',function);
								picker3 = eventsTag(predatorSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
						else if(object.eventL2=='Non-Predator Animal'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								picker3 = eventsTag(nonPredSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
						else if(object.eventL2=='Human'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								picker3 = eventsTag(humanSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
				
			}
			else if(object.eventL1=='Camera Interaction'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataCamera);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}
			else if(object.eventL1=='Chick Behavior'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataChick);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}
			else if(object.eventL1=='Error'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataError);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}
			else if(object.eventL1=='Miscellaneous'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataMisc);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}
			else if(object.eventL1=='Parent Behavior'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataBehavior);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
						if(object.eventL2=='On Nest'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								picker3 = eventsTag(onNestSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
						else if(object.eventL2=='Off Nest'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								picker3 = eventsTag(offNestSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}
			else if(object.eventL1=='Parent Care'){
				self.add(selectEvt2Btn);
				selectEvt2Btn.addEventListener('click',function(){
					picker2 = events(eventDataCare);
					self.add(picker2);
					Ti.App.addEventListener('done',function(data){
						selectEvt2Btn.title = data.info;
						object.eventL2 = data.info;
						self.remove(picker2);
						if(object.eventL2=='Brooding Chicks'){
							self.add(selectEvt3Btn);
							selectEvt3Btn.addEventListener('click',function(){
								picker3 = eventsTag(broodingSelection);
								self.add(picker3);
								Ti.App.addEventListener('done2',function(data){
									selectEvt3Btn.title = data.info;
									object.eventL3 = data.info;
									self.remove(picker3);
								});
								Ti.App.addEventListener('cancel2',function(data){
									self.remove(picker3);
								});
							});
						}
					});
					Ti.App.addEventListener('cancel',function(data){
						self.remove(picker2);
					});
				});
			}		
		});
		cancelBtn.addEventListener('click',function(){self.remove(pickerView);});
	});
	
	// ********** EVENT DATA *********************
	predatorSelection = ['Raptor','Skunk','Badger','Fox','Racoon','Coyote','Crow/Raven','Gull'];
	onNestSelection = ['Sitting','Standing'];
	offNestSelection = ['Sitting','Standing','Walking','Flying'];
	broodingSelection = ['On Nest','Off Nest'];
	nonPredSelection = ['Porcupine','Deer','Rodent','Songbird','Canada Goose','Other Waterfowl','Phesant','Other Sharp-Tailed Grouse','Other Plover','Other Tern','Salamander','Frog/Toad','Insect','Snake','Horse','Cattle'];
	humanSelection = ['Researcher','Other','On Nest','Off Nest'];
	nullSelection = [''];
	
	eventDataTerritorial=['Nest Defense','Predator','Non-Predator Animal','Human'];
	eventDataCamera=['Attack','Physical Inspection','Observation'];
	eventDataChick=['Eggs Hatching','In Video'];
	eventDataError=['Too Dark','Video Error','Camera Issue'];
	eventDataMisc=['Unpecified'];
	eventDataBehavior=['Not In Video','On Nest','Off Nest'];
	eventDataCare=['Brooding Chicks','Eggshell Removal'];
		
	//var eventPicker2 = blank;
	//var eventPicker3=blank;
	// ********** EVENT DATA *********************	
		
		var startTimeLabel = Ti.UI.createLabel({
			text:'Start Time',
			top: 200,
			left:40,
			color:'black'			
		});
		var startTime = Ti.UI.createLabel({
			top: 220,left:20,width:120, value:startTime,color:'black'
			
		});
		/*startTime.addEventListener('change',function(){
			startTime.setSelection(startTime.value.length,startTime.value.length);
			Ti.API.info(startTime.value);
			if(startTime.value.length == 2||startTime.value.length == 5) {
        		startTime.value += ":";
        		Ti.API.info(startTime.value.length);
    		}
		});*/
		var endTime = Ti.UI.createLabel({
			top: 220,right:20,width:120,value:endTime,color:'black'
		});
		/*endTime.addEventListener('change',function(){
			endTime.setSelection(endTime.value.length,endTime.value.length);
			Ti.API.info(endTime.value);
			if(endTime.value.length == 2||endTime.value.length == 5) {
        		endTime.value += ":";
        		Ti.API.info(endTime.value.length);
    		}
		});*/
		var endTimeLabel = Ti.UI.createLabel({
			text:'End Time',
			top: 200,
			right:40,
			color:'black'
		});
		var descriptionLabel = Ti.UI.createLabel({
			text:'Description',
			top: 260,
			color:'black'
		});
		var description = Ti.UI.createTextArea({
			top: 320,width:300,autocorrect:true,
			height:50,borderWidth:1,borderColor:'black',keyboardType:Ti.UI.KEYBOARD_DEFAULT
		});
		
		self.add(description);
		self.add(startTimeLabel);
		self.add(endTimeLabel);
		self.add(startTime);
		self.add(endTime);
		
		var saveEventButton = Titanium.UI.createButton({
			bottom: 20,
			height:40,
			backgroundColor:'#46fb00',
			title:'Save Event',
			width:300
		});
		//var obj = [];
		self.add(saveEventButton);
		saveEventButton.addEventListener('click',function(){
			var data = object.eventL1 +"-"+object.eventL2+"-"+object.eventL3+"//"+startTime.value+"//"+endTime.value+"//"+description.value;
			Ti.App.fireEvent('modal:passEventData',{info: data});
			self.close();
			//return object;
			//obj.push({el1: eventPicker1.getSelectedRow(0).title});
		});
		var cancelEventButton = Titanium.UI.createButton({
			bottom: 60,
			height:40,
			//backgroundColor:'#46fb00',
			title:'Cancel',
			width:300
		});
		self.add(cancelEventButton);
		cancelEventButton.addEventListener('click',function(){
			self.close();
		});
		self.open({modal:true});
	
	return object;
}
function getTime(time){
	//time = time.toString();
	var hr;
	var min;
	var sec = time * 0.001;
	sec=sec.toString();
	var finalTime;
	var temp = sec.split('.');
	sec = temp[0];
	if(sec>59){
		min = sec / 60;
		min = min.toString();
		temp = min.split('.');
		sec = temp[1]*60;
		min = temp[0];
		if(min>59){
			hr = min/60;
			hr = hr.toString();
			temp = hr.split('.');
			min = temp[1]*60;
			hr = temp[0];
		}
	}
	if(sec.length==0){sec = '00';}
	if(sec.length==1){sec = '0'+sec;}
	if(min==null){min='00';}
	if(min.length==1){min = '0'+min;}
	if(hr==null){hr = '00';}
	if(hr.length==1){hr = '0'+hr;} 
	Ti.API.info(hr+':'+min+':'+sec);
	finalTime = hr+':'+min+':'+sec;
	return finalTime;
}

