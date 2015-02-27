Ti.include('eventListItemDetail.js');
var listEventTemplate = {
	seperateData:[
		{
			type:Ti.UI.Label,
			bindID:'title'		
		},
		{
			type:Ti.UI.Label,
			bindID:'typeL2'		
		},
		{
			type:Ti.UI.Label,
			bindID:'typeL3'		
		},
		{
			type:Ti.UI.Label,
			bindID:'startTime'			
		},
		{
			type:Ti.UI.Label,
			bindID:'endTime'
						
		},
		{
			type:Ti.UI.Label,
			bindID:'description'			
		},
	],
};
function eventList(data){
	//Ti.API.info(data.length);
	Ti.API.info(data.toString());
	var listItems = Ti.UI.createListView({defaultItemTemplate:listEventTemplate,top:35,backgroundColor:'white'});
	var sections=[];
	var itemData=[];
	var eventData=[];
	var eventDataArray=[];
	var eventTypes=[];
	var startTime=[];
	var endTime=[];
	var desciption=[];
	var itemSection = Ti.UI.createListSection({headerTitle:"Events"});
	
	if(data.indexOf('*')>-1){
		eventData = data.split('*');
		var size = eventData.length;
		var i = 0;
		while(i<size){
			eventDataArray = eventData[i].split('//');
			eventTypes = eventDataArray[0].split('-');
			startTime = eventDataArray[1];
			endTime = eventDataArray[2];
			description = eventDataArray[3];
			
			itemData.push({properties:{title:eventTypes[0],
					typeL2:eventTypes[1],
					typeL3:eventTypes[2],
					startTime:startTime,
					endTime:endTime,
					description:description
					}
			});
			i++;
		}
	}
	else{
		eventDataArray = data.split('//');
		eventTypes = eventDataArray[0].split('-');
		Ti.API.info(eventTypes.length);
		startTime = eventDataArray[1];
		endTime = eventDataArray[2];
		description = eventDataArray[3];
		
		itemData.push({properties:{title:eventTypes[0],
				typeL2:eventTypes[1],
				typeL3:eventTypes[2],
				startTime:startTime,
				endTime:endTime,
				description:description
				}
		});
		Ti.API.info('Done');
	}
	itemSection.setItems(itemData);
	//Ti.API.info(itemSection);
	sections.push(itemSection);
	listItems.sections = sections;
	var selItem;
	listItems.addEventListener('itemclick', function(e){
		var a = itemSection.getItemAt(e.itemIndex);
		selItem=e.itemIndex;
		//Ti.API.info(a);
		listItemDetail(a);
	});
	Ti.App.addEventListener('modal:delete', function(deleteData){
		//itemSection.deleteItemsAt(selItem,1);
		var newData = deleteEvt(data,deleteData.info);
		eventList(newData);
	});
	Ti.API.info('Done with list create');
	//self.open({modal:true});
	return listItems;
}

function deleteEvt(data, itmDelete){
	Ti.API.info(data);
	Ti.API.info(itmDelete.properties.title);
	var deleteData = [];
	if(data.indexOf('*')>-1){
		deleteData = data.split('*');
		var deleteSize = deleteData.length;
		var j = 0;
		while(j<deleteSize){
			var deleteDataArray = deleteData[j].split('//');
			var deleteComp = deleteDataArray[0];
			if(deleteComp == (itmDelete.properties.title+'-'+itmDelete.properties.typeL2+'-'+itmDelete.properties.typeL3)){
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