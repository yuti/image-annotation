f<?php

if ( current_user_can('manage_options') ) {
	$manage = true;
}

//*************** Table ***************
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Image_Annotation_List_Table extends WP_List_Table {
    function __construct(){
		global $status, $page;
			parent::__construct( array(
				'singular'  => __( 'note', 'imageannotatetable' ),     //singular name of the listed records
				'plural'    => __( 'notes', 'imageannotatetable' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
	
		) );
    }

  function no_items() {
    _e( 'No notes found.' );
  }

  function column_default( $item, $column_name ) {
    switch( $column_name ) { 
        case 'note_img_ID':
			return $item->$column_name;
		case 'note_text':
            return '<strong>Submitted on :</strong>'.date( 'y/m/d g:i A', strtotime($item->note_date)).'<br/>'.nl2br($item->$column_name);
		case 'note_author':
			return '<strong>'.get_avatar($row->note_email, 30, '').$item->$column_name.'</strong><br /><a href="mailto:'. $item->note_email.'">'. $item->note_email.'</a>';
		case 'note_response':
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				global $wpdb;
				$list = $wpdb->get_results("select * from " . $wpdb->prefix . "comments where comment_ID ='".$item->note_comment_ID."' and comment_content = '".$item->note_text."'");
				$count;
				foreach ($list as $t) {
					$comment_approved = $t->comment_approved;
					$post = get_post($t->comment_post_ID);
					$posttitle = $post->post_title;
					return $posttitle;
					$count ++;
				}
			}
			if($count == 0) {
				$post = get_post($item->note_post_ID);
				$posttitle = $post->post_title;
				if($posttitle==''){
					return 'Not sync with wordpress comment';
				}else{
					return $posttitle;	
				}
			}
        default:
            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}

	function get_sortable_columns() {
	  $sortable_columns = array(
		'note_img_ID'  => array('note_img_ID',false),
		'note_author' => array('note_author',false),
		'note_text'   => array('note_text',false)
	  );
	  return $sortable_columns;
	}

	function get_columns(){
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'note_img_ID' => __( 'IMG ID', 'imageannotatetable' ),
				'note_author' => __( 'Author', 'imageannotatetable' ),
				'note_text'    => __( 'Text', 'imageannotatetable' ),
				'note_response' => __( 'In Response to', 'imageannotatetable' ),
				'note_action' => __( 'Action', 'imageannotatetable' )
			);
			 return $columns;
		}
	
	function usort_reorder( $a, $b ) {
	  // If no sort, default to title
	  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'note_ID';
	  // If no order, default to asc
	  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
	  // Determine sort order
	  $result = strcmp( $a[$orderby], $b[$orderby] );
	  // Send final sort direction to usort
	  return ( $order === 'asc' ) ? $result : -$result;
	}
	
	function column_note_action($item){
		if( (get_option('demon_image_annotation_comments') == '0') ) {
			//sync with wordpress comments
			if($item->comment_approved == '1') {
				$condition = 'Unapprove';
				$text = 'Approve';
			} else if($item->comment_approved == '0') {
				$condition = 'Approve';
				$text = 'Unapprove';
			} else if($item->comment_approved == 'trash') {
				$condition = 'Restore';
				$text = 'Trash';
			}
			
			//not sycn or sync but with old notes
			if($item->comment_approved == ''){
				$condition = 'Notsync';
				if($item->note_comment_ID == 0){
					//deleted comments
					$text = 'Not sync with wordpress comment';
				}else{
					//not Sycn
					$text = '<span style="color:#a00">Deleted Comment</span>';
				}
			}
		}else{
			if($item->note_approved == '1') {
				$condition = 'Unapprove';
				$text = 'Approve';
			} else {
				$condition = 'Approve';
				$text = 'Unapprove';
			}
		}
		
		if ( current_user_can('moderate_comments') ) {
			$moderate = true;
		}
	  	if($moderate){
		  if($condition == 'Notsync'){
			  $actions = array(
					'delete'    => sprintf('<a href="?page=%s&action=%s&note=%s&paged=%s">Delete</a>',$_REQUEST['page'],'delete', $item->note_ID, $_REQUEST['paged']),
				);
		}else{
			$actions = array(
				$condition  => sprintf('<a href="?page=%s&action=%s&note=%s&paged=%s">'.$condition.'</a>',$_REQUEST['page'],strtolower($condition),$item->note_ID, $_REQUEST['paged']),
				'edit'      => sprintf('<a href="?page=%s&action=%s&note=%s&paged=%s">Edit</a>',$_REQUEST['page'],'edit',$item->note_ID, $_REQUEST['paged']),
				'delete'    => sprintf('<a href="?page=%s&action=%s&note=%s&paged=%s">Delete</a>',$_REQUEST['page'],'delete', $item->note_ID, $_REQUEST['paged']),
			);
		}
			return sprintf('%1$s %2$s', $text, $this->row_actions($actions) );
	  }else{
			return sprintf('%1$s %2$s', $text, $this->row_actions($actions) );  
	  }
	}
	
	function single_row( $a_comment ) {
			global $comment;
			$comment = $a_comment;
			$condition;
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//sync with wordpress comments
				if($comment->comment_approved == '1') {
					$condition = 'approved';
				} else if($comment->comment_approved == '0') {
					$condition = 'unapproved';
				} else if($comment->comment_approved == 'trash') {
					$condition = 'deleted';
				}
				
				//not sycn or sync but with old notes
				if($comment->comment_approved == ''){
					$condition = 'deleted';
				}
			}else{
				if($comment->note_approved == '1') {
					$condition = 'approved';
				} else {
					$condition = 'unapproved';
				}
			}
			$the_comment_class = join( ' ', get_comment_class( $condition ) );
			
			echo "<tr id='comment-$comment->note_ID' class='$the_comment_class'>";
			echo $this->single_row_columns( $comment );
			echo "</tr>\n";
	}
	
	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' , 'imageannotatetable'),
			'approve' => __( 'Approve' , 'imageannotatetable'),
			'unapprove' => __( 'Unapprove' , 'imageannotatetable')
		);
	
		return $actions;
	}
	
	// Handle bulk actions
	function process_bulk_action() {
		$noteid = ( is_array( $_REQUEST['note'] ) ) ? $_REQUEST['note'] : array( $_REQUEST['note'] );
		global $wpdb;
		$table_note = $wpdb->prefix . "demon_imagenote";
		$table_comment = $wpdb->prefix . "comments";
		
		// Define our data source
		if ( 'delete' === $this->current_action() ) {
			/*foreach ( $noteid as $id ) {
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
				$wpdb->query( "DELETE FROM wp_comments WHERE comment_ID = ".$comment_id);
			}*/
			echo '<div class="updated"><p><strong>Images Note Deleted.</strong></p></div>';
		} else if ( 'approve' === $this->current_action() ) {
			/*foreach ( $noteid as $id ) {
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
			}*/
			echo '<div class="updated"><p><strong>Images Note Approved.</strong></p></div>';
		} else if ( 'restore' === $this->current_action() ) {
			/*foreach ( $noteid as $id ) {
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
			}*/
			echo '<div class="updated"><p><strong>Images Note Restored.</strong></p></div>';
		} else if ( 'unapprove' === $this->current_action() ) {
			/*foreach ( $noteid as $id ) {
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
			}*/
			echo '<div class="updated"><p><strong>Images Note Unapproved.</strong></p></div>';
		} else if ( 'edit' === $this->current_action() ) {
			echo '<div class="updated"><p><strong>Images Note Edit.</strong></p></div>';
		} else if ( 'update' === $this->current_action() ) {
			echo '<div class="updated"><p><strong>Images Note Updated.</strong></p></div>';
		}
		
		if (isset($_POST['update_single_note'])) {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesactionupdate')) die('Update security violated');	
			if($_POST['update_single_note'] == "yes") {
				$imgid = $_POST['note_ID'];
				$commentid = $_POST['note_comment_ID'];
				
				if( (get_option('demon_image_annotation_comments') == '0') ) {
					$wpdb->query("UPDATE ".$table_comment." SET comment_content = '".$_POST['note_text']."', comment_author = '".$_POST['note_author']."', comment_author_email = '".$_POST['note_email']."' WHERE comment_ID = ".$commentid);
				}
				$query = "UPDATE `".$table_note."` SET
									`note_author` = '".$_POST['note_author']."',
									`note_email` = '".$_POST['note_email']."',
									`note_top` = '".$_POST['note_top']."',
									`note_left` = '".$_POST['note_left']."',
									`note_width` = '".$_POST['note_width']."',
									`note_height` = '".$_POST['note_height']."',
									`note_text` = '".$_POST['note_text']."'	
									where note_ID = '".$imgid."'
								";
				$wpdb->query($query);
			}
		}
	}
	
	function column_cb($item) {
		if ( current_user_can('moderate_comments') ) {
			$moderate = true;
		}
		if($moderate){
			return sprintf(
				'<input type="checkbox" name="note[]" value="%s" />', $item->note_ID
			);
		}
	}
	
	function get_views() {
		global $allnum, $pendingnum, $approvednum;
		$current = ( !empty($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all');
		
		$allclass = ($current == 'all' || $current == '' ? ' class="current"' :'');
		$pendingclass = ($current == 'pending' ? ' class="current"' :'');
		$approvedclass = ($current == 'approved' ? ' class="current"' :'');
			
		$status_links = array(
			"all"       => __("<a href='".sprintf('?page=%s',$_REQUEST['page'])."' ".$allclass.">All</a> ".sprintf('(%s)', $allnum)."",'imageannotatetable'),
			"pending" => __("<a href='".sprintf('?page=%s&filter=%s',$_REQUEST['page'],'pending')."' ".$pendingclass.">Pending</a> ".sprintf('(%s)', $pendingnum)."",'imageannotatetable'),
			"approved"   => __("<a href='".sprintf('?page=%s&filter=%s',$_REQUEST['page'],'approved')."' ".$approvedclass.">Approved</a> ".sprintf('(%s)', $approvednum)."",'imageannotatetable')
		);
		return $status_links;
	}
	
	function editNote() {
		if ( 'edit' === $this->current_action() ) {
			$noteid = ( is_array( $_REQUEST['note'] ) ) ? $_REQUEST['note'] : array( $_REQUEST['note'] );
			global $wpdb;
			$table_note = $wpdb->prefix . "demon_imagenote";
			$table_comment = $wpdb->prefix . "comments";
			
			foreach ( $noteid as $id ) {
				$id = absint( $id );
				
				$query = "SELECT * from ".$table_note." where note_ID = $id";
				$result = $wpdb->get_results($query);
				?>
                <div class="wrap">
				<form name="dia_update_form" method="post" action="<?php echo sprintf('?page=%s&action=%s&paged=%s',$_REQUEST['page'],'update',$_REQUEST['paged']); ?>">
          
				<input type="hidden" name="update_single_note" value="yes">
				<?php wp_nonce_field('imagenotesactionupdate') ?>
                
				<?php
				//print_r($result);
				foreach ($result as $r) {
					echo '<table class="widefat" width="100%">';
					echo '<thead><tr>';
					echo '<th colspan="2">Edit Image Note : '.$r->note_img_ID.'<input type="hidden" name="note_ID" value="'.$r->note_ID.'" /><input type="hidden" name="note_comment_ID" value="'.$r->note_comment_ID.'" /></th>';
					echo '</tr></thead>';
					echo '<tbody>';
					echo '<tr>';
					echo '<td width="120">Image Preview</td>';
					echo '<td>';
					
					$postid = $r->note_post_ID;
					
					if($postid == 0){
						//grab post ID from comment
						global $wpdb;
						$list = $wpdb->get_results("select * from " . $wpdb->prefix . "comments where comment_ID ='".$r->note_comment_ID."' and comment_content = '".$r->note_text."'");
						foreach ($list as $t) {
							$postid = $t->comment_post_ID;
						}
					}
					if($postid == 0){
						//grab post ID from note image ID
						$postid = dia_getBetween('-','-',$r->note_img_ID);
					}
					
					if($postid != 0){
						//find all images
						$post = get_post($postid);
						$postcontent = $post->post_content;
						preg_match_all('/<img[^>]+>/i', $postcontent, $matches);
						//print_r($matches);
						
						//find id and src
						$img = array();
						foreach( $matches[0] as $img_tag){
							preg_match_all('/(id|src)=("[^"]*")/i',$img_tag, $img[$img_tag]);
						}
						//print_r($img);
						
						$imgid;
						$imgsrc;
						$count;
						foreach( $img as $img_tag){
							$first = trim($img_tag[0][0]);
							$second = trim($img_tag[0][1]);
							
							if(strpos($first,'id=')!==false){
								$imgid = substr($first, 4, -1);
								$imgsrc = substr($second, 5, -1);
							}else if(strpos($first,'src=')!==false){
								$imgsrc = substr($first, 5, -1);
								$imgid=md5($imgsrc);
								$imgid='img-'.$postid.'-'.substr($imgid,0,10);
							}
							
							if($r->note_img_ID == $imgid){
								$notelink = sprintf('?page=%s&action=%s&note=%s&paged=%s"',$_REQUEST['page'],strtolower($condition),$item->note_ID, $_REQUEST['paged']);
								echo '<div id="dia-admin-holder" data-note-ID="'.$r->note_ID.'" date-note-link="'.$notelink.'">';
								echo '<img id="'.$imgid.'" addable="false" src="'.$imgsrc.'">';	
								echo '</div>';
							}
							$count++;
						}
						if($count==0){
							echo 'No preview image';
						}
					}else{
						echo 'No preview image';
					}
										
					echo '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Author</td>';
					echo '<td><input name="note_author" type="text" size="40" value="'.$r->note_author.'" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Email</td>';
					echo '<td><input name="note_email" type="text" size="40" value="'.$r->note_email.'" /></td>';
					echo '</tr>';
					
					echo '<tr>';
					echo '<td>Top</td>';
					echo '<td><input name="note_top" type="text" size="5" value="'.$r->note_top.'" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Left</td>';
					echo '<td><input name="note_left" type="text" size="5" value="'.$r->note_left.'" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Width</td>';
					echo '<td><input name="note_width" type="text" size="5" value="'.$r->note_width.'" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Height</td>';
					echo '<td><input name="note_height" type="text" size="5" value="'.$r->note_height.'" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Text</td>';
					echo '<td><textarea name="note_text" cols="32" rows="5">'.$r->note_text.'</textarea></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td></td>';
					echo '<td><input type="submit" name="update" value="update" class="button-primary action" /><input type="button" name="cancel" value="cancel"  class="button-secondary action" onclick="window.location = \'?page='.$_REQUEST['page'].'&paged='.$_REQUEST['paged'].'\';" /></td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo "</table>";
				
				?></form></div><?php
			}
		}
	}
	
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers, $screen, $allnum, $pendingnum, $approvednum;
		$table_note = $wpdb->prefix . "demon_imagenote";
		$table_comment = $wpdb->prefix . "comments";
		$screen = get_current_screen();
		$filter = ( isset($_REQUEST['filter']) ? $_REQUEST['filter'] : 'all');
		
		/* Handle our bulk actions */
			$this->process_bulk_action();
			
		/* -- Preparing your query -- */
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//with wordpress comment
				$queryall = "SELECT ".$table_note.".*, ".$table_comment.".comment_approved FROM ".$table_note." LEFT OUTER JOIN ".$table_comment." on ".$table_comment.".comment_ID = ".$table_note.".note_comment_ID WHERE ".$table_note.".note_comment_ID != 0";
				
				$querypending = "SELECT ".$table_note.".*, ".$table_comment.".comment_approved FROM ".$table_note." LEFT OUTER JOIN ".$table_comment." on ".$table_comment.".comment_ID = ".$table_note.".note_comment_ID WHERE ".$table_note.".note_comment_ID != 0 AND note_approved = 0";
				
				$queryapproved = "SELECT ".$table_note.".*, ".$table_comment.".comment_approved FROM ".$table_note." LEFT OUTER JOIN ".$table_comment." on ".$table_comment.".comment_ID = ".$table_note.".note_comment_ID WHERE ".$table_note.".note_comment_ID != 0 AND note_approved = 1";
			} else {
				$queryall = "SELECT * FROM ".$table_note." WHERE ".$table_note.".note_comment_ID = 0";
				$querypending = "SELECT * FROM ".$table_note." WHERE ".$table_note.".note_comment_ID = 0 AND note_approved = 0";
				$queryapproved = "SELECT * FROM ".$table_note." WHERE ".$table_note.".note_comment_ID = 0 AND note_approved = 1";
			}
			
			$wpdb->get_results( $queryall );
			$allnum = $wpdb->num_rows;
			
			$wpdb->get_results( $querypending );
			$pendingnum = $wpdb->num_rows;
			
			$wpdb->get_results( $queryapproved );
			$approvednum = $wpdb->num_rows;
			
			$additionalquery = '';
			if($filter == 'pending'){
				$additionalquery = ' AND note_approved = 0';
			}else if($filter == 'approved'){
				$additionalquery = ' AND note_approved = 1';
			}
			
			//check is sync with wordpress comment
			if( (get_option('demon_image_annotation_comments') == '0') ) {
				//with wordpress comment
				$query = "SELECT ".$table_note.".*, ".$table_comment.".comment_approved FROM ".$table_note." LEFT OUTER JOIN ".$table_comment." on ".$table_comment.".comment_ID = ".$table_note.".note_comment_ID WHERE ".$table_note.".note_comment_ID != 0".$additionalquery;
			} else {
				$query = "SELECT * FROM ".$table_note." WHERE ".$table_note.".note_comment_ID = 0".$additionalquery;
			}
			
		/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
			$order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
			if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; } else { $query.= ' ORDER BY note_ID DESC';}
	
		/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query($query); //return the total number of affected rows
			//How many to display per page?
			$perpage = 10;
			//Which page is this?
			$paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
			//Page Number
			if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
			//How many pages do we have in total?
			$totalpages = ceil($totalitems/$perpage);
			//adjust the query to take pagination into account
			if(!empty($paged) && !empty($perpage)){
				$offset=($paged-1)*$perpage;
				$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
			}
	
		/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );
			//The pagination links are automatically built according to those parameters
	
		/* -- Register the Columns -- */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
			
		/* -- Fetch the items -- */
			$this->items = $wpdb->get_results($query);
	}

} //class

