function connect(user,passwd){
	var xhr = Titanium.Network.createHTTPClient();
	Ti.API.info("connect.js");
	if(user != '' && passwd != ''){
		xhr.open('POST', 'http://134.129.213.31/csg/wildlife_jhuhn/mobileLogin.php');
		var data = {user: user,pass:passwd};
		xhr.send(data);
	}
	else{
		alert("Email and Password required");
	}
	xhr.onload = function(){
		Ti.API.info("");
		var json = JSON.parse(this.responseText);
		if(json.logged == true){
	    	Ti.App.fireEvent('valid',{usrID: json.id});
	    	return;
		}
		else if(json.logged==false){
	    	Ti.App.fireEvent('valid',{usrID: -1});
	    	return;
		}
	};
}
