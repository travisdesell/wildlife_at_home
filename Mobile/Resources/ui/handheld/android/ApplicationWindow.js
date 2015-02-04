Ti.include('connect.js','selectSpecies.js');
function ApplicationWindow(title){
	var self = Ti.UI.createWindow({title:title,orientationModes:[Titanium.UI.PORTRAIT]});
	var userField = Ti.UI.createTextField({
		top:160,left:160,width:150
	});
	var userLabel = Ti.UI.createLabel({
		top:165,text:'Email:',left:60
	});
	var passField = Ti.UI.createTextField({
		top:220,left:160,width:150,passwordMask:true
	});
	var passLabel = Ti.UI.createLabel({
		top:225,text:'Password:',left:60
	});
	var loginButton = Ti.UI.createButton({
		top:280,left:60,width:250,backgroundColor:'#0004FF',title:'Log In'
	});
	
	self.add(userField);
	self.add(userLabel);
	self.add(passField);
	self.add(passLabel);
	self.add(loginButton);
	loginButton.addEventListener('click',function(){
		connect(userField.value,passField.value);
	});
	Ti.App.addEventListener('valid',function(info){
		Ti.API.info(info.usrID);
		if(info.usrID>-1){
			var species = selectSpecies(info.usrID);
			species.open();
			self.close();
		}
		if(info.usrID==-1){
			alert("User Name/Password Incorrect");
			userField.value='';
			passField.value='';
		}
	});
	return self;
};
module.exports = ApplicationWindow;
