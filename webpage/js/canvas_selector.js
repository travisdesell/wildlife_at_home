var canvasSelector = function (canvas, image, context) {
    this.canvas = canvas;
    this.canvasHTML = canvas[0];
    this.image = image;
    this.ctx = this.canvasHTML.getContext("2d");
    this.mc = new Hammer.Manager(this.canvasHTML);
    this.callback = context.callback || null;
    this.deleteCallback = context.deleteCallback || null;
    this.resizeFunc = context.onResize || this.onResize;
    this.logging = context.logging === true;
    this.minSize = context.minSize || 20;
    this.errorMargin = context.errorMargin || 5;
    this.allowOverlap = (context.allowOverlap !== false ? true : false);
    this.progressBarX = context.progressBarX || null;
    this.progressBarY = context.progressBarY || null;
    this.scaleArea = context.scaleArea || null;
    
    // variables that get updated automatically
    this.rectangles = [];
    this.movingRectangle = null;
    this.resizingRectangle = null;
    this.initLeft = 0;
    this.initTop = 0;
    this.initScale = 1.0;
    this.curLeft = this.initLeft;
    this.curTop = this.initTop;
    this.curScale = this.initScale;
    
    // add in our supported gestures
    var doubleTap = new Hammer.Tap({ event: 'doubletap', taps: 2, posThreshold: 20, threshold: 5 });
    var tripleTap = new Hammer.Tap({ event: 'tripletap', taps: 3, posThreshold: 20, threshold: 5 });
    this.mc.add(new Hammer.Pan({ direction: Hammer.DIRECTION_ALL, threshold: 0 }));
    this.mc.add(doubleTap);
    this.mc.add(tripleTap);
    this.mc.add(new Hammer.Pinch({ threshold: 0 }));
    
    // register the function with the event
    var savedThis = this;
    this.mc.on("panstart panmove panend", function (ev) {
        savedThis.onPan(savedThis, ev);
    });
    this.mc.on("doubletap", function (ev) {
        savedThis.onDoubleTap(savedThis, ev);
    });
    this.mc.on("pinchstart pinchmove pinchend", function (ev) {
        savedThis.onPinch(savedThis, ev);
    });
    this.mc.on("tripletap", function (ev) {
        savedThis.onTripleTap(savedThis, ev);
    });
    
    // make sure we can double and triple tap
    tripleTap.recognizeWith(doubleTap);
    doubleTap.requireFailure(tripleTap);
    
    // add support for changing cursor type
   	this.canvas.mousemove(function (ev) {
    	savedThis.mouseMoved(savedThis, ev);
    });
    
    // add scrollwheel zoom support
    this.canvas.mousewheel(function (ev) {
    	var zoom = 0.025;
    	if (ev.deltaY > 0) zoom = 1 + zoom;
    	else zoom = 1 - zoom;

        var offset = savedThis.canvas.offset();
    	
    	savedThis.onPinch(savedThis, {
    		"type": "pinchend",
    		"scale": zoom,
            "center": {
                'x': ev.clientX - offset.left,
                'y': ev.clientY - offset.top 
            }
    	});

    	return false;
    });

    // see if we were sent in any rectangles
    if (context.rectangles) {
        // make sure we're sorted
        context.rectangles.sort(function(a,b) {
            return a.id - b.id;
        });

        // add them all programattically
        context.rectangles.forEach(function(e) {
            savedThis.addRectangle(savedThis, e, false);
        });
    }
};

canvasSelector.prototype.addRectangle = function(obj, rect, redraw) {
	obj.rectangles.push(rect);

	if (redraw !== false) obj.redrawCanvas(obj);
	
	// call the callback
	if (obj.callback) obj.callback(rect.id);
};

/** When the mouse moves, change the cursor */
canvasSelector.prototype.mouseMoved = function(obj, ev) {
    var point = obj.getScaledPoint(obj, {
    	"center": {
    		"x": ev.clientX,
    		"y": ev.clientY
    	}
    });
    
    var movingRectangle = obj.getRectangle(obj, point.x, point.y);
    if (movingRectangle) {
        var resizingRectangle = obj.isRectangleCorner(obj, movingRectangle, point.x, point.y);
        if (!resizingRectangle) {
            obj.canvas.css("cursor", "move");
        } else if (resizingRectangle.corner == 1 || resizingRectangle.corner == 3) {
            obj.canvas.css("cursor", "nwse-resize");
        } else if (resizingRectangle.corner == 2 || resizingRectangle.corner == 4) {
            obj.canvas.css("cursor", "nesw-resize");
        } else if (resizingRectangle.side == 1 || resizingRectangle.side == 3) {
            obj.canvas.css("cursor", "ew-resize");
        } else if (resizingRectangle.side == 2 || resizingRectangle.side == 4) {
        	obj.canvas.css("cursor", "ns-resize");
        }
    } else {
    	obj.canvas.css("cursor", "default");
    }
};

