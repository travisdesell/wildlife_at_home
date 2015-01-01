function connect(user,pass){
	var xhr = Titanium.Network.createHTTPClient();
	Ti.API.info(user,pass);
	if(user != '' && pass != ''){
		xhr.open('POST', 'http://134.129.213.31/wildlife/jhuhn/test/mobileLogin.php');
		var data = {user: user, pass: pass};
		xhr.send(data);
	}
	else{
		alert("Username and Password required");
	}
	Ti.API.info('working');
	xhr.onload = function(){
		var json = JSON.parse(this.responseText);
		if(json.logged == true){
			Ti.API.info('Matches info');
	    	Ti.App.fireEvent('valid',{info: 'true'});
	    	return;
		}
		else{
			Ti.API.info('Username/Password doesn\'t match');
	    	Ti.App.fireEvent('valid',{info: 'false'});
	    	return;
		}
	};
}

/*	xhr.onload = function(){
		//Titanium.API.info('test');
	    var json = JSON.parse(xhr.responseText);
	    if (!json) { 
	    
	    	Titanium.API.info('Error - Null return!'); 
	        return;
	    }
	    var json = json.user;
	    var pos;
	    for( pos=0; pos < json.length; pos++){
	    	if(json[pos].usr_name == user){
	    		if(json[pos].password==pass){
	    			Ti.API.info('Matches info');
	    			Ti.App.fireEvent('valid',{info: 'true'});
	    			return;
	    		}
	    		else{
	    			Ti.API.info('Password doesn\'t match');
	    			Ti.App.fireEvent('valid',{info: 'false'});
	    			return;
	    		}
	    	}
	        //Ti.API.info(json[pos].usr_name, json[pos].password);
	    }
	    Ti.API.info('NO USER BY THAT NAME');
	    Ti.App.fireEvent('valid',{info: 'false'});
	    //Ti.API.info(json);
	    return;
	};
	xhr.open('GET', '192.168.1.129/connect.php');
	xhr.send();
}*/