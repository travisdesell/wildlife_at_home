Ti.include('eventsTag.js','picker1.js','events.js','getEventData.js');
function dataModal(stTime,eTime,spec){
	var self = Ti.UI.createWindow({
		title:'Add Event',
		modal:true
	});
	var species;
	if(spec==1){species='sharptailed_grouse';}
	if(spec==2){species='least_tern';}
	if(spec==3){species='piping_plover';}
	var events;
	var eventDataCat=[];
	var eventDataName=[];
	var eventDataPT=[];
	var eventDataID=[];
	var pickerOpt1=[];
	var pickerOpt2=[];
	var pickerOpt3=[];
	var eventData;
	getEventData(spec);
	Ti.App.addEventListener('vidEvents',function(info){
		//Ti.API.info(info.data);
		events = info.data;
		var startTime=getTime(stTime);
		var endTime=getTime(eTime);
		var object={
			evt1:'',
			evt2:'',
			evt3:''
		};
		eventData = events.split('**');
		
		for(var i in eventData){
			var j = eventData[i].split('-');
			Ti.API.info(j.length);
			Ti.API.info(j[0]+"-"+j[1]+"-"+j[2]+"-"+j[3]);
			if(j[0]!=''){
				eventDataCat.push(j[0]);
				eventDataName.push(j[1]);
				if(j[2]==''){
					eventDataPT.push('null');
				}
				eventDataID.push(j[3]);
			}
		}
		for(var k in eventDataCat){
			if (pickerOpt1.indexOf(k)=-1){
				pickerOpt1.push(k);
			}
			else{}
		}
		Ti.API.info(pickerOpt1);
	});
	
	return self;
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