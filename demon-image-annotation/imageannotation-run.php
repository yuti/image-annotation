<?php
require_once( "config.php" );
include_once('imageannotation-data.php');

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';


if($action == "get") {
	dia_getResults();	
} else if($action == "save") {
	dia_getSave();	
} else if($action == "delete") {
	dia_getDelete();	
}

//*************** Save ***************
function dia_getSave() {
	$imgID = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';	
	$postID = isset($_REQUEST['postid']) ? trim($_REQUEST['postid']) : 0;	
	
	//get data from jQuery
	$data = array(
		$_GET["top"],
		$_GET["left"],
		$_GET["width"],
		$_GET["height"],
		$_GET["text"],
		$_GET["id"],
		$_GET["noteID"],
		$_GET["author"],
		$_GET["email"],
	);	
	
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	if($data[5] != "new") {		
		//find the old image note from comment
		$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE note_img_ID='".$imgID."' and note_ID='".$data[5]."'");
		foreach ($result as $commentresult) {
			$comment_id = (int)$commentresult->note_comment_ID; //comment ID
			$comment_author = $commentresult->note_author; //comment Author
			$comment_email = $commentresult->note_email; //comment Email
		};
		
		//update comment
		if( (get_option('demon_image_annotation_comments') == '0') ) {
			$wpdb->query("UPDATE wp_comments SET comment_content = '".$data[4]."' WHERE comment_ID = ".$comment_id);
		}
		
		//update image note
		$wpdb->query("UPDATE ".$table_name."
		SET note_top = '".$data[0]."',
			note_left = '".$data[1]."',
			note_width = '".$data[2]."',
			note_height = '".$data[3]."',
			note_text = '".$data[4]."',
			note_text_ID = '"."id_".md5($data[4])."' WHERE note_ID = ".$data[6]);
		
	} else {
		//if image note is new
		$comment_post_ID = $postID;		
		$comment_author       = ( isset($_GET['author']) )  ? trim(strip_tags($_GET['author'])) : null;
		$comment_author_email = ( isset($_GET['email']) )   ? trim($_GET['email']) : null;
		$comment_author_url   = ( isset($_GET['url']) )     ? trim($_GET['url']) : null;
		$comment_content      = $data[4];
		
		//If the user is logged in, get author name and author email
		$user = wp_get_current_user();
		if ( $user->ID ) {
			if ( empty( $user->display_name ) )
				$user->display_name=$user->user_login;
				$comment_author       = $wpdb->escape($user->display_name);
				$comment_author_email = $wpdb->escape($user->user_email);
				$comment_author_url   = $wpdb->escape($user->user_url);
				if ( current_user_can('unfiltered_html') ) {
					if ( wp_create_nonce('unfiltered-html-comment_' . $comment_post_ID) != $_POST['_wp_unfiltered_html_comment'] ) {
						kses_remove_filters();
						kses_init_filters();
					}
				}
		}
		
		$autoapprove = 1;
		if( (get_option('demon_image_annotation_autoapprove') == '1') ) {
			$autoapprove = 0;
		}
		
		//add to comment
		if( (get_option('demon_image_annotation_comments') == '0') ) {
			$user_ID = $user->ID;
			$comment_type = '';
			$comment_parent = isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0;
			$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');
			if($autoapprove == 1){
				$comment_id = wp_insert_comment($commentdata);
			}else{
				$comment_id = wp_new_comment($commentdata);	
			}
		}
		
		//add to image note
		$wpdb->query("INSERT INTO `".$table_name."`
										(
											`note_img_ID`,
											`note_comment_ID`,
											`note_post_ID`,
											`note_author`,
											`note_email`,
											`note_top`,
											`note_left`,
											`note_width`,
											`note_height`,
											`note_text`,
											`note_text_id`,
											`note_editable`,
											`note_approved`,
											`note_date`
										)
										VALUES (
										'".$imgID."',
										'".$comment_id."',
										'".$postID."',
										'".$comment_author."',
										'".$comment_author_email."',
										".$data[0].",
										".$data[1].",
										".$data[2].",
										".$data[3].",
										'".$data[4]."',
										'"."id_".md5($data[4])."',
										1,
										'".$autoapprove."',
										now()
										)");
	}	
	//output JSON array
	echo '{ "status":true, "annotation_id": "id_'.md5($data[4]).'" }';
}

//*************** Delete ***************
function dia_getDelete() {
	$qsType = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';
	$data = array(
		$_GET["id"],
	);
	
	//find the comment ID
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	$result = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE note_img_ID='".$qsType."' and note_ID='".$data[0]."'");
	echo "SELECT * FROM ".$table_name." WHERE note_img_ID='".$qsType."' and note_ID='".$data[0]."'";
	foreach ($result as $commentresult) {
		$comment_id = (int)$commentresult->note_comment_ID; //comment ID
	};
	
	//delete note from comment
	$wpdb->query("DELETE FROM wp_comments WHERE comment_ID = ".$comment_id);
	
	//delete note from image note
	$wpdb->query("DELETE FROM ".$table_name." WHERE note_img_ID='".$qsType."' and note_ID='".$data[0]."'");
	
	//output JSON array
	echo '{ "status":true }';
}

//*************** Get ***************
function dia_getResults() {
	$qsType = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';
	$qsPreview = isset($_REQUEST['preview']) ? trim($_REQUEST['preview']) : '';	
	
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
	
	if( (get_option('demon_image_annotation_comments') == '0') ) {
		$query = "SELECT * FROM ".$table_name." WHERE note_img_ID = '".$qsType."' AND note_comment_ID != 0";
	}else{
		$query = "SELECT * FROM ".$table_name." WHERE note_img_ID = '".$qsType."' AND note_comment_ID = 0";	
	}
	
	$result = $wpdb->get_results($query)
;	
	//output JSON array
	$json = array();
	
	foreach ($result as $row) {	
		$commentApprove;
		
		if($qsPreview == 1){
			$commentApprove = 1;
		}else if( (get_option('demon_image_annotation_comments') == '0') ) {
			//sync with approved comment
			$commentApprove = $wpdb->get_var("SELECT comment_approved FROM wp_comments WHERE comment_ID = ".(int)$row->note_comment_ID);
			
			//empty string will use image note approved value;
			if($commentApprove == "") {
				$commentApprove = $row->note_approved;
			}
		} else {
			//not sync with approved comment
			$commentApprove = $row->note_approved;
		}
		
		if($commentApprove == 1) {
			$notetext = $row->note_text;//dia_txt2html($row->note_text);
			
			if( (get_option('demon_image_annotation_gravatar') == '0') ) {
				//display author avatar
				if(get_option('demon_image_annotation_gravatar_deafult') != '') {
					$defaultgravatar = get_bloginfo('template_url').get_option('demon_image_annotation_gravatar_deafult');
				} else {
					$defaultgravatar = '';
				}
				$author = "<div class='dia-author'>".get_avatar($row->note_email, 20, $defaultgravatar)." ".$row->note_author."</div>";
			} else {
				$author = "<div class='dia-author'>".$row->note_author."</div>";
			}
			$json['id'] = $row->note_ID;
			$json['top'] = $row->note_top;
			$json['left'] = $row->note_left;
			$json['width'] = $row->note_width;
			$json['height'] = $row->note_height;
			$json['text'] = $notetext;
			$json['note_id'] = $row->note_text_ID;
			$json['editable'] = true;
			$json['author'] = $author;
			$json['commentid'] = $row->note_comment_ID;
			$data[] = $json;
		}
	};
	
	echo '{ "status":true, "note": '.json_encode($data).' }';
}
?>