Ti.include('nextVid.js');
function endVid(info,vidID,usrID,species,loc,rand){
	var xhr = Titanium.Network.createHTTPClient();
	if(info != ''){
		//var species = spec;
		Ti.API.info(info);
		xhr.open('POST', 'http://134.129.213.31/csg/wildlife_jhuhn/mobileAddObservations.php');
		var data = {info:info,vidID:vidID,usrID:usrID,loc:loc,species:species,rand:rand};
		//Ti.API.info(info);
		xhr.send(data);
	}
	else{
		alert("Error");
	}
	xhr.onload = function(){
		Ti.API.info("Trying to get data");
		Ti.API.info(this.responseText);
		//Ti.API.info(json);
		//TI.App.fireEvent('valid',{info:'true'});
	};
}