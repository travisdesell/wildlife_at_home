function getSpeciesVid(userID,species,loc){
	var xhr = Titanium.Network.createHTTPClient();
	if(userID != '' && species != ''&&loc!=''){
		xhr.open('POST', 'http://134.129.213.31/csg/wildlife_jhuhn/mobileGetVideo.php');
		var data = {id:userID,species:species,loc:loc};
		xhr.send(data);
	}
	else{
		alert("Error");
	}
	xhr.onload = function(){
		Ti.API.info("Trying to get data");
		Ti.API.debug(this.responseText);
		//Ti.API.info(this.responseXML);
		var json = JSON.parse(this.responseText);
		
		if(json.worked!= 'f'){
			Ti.API.info('got Vid');
	    	Ti.App.fireEvent('vidInfo',{info:'true',vidID: json.vidID,file:json.fp,startTime:json.startTime,animalID:json.animalID});
	    	return;
		}
		if(json.worked=='f') { 
			Ti.API.info('noVid');
	    	Ti.App.fireEvent('vidInfo',{info: 'false'});
	    	return;
		}
		//Ti.API.info(json);
		//TI.App.fireEvent('valid',{info:'true'});
	};
}