function deleteEvt(data, itmDelete){
	Ti.API.info(data);
	Ti.API.info(itmDelete);
	var itemDelSplit = itmDelete.split('//');
	var evtDelType = itemDelSplit[0].split('-');
	var deleteData = [];
	if(data.indexOf('*')>-1){
		deleteData = data.split('*');
		var deleteSize = deleteData.length;
		var j = 0;
		while(j<deleteSize){
			var deleteDataArray = deleteData[j].split('//');
			var deleteComp = deleteDataArray[0];
			if(deleteComp == (evtDelType[0]+'-'+evtDelType[1]+'-'+evtDelType[2])){
				Ti.API.info('delete item');
				Ti.API.info(deleteData.length);
				Ti.API.info(deleteData);
				deleteData.splice(j,1);
				Ti.API.info(j);
				Ti.API.info(deleteData);
				Ti.API.info(deleteData.length);
				return deleteData;
				//j++;
				//Ti.API.info(j);
			}
			j++;
		}
		//Pass data back to eventList to create new list
	}
	else{
		deleteData = data.split('//');
		deleteComp = deleteData[0];
	}
}