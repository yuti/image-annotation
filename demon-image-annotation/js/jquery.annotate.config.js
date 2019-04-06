(function($) {
    $.fn.initAnnotate = function(options) {
		var imgcount = 1;
		var prevPostID = '';
        var opts = $.extend({}, $.fn.initAnnotate.defaults, options);
		pluginPath = opts.pluginPath;
		container = opts.container;
		adminOnly = opts.adminOnly;
		pageOnly = opts.pageOnly;
		autoResize = opts.autoResize;
		numbering = opts.numbering;
		removeImgTag = opts.removeImgTag;
		mouseoverDesc = opts.mouseoverDesc;
		maxLength = opts.maxLength;
		imgLinkOption = opts.imgLinkOption;
		imgLinkDesc = opts.imgLinkDesc;
		userLevel = opts.userLevel;
		previewOnly = opts.previewOnly;
		
		$(container + ' img').each(function(index) {
			var postID = '';
			if(postID != $(this).attr("data-post-id")){
				postID = $(this).attr("data-post-id");
				postID = postID == undefined ? '' : postID;
				$(this).attr("data-post-id", postID);
				if(prevPostID != postID){
					imgcount = 1;
					prevPostID = postID;
				}	
			}
			var idname = $(this).attr("id");
			var source = $(this).attr('src');
			idname = idname == undefined ? '' : idname;
			
			var exclude = false;
			if($(this).attr('exclude') != undefined || idname.substring(4,idname.length) == 'exclude'){
				exclude = true;
			}
			if(!exclude){
				if(idname.substring(0,4) == "img-") {
					var editable=false;
					//check if image annotation addable attribute exist
					var addablecon = $(this).attr("addable");
					
					//disable if image annotation addable for admin only
					if(adminOnly==0){
						//admin
						addablecon = false;
					}else{
						//not admin
						addablecon = addablecon == undefined ? true : addablecon;
					}
										
					//disable addable button if not in single page
					if(pageOnly==1||pageOnly==2){
						//addablecon = false;
					}
					
					//find image link if exist
					var imagelink = $(this).parent("a").attr('href');
					var imagetitle = $(this).parent("a").attr('title');
					imagetitle = imagetitle == undefined ? '' : imagetitle;
					
					//deactive the link if exist
					$(this).parent("a").removeAttr("href");
					if(removeImgTag==0) {
						//remove the link title attribute
						$(this).parent("a").removeAttr("title");
					}
					$(this).wrap($('<div id=' + idname.substring(4,idname.length) + ' class="dia-holder" ></div>'));
					var imagenotetag = mouseoverDesc != '' ? mouseoverDesc : mouseoverDesc;
					
					var divider;
					var thisImgLinkDesc = imgLinkDesc;
					if(mouseoverDesc != '') {
						if(imgLinkOption==0){
							thisImgLinkDesc = thisImgLinkDesc == '%TITLE%' ? imagetitle : thisImgLinkDesc;
							divider = imagelink != undefined ? ' | ' : '';
						}else{
							thisImgLinkDesc=''
							divider = '';	
						}
					} else {
						if(imgLinkOption==0){
							thisImgLinkDesc = thisImgLinkDesc == '%TITLE%' ? imagetitle : thisImgLinkDesc;
							divider = thisImgLinkDesc != '' ? ' | ' : '';
						}else{
							thisImgLinkDesc=''
							divider = '';
						}
					}
					
					if(userLevel > 3) {
						//admin
						editable = true;
						addablecon = true;
					} else {
						//annyoumous
						editable = false;
					}
					
					var imagelinktag = imagelink != undefined ? '<a href="' + imagelink + '" target="blank">' + thisImgLinkDesc + '</a>' : '';
					var newimgcount = imgcount < 10 ? "0" + imgcount : imgcount;
					if(mouseoverDesc!=''){
						newimgcount+=' | ';
					}
					if(numbering == 1){
						newimgcount='';
					}
					$(this).before('<div class="dia-desc-holder"><span class="dia-desc">'+ newimgcount + imagenotetag + divider + imagelinktag + '</span></div>');
					imgcount++;
					
					$(this).mouseover(function() {
						$(this).annotateImage({
							pluginPath: pluginPath,
							getPostID: $(this).attr("data-post-id"),
							getImgID: idname,
							autoResize: autoResize,
							editable: editable,
							addable: addablecon,
							maxLength: maxLength,
							previewOnly: previewOnly
						});
					});
				}
			}
		});
		
		$.fn.initAnnotate.commentThumbnail();
    };
	
	//inject thumbnails
	$.fn.initAnnotate.commentThumbnail = function() {
		$('div').each(function() {
			var divid = $(this).attr("id");
			divid = divid == undefined ? '' : divid;
			if(divid.substring(0,8) == "comment-") {
				var getimgsrc = $.fn.initAnnotate.imageSource(divid.substring(8,divid.length));
				if(getimgsrc != "") {
					$(this).remove("noted");
					$(this).html('<a href="#' + divid.substring(8,divid.length) + '"><div class="dia-thumbnail"><div class="dia-thumbnail-src" style="background:url('+getimgsrc+') no-repeat; background-size:cover;"></div></div></a>');
				}
			}
		});
	}
	
	
	
	//get image source from post for thumbnail
	$.fn.initAnnotate.imageSource = function(id) {
		var idreturn = "";
		$(container + ' img').each(function(index) {
			var imgid = $(this).attr("id");
			if(imgid == "img-" + id) {
				idreturn = $(this).attr("src");
			}
		});
		return idreturn;
	}	
})(jQuery);