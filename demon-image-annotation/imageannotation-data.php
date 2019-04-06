<?php
global $wpdb;
$table_note = $wpdb->prefix . "demon_imagenote";
$table_comment = $wpdb->prefix . "comments";

if( (get_option('demon_image_annotation_comments') == '0') ) {
	//Moderate
	$query = "SELECT ".$table_note.".note_ID, ".$table_note.".note_approved, ".$table_comment.".comment_approved
			FROM ".$table_note.", ".$table_comment."
			WHERE ".$table_note.".note_approved != ".$table_comment.".comment_approved
			AND ".$table_note.".note_comment_ID = ".$table_comment.".comment_ID";
	
	$result = $wpdb->get_results($query);
	foreach ($result as $r) {
		$wpdb->query("UPDATE ".$table_note." SET note_approved = '".$r->comment_approved."' WHERE note_ID = ".$r->note_ID);
	}
	
	//Content
	$query = "SELECT ".$table_note.".note_ID, ".$table_note.".note_text, ".$table_comment.".comment_content
			FROM ".$table_note.", ".$table_comment."
			WHERE ".$table_note.".note_text != ".$table_comment.".comment_content
			AND ".$table_note.".note_comment_ID = ".$table_comment.".comment_ID";
	
	$result = $wpdb->get_results($query);
	foreach ($result as $r) {
		$wpdb->query("UPDATE ".$table_note." SET note_text = '".addslashes($r->comment_content)."' WHERE note_ID = ".$r->note_ID);
	}
	
	//Author
	$query = "SELECT ".$table_note.".note_ID, ".$table_note.".note_author, ".$table_comment.".comment_author
			FROM ".$table_note.", ".$table_comment."
			WHERE ".$table_note.".note_author != ".$table_comment.".comment_author
			AND ".$table_note.".note_comment_ID = ".$table_comment.".comment_ID";
	
	$result = $wpdb->get_results($query);
	foreach ($result as $r) {
		$wpdb->query("UPDATE ".$table_note." SET note_author = '".$r->comment_author."' WHERE note_ID = ".$r->note_ID);
	}
	
	//Email
	$query = "SELECT ".$table_note.".note_ID, ".$table_note.".note_email, ".$table_comment.".comment_author_email
			FROM ".$table_note.", ".$table_comment."
			WHERE ".$table_note.".note_email != ".$table_comment.".comment_author_email
			AND ".$table_note.".note_comment_ID = ".$table_comment.".comment_ID";
	
	$result = $wpdb->get_results($query);
	foreach ($result as $r) {
		$wpdb->query("UPDATE ".$table_note." SET note_email = '".$r->comment_author_email."' WHERE note_ID = ".$r->note_ID);
	}
}

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
if (is_admin() && $action != ''){
	$noteid = ( is_array( $_REQUEST['note'] ) ) ? $_REQUEST['note'] : array( $_REQUEST['note'] );
	
	// Define our data source
	if ( 'delete' === $action ) {
		foreach ( $noteid as $id ) {
			$id = absint( $id );
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//find comment id and comment text
				$query = "SELECT * from ".$table_note." where note_ID =".$id;
				$result = $wpdb->get_results($query);
				foreach ($result as $r) {
					$comment_id = $r->note_comment_ID;
					$content = $r->note_text;
					//delete comment
					$wpdb->query("DELETE FROM ".$table_comment." WHERE comment_ID = ".$comment_id." and comment_content = '".$content."'"); 
				}
			}
			//delete note
			$wpdb->query( "DELETE FROM ".$table_note." WHERE note_ID = $id");
			$wpdb->query( "DELETE FROM uf_comments WHERE comment_ID = ".$comment_id);
		}
	} else if ( 'approve' === $action ) {
		foreach ( $noteid as $id ) {
			$id = absint( $id );
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//find comment id and comment text
				$query = "SELECT * from ".$table_note." where note_ID =".$id;
				$result = $wpdb->get_results($query);
				foreach ($result as $r) {
					$comment_id = $r->note_comment_ID;
					$content = $r->note_text;
					//approve comment
					$wpdb->query("UPDATE ".$table_comment." SET comment_approved = '1' WHERE comment_ID = ".$comment_id." and comment_content = '".$content."'");
				}
			}
			//approve note
			$wpdb->query( "UPDATE ".$table_note." SET `note_approved` = '1' where note_ID = $id");
		}
	} else if ( 'restore' === $action ) {
		foreach ( $noteid as $id ) {
			$id = absint( $id );
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//find comment id and comment text
				$query = "SELECT * from ".$table_note." where note_ID =".$id;
				$result = $wpdb->get_results($query);
				foreach ($result as $r) {
					$comment_id = $r->note_comment_ID;
					$content = $r->note_text;
					//approve comment
					$wpdb->query("UPDATE ".$table_comment." SET comment_approved = '1' WHERE comment_ID = ".$comment_id." and comment_content = '".$content."'");
				}
			}
			//approve note
			$wpdb->query( "UPDATE ".$table_note." SET `note_approved` = '1' where note_ID = $id");
		}
	} else if ( 'unapprove' === $action ) {
		foreach ( $noteid as $id ) {
			$id = absint( $id );
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//find comment id and comment text
				$query = "SELECT * from ".$table_note." where note_ID =".$id;
				$result = $wpdb->get_results($query);
				foreach ($result as $r) {
					$comment_id = $r->note_comment_ID;
					$content = $r->note_text;
					//approve comment
					$wpdb->query("UPDATE ".$table_comment." SET comment_approved = '0' WHERE comment_ID = ".$comment_id." and comment_content = '".$content."'");
				}
			}
			//unapprove note
			$wpdb->query( "UPDATE ".$table_note." SET `note_approved` = '0' where note_ID = $id");
		}
	}
}
?>