/** Logging function that can be turned on via context.logging. */
canvasSelector.prototype.logEvent = function(str) {
    if (this.logging) console.log(str);
};

/** Gets the center point of th event based on our offsets. */
canvasSelector.prototype.getCenter = function(obj, ev) {
	var offset = obj.canvas.offset();
	return {
		'x': $(document).scrollLeft() + ev.center.x - offset.left,
		'y': $(document).scrollTop() + ev.center.y - offset.top
	};
}

/** Get a point scaled to the current offset and scale. */
canvasSelector.prototype.getScaledPoint = function(obj, ev, offset) {
	var center = obj.getCenter(obj, ev);
	var trueOffset = offset || 0;

	return {
		'x': Math.floor((center.x - obj.curLeft - trueOffset) / obj.curScale),
		'y': Math.floor((center.y - obj.curTop - trueOffset) / obj.curScale)
	};
}

/** Default onResize function that ensures the canvas is within the bounds
 * of the viewport. Can be overwritten via context.onResize
 */
canvasSelector.prototype.onResize = function(obj) {
    var windowHeight = window.innerHeight || $(window).height();
    var windowWidth = window.innerWidth || $(window).width();
    var y = obj.canvas.offset().top;
    var height = windowHeight - $(".footer").height()*2 - y - 20;
    
    /* scale it to the parent container */
    var p = obj.canvas.parent();
    var width = p.width();

    // fill all available space and minimum size
    if (width > obj.image.width) width = obj.image.width;
    else if (width < 400) width = 400;
    if (height > obj.image.height) height = obj.image.height;
    else if (height < 400) height = 400; 

    obj.ctx.canvas.width = width;
    obj.ctx.canvas.height = height;

    // update the progress bar
    if (obj.progressBarX) {
        $("#" + obj.progressBarX).width(width + 5);
    }

    if (obj.progressBarY) {
        $("#" + obj.progressBarY).height(height + 5);
    }
	
	obj.redrawCanvas(obj);
};

/** Panning is complicated because we have to see if the user is attempting to
 * drag a square, resize a square, or move around the canvas.
 */
