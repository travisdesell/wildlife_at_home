function getEventData(spec){
	var xhr = Titanium.Network.createHTTPClient();
	if(spec != ''){
		var species = spec;
		Ti.API.info(species);
		xhr.open('POST', 'http://134.129.213.31/csg/wildlife_jhuhn/mobileGetEvents.php');
		var data = {species:spec};
		xhr.send(data);
	}
	else{
		alert("Error");
	}
	xhr.onload = function(){
		Ti.API.info("Trying to get data");
		//Ti.API.debug(this.responseText);
		//Ti.API.info(this.responseText);
		var json =JSON.parse(this.responseText);
		//Ti.API.info(json.data);
		Ti.App.fireEvent('vidEvents',{info:true,data:json.data});
		return;
		/*if(json.success!= false){
			//Ti.API.info('got data');
	    	Ti.App.fireEvent('vidEvents',{info:true,data:json.eventData});
	    	return;
		}
		else { 
			Ti.API.info('noVid');
	    	Ti.App.fireEvent('vidEvents',{info: 'false'});
	    	return;
		}*/
		//Ti.API.info(json);
		//TI.App.fireEvent('valid',{info:'true'});
	};
}