?>

<?php 
//*************** JS Notice ***************
	$jsupdate = isset($_REQUEST['jsupdate']) ? trim($_REQUEST['jsupdate']) : '';
	if($jsupdate == 'delete'){
		echo '<div class="updated"><p><strong>Images Note Deleted.</strong></p></div>';
	}else if($jsupdate == 'update'){
		echo '<div class="updated"><p><strong>Images Note Updated.</strong></p></div>';		
	}
	
	//tab settings 
	$page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 'imagenotes';
	if(!$manage){
		$page = 'imagenotes';
	}
	global $wpdb;
	$table_name = $wpdb->prefix . "demon_imagenote";
?>

<?php 
//*************** Header ***************
?>
<div class="wrap">
<?php  echo "<h2>" . __( 'demon-Image-Annotation Settings', 'dia_trdom' ) . "</h2>"; ?>
Visit my site for more update. <a href="http://www.superwhite.cc" target="_blank">http://www.superwhite.cc</a><br />
If you enjoy using demon Image Annotation and find it useful, please consider making a donation. 
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>


<h2>
<?php
	$pluginurl = 'admin.php?page=';
?>

<?php if($manage){?>
	<a href="<?php echo $pluginurl.'dia_image_notes'; ?>" class="nav-tab<?php $page == 'dia_image_notes' ? print " nav-tab-active" : ''; ?>">Image Notes</a>
    <a href="<?php echo $pluginurl.'dia_settings'; ?>" class="nav-tab<?php $page == 'dia_settings' ? print " nav-tab-active" : '' ?>">Settings</a>
    <a href="<?php echo $pluginurl.'dia_usage'; ?>" class="nav-tab<?php $page == 'dia_usage' ? print " nav-tab-active" : '' ?>">Usage</a>
<?php }?>
<div style="border-top:#CCC solid 1px; width:100%;"></div>
</h2>
</div>


