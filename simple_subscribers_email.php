<?PHP
	
	/*
		Plugin Name: Simple Subscribers Email
		Description: Turns subscribers into email subscribers
		Author: pgogy
		Version: 0.1
	*/
	
	class simple_subscribers_email{
	
		function __construct(){
		
			add_action("edit_user_profile", array($this,"edit_user"));
			add_action("show_user_profile", array($this,"edit_user"));
			add_action("personal_options_update", array($this,"save_user"));
			add_action("edit_user_profile_update", array($this,"save_user"));
			add_action("user_register", array($this,"registration_save"));
			add_action("draft_to_publish", array($this,"send_emails"));
			add_action("future_to_publish", array($this,"send_emails"));
			 
		}
		
		function mail_from( $email )
		{
			return get_option('admin_email');
		}

		function mail_from_name( $name )
		{
			return get_bloginfo('name');
		}
		
		function set_content_type( $content_type ) {
				return 'text/html';
		}
		
		function send_emails($post_id){
			$users = get_users();
			foreach($users as $user){

				if(get_the_author_meta('sse_subscribe_data', $user->ID) == "on"){
			
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					add_filter( 'wp_mail_from', array($this, 'mail_from') );
					add_filter( 'wp_mail_from_name', array($this, 'mail_from_name') );
								
					$unsubscribe = site_url() . "/wp-admin/profile.php";
				
					$post = get_post($post_id);
				
					$email = "<p>" . __("Hello", 'simple_subscribers_email') . ",</p><p>" . __("There is new content on", "simple_subscribers_email") . " " . get_bloginfo('name') . "</p>.";
					$email .= "<p>" . __("Here is a link to the new post", "simple_subscribers_email") . " <a href='" . get_the_permalink($post_id) . "'>" . $post->post_title . "</a></p>.";
					$email .= "<p>" . __("Here is the post content", "simple_subscribers_email") . "</p><p>" . $post->post_content . "</p>.";
					$email .= "<p><a href='" . $unsubscribe . "'>" . __("Unsubscribe", 'simple_subscribers_email') . "</a></p>"; 
					$email .= "<p>" . __("Thanks", "simple_subscribers_email") . "</p>"; 
				
					wp_mail($user->user_email, "[" . get_bloginfo('name') . "] : " .  __("New Post", 'simple_subscribers_email') . " " . $post->post_title, $email);
				
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					remove_filter( 'wp_mail_from', array($this, 'mail_from') );
					remove_filter( 'wp_mail_from_name', array($this, 'mail_from_name') );	

				}			
			}
		}
		
		function registration_save( $user_id ) {
			update_user_meta($user_id, 'sse_subscribe_data', "on");
		}
		
		function save_user($user_id) {
			if(isset($_POST['sse_subscribe'])){
				if ( current_user_can('edit_user',$user_id) ){
						update_user_meta($user_id, 'sse_subscribe_data', "on");
					}
			}else{
				update_user_meta($user_id, 'sse_subscribe_data', "off");
			}	
		}
		
		function edit_user($user_id){
			?>
			<table class="form-table">
			<tr>
				<th>
					<label for="tc_subscribe"><?php _e('Email Subscription'); ?></label>
				</th>
				<td>
					<?PHP
						$output = "";
						$checked = get_the_author_meta('sse_subscribe_data', $user_id->data->ID);
						if($checked == "on"){
							$output = "checked";
						}
					?>
					<input <?PHP echo $output; ?> type="checkbox" name="sse_subscribe" id="sse_subscribe" />
					<span class="description"><?php _e('Do you wish to receive emails when new posts are added?', 'simple_subscribers_email'); ?></span>
				</td>
			</tr>
			<tr>
			</table>
			<?php
		}
	
	}
	
	$simple_subscribers_email = new simple_subscribers_email();