canvasSelector.prototype.onPan = function(obj, ev) {
    // see if there is a rectangle where we're panning
	if (ev.type == 'panstart') {
		var point = obj.getScaledPoint(obj, ev);
		
		obj.movingRectangle = obj.getRectangle(obj, point.x, point.y);
		if (obj.movingRectangle) {
			obj.resizingRectangle = obj.isRectangleCorner(obj, obj.movingRectangle, point.x, point.y);
		}
	}

	// undo pan ending
	if (ev.type == 'panend') {
		if (obj.movingRectangle) {
			obj.movingRectangle = false;
			
			if (obj.resizingRectangle) {
				obj.resizingRectangle = false;
			}
		} else {
			obj.initLeft = obj.curLeft;
			obj.initTop = obj.curTop;
		}
		return;
	}

	// move the rectangle if we grabbed one
	if (obj.movingRectangle && !obj.resizingRectangle) {
		var scaledPoint = obj.getScaledPoint(obj, ev, obj.movingRectangle.width / 2);

		if ((scaledPoint.x + obj.movingRectangle.width) > obj.image.width) {
			scaledPoint.x = obj.image.width - obj.movingRectangle.width;
		} if (scaledPoint.x < 0) {
			scaledPoint.x = 0;
		}
		if ((scaledPoint.y + obj.movingRectangle.height) > obj.image.height) {
			scaledPoint.y = obj.image.height - obj.movingRectangle.height;
		} if (scaledPoint.y < 0) {
			scaledPoint.y = 0;
		}
		
		// check for collision
		if (obj.isCollision(obj, obj.movingRectangle, scaledPoint.x, scaledPoint.y, obj.movingRectangle.width, obj.movingRectangle.height)) {
			obj.logEvent("Collision!");
		} else {
			obj.movingRectangle.left = scaledPoint.x;
			obj.movingRectangle.top = scaledPoint.y;
		}
		
		obj.redrawCanvas(obj);
		return;
	}
	
	if (obj.resizingRectangle) {
		// store current vals
		var scaledPoint = obj.getScaledPoint(obj, ev);
		var newLeft = obj.movingRectangle.left;
		var newRight = (obj.movingRectangle.left + obj.movingRectangle.width);
		var newTop = obj.movingRectangle.top;
		var newBottom = (obj.movingRectangle.top + obj.movingRectangle.height);
		
		// see what values we're updating
		if (obj.resizingRectangle.corner == 1 || obj.resizingRectangle.corner == 4 || obj.resizingRectangle.side == 1) newLeft = scaledPoint.x;
		else if (obj.resizingRectangle.corner == 2 || obj.resizingRectangle.corner == 3 || obj.resizingRectangle.side == 3) newRight = scaledPoint.x;
		
		if (obj.resizingRectangle.corner == 1 || obj.resizingRectangle.corner == 2 || obj.resizingRectangle.side == 2) newTop = scaledPoint.y;
		else if (obj.resizingRectangle.corner == 3 || obj.resizingRectangle.corner == 4 || obj.resizingRectangle.side == 4) newBottom = scaledPoint.y;
		
		// make sure we fit our size constraint
		if ((newRight - newLeft) < obj.minSize || (newBottom - newTop) < obj.minSize) {
			obj.logEvent('Size too small.');
			return;
		}
		
		// make sure we don't collide
		if (obj.isCollision(obj, obj.movingRectangle, newLeft, newTop, newRight - newLeft, newBottom - newTop)) {
			obj.logEvent("COLLISION");
		} else {
			// update
			obj.movingRectangle.left = newLeft;
			obj.movingRectangle.top = newTop;
			obj.movingRectangle.width = newRight - newLeft;
			obj.movingRectangle.height = newBottom - newTop;
		}
		
		obj.redrawCanvas(obj);
		return;
	}

	obj.logEvent("Pan ( " + ev.deltaX + ", " + ev.deltaY + " )");
	var tmpLeft = obj.initLeft + ev.deltaX;
	var tmpTop = obj.initTop + ev.deltaY;

	obj.logEvent(tmpLeft + ", " + tmpTop);
	obj.logEvent(obj.image.width + ", " + obj.image.height + ", " + obj.curScale);

	// make sure the image stays within the canvas
	if (tmpLeft + (obj.image.width * obj.curScale) < obj.canvas.width()) {
        tmpLeft = obj.canvas.width() - (obj.image.width * obj.curScale);
    }
    if (tmpTop + (obj.image.height * obj.curScale) < obj.canvas.height()) {
		tmpTop = obj.canvas.height() - (obj.image.height * obj.curScale);
	}

	// make sure we have a negative value
	if (tmpLeft > 0) tmpLeft = 0;
    if (tmpTop > 0) tmpTop = 0;

	// update the current locations and redraw
	obj.curLeft = tmpLeft;
	obj.curTop = tmpTop;

	obj.redrawCanvas(obj);

    if (ev.type == 'keypress') {
        obj.initLeft = obj.curLeft;
        obj.initTop = obj.curTop;
    }
};

/** Double tap creates a new rectangle, if it will fit at the given location. */
canvasSelector.prototype.onDoubleTap = function(obj, ev) {
    var size = 30;
	var radius = size / 2;
	var point = obj.getScaledPoint(obj, ev, radius);
	var center = obj.getScaledPoint(obj, ev);

    obj.logEvent(point);
	
	size = size / obj.curScale;
	if (size < obj.minSize) size = obj.minSize;

	// make sure we don't have a rectangle here already
	if (obj.isCollision(obj, null, point.x, point.y, size, size)) {
		obj.logEvent("SELECTION ALREADY MADE IN THIS AREA!");
		return;
	}

	// push on to our rectangles
	var id = 1;
	
	if (obj.rectangles.length > 0) {
	    id = obj.rectangles[obj.rectangles.length-1].id + 1;
	}

    obj.addRectangle(obj, {
        'left': point.x,
        'top': point.y,
        'width': size,
        'height': size,
        'id': id
    });
};

canvasSelector.prototype.removeRect = function(obj, id) {
    var rect = undefined;

    obj.rectangles.forEach(function(e) {
        if (e.id == id) {
            rect = e;
            return;
        }
    });

    if (rect) obj.internalRemoveRect(obj, rect);
};

canvasSelector.prototype.internalRemoveRect = function(obj, rect) {
    obj.logEvent("DELETING RECTANGLE");
    var index = obj.rectangles.indexOf(rect);
    var id = rect.id;
    obj.rectangles.splice(index, 1);
    
    obj.redrawCanvas(obj);
    
    if (obj.deleteCallback) {
        obj.deleteCallback(id, obj.rectangles.length == 0);
    }
};

