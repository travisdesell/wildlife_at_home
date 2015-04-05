function initDraw(canvas) {

    var nothing_here = 0;
    var $_GET = {};
    if(document.location.toString().indexOf('?') !== -1) {
		var query = document.location
			.toString()
			.replace(/^.*?\?/, '')
			.replace(/#.*$/, '')
			.split('&');
		for(var i=0, l=query.length; i<l; i++) {
			var aux = decodeURIComponent(query[i]).split('=');
			$_GET[aux[0]] = aux[1];
		}
    }
    var project = $_GET['p'];
    if (!project) project = 1;
	
	$('#submit-selections-button:not(.bound)').addClass('bound').click(function() {
		console.log("the submit button was clicked!");

		var submission_info = {};

		if(!nothing_here)
		{

			for (var i = 0; i < element_count; i++) { //Get the information from each rectangle
		    	var current_element = elements[i];
			if (project === 1) var check = document.getElementById('check'+current_element.id).checked ? true : false;
			else var check = false;

		  	var rectangle_info = {
				width : current_element.style.width,
				height : current_element.style.height,
				left : current_element.style.left,
				top : current_element.style.top,
				nest : check, 
				species : species_ids[document.getElementById('speciesDropdown'+current_element.id).selectedIndex],
				comments : document.getElementById('comment'+current_element.id).value,
				image_id : $(".img-responsive").attr("id"),
				nothing_here : nothing_here
				};

		    	console.log("info for element " + i + " is: '" + JSON.stringify(rectangle_info) + "'");

		    	submission_info[i] = rectangle_info;
			}

			console.log("full info: " + JSON.stringify(submission_info)); //Turn rectangle info into JSON

			$.ajax({ //Send Rectangle info to submission script
		    	type: 'POST',
		    	url: './canvas_submission.php',
		    	data: { some_id : 532,
			    	data : JSON.stringify(submission_info) },
		    	dataType : 'text',
		    	success : function(response) {
				console.log("response from the server was: '" + response + "'");
		    	},
		    	error : function(jqXHR, textStatus, errorThrown) {
				console.log("error was thrown: '" + errorThrown + "'");
		    	},
		    	async: true
			});
		}
		else
		{
			var nothing = {
				image_id : $(".img-responsive").attr("id"),
				nothing_here : nothing_here,
				comments : document.getElementById('comments').value
			};

			//submission_info[0] = nothing;

			$.ajax({ //Send Rectangle info to submission script
		    	type: 'POST',
		    	url: './canvas_submission.php',
		    	data: { some_id : 532,
			    	data : JSON.stringify(nothing) },
		    	dataType : 'text',
		    	success : function(response) {
				console.log("response from the server was: '" + response + "'");
		    	},
		    	error : function(jqXHR, textStatus, errorThrown) {
				console.log("error was thrown: '" + errorThrown + "'");
		    	},
		    	async: true
			});


		}

			

	//	alert("Thx for the submission!");
		$('#submitModal').modal('show');
		//location.reload();

    });

    var mouse = {
        x: 0,
        y: 0,
        startX: 0,
        startY: 0
    };

    var buffer = 4;
    var original_top = 0;
    var original_left = 0;
    var is_dragging = false;

    var current_action = "";
    var current_element = null;
    var element_count = 0;
    var element_id = 0;
    var elements = [];
    var images = document.getElementsByClassName('img-responsive');
    var imag = images[0];
    var species = [];
    var species_ids = [];

    function setMousePosition(e) {
        var ev = e || window.event; //Moz || IE

        var rect = canvas.getBoundingClientRect();

        if (ev.pageX) { //Moz
            mouse.x = ev.pageX - window.pageXOffset;
            mouse.y = ev.pageY - window.pageYOffset;

        } else if (ev.clientX) { //IE
            mouse.x = ev.clientX - document.body.scrollLeft;
            mouse.y = ev.clientY - document.body.scrollTop;
        }

        mouse.x -= rect.left;
        mouse.y -= rect.top;

        mouse.x += canvas.offsetLeft;
        mouse.y += canvas.offsetTop;

//        console.log("mouse.y: " + mouse.y + ", ev.pageY: " + ev.pageY + ", window.pageYOffset: " + window.pageYOffset + ", document.body.scrollTop: " + document.body.scrollTop + ", canvas.offsetTop: " + canvas.offsetTop);
//        console.log("mouse.x: " + mouse.x + ", ev.pageX: " + ev.pageX + ", window.pageXOffset: " + window.pageXOffset + ", document.body.scrollLeft: " + document.body.scrollLeft + ", canvas.offsetLeft: " + canvas.offsetLeft);
    };

    function getRectanglePosition(element) {
        var rect_left = parseInt( element.style.left.substring(0, element.style.left.length - 2) );
        var rect_width = parseInt( element.style.width.substring(0, element.style.width.length - 2) );
        var rect_right = rect_left + rect_width;

        var rect_top = parseInt( element.style.top.substring(0, element.style.top.length - 2) );
        var rect_height = parseInt( element.style.height.substring(0, element.style.height.length - 2) );
        var rect_bottom = rect_top + rect_height;

        /*
           console.log("mouse.x: " + mouse.x + ", rect_left: " + rect_left + ", rect_right: " + rect_right);
           console.log("mouse.y: " + mouse.y + ", rect_top: " + rect_top + ", rect_bottom: " + rect_bottom);
           */

        if (Math.abs(mouse.x - rect_left) < buffer && Math.abs(mouse.y - rect_top) < buffer) {
            return "top left";
        }  else if (Math.abs(mouse.x - rect_right) < buffer && Math.abs(mouse.y - rect_top) < buffer) {
            return "top right";
        }  else if (Math.abs(mouse.x - rect_left) < buffer && Math.abs(mouse.y - rect_bottom) < buffer) {
            return "bottom left";
        }  else if (Math.abs(mouse.x - rect_right) < buffer && Math.abs(mouse.y - rect_bottom) < buffer) {
            return "bottom right";
        } else if (Math.abs(mouse.x - rect_left) < buffer && mouse.y >= rect_top - buffer && mouse.y <= rect_bottom + buffer) {
            return "left";
        } else if (Math.abs(mouse.x - rect_right) < buffer && mouse.y >= rect_top - buffer && mouse.y <= rect_bottom + buffer) {
            return "right";
        } else if (Math.abs(mouse.y - rect_top) < buffer && mouse.x >= rect_left - buffer && mouse.x <= rect_right + buffer) {
            return "top";
        } else if (Math.abs(mouse.y - rect_bottom) < buffer && mouse.x >= rect_left - buffer && mouse.x <= rect_right + buffer) {
            return "bottom";
        } else if (mouse.y >= rect_top - buffer && mouse.y <= rect_bottom + buffer && mouse.x >= rect_left - buffer && mouse.x <= rect_right + buffer) {
            return "move";
        } else {
            return "";
        }
    };
    
    //Ben
    $('#canvas').on('click', '.close-btn', function() {
		var id = $(this).parent().attr("id"); //Find the rectangle that this button belongs to
		elements.splice(jQuery.inArray($(this).parent()[0], elements), 1);
		element_count--;
		$(this).parent().remove()

		//remove selection information when rectangle is removed
		var elem = document.getElementById('S'+id); //Jaeden
		elem.remove(); //Jaeden
		if (element_count < 1) $('#submit-selections-button').prop('disabled', true);
 


	    console.log("Close button was clicked");
    });

    $('#modalSubButton').on('click', function() {
	    location.reload();
    });


    canvas.onmousemove = function (e) {
	setMousePosition(e);
	if (current_action == "") {//Change the cursor when moused over certain areas
	    for (var i = 0; i < element_count; i++) {
		var position = getRectanglePosition(elements[i]);

		if (position == "") {
		    elements[i].style.border = '3px solid #FF0000';
		    elements[i].children[0].style.visibility = "hidden";
		    canvas.style.cursor = "default";
		} else {
		    elements[i].children[0].style.visibility = "visible";
		    elements[i].style.border = '5px solid #FF0000';
		}

		if (position == "top left") {
		    canvas.style.cursor = "nwse-resize";

		}  else if (position == "top right") {
		    //canvas.style.cursor = "nesw-resize";

		}  else if (position == "bottom left") {
		    canvas.style.cursor = "nesw-resize";

		}  else if (position == "bottom right") {
		    canvas.style.cursor = "nwse-resize";

		} else if (position == "left") {
		    canvas.style.cursor = "ew-resize";

		} else if (position == "right") {
		    canvas.style.cursor = "ew-resize";

		} else if (position == "top") {
		    canvas.style.cursor = "ns-resize";

		} else if (position == "bottom") {
		    canvas.style.cursor = "ns-resize";

		} else if (position == "move") {
		    canvas.style.cursor = "move";
		}
	   }
	}


	if (is_dragging) {//If the mouse is dragging, allow creation of boxes or adjusting
		close_button = current_element.children[0];
		close_button.style.left = current_element.offsetWidth - close_button.clientWidth + 'px';
		close_button.style.top = current_element.clientTop - 14 + 'px';

		    if (current_action == "creating element") {
			current_element.style.width = Math.abs(mouse.x - mouse.startX) + 'px';
			current_element.style.height = Math.abs(mouse.y - mouse.startY) + 'px';
			current_element.style.left = (mouse.x - mouse.startX < 0) ? mouse.x + 'px' : mouse.startX + 'px';
			current_element.style.top = (mouse.y - mouse.startY < 0) ? mouse.y + 'px' : mouse.startY + 'px';
				    } else if (current_action == "move element") {
			/*
			   console.log("current_action != '" + current_action + "', element_count: " + element_count);
			   console.log("startX: " + mouse.startX + ", mouse.x: " + mouse.x + ", startY: " + mouse.startY + ", mouse.y: " + mouse.y);
			   */
			canvas.style.cursor = "move";
		
		if(   ((original_left+(mouse.x-mouse.startX))>15)   &&   ((original_top+(mouse.y-mouse.startY))>0)  && ((original_left+original_width+(mouse.x-mouse.startX))<1040)    &&   ((original_top+original_height+(mouse.y-mouse.startY))<768))   //fixed dragging outside image, Jaeden
			{
				current_element.style.left = (original_left + (mouse.x - mouse.startX)) + 'px';
				current_element.style.top = (original_top + (mouse.y - mouse.startY)) + 'px';
			}		    
			}


		      else if (current_action == "right resize") {
			current_element.style.width = (original_width + (mouse.x - mouse.startX)) + 'px';

		    } else if (current_action == "left resize") {
			current_element.style.left = (original_left + (mouse.x - mouse.startX)) + 'px';
			current_element.style.width = (original_width - (mouse.x - mouse.startX)) + 'px';

		    } else if (current_action == "bottom resize") {
			current_element.style.height = (original_height + (mouse.y - mouse.startY)) + 'px';

		    } else if (current_action == "top resize") {
			current_element.style.top = (original_top + (mouse.y - mouse.startY)) + 'px';
			current_element.style.height = (original_height - (mouse.y - mouse.startY)) + 'px';

		    } else if (current_action == "top left resize") {
			current_element.style.top = (original_top + (mouse.y - mouse.startY)) + 'px';
			current_element.style.height = (original_height - (mouse.y - mouse.startY)) + 'px';

			current_element.style.left = (original_left + (mouse.x - mouse.startX)) + 'px';
			current_element.style.width = (original_width - (mouse.x - mouse.startX)) + 'px';

		    } else if (current_action == "top right resize") {
			current_element.style.top = (original_top + (mouse.y - mouse.startY)) + 'px';
			current_element.style.height = (original_height - (mouse.y - mouse.startY)) + 'px';

			current_element.style.width = (original_width + (mouse.x - mouse.startX)) + 'px';

		    } else if (current_action == "bottom left resize") {
			current_element.style.height = (original_height + (mouse.y - mouse.startY)) + 'px';

			current_element.style.left = (original_left + (mouse.x - mouse.startX)) + 'px';
			current_element.style.width = (original_width - (mouse.x - mouse.startX)) + 'px';

		    } else if (current_action == "bottom right resize") {
			current_element.style.height = (original_height + (mouse.y - mouse.startY)) + 'px';

			current_element.style.width = (original_width + (mouse.x - mouse.startX)) + 'px';

		    } else {
			console.log("current_action != '" + current_action + "', element_count: " + element_count);
		    }
		}
		e.preventDefault();
		//e.stopPropagation();
	     	imag.style.MozUserSelect = "none";
    }

    //Ben
    canvas.onmouseup = function(e) {//Set dragging to false, so that mousemove won't respond to resizing
	     is_dragging = false;
	     console.log("dragging is false");
             setMousePosition(e);
             //console.log("click: mouse.x:" + mouse.x + ", mouse.y: " + mouse.y);
             console.log("finished action: '" + current_action + "'");

             current_element = null;
             current_action = "";
             canvas.style.cursor = "default";
    }


    canvas.onmousedown = function(e) {//Adjustment to use dragging, Ben
	imag.draggable = false;
	is_dragging = true;
	//imag.style.MozUserSelect = "auto";
	if (    (e.which == 1)    &&    (nothing_here === 0)    ) {
		    //get the position of the mouse.
		    setMousePosition(e);

		    //if the mouse is on an element, resize it
		    //if the mouse is not on an element, create one
		    current_element = null;
		    current_action = "";

		    for (var i = 0; i < element_count; i++) {
			var position = getRectanglePosition(elements[i]);

			if (position == "top left") {
			    current_element = elements[i];
			    current_action = "top left resize";
			    canvas.style.cursor = "nwse-resize";

			}  else if (position == "top right") {
			    current_element = elements[i];
			    //current_action = "top right resize";
			    //canvas.style.cursor = "nesw-resize";

			}  else if (position == "bottom left") {
			    current_element = elements[i];
			    current_action = "bottom left resize";
			    canvas.style.cursor = "nesw-resize";

			}  else if (position == "bottom right") {
			    current_element = elements[i];
			    current_action = "bottom right resize";
			    canvas.style.cursor = "nwse-resize";

			} else if (position == "left") {
			    current_element = elements[i];
			    current_action = "left resize";
			    canvas.style.cursor = "ew-resize";

			} else if (position == "right") {
			    current_element = elements[i];
			    current_action = "right resize";
			    canvas.style.cursor = "ew-resize";

			} else if (position == "top") {
			    current_element = elements[i];
			    current_action = "top resize";
			    canvas.style.cursor = "ns-resize";

			} else if (position == "bottom") {
			    current_element = elements[i];
			    current_action = "bottom resize";
			    canvas.style.cursor = "ns-resize";

			} else if (position == "move") {
			    current_element = elements[i];
			    current_action = "move element";
			    canvas.style.cursor = "move";

			} else {
			    elements[i].style.border = '3px solid #FF0000';
			}
		    }

		    mouse.startX = mouse.x;
		    mouse.startY = mouse.y;
	//            console.log("mouse.startY: " + mouse.startY + "mouse.startx: " + mouse.startX);

		if( (mouse.x > 15) && (mouse.x < 1040) && (mouse.y > 0) && (mouse.y < 768) ) //this if statement: Jaeden (makes sure you can't make boxes outside image)
		{
				

		    if (current_element != null) {
			current_element.style.border = '2px solid #FF0000';
			console.log("selected an element, performing action: '" + current_action + "'");

			//initialize the original top corner of the rectangle
			original_left = parseInt( current_element.style.left.substring(0, current_element.style.left.length - 2) ); 
			original_top = parseInt( current_element.style.top.substring(0, current_element.style.top.length - 2) ); 
			original_height = parseInt( current_element.style.height.substring(0, current_element.style.height.length - 2) ); 
			original_width = parseInt( current_element.style.width.substring(0, current_element.style.width.length - 2) ); 
		    } else {
			current_action = "creating element";

			current_element = document.createElement('div');
			current_element.className = 'rectangle';
			
			//Yellow id number inside box
			current_element.style.left = mouse.x + 'px';
			current_element.style.top = mouse.y + 'px';
			current_element.style.width = "50px"
			current_element.innerHTML = "&nbsp;"+(element_id+1);
			current_element.style.height = "50px";

			//Close button
			close_button = document.createElement('span');
			close_button.className = 'close-btn';
			closex = document.createElement('a');
			closex.innerHTML = 'X';
			close_button.style.left = '35px';
			close_button.appendChild(closex);
			current_element.appendChild(close_button);
			current_element.id = element_id;

			canvas.appendChild(current_element);
			canvas.style.cursor = "crosshair";

							

			//add selection information when a rectangle is created

			var table = "<div class='well well-small' id='S" + element_id + "'>"+
				"<table border='1'>"+
					"<tr>"+
						"<td align='center'>Selection " + (element_id+1) + "</td>" +
						"<td align='right'><button id='remove"+element_id+"' class='btn delete btn-danger' >Remove Selection</button></td>"+
					"</tr>"+
					"<tr>"+
						"<td align='center'>Species:</td>"+
							"<td> <select id='speciesDropdown"+element_id+"'>";
			
			if (species.length < 1) {
				$.post("canvas_select.php", 'p=' + project,
				function(data) {
					for (var i = 0; i < data.length; ++i) {
						species.push(data[i].name);
						species_ids.push(data[i].id);
					}				
					for (var i = 0; i < species.length; ++i) {
						table += "<option value='"+species[i]+"'>"+species[i]+"</option>";
					}//BCC
					
					table += "</select></td>"+
						"</tr>"+
						"<tr>";
					if (project == 1) table += "<td align='center'>On nest?&nbsp;<input type='checkbox' id='check"+element_id+"'>&nbsp;</input> </td>";
					else table += "<td align='center'></td>";
					table += "<td><textarea type='text' size='34' maxlength='512' value ='' id='comment"+element_id+"' placeholder='comments' row='1'></textarea></td>" + 
						"</tr>"+
					"</table>"+
					"</div>"; //Jaeden
					$("#selection-information").append(table);
					element_id++;

				}, "json");
				
			} else {
				for (var i = 0; i < species.length; ++i) {
					table += "<option value='"+species[i]+"'>"+species[i]+"</option>";
				}//BCC
				table += "</select></td>"+
					"</tr>"+
					"<tr>";
				if (project == 1) table += "<td align='center'>On nest?&nbsp;<input type='checkbox' id='check"+element_id+"'>&nbsp;</input> </td>";
				else table += "<td align='center'></td>";
				table += "<td><textarea type='text' size='34' maxlength='512' value ='' id='comment"+element_id+"' placeholder='comments' row='1'></textarea></td>" + 
					"</tr>"+
				"</table>"+
				"</div>"; //Jaeden
				$("#selection-information").append(table);
				element_id++;
			}


					
			
			elements[element_count] = current_element;
			element_count++;
			if (element_count === 1) $('#submit-selections-button').prop('disabled', false);
		    }
		}
	}
    }

    $("body").on("click", ".delete", function() {
	var btnId = $(this).attr("id");
	var elemId = btnId.substring(6);
	var selectId = "S"+elemId;

	var elem1 = document.getElementById(elemId);

	elements.splice(elements.indexOf(elem1), 1);
	element_count--;
	if (element_count < 1) $('#submit-selections-button').prop('disabled', true);

	elem1.remove();

	var elem2 = document.getElementById(selectId);
	elem2.remove();

    }); //Jaeden

    $("body").on("click", ".nothing", function() {
	//this changes the visibility of the comments box for an empty image and the visibility of the selections
	//also toggles ability to draw boxes
	if(nothing_here === 0) 
	{
		nothing_here=1;
		$('#submit-selections-button').prop('disabled', false);
  		document.getElementById("comments").style.display = 'initial';

		var size = document.getElementsByClassName('well').length;
		for(var i=0; i<size; i++)
			document.getElementsByClassName('well')[i].style.display='none';

		size = document.getElementsByClassName('rectangle').length;
		for(var i=0; i<size; i++)
			document.getElementsByClassName('rectangle')[i].style.display='none';
		
	}
	else if(nothing_here === 1)
	{
		nothing_here =0;
  		document.getElementById("comments").style.display = 'none';
		if(element_count < 1 ) $('#submit-selections-button').prop('disabled', true);

		var size = document.getElementsByClassName('well').length;
		for(var i=0; i<size; i++)
			document.getElementsByClassName('well')[i].style.display='';

		size = document.getElementsByClassName('rectangle').length;
		for(var i=0; i<size; i++)
			document.getElementsByClassName('rectangle')[i].style.display='';

	}

    });  //jaeden

//TODO add comment box when 'nothing here' button is clicked -Jaeden

}


$(document).ready(function() {	
    initDraw(document.getElementById('canvas'));
    $("#comments").val('');
    $('#submit-selections-button').prop('disabled', true);

});