<?php if($page == 'dia_settings') {
//*************** Settings ***************

		//admin settings
		if($_POST['dia_hidden'] == 'Y') {
			/*//show on home page
			$dia_homedisplay = $_POST['dia_homedisplay'];
			update_option('demon_image_annotation_homedisplay', $dia_homedisplay);
			
			//show on post page
			$dia_postdisplay = $_POST['dia_postdisplay'];
			update_option('demon_image_annotation_postdisplay', $dia_postdisplay);
			
			//show on post page
			$dia_pagedisplay = $_POST['dia_pagedisplay'];
			update_option('demon_image_annotation_pagedisplay', $dia_pagedisplay);*/
			
			//post content wrapper
			$dia_csscontainer = $_POST['dia_csscontainer'];
			update_option('demon_image_annotation_postcontainer', $dia_csscontainer);
			
			//plugin status
			$dia_display = $_POST['dia_display'];
			update_option('demon_image_annotation_display', $dia_display);
			
			//admin only
			$dia_admin = $_POST['dia_admin'];
			update_option('demon_image_annotation_admin', $dia_admin);
			
			//auto resize image
			$dia_autoresize = $_POST['dia_autoresize'];
			update_option('demon_image_annotation_autoresize', $dia_autoresize);
			
			//comments thumbnail
			$dia_thumbnail = $_POST['dia_thumbnail'];
			update_option('demon_image_annotation_thumbnail', $dia_thumbnail);
			
			//image note gravatar
			$dia_gravatar = $_POST['dia_gravatar'];
			update_option('demon_image_annotation_gravatar', $dia_gravatar);
			
			//image note gravatar
			$dia_gravatardefault = $_POST['dia_gravatardefault'];
			update_option('demon_image_annotation_gravatar_deafult', $dia_gravatardefault);
			
			//auto approve comment
			$dia_autoapprove = $_POST['dia_autoapprove'];
			update_option('demon_image_annotation_autoapprove', $dia_autoapprove);
			
			//wordpress comment
			$dia_comments = $_POST['dia_comments'];
			update_option('demon_image_annotation_comments', $dia_comments);
			
			//auto insert image id attribute
			$dia_autoimageid = $_POST['dia_autoimageid'];
			update_option('demon_image_annotation_autoimageid', $dia_autoimageid);
			
			//numbering
			$dia_numbering = $_POST['dia_numbering'];
			update_option('demon_image_annotation_numbering', $dia_numbering);
			
			//mouse over desc
			$dia_mouseoverdesc = $_POST['dia_mouseoverdesc'];
			update_option('demon_image_annotation_mouseoverdesc', $dia_mouseoverdesc);
			
			//link
			$dia_linkoption = $_POST['dia_linkoption'];
			update_option('demon_image_annotation_linkoption', $dia_linkoption);
			
			//link desc
			$dia_linkdesc = $_POST['dia_linkdesc'];
			update_option('demon_image_annotation_linkdesc', $dia_linkdesc);
			
			//mouseover text
			$dia_clickable_text = $_POST['dia_clickable_text'];
			update_option('demon_image_annotation_clickable_text', $dia_clickable_text);
			
			//note maxlength
			$dia_maxlength = $_POST['dia_maxlength'];
			update_option('demon_image_annotation_maxlength', $dia_maxlength);
			
			?>
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
			<?php
		} else {
			//Normal page display
			$dia_csscontainer = get_option('demon_image_annotation_postcontainer');
			$dia_display = get_option('demon_image_annotation_display');
			$dia_admin = get_option('demon_image_annotation_admin');
			$dia_autoresize = get_option('demon_image_annotation_autoresize');
			$dia_thumbnail = get_option('demon_image_annotation_thumbnail');
			$dia_gravatar = get_option('demon_image_annotation_gravatar');
			$dia_gravatardefault = get_option('demon_image_annotation_gravatar_deafult');
			$dia_everypage = get_option('demon_image_annotation_everypage');
			$dia_autoapprove = get_option('demon_image_annotation_autoapprove');
			$dia_comments = get_option('demon_image_annotation_comments');
			
			$dia_autoimageid = get_option('demon_image_annotation_autoimageid');
			$dia_numbering = get_option('demon_image_annotation_numbering');
			$dia_mouseoverdesc = get_option('demon_image_annotation_mouseoverdesc');
			$dia_linkoption = get_option('demon_image_annotation_linkoption');
			$dia_linkdesc = get_option('demon_image_annotation_linkdesc');
			$dia_clickable_text = get_option('demon_image_annotation_clickable_text');
			$dia_maxlength = get_option('demon_image_annotation_maxlength');
		}
	?>
    
    <div class="wrap">
    <br/><br/>
    <form name="dia_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="dia_hidden" value="Y">        
        <?php    echo "<h4>" . __( 'Image Annotation Settings', 'dia_trdom' ) . "</h4>"; ?>
        <hr /> 
        <table class="form-table" width="100%">
            <tr>
                <th>
                    <label><?php _e("Plugin Status : " ); ?></label>
                </th>
              <td>
                  <?php
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_display == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_display' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable or disable the demon-image-annotaion plugins although you want it to be Activate.</p>
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Post Content Wrapper : " ); ?></label>
                </th>
                <td>
                    <input type="text" name="dia_csscontainer" value="<?php echo ($dia_csscontainer == '') ? '' : $dia_csscontainer; ?>" size="20"><em><?php _e(" eg: #entrybody, .entrybody" ); ?></em><br />
                    <p>The image annotation plugins initiate by targeting post content wrapper,<br />
                    put in the div wrapper id or class where your post content appear,<br />
                    leave it empty if you don't know what to do.</p><br />
                    <strong>Example (.entrybody)</strong><br />
                    <code>
                    &lt;div class="entrybody&gt;<br />
                    &nbsp;&nbsp;&nbsp; &lt;?php the_content(); ?&gt;<br />
                    &lt;/div&gt;</code><br /><br />
                    <em>Leave it empty will treat all images as image annotation.</em>
                </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Auto Generate Image ID : " ); ?></label>
                </th>
              <td>
                  <?php
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_autoimageid == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_autoimageid' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable jQuery to auto add an id attribute to HTML img tag,<br />
                    the uniqe id will be generate by img src starting with 'img-postid-',<br />
                    it will skip if the id attribute of img tag is already exist.</p><br />
                    
                    <strong>Example (img-postid-4774005463)</strong><br />
                    <code>&lt;img id="img-12-4774005463" src="http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg" /&gt;</code><br /><br />
                    
                    <em>Disable this option if you want to manually add img tag id attribute to all images.</em><br />
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Admin Only : " ); ?></label>
                </th>
              <td>
                  <?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_admin == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_admin' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to allow Add Note for login user only who can moderate comment, public still able to see image annotation.</p>
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Auto Resize Images : " ); ?></label>
                </th>
              <td>
                  <?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_autoresize == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_autoresize' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to auto resizing images to fit content max width.</p>
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Numbering : " ); ?></label>
                </th>
              <td>
                    <?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_numbering == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_numbering' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to show numbering every image annotation.</p><br/>
                    <strong>Example</strong><br />
                    <code><strong>03</strong> | Mouseover to load notes | Image Note by Flickr</code>
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Mouseover Description : " ); ?></label>
                </th>
              <td>
                    <input type="text" name="dia_mouseoverdesc" size="30" value="<?php echo ($dia_mouseoverdesc == '') ? '' : $dia_mouseoverdesc; ?>" size="20"><em><?php _e(" eg: Mouseover to load notes" ); ?></em>
                    <br />
                    <p>Show description on top of every image annotation, leave it empty to hide.</p><br />
                    <strong>Example</strong><br />
                    <code>03 | <strong>Mouseover to load notes</strong> | Image Note by Flickr</code>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Image Hyperlink : " ); ?></label>
                </th>
              <td>
              		<?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_linkoption == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_linkoption' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to show image hyberlink if image is embed as a link, it will show behind load note instruction.</p><br />
                    
                    <input type="text" name="dia_linkdesc" size="30" value="<?php echo ($dia_linkdesc == '') ? '' : $dia_linkdesc; ?>" size="20"><em><?php _e(" eg: Source, Link, Flickr" ); ?></em>
                    <br />
                    <p>Image hyperlink text behind load note instruction, input %TITLE% to show image link title attribute.</p><br/>
                    <strong>Example</strong><br />
                    <code>03 | Mouseover to load notes | <strong>Image Note by Flickr</strong></code>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Remove Mouseover Text : " ); ?></label>
                </th>
              <td>
                  <?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_clickable_text == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_clickable_text' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to remove mouseover text, it will remove a tag title attribute if your image is clickable.</p>
              </td>
            </tr>
        </table><br /><br />
        
        <?php    echo "<h4>" . __( 'Comment Settings', 'dia_trdom' ) . "</h4>"; ?>
        <hr /> 
        <table class="form-table" width="100%">
        	<tr>
                <th>
                    <label><?php _e("Wordpress Comments : " ); ?></label>
                </th>
              <td>
                  <?php
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_comments == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_comments' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to sync all the image note with wordpress commenting system,<br />
                    new image note from annoymous will add into wordpress comment as waiting approval.<p/>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Approve Comments: " ); ?></label>
                </th>
              <td>
                  <?php
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_autoapprove == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_autoapprove' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to automatically approve new comments without moderation or approval.</p>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Comments Thumbnail : " ); ?></label>
                </th>
              <td>
                  <?php
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_thumbnail == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_thumbnail' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to show image thumbnail in comment list.</p>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Comments Maxlength : " ); ?></label>
                </th>
              <td>
                  <input type="number" name="dia_maxlength" max="300" value="<?php echo ($dia_maxlength == '') ? '140' : $dia_maxlength; ?>" size="20"><em><?php _e(" eg: 140" ); ?></em>
                    <br />
                    <p>Maximum characters for image note input.</p>
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("Image Note Gravatar : " ); ?></label>
                </th>
              <td>
                  <?php 
                    $sndisplaymode = array( 0 => __( 'Enable' ), 1 => __( 'Disable' ) );	
                    foreach ( $sndisplaymode as $key => $value) {
                        $selected = $dia_gravatar == $key ? 'checked="checked"' : '';
                        echo "<label><input type='radio' name='dia_gravatar' value='" . esc_attr($key) . "' $selected/> $value</label>";
                    } ?>
                    <br />
                    <p>Enable to show gravatar in image note.</p><br />
                    <em>Default gravatar : </em><br/><?php echo get_bloginfo('template_url'); ?><input type="text" name="dia_gravatardefault" value="<?php echo $dia_gravatardefault ?>" size="20"><?php _e(" eg: /images/default.png" ); ?><br />
              </td>
            </tr>
        </table>
        <hr /> 
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Update Options', 'dia_trdom' ) ?>" />
        </p>
        </form>
    </div>
<?php } else if($page == 'dia_usage') {
	
	
//*************** Usage ***************
?>
	<div class="wrap">
    	<br/><br/>
    	<h3>How to use:</h3>
        <hr/>
    	<ol>
    		<li>
            	<p>First enter div wrapper <strong>id</strong> or <strong>class</strong> in settings where your post content appear, or else the plugin can't find the wrapper to start. Leave it empty if you don't know what to do.</p>
                <strong>Example (.entrybody)</strong><br />
                <code>
                &lt;div class="entrybody&gt;<br />
                &nbsp;&nbsp;&nbsp; &lt;?php the_content(); ?&gt;<br />
                &lt;/div&gt;</code><br /><br />
            </li>
            
            <li>
            	<p>To embed annotations and comments on images, your img tag must have id attribute value start with <strong>‘img-‘</strong>, this plugin already did the trick if you enable <strong>Auto Generate Image ID</strong> option.</p><br />
            </li>
            
            <li>
            	<p>
                If you wish to add an id attribute maunally, here is the guide on how to insert id attribute to img tag.<br />
                - First disable <strong>Auto Generate Image ID</strong> option<br />
                - Add an id attribute start with <strong>‘img-‘</strong> follow by unique id to img tag.<br />
                - All the images must have unique and different id or else you will get the same comments.
                </p>
                <strong>Example (img-4774005463)</strong><br />
                <code>
                &lt;img id=&quot;img-4774005463&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
                </code>
                <br /><br />
            </li>
            
            <li>
            	<p>
                Decide the option for <strong>Wordpress Comments</strong> setting.
                </p>
                <p>
                <strong>Sync with wordpress comments:</strong><br/>
                - image note sync with wordpress comment database<br/>
                - modified comment will auto update both database<br/>
                - deleted comment from wordpress comment will not sync, have to delete manually in image notes table list.<br />
                - new image note from annoymous will auto add into wordpress comment as waiting approval.<br/>
                - the image note only publish when the comment is approve.<br/><br/>
                
                <strong>Not sync with wordpress comments:</strong><br/>
                - standalone image note database.<br/>
                - new image note will publish without approval.
                </p>
                <p>Pls note if you switch the option, the comments added with previous option will not load.</p>
            </li>
        </ol><br/><br/>
        <h3>Usage:</h3>
        <hr/>
        <ol>
            <li>
            	<p><strong>Disable Add Note button:</strong><br/>
                Add an addable attribute with value “false” to disable the add note button, but image notes still viewable.<br/>
                Login User who can Moderate Comments still able to see Add button option.
                </p>
                <code>
                &lt;img id=&quot;img-4774005463&quot; addable=&quot;false&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
                </code>
                <br /><br />
            </li>
            
            <li>
            	<p><strong>Exclude image:</strong><br/>
                Add an exclude attribute to disable image annotation function.</p>
                <code>
                &lt;img exclude id=&quot;img-4774005463&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
                </code>
                <br /><br />
            </li>
            
            <li>
            	<p><strong>Comments thumbnail:</strong><br/>
                To add thumbnails to your comments list manually, just add the php code below in your comment callback function.</p>
                <code>
                &lt;?php if (function_exists(&#39;dia_thumbnail&#39;)) {
                    dia_thumbnail($comment-&gt;comment_ID);
                }?&gt;
                </code>
                <br /><br />
            </li>
       	</ol><br/><br/>
        <h3>Other Notes:</h3>
        <hr/>   
        <ol>
        	<li>
            	There's a new method to exlcude image annotation after version 3, but previous version method id="img-exclude" still work.
            </li>
            <li>
            	Image preview for admin editing is only support version 3 and above, image note added with previous version does not support.
            </li>
        </ol>
    </div>
    
<?php } else {
	
	
//*************** Image Notes ***************
?>
    <div class="wrap">
        <?php
		  echo '</pre><div class="wrap">';
		  
		  $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
		  
		  global $myListTable;
		  $myListTable = new Image_Annotation_List_Table();
		  ?>
          <br/><br/>
		  
          <?php
		  if($action == 'edit'){
		  	$myListTable->editNote();
		  }else{
		  	$myListTable->prepare_items();
			$myListTable->views();
			?> 
            <form method="post">
				<input type="hidden" name="page" value="imageannotatetable">
			<?php
				$myListTable->display();
			?>
			</form></div>
            <?php
		  }
		?>
    </div>
<?php }

//*************** Function ***************
function dia_getBetween($var1="",$var2="",$pool){
	$temp1 = strpos($pool,$var1)+strlen($var1);
	$result = substr($pool,$temp1,strlen($pool));
	$dd=strpos($result,$var2);
	if($dd == 0){
		$dd = strlen($result);
	}
	return substr($result,0,$dd);
}
?>