/** Triple tap is currently a way for touch users to destroy a square. */ 
canvasSelector.prototype.onTripleTap = function(obj, ev) {
	var size = 30;
	var radius = size / 2;
	var point = obj.getScaledPoint(obj, ev, radius);
	var center = obj.getScaledPoint(obj, ev);
	var rect = obj.getRectangle(obj, center.x, center.y);

	// delete the rectangle if we found one
	if (rect) {
        obj.internalRemoveRect(obj, rect);
		return;
	}

	obj.logEvent("NO RECTANGLE FOUND");
};

canvasSelector.prototype.onPinch = function(obj, ev) {
    if (ev.type == 'pinchstart') {
		//initScale = transform.scale || 1;
	}

	obj.logEvent("Pinch ( " + ev.scale + " )");
    obj.logEvent(ev);

	var tmpScale = obj.initScale * ev.scale;
    if (tmpScale < 1.0) {
        if (obj.initScale == 1.0) return;
        tmpScale = 1.0;
    } else if (tmpScale > 4.0) {
        if (obj.initScale == 4.0) return;
        tmpScale = 4.0;
    }

    // we have to make sure that the image stays within the frame in case we've panned
    console.log(ev);
    var tmpLeft = obj.curLeft * ev.scale;

    obj.logEvent(obj.image.width + ", " + obj.curLeft + ", " + tmpLeft + tmpScale + ", " + obj.canvas.width());
    if (tmpLeft > 0) {
        tmpLeft = 0;
    } else if (Math.ceil(obj.image.width * tmpScale + tmpLeft) < obj.canvas.width()) {
        // see if we can correct the curLeft to fit within the bounds
        tmpLeft = Math.ceil(obj.canvas.width() - (obj.image.width * tmpScale));
    }

    var tmpTop = obj.curTop * ev.scale;
    if (tmpTop > 0) {
        tmpTop = 0;
    }
    else if (Math.ceil(obj.image.height * tmpScale + tmpTop) < obj.canvas.height()) {
        // see if we can correct the curHeight to fit within the bounds
        tmpTop = Math.ceil(obj.canvas.height() - (obj.image.height * tmpScale));
    }

    // we've made it this far, so lets correct the left/top and scale
    obj.curLeft = tmpLeft;
    obj.curTop = tmpTop;
    obj.curScale = tmpScale;

	if (ev.type == 'pinchend') {
		obj.initScale = obj.curScale;
        obj.initLeft = obj.curLeft;
        obj.initTop = obj.curTop;
	}

    if (obj.scaleArea) {
        $("#" + obj.scaleArea).html(tmpScale.toFixed(2) + "x");
    }
	obj.redrawCanvas(obj);
};

/** Attempts to get the rectangle at a given location. */
canvasSelector.prototype.getRectangle = function(obj, x, y) {
	var rect = undefined;
	
	obj.rectangles.forEach(function (e) {
		obj.logEvent(e.left + ", " + e.top + ", " + e.width + ", " + e.height + ", (" + x + ", " + y + ")");
		if (	e.left <= (x + obj.errorMargin) && (x - obj.errorMargin) <= (e.left + e.width) &&
				e.top <= (y + obj.errorMargin) && (y - obj.errorMargin) <= (e.top + e.height)) {
			rect = e;
			
			return;
		}
	});

	return rect;
}

/** Determines if there is a collision between rectangles at a given location. */
canvasSelector.prototype.isCollision = function(obj, rect, left, top, width, height) {
    if (obj.allowOverlap) return false;
	var returnVal = false;
	
	obj.rectangles.forEach(function (e) {
		if (returnVal || rect == e) return;
		
		if ((top + height) < e.top) return;
		if (top > (e.top + e.height)) return;
		if ((left + width) < e.left) return;
		if (left > (e.left + e.width)) return;
		
		returnVal = true;
	});
	
	return returnVal;
}

