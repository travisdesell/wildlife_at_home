Ti.include('events.js','eventsTag.js');
function eventModal(){
	var object = {
		eventL1: '',
		eventL2: '',
		eventL3: ''
	};
	eventDataTerritorial=['Nest Defense','Predator','Non-Predator Animal','Human'];
	eventDataCamera=['Attack','Physical Inspection','Observation'];
	eventDataChick=['Eggs Hatching','In Video'];
	eventDataError=['Too Dark','Video Error','Camera Issue'];
	eventDataMisc=['Unpecified'];
	eventDataBehavior=['Not In Video','On Nest','Off Nest'];
	eventDataCare=['Brooding Chicks','Eggshell Removal'];
	
	var eventWindow = Titanium.UI.createWindow({
		title:'Add Event',
		//exitOnClose:true,
		modal:true
		//fullscreen:false
	});
	var eventPicker1 = Titanium.UI.createPicker({
		top: 20,
		width:300
	});
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
	eventWindow.add(eventPicker1);
	eventPicker1.addEventListener('change',function(){
		if(eventPicker1.getSelectedRow(0).title == 'Territorial'){
		//Ti.API.info("You selected row: "+ eventPicker1.getSelectedRow(0).title);
			object.eventL1='Territorial';
			var eventPicker2 = events(eventDataTerritorial);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Camera Interaction'){
			object.eventL1='Camera Interaction';
			var eventPicker2 = events(eventDataCamera);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Chick Behavior'){
			object.eventL1='Chick Behavior';
			var eventPicker2 = events(eventDataChick);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Error'){
			object.eventL1='Error';
			var eventPicker2 = events(eventDataError);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Miscellaneous'){
			object.eventL1='Miscellaneous';
			var eventPicker2 = events(eventDataMisc);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Parent Behavior'){
			object.eventL1='Parent Behavior';
			var eventPicker2 = events(eventDataBehavior);
			eventWindow.add(eventPicker2);
		}
		else if(eventPicker1.getSelectedRow(0).title == 'Parent Care'){
			object.eventL1='Parent Care';
			var eventPicker2 = events(eventDataCare);
			eventWindow.add(eventPicker2);
		}
		var predatorSelection = ['Raptor','Skunk','Badger','Fox','Racoon','Coyote','Crow/Raven','Gull'];
		var onNestSelection = ['Sitting','Standing'];
		var offNestSelection = ['Sitting','Standing','Walking','Flying'];
		var broodingSelection = ['On Nest','Off Nest'];
		var nonPredSelection = ['Porcupine','Deer','Rodent','Songbird','Canada Goose','Other Waterfowl','Phesant','Other Sharp-Tailed Grouse','Other Plover','Other Tern','Salamander','Frog/Toad','Insect','Snake','Horse','Cattle'];
		var humanSelection = ['Researcher','Other','On Nest','Off Nest'];
		eventPicker2.addEventListener('change',function(){
			if(eventPicker2.getSelectedRow(0).title == 'Predator'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(predatorSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
			else if(eventPicker2.getSelectedRow(0).title == 'Non-Predator Animal'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(nonPredSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
			else if(eventPicker2.getSelectedRow(0).title == 'Human'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(humanSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
			else if(eventPicker2.getSelectedRow(0).title == 'Brooding Chicks'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(broodingSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
			else if(eventPicker2.getSelectedRow(0).title == 'On Nest'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(onNestSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
			else if(eventPicker2.getSelectedRow(0).title == 'Off Nest'){
				object.eventL2=eventPicker2.getSelectedRow(0).title;
				var eventPicker3 = eventsTag(offNestSelection);
				eventWindow.add(eventPicker3);
				eventPicker3.addEventListener('change',function(){
					object.eventL3=eventPicker3.getSelectedRow(0).title;
				});
			}
		});
	});
	var saveEventButton = Titanium.UI.createButton({
		bottom: 20,
		height:40,
		backgroundColor:'#46fb00',
		title:'Save Event',
		width:300
	});
	//var obj = [];
	eventWindow.add(saveEventButton);
	eventWindow.open({modal:true});
	saveEventButton.addEventListener('click',function(){
		TI.App.
		Ti.API.info(object.eventL1 + " "+ object.eventL2+ " "+ object.eventL3);
		eventWindow.close();
		//return object;
		//obj.push({el1: eventPicker1.getSelectedRow(0).title});
	});
	//return eventWindow;	
}