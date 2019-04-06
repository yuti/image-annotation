(function($) {
    $.fn.annotateImage = function(options) {
        ///	<summary>
        ///		Creates annotations on the given image.
        ///	</summary>
        var opts = $.extend({}, $.fn.annotateImage.defaults, options);
        var image = this;
		
		// Assign defaults options
		this.image = this;
		this.scalePercent = opts.scalePercent;
		this.imageLoaded = opts.imageLoaded;
		this.noteLoaded = opts.noteLoaded;
		this.mode = opts.mode;
		
		this.autoResize = opts.autoResize;
		this.getPostID = opts.getPostID;
		this.getImgID = opts.getImgID;
        this.pluginPath = opts.pluginPath;
        this.editable = opts.editable;
		this.addable = opts.addable;
        this.useAjax = opts.useAjax;
        this.notes = opts.notes;
		this.maxLength = opts.maxLength;
		this.previewOnly = opts.previewOnly;
		this.formProcess = false;
		
		// Add the canvas
        this.canvas = $('<div class="dia-canvas"><div class="dia-view dia-loading"></div><div class="dia-edit"><div class="dia-edit-area"></div></div></div>');
        this.canvas.children('.dia-edit').hide();
        this.canvas.children('.dia-view').hide();
        this.image.after(this.canvas);

        // Give the canvas and the container their size and background
		this.canvas.height(this.height());
        this.canvas.width(this.width());
		this.canvas.closest('.dia-holder').width(this.width());
        this.canvas.css('background-image', 'url("' + this.attr('src') + '")');
		this.canvas.children('.dia-view, .dia-edit').height(this.height());
        this.canvas.children('.dia-view, .dia-edit').width(this.width());
		
		// Add the behavior: hide/show the notes when hovering the picture
        this.canvas.hover(function() {
            if ($(this).children('.dia-edit').css('display') == 'none') {
                $(this).children('.dia-view').show();
            }
        }, function() {
            if($(this).children().hasClass('dia-error') || $(this).children().hasClass('dia-loading')) {
            	$(this).children('.dia-view').show();
			} else {
				$(this).children('.dia-view').hide();
			}
        });

        this.canvas.children('.dia-view').hover(function() {
			if($(this).hasClass('dia-error')) {
				$(this).show();	
				$(this).removeClass('dia-error');
				$(this).addClass('dia-loading');
				$.fn.annotateImage.ajaxLoad(this);
			} else {
				$(this).show();	
			}
        }, function() {
			if($(this).hasClass('dia-error') || $(this).hasClass('dia-loading')) {
            	$(this).show();
			} else {
				$(this).hide();
			}
        });

        // load the notes		
        if (this.useAjax) {
            $.fn.annotateImage.ajaxLoad(this);
        } else {
            $.fn.annotateImage.load(this);
        }

        // Add the "Add a note" button
        if (this.addable==true) {
			this.button = $('<a class="dia-add" id="dia-add" href="#' + this.getImgID.substring(4,this.getImgID.length) + '">ADD NOTE</a>');
            this.button.click(function() {
                $.fn.annotateImage.add(image);
            });
			this.canvas.closest('.dia-holder').find('.dia-desc-holder').append(this.button);
        }
		
		// Hide the original image tag
        this.hide();
		
		// Auto resize image to fit content max width
		if(this.autoResize==0){
			this.canvas.css('background-size', '100%');
			$.fn.annotateImage.preLoadImage(this);
		}
		
        return this;
    };
	
	//Plugin Defaults Options
    $.fn.annotateImage.defaults = {
		autoResize:1,
		scalePercent:1,
		imageLoaded:false,
		noteLoaded:false,
        mode:'view',
        pluginPath: '',
        editable: true,
        useAjax: true,
		maxLength: 140,
		previewOnly: 0,
        notes: new Array()
    };
	
	$.fn.annotateImage.ajaxTimeOut = function(image) {
		//ajax time out display error
		if(image.noteLoaded == false) {
			image.canvas.children('.dia-view').removeClass('dia-loading');
			image.canvas.children('.dia-view').addClass('dia-error');
			image.canvas.children('.dia-view').show();
		}
	}

    $.fn.annotateImage.clear = function(image) {
        //Clears all existing annotations from the image.
		
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[i].destroy();
        }
        image.notes = new Array();
    };

    $.fn.annotateImage.ajaxLoad = function(image) {
        //Loads the annotations from the "getUrl" property passed in on the options object.
		
		image.ajaxLoadTime = setTimeout($.fn.annotateImage.ajaxTimeOut, 15000, image);
		
        $.getJSON(image.pluginPath + '.php?action=get&imgid=' + image.getImgID + '&preview=' + image.previewOnly + '&ticks=' + $.fn.annotateImage.getTicks(), function(data) {
			image.canvas.children('.dia-view').removeClass('dia-loading');
			image.noteLoaded = true;
			if(data.note != null){
            	image.notes = data.note;
				$.fn.annotateImage.load(image);
				$.fn.annotateImage.callbackScale(image);
			}
        });
    };

    $.fn.annotateImage.load = function(image) {
        //Loads the annotations from the notes property passed in on the options object.
		
		var targetNoteID = image.closest('#dia-admin-holder').attr('data-note-ID');
		targetNoteID = targetNoteID == undefined ? '' : targetNoteID;
		
		if(image.notes.length != 0) {
			var depth_arr = [];
			for (var i = 0; i < image.notes.length; i++) {
				depth_arr.push({id:i, amount:Number(image.notes[i].width) + Number(image.notes[i].height)});
			}
			function SortByAmount(a, b){
			  var aName = a.amount;
			  var bName = b.amount; 
			  return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
			}
			depth_arr.sort(SortByAmount); 
			
			var zindex = 1;
			for (var i = depth_arr.length - 1; i >= 0; i--) {
				if(targetNoteID!=''){
					//target single note
					if(targetNoteID == image.notes[i].id){
						image.notes[i] = new $.fn.annotateView(image, image.notes[i], 1);
					}
				}else{
					//multiple notes
					var targetID = depth_arr[i].id;
					image.notes[targetID] = new $.fn.annotateView(image, image.notes[targetID], zindex);
					
					zindex++;
				}
			}
		}
    };

    $.fn.annotateImage.getTicks = function() {
        //Gets a count og the ticks for the current date. 
		//This is used to ensure that URLs are always unique and not cached by the browser. 
		      
        var now = new Date();
        return now.getTime();
    };

    $.fn.annotateImage.add = function(image) {
        //Adds a note to the image.
		       
        if (image.mode == 'view') {
            image.mode = 'edit';

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(image);

            $.fn.annotateImage.createSaveButton(editable, image);
            $.fn.annotateImage.createCancelButton(editable, image);
        }
    };

    $.fn.annotateImage.createSaveButton = function(editable, image, note) {
        //Creates a Save button on the editable note.
		
		console.log('get ' + image.getPostID);
        var ok = $('<a class="dia-edit-ok">OK</a>');

        ok.click(function() {
		
			var form = $('#dia-edit-form form');
			var text = $('#dia-text').val();
			var author = $('#noteauthor').val();
			var email = $('#noteemail').val();
			
			author = author == undefined ? "" : author
			email = email == undefined ? "" : email
			
			var check = false;
			var errorMsg = '';
			if(text != "") {
				if(!image.editable) {
					if(author != "" && email !="") {
						AtPos = email.indexOf("@")
						StopPos = email.lastIndexOf(".")
						
						if (AtPos == -1 || StopPos == -1) {
							errorMsg = 'Please enter a valid email.';	
						} else {
							check = true;
						}
					} else {
						errorMsg = 'Please fill the required fields (name, email).';	
					}
				} else {
					check = true
				}
			} else {
				errorMsg = 'Please type a note';
			}
			$("#dia-edit-form #errormsg").html('<span style="color:#C00">'+errorMsg+'</span>');
			
			if(check && !image.formProcess) {
				image.formProcess = true;
				$.fn.annotateImage.appendPosition(form, editable, image);
				image.mode = 'view';
			
				// Save via AJAX
				if (image.useAjax) {
					$.ajax({
						url: image.pluginPath + ".php?action=save&imgid=" + image.getImgID + "&postid=" + image.getPostID,
						data: form.serialize(),
						error: function(xhr, ajaxOptions, thrownError) { 
									image.formProcess = false;
									alert("An error occured saving that note.");
								},
						success: function(data) {
							image.formProcess = false;
							if(data.status == true){
								var redictLink = $('#dia-admin-holder').attr('date-note-link');
								if(redictLink != undefined){
									window.location = redictLink+'&jsupdate=update';	
								}
								
								// Add to canvas
								if (note) {
									note.resetPosition(editable, text);
								} else {
									editable.note.editable = true;
									note = new $.fn.annotateView(image, editable.note)
									note.resetPosition(editable, text);
									image.notes.push(editable.note);
								}
								editable.destroy();
							}else{
								//error
								$.fn.annotateImage.toggleForm(image, true);
								$("#dia-edit-form #errormsg").html('<span style="color:#C00">Error, please try again.</span>');
							}
					},
						dataType: "json"
					});
				}
				$.fn.annotateImage.toggleForm(image, false);
				$('#dia-edit-form').find("div#errormsg").html('<span>Saving...</span>');
			}
        });
        editable.form.find('#dia-action').append(ok);
    };

    $.fn.annotateImage.createCancelButton = function(editable, image) {
        //Creates a Cancel button on the editable note.
		
        var cancel = $('<a class="dia-edit-close">Cancel</a>');
        cancel.click(function() {
            editable.destroy();
            image.mode = 'view';
        });
        editable.form.find('#dia-action').append(cancel);
    };

    $.fn.annotateEdit = function(image, note) {
        //Defines an editable annotation area.
		
        this.image = image;
		if (note) {
            this.note = note;
        } else {
			var newNote = new Object();
            newNote.id = 'new';
            newNote.top = 30;
            newNote.left = 30;
            newNote.width = 30;
            newNote.height = 30;
            newNote.text = "";
            this.note = newNote;
        }

        // Set area
        var area = image.canvas.children('.dia-edit').children('.dia-edit-area');
        this.area = area;
        this.area.css('height', $.fn.annotateImage.returnScale(this.note.height, true, this.image) + 'px');
        this.area.css('width', $.fn.annotateImage.returnScale(this.note.width, true, this.image) + 'px');
        this.area.css('left', $.fn.annotateImage.returnScale(this.note.left, true, this.image) + 'px');
        this.area.css('top', $.fn.annotateImage.returnScale(this.note.top, true, this.image) + 'px');
		
        // Show the edition canvas and hide the view canvas
        image.canvas.children('.dia-view').hide();
        image.canvas.children('.dia-edit').show();
		
		//filter note
		var notetext = this.note.text;
		
        // Add the note (which we'll load with the form afterwards)
		var inputMax = Number(image.maxLength);
		inputMax = isNaN(inputMax) ? 140 : inputMax;
		
		var errorMsg = '';
		if(image.editable) {
			errorMsg = 'You can start edit the note here.';
			var form = $('<div id="dia-edit-form" style="height:auto;"><form><input type="hidden" id="noteID" name="noteID" value="' + this.note.id + '"><textarea id="dia-text" name="text" rows="8" cols="30" maxlength="'+inputMax+'">' + notetext + '</textarea></form><div id="errormsg">'+errorMsg+'</div><div id="dia-action"></div></div>');
		} else {
			errorMsg = 'Fill in the require fields to submit.';
        	var form = $('<div id="dia-edit-form"><form><input type="hidden" id="noteID" name="noteID" value="' + this.note.id + '"><label for="author">Name : </label><input name="author" id="noteauthor" type="text" maxlength="100" /><br /><label for="email" >Email : </label><input name="email" id="noteemail" type="text" maxlength="100" /><textarea id="dia-text" name="text" rows="3" cols="30" maxlength="'+inputMax+'">' + notetext + '</textarea></form><div id="errormsg">'+errorMsg+'</div><div id="dia-action"></div></div>');
		}
        this.form = form;
		
        $('body').append(this.form);
        this.form.css('left', this.area.offset().left + 'px');
        this.form.css('top', (parseInt(this.area.offset().top) + parseInt(this.area.height()) + 7) + 'px');
		
		$('textarea[maxlength]').keyup(function(){
			var max = parseInt($(this).attr('maxlength'));
			if($(this).val().length > max){
				$(this).val($(this).val().substr(0, $(this).attr('maxlength')));
				$("#dia-edit-form #errormsg").html('<span style="color:#C00">You have ' + (max - $(this).val().length) + ' characters remaining</span>');
			} else {
				$("#dia-edit-form #errormsg").html('You have ' + (max - $(this).val().length) + ' characters remaining');	
			}
		});
		
        // Set the area as a draggable/resizable element contained in the image canvas.
        // Would be better to use the containment option for resizable but buggy
        area.resizable({
            handles: 'all',

            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            }
        })
        .draggable({
            containment: image.canvas,
            drag: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            },
            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height()) + 2) + 'px');
            }
        });
        return this;
    };

    $.fn.annotateEdit.prototype.destroy = function() {
        //Destroys an editable annotation area.
		     
        this.image.canvas.children('.dia-edit').hide();
        this.area.resizable('destroy');
        this.area.draggable('destroy');
        this.area.css('height', '');
        this.area.css('width', '');
        this.area.css('left', '');
        this.area.css('top', '');
        this.form.remove();
    }

    $.fn.annotateView = function(image, note, zindex) {
        //Defines a annotation area.
        this.image = image;
        this.note = note;
        this.editable = (note.editable && image.editable);
		
        // Add the area
        this.area = $('<div data-note-id="'+this.note.id+'" class="dia-area' + (this.editable ? ' dia-area-editable' : '') + '"><div></div></div>');
        image.canvas.children('.dia-view').prepend(this.area);

        // Add the note
		note.author = note.author == undefined ? '' : note.author;
		this.form = $('<div class="dia-note">' + note.author + '<div class="dia-note-text">'+note.text.replace(/\n/g, "<br />")+'</div></div>');
        this.form.hide();
        image.canvas.children('.dia-view').append(this.form);
        this.form.children('span.actions').hide();
		
		this.area.css('z-index', zindex);
		this.form.css('z-index', zindex);
		
        // Set the position and size of the note
        this.setPosition();

        // Add the behavior: hide/display the note when hovering the area
        var annotation = this;
        this.area.hover(function() {
            annotation.show();
        }, function() {
            annotation.hide();
        });

        // Edit a note feature
        if (this.editable) {
            var form = this;
            this.area.click(function() {
                form.edit();
            });
        } else {
			this.area.click(function() {
				window.location.hash = "#comment-" + note.commentid;
			});
		}
    };

    $.fn.annotateView.prototype.setPosition = function() {
        //Sets the position of an annotation.
		
		this.area.children('div').height($.fn.annotateImage.returnScale(parseInt(this.note.height) - 2, true, this.image) + 'px');
        this.area.children('div').width($.fn.annotateImage.returnScale(parseInt(this.note.width) - 2, true, this.image) + 'px');
        this.area.css('left', $.fn.annotateImage.returnScale(this.note.left, true, this.image) + 'px');
        this.area.css('top', $.fn.annotateImage.returnScale(this.note.top, true, this.image) + 'px');
        this.form.css('left', $.fn.annotateImage.returnScale(this.note.left, true, this.image) + 'px');
        this.form.css('top', ($.fn.annotateImage.returnScale(parseInt(this.note.top), true, this.image) + $.fn.annotateImage.returnScale(parseInt(this.note.height), true, this.image) + 7) + 'px');
    };

    $.fn.annotateView.prototype.show = function() {
        //Highlights the annotation
		
        if(this.form.oldindex == undefined) {
			this.form.oldindex = this.form.css("z-index");
		}
		this.form.css('z-index', 100);
        this.form.fadeIn(250);
        if (!this.editable) {
            this.area.addClass('dia-area-hover');
        } else {
            this.area.addClass('dia-area-editable-hover');
        }
    };

    $.fn.annotateView.prototype.hide = function() {
        //Removes the highlight from the annotation.
		    
        this.form.fadeOut(250);
		this.form.css('z-index', this.form.oldindex);
        this.area.removeClass('dia-area-hover');
        this.area.removeClass('dia-area-editable-hover');
    };

    $.fn.annotateView.prototype.destroy = function() {
        //Destroys the annotation.
		    
        this.area.remove();
        this.form.remove();
    }

    $.fn.annotateView.prototype.edit = function() {
        //Edits the annotation.
		     
        if (this.image.mode == 'view') {
            this.image.mode = 'edit';
            var annotation = this;

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(this.image, this.note);

            $.fn.annotateImage.createSaveButton(editable, this.image, annotation);

            // Add the delete button
            var del = $('<a class="dia-edit-delete">Delete</a>');
            del.click(function() {
                var form = $('#dia-edit-form form');
				
				$.fn.annotateImage.appendPosition(form, editable, this.image)
                if (annotation.image.useAjax) {
                    $.ajax({
                        url: annotation.image.pluginPath + ".php?action=delete&imgid=" + annotation.image.getImgID,
                        data: form.serialize(),
                        error: function(e) { alert("An error occured deleting that note.")},
						success: function(data) {
							var redictLink = $('#dia-admin-holder').attr('date-note-link');
							if(redictLink != undefined){
								window.location = redictLink+'&jsupdate=delete';	
							}
						}
                    });
                }

                annotation.image.mode = 'view';
                editable.destroy();
                annotation.destroy();
            });
            editable.form.find('#dia-action').append(del);

            $.fn.annotateImage.createCancelButton(editable, this.image);
        }
    };

    $.fn.annotateImage.appendPosition = function(form, editable, image) {
        //Appends the annotations coordinates to the given form that is posted to the server.
		
        var areaFields = $('<input type="hidden" value="' + $.fn.annotateImage.returnScale(editable.area.height(), false, image) + '" name="height"/>' +
                           '<input type="hidden" value="' + $.fn.annotateImage.returnScale(editable.area.width(), false, image) + '" name="width"/>' +
                           '<input type="hidden" value="' + $.fn.annotateImage.returnScale(editable.area.position().top, false, image) + '" name="top"/>' +
                           '<input type="hidden" value="' + $.fn.annotateImage.returnScale(editable.area.position().left, false, image) + '" name="left"/>' +
                           '<input type="hidden" value="' + editable.note.id + '" name="id"/>');
        form.append(areaFields);
    }
	
	$.fn.annotateImage.toggleForm = function(image, con) {
        //Toggle form input
		
		if(!con){
			$('#dia-edit-form').find('a').hide();
			$('#dia-edit-form').find('input').attr('disabled', 'disabled');
			$('#dia-edit-form').find('textarea').attr('disabled', 'disabled');
		}else{
			$('#dia-edit-form').find('a').show();
			$('#dia-edit-form').find('input').removeAttr('disabled');
			$('#dia-edit-form').find('textarea').removeAttr('disabled');
		}	
    }

    $.fn.annotateView.prototype.resetPosition = function(editable, text) {
		//Sets the position of an annotation.
        this.form.find('div.dia-note-text').html(text.replace(/\n/g, "<br />"));
        this.form.hide();

        // Resize
        this.area.children('div').height(editable.area.height() + 'px');
        this.area.children('div').width((editable.area.width() - 2) + 'px');
        this.area.css('left', (editable.area.position().left) + 'px');
        this.area.css('top', (editable.area.position().top) + 'px');
        this.form.css('left', (editable.area.position().left) + 'px');
        this.form.css('top', (parseInt(editable.area.position().top) + parseInt(editable.area.height()) + 7) + 'px');

        // Save new position to note
		
        this.note.top = $.fn.annotateImage.returnScale(editable.area.position().top, false, this.image);
        this.note.left = $.fn.annotateImage.returnScale(editable.area.position().left, false, this.image);
        this.note.height = $.fn.annotateImage.returnScale(editable.area.height(), false, this.image);
        this.note.width = $.fn.annotateImage.returnScale(editable.area.width(), false, this.image);
        this.note.text = text;
        this.note.id = editable.note.id;
        this.editable = true;
    };
	
	$.fn.annotateImage.callbackScale = function(image) {	
		//Callback when image and notes is loaded
		
		if(image.noteLoaded && image.imageLoaded){
			for (var i = 0; i < image.notes.length; i++) {
				image.notes[i].setPosition();
			}
		}
    };
	
	$.fn.annotateImage.preLoadImage = function(image) {
		//Preload original image to return width and height
		
		var tmpImg = new Image() ;
		tmpImg.src = $(image).attr('src');
		tmpImg.onload = function() {
			image.imageLoaded=true;
			image.scalePercent = image.closest('.dia-holder').width() / this.width;
			$.fn.annotateImage.callbackScale(image);
		} ;
    };
	
	$.fn.annotateImage.returnScale = function(num, con, image) {
		// Set scale percent to notes dimension and position
		
		if(con){
			return Math.round(num*image.scalePercent);
		}else{
			return Math.round(num/image.scalePercent);	
		}
    };
})(jQuery);