/** Redraws the canvas (called any time the canvas changes). */
canvasSelector.prototype.redrawCanvas = function(obj) {
	obj.ctx.fillStyle = "rgb(255, 255, 255)";
	obj.ctx.fillRect(0, 0, obj.canvas.width(), obj.canvas.height());

	obj.ctx.save();
	obj.ctx.setTransform(1, 0, 0, 1, obj.curLeft, obj.curTop);
	obj.ctx.scale(obj.curScale, obj.curScale);
	obj.ctx.drawImage(obj.image, 0, 0, obj.image.width, obj.image.height);

    // update the progress bar
    if (obj.progressBarX) {
        var leftProgress = 100.0 * (-obj.curLeft) / (obj.image.width * obj.curScale);
        if (leftProgress < 0.01) {
            leftProgress = 0.0;
        }

        var middleProgress = 100.0 * obj.canvas.width() / (obj.image.width * obj.curScale);
        var rightProgress =  100.0 - middleProgress - leftProgress;
        if (rightProgress < 0.01) {
            rightProgress = 0.0;
        }

        $("#" + obj.progressBarX + "_left").width(leftProgress + "%");
        $("#" + obj.progressBarX + "_middle").width(middleProgress + "%");
        $("#" + obj.progressBarX + "_right").width(rightProgress + "%");
    }

    if (obj.progressBarY) {
        var leftProgress = 100.0 * (-obj.curTop) / (obj.image.height * obj.curScale);
        if (leftProgress < 0.01) {
            leftProgress = 0.0;
        }

        var middleProgress = 100.0 * obj.canvas.height() / (obj.image.height * obj.curScale);
        var rightProgress =  100.0 - middleProgress - leftProgress;
        if (rightProgress < 0.01) {
            rightProgress = 0.0;
        }

        $("#" + obj.progressBarY + "_top").height(leftProgress + "%");
        $("#" + obj.progressBarY + "_middle").height(middleProgress + "%");
        $("#" + obj.progressBarY + "_bottom").height(rightProgress + "%");
    }

	// draw all the rectangles
	obj.ctx.font = "16px serif";
    var red = 255;
    var green = 0;
    var blue = 0;
	obj.rectangles.forEach(function(e) {
		obj.ctx.beginPath();
		obj.ctx.fillStyle="rgba(" + red + ", " + green + ", " + blue + ", 0.15)";
		obj.ctx.fillRect(e.left, e.top, e.width, e.height);
		obj.ctx.lineWidth="2";
		obj.ctx.strokeStyle="red";
		obj.ctx.rect(e.left, e.top, e.width, e.height);
		obj.ctx.stroke();
		
		obj.ctx.beginPath();
		obj.ctx.fillStyle="rgb(255, 255, 255)";
		obj.ctx.strokeStyle="rgb(255, 255, 255)";
		obj.ctx.lineWidth="1";
		obj.ctx.fillText(e.id, e.left, e.top+8);
		obj.ctx.strokeText(e.id, e.left, e.top+8);
		obj.ctx.stroke();
	});

	obj.ctx.restore();
	
	// change the color of the border based on if we can / cannot go that location
	if (obj.curLeft < 0) obj.canvas.css('border-left-style', 'dashed');
	else obj.canvas.css('border-left-style', 'solid');
	
	if (obj.curTop < 0) obj.canvas.css('border-top-style', 'dashed');
	else obj.canvas.css('border-top-style', 'solid');
	
	if (obj.curLeft + (obj.image.width * obj.curScale) > obj.canvas.width()) obj.canvas.css('border-right-style', 'dashed');
	else obj.canvas.css('border-right-style', 'solid');
	
	if (obj.curTop + (obj.image.height * obj.curScale) > obj.canvas.height()) obj.canvas.css('border-bottom-style', 'dashed');
	else obj.canvas.css('border-bottom-style', 'solid');
}

/** Determines if we are at an edge of a rectangle for resizing. */
canvasSelector.prototype.isRectangleCorner = function(obj, rect, x, y) {
	var leftEdge = (rect.left - obj.errorMargin) <= x && x <= (rect.left + obj.errorMargin);
	var rightEdge = ((rect.left + rect.width) - obj.errorMargin) <= x && x <= ((rect.left + rect.width) + obj.errorMargin);
	var topEdge = (rect.top - this.errorMargin) <= y && y <= (rect.top + this.errorMargin);
	var bottomEdge = ((rect.top + rect.height) - obj.errorMargin) <= y && y <= (rect.top + rect.height + obj.errorMargin);
	
	var ret = {
	    "corner": 0,
	    "side": 0
	};
	
	if (leftEdge) {
	    if (topEdge) ret.corner = 1;
	    else if (bottomEdge) ret.corner = 4;
	    else ret.side = 1;
	} else if (rightEdge) {
	    if (topEdge) ret.corner = 2;
	    else if (bottomEdge) ret.corner = 3;
	    else ret.side = 3;
	} else if (topEdge) {
	    ret.side = 2;
	} else if (bottomEdge) {
	    ret.side = 4;
	} else {
	    return null;
	}
	
	return ret;
}
