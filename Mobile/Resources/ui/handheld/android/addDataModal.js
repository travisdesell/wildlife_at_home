Ti.include('eventsTag.js','picker1.js','events.js');
//'eventList.js','connect.js','timePicker.js'
function addDataModal(stTime,eTime){
	var self = Titanium.UI.createWindow({
		title:'Add Event',
		modal:true
		//fullscreen:false
	});
	var scrollView = Ti.UI.createScrollView({
		contentWidth:'auto',
		contentHeight:'60%',
		showVerticalScrollIndicator:true,
		showHorizontalScrollIndicator:true
	});
	Ti.API.info(stTime + ' \t ' + eTime);
	var startTime = getTime(stTime);
	var endTime= getTime(eTime);
	stTime = stTime*.001;
	eTime = eTime*.001;
	var object = {
		eventL1: 'Event 1',
		eventL2: 'Event 2',
		eventL3: 'Event 3'
	};
	var id;
	eventDataTerritorial=['Nest Defense','Predator','Non-Predator Animal','Human'];
	eventDataCamera=['Attack','Physical Inspection','Observation'];
	eventDataChick=['Eggs Hatching','In Video'];
	eventDataError=['Too Dark','Video Error','Camera Issue'];
	eventDataMisc=['Unpecified'];
	eventDataBehavior=['Not In Video','On Nest','Off Nest'];
	eventDataCare=['Brooding Chicks','Eggshell Removal'];
	
	var eventPicker1 = Titanium.UI.createPicker({
		top: 20,
		width:300
	});
		//var eventPicker3;
	var eventData1 = [];
	eventData1[0]=Ti.UI.createPickerRow({title:'Select Event'});
	eventData1[1]=Ti.UI.createPickerRow({title:'Camera Interaction'});
	eventData1[2]=Ti.UI.createPickerRow({title:'Chick Behavior'});
	eventData1[3]=Ti.UI.createPickerRow({title:'Error'});
	eventData1[4]=Ti.UI.createPickerRow({title:'Miscellaneous'});
	eventData1[5]=Ti.UI.createPickerRow({title:'Parent Behavior'});
	eventData1[6]=Ti.UI.createPickerRow({title:'Parent Care'});
	eventData1[7]=Ti.UI.createPickerRow({title:'Territorial'});
	eventPicker1.add(eventData1);
	eventPicker1.selectionIndicator=true;
	scrollView.add(eventPicker1);
	
	var predatorSelection = ['Raptor','Skunk','Badger','Fox','Racoon','Coyote','Crow/Raven','Gull'];
		var onNestSelection = ['Sitting','Standing'];
		var offNestSelection = ['Sitting','Standing','Walking','Flying'];
		var broodingSelection = ['On Nest','Off Nest'];
		var nonPredSelection = ['Porcupine','Deer','Rodent','Songbird','Canada Goose','Other Waterfowl','Phesant','Other Sharp-Tailed Grouse','Other Plover','Other Tern','Salamander','Frog/Toad','Insect','Snake','Horse','Cattle'];
		var humanSelection = ['Researcher','Other','On Nest','Off Nest'];
		var nullSelection = [''];
		
		var eventPickerPred = eventsTag(predatorSelection);
		var eventPickerNonPred = eventsTag(nonPredSelection);
		var eventPickerHum = eventsTag(humanSelection);
		var eventPickerBrood = eventsTag(broodingSelection);
		var eventPickerOnNest = eventsTag(onNestSelection);
		var eventPickerOffNest = eventsTag(offNestSelection);
		var blank = eventsTag(['']);
		
		var eventPicker2 = blank;
		var eventPicker3=blank;
		
		eventPicker1.addEventListener('change',function(){
			if(eventPicker1.getSelectedRow(0).title){
				scrollView.remove(eventPicker2);
				scrollView.remove(eventPicker3);
			}
			if(eventPicker1.getSelectedRow(0).title == 'Territorial'){
				object.eventL1='Territorial';
				eventPicker2=events(eventDataTerritorial);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Camera Interaction'){
				object.eventL1='Camera Interaction';
				eventPicker2=events(eventDataCamera);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Chick Behavior'){
				object.eventL1='Chick Behavior';
				eventPicker2=events(eventDataChick);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Error'){
				object.eventL1='Error';
				eventPicker2=events(eventDataError);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Miscellaneous'){
				object.eventL1='Miscellaneous';
				eventPicker2=events(eventDataMisc);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Parent Behavior'){
				object.eventL1='Parent Behavior';
				eventPicker2=events(eventDataBehavior);
				scrollView.add(eventPicker2);
			}
			else if(eventPicker1.getSelectedRow(0).title == 'Parent Care'){
				object.eventL1='Parent Care';
				eventPicker2=events(eventDataCare);
				scrollView.add(eventPicker2);
			}
			eventPicker2.addEventListener('change',function(){
				Ti.API.info("Change Works");
				if(eventPicker2.getSelectedRow(0).title == 'Predator'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(predatorSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 30;
					});
				}
				else if(eventPicker2.getSelectedRow(0).title == 'Non-Predator Animal'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(nonPredSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 31;
					});
				}
				else if(eventPicker2.getSelectedRow(0).title == 'Human'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(humanSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 37;
					});
				}
				else if(eventPicker2.getSelectedRow(0).title == 'Brooding Chicks'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(broodingSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 9;
					});
				}
				else if(eventPicker2.getSelectedRow(0).title == 'On Nest'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(onNestSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 41;
					});
				}
				else if(eventPicker2.getSelectedRow(0).title == 'Off Nest'){
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					eventPicker3=eventsTag(offNestSelection);
					scrollView.add(eventPicker3);
					eventPicker3.addEventListener('change',function(){
						object.eventL3=eventPicker3.getSelectedRow(0).title;
						id = 42;
					});
				}
				else{
					object.eventL2=eventPicker2.getSelectedRow(0).title;
					if(eventPicker2.getSelectedRow(0).title=='Attack'){
						id = 34;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Physical Inspection'){
						id = 35;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Observation'){
						id = 36;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Eggs Hatching'){
						id = 17;
					}
					else if(eventPicker2.getSelectedRow(0).title=='In Video'){
						id = 18;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Camera Issue'){
						id = 40;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Video Error'){
						id = 39;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Too Dark'){
						id = 38;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Unspecified'){
						id = 32;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Not In Video'){
						id = 4;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Eggshell Removal'){
						id = 12;
					}
					else if(eventPicker2.getSelectedRow(0).title=='Nest Defense'){
						id = 26;
					}
					//eventPicker3=eventsTag(nullSelection);
					//scrollView.add(eventPicker3);
					scrollView.remove(eventPicker3);
					object.eventL3 = '';
				}
				
			});
		});
		
		var startTimeLabel = Ti.UI.createLabel({
			text:'Start Time',
			top: 200,
			left:40,
			color:'white'			
		});
		var startTime = Ti.UI.createTextField({
			top: 220,left:20,width:120,value:startTime,
			keyboardType:Ti.UI.KEYBOARD_NUMBER_PAD
		});
		/*startTime.addEventListener('change',function(){
			startTime.setSelection(startTime.value.length,startTime.value.length);
			Ti.API.info(startTime.value);
			if(startTime.value.length == 2||startTime.value.length == 5) {
        		startTime.value += ":";
        		Ti.API.info(startTime.value.length);
    		}
		});*/
		var endTime = Ti.UI.createTextField({
			top: 220,right:20,width:120,value:endTime,
			keyboardType:Ti.UI.KEYBOARD_NUMBER_PAD
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
			color:'white'
		});
		var descriptionLabel = Ti.UI.createLabel({
			text:'Description',
			top: 260,
			color:'white'
		});
		var description = Ti.UI.createTextField({
			top: 320,width:Ti.UI.FILL,autocorrect:true,
			height:100,keyboardType:Ti.UI.KEYBOARD_DEFAULT,
			returnKeyType: Ti.UI.RETURNKEY_GO
		});
		
		scrollView.add(description);
		scrollView.add(startTimeLabel);
		scrollView.add(endTimeLabel);
		scrollView.add(startTime);
		scrollView.add(endTime);
		
		var saveEventButton = Titanium.UI.createButton({
			bottom: 20,
			height:40,
			backgroundColor:'#46fb00',
			title:'Save Event',
			width:300
		});
		//var obj = [];
		scrollView.add(saveEventButton);
		saveEventButton.addEventListener('click',function(){
			var data = object.eventL1 +"-"+object.eventL2+"-"+object.eventL3+"//"+stTime+"//"+eTime+"//"+description.value+"//"+id;
			Ti.App.fireEvent('modal:passEventData',{info: data});
			self.close();
			//return object;
			//obj.push({el1: eventPicker1.getSelectedRow(0).title});
		});
		var cancelEventButton = Titanium.UI.createButton({
			bottom: 80,
			height:40,
			//backgroundColor:'#46fb00',
			title:'Cancel',
			width:300
		});
		scrollView.add(cancelEventButton);
		cancelEventButton.addEventListener('click',function(){
			self.close();
		});
		self.add(scrollView);
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
