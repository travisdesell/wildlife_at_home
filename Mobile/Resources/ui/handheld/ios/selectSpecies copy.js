Ti.include('watchVid.js');
function selectSpecies(){
	var self = Ti.UI.createWindow({
		title:'wildlife@Home',
		bcakgroundColor:'#FFFFFF'
	});
	var grouseBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Grouse Video',
		top:140
	});
	var ternBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Tern Video',
		top:240
	});
	var ploverBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Plover Video',
		top:340
	});
	var grouseLocBeldenBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Belden Location',
		top:140
	});
	var grouseLocBlaisdellBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Blaisdell Location',
		top:240
	});
	var grouseLocLostwoodBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Lostwood Location',
		top:340
	});
	var ternLocMissouriRvrBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Missouri River Location',
		top:240
	});
	var ploverLocMissouriRvrBtn = Ti.UI.createButton({
		height:80,width:200,title:'Watch Missouri River Location',
		top:240
	});
	var backBtn= Ti.UI.createButton({
		bottom: 20,
		height:40,
		backgroundColor:'#8A8A8A',
		title:'Back',
		width:300
	});
	self.add(grouseBtn);
	self.add(ternBtn);
	self.add(ploverBtn);
	
	grouseBtn.addEventListener('click',function(){
		self.removeAllChildren();
		self.add(grouseLocBeldenBtn);
		self.add(grouseLocBlaisdellBtn);
		self.add(grouseLocLostwoodBtn);
		self.add(backBtn);
	});
	ternBtn.addEventListener('click',function(){
		self.removeAllChildren();
		self.add(ternLocMissouriRvrBtn);
		self.add(backBtn);
	});
	ploverBtn.addEventListener('click',function(){
		
		self.removeAllChildren();
		self.add(ploverLocMissouriRvrBtn);
		self.add(backBtn);
	});
	grouseLocBeldenBtn.addEventListener('click',function(){
		var video = watchVid();
		video.open();
		self.close();
	});
	grouseLocBlaisdellBtn.addEventListener('click',function(){
		var video = watchVid();
		video.open();
		self.close();
	});
	grouseLocLostwoodBtn.addEventListener('click',function(){
		var video = watchVid();
		video.open();
		self.close();
	});
	ternLocMissouriRvrBtn.addEventListener('click',function(){
		var video = watchVid();
		video.open();
		self.close();
	});
	ploverLocMissouriRvrBtn.addEventListener('click',function(){
		var video = watchVid();
		video.open();
		self.close();
	});
	backBtn.addEventListener('click',function(){
		self.removeAllChildren();
		self.add(grouseBtn);
		self.add(ternBtn);
		self.add(ploverBtn);
	});
	return self;
};
module.exports = selectSpecies;
