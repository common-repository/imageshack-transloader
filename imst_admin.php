<?php


//admin menu
function imst_admin() {
	if (function_exists('add_options_page')) {
		add_options_page('ImageShack Transloader: configurations', 'ImageShack Transloader', 1, basename(__FILE__), 'imst_admin_panel');
  }
}


function imst_admin_panel() {

	//Add options if first time running
	add_option('imst_username', 'imst_username', 'Optional username for transload external post images to ImageShack','yes');
	add_option('imst_password', 'imst_password', 'ImageShack password','yes');

	
	if (isset($_POST['imst_username'])) {
		//update settings
		$imst_username = $_POST['imst_username'];
		$imst_password = $_POST['imst_password'];

		update_option('imst_username', $imst_username);
		update_option('imst_password', $imst_password);
	} else {
		//load settings from database
		$imst_username = get_option('imst_username');
		$imst_password= get_option('imst_password');		
	}

	


	
	?>
	<div class=wrap>
	
	
		
	
		<?php 
		if ($_POST["message"] != ''){					
			echo '<div id="message" class="updated fade"><p>'. $_POST["message"] .'</p></div>';
		}		
		?>
		
	
		<form method="post">

			<h2>Configurations : ImageShack Transloader</h2>

			<fieldset name="set1">
				<h3>Store images in an ImageShack account (optional):</h3>							

				<p>					
				ImageShack username:											
				<input name="imst_username" type="input" value="<?php echo $imst_username; ?>">* (<strong>do not </strong>use your ImageShack e-mail here)				
				<br/>
				(This required username is <strong>displayed at top right corner at <a href="http://imageshack.us/" target="_blank">ImageShack </a></strong>when you are logged in)
				<br/>
				</p>
				
				<p>					
				ImageShack password:											
				<input name="imst_password" type="password" value="<?php echo $imst_password; ?>"/>*
				<br/>					
				</p>				
				
				*Just left both fields empty if you want send images anonimously. 
				<br/>
				<br/>
				<em>
				No problems doing that,  but...
				<br/>				
				... if you still don't have a ImageShack account, you should consider <a href="http://my.imageshack.us/registration/" title="Create a free ImageShack account" target="_blank">create one.</a>
				<br/>
				It's <strong>really free</strong> and even using this free ImageShack account you can 
				<a href="http://my.imageshack.us/v_images.php" title="ImageShack: My Images" target="_blank">keep track</a> of which imagens you are hosting with them.
				<br/>				
				This may be useful as a backup of all your images.
				
				<br/>			
				
				</em>

			</fieldset>	
			
			<input type="hidden" name="message" value="Yes! Settings updated!" />


			<div class="submit">
				<input type="submit" name="info_update" value="Update Options" />
			</div>

		</form>	
		
		
		
		
<?php
require_once('imst_donate.php');
?>			
		

		
		
		
		
		<h2>Tutorial video: How to use ImageShack Transloader?</h2>		
		
		
		<h3>Watch the video... it is really easy to understand!</h3>											
		
		
		<object width="640" height="505"><param name="movie" value="http://www.youtube.com/v/w2VvzPcJThQ?fs=1&amp;hl=pt_BR&amp;rel=0&amp;hd=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/w2VvzPcJThQ?fs=1&amp;hl=pt_BR&amp;rel=0&amp;hd=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="505"></embed></object>
		

		<br/>			
		<br/>			
		<br/>			



		
		
	</div>
	
	
	
	
	
<?php

}

//adds the hook to create the admin menu
add_action('admin_menu', 'imst_admin');

?>
