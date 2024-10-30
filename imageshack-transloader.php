<?php
/*
Plugin Name: ImageShack Transloader
Version: 1.0
Description: Transload your post images to <a target="_blank" href="http://imageshack.us">ImageShack</a> to save server resources. To insert an image in a post while editing, just <strong>drag a image from any website </strong>and <strong>drop it </strong>inside the post content area. The image  will be automatically optimized and transloaded to ImageShack when you hit the "Publish" button. You can optionally enter your ImageShack account credentials to where images will be uploaded.
Author: Alex Benfica
Author URI: http://www.alexbenfica.com/
Plugin URI: http://www.dowordpress.com.br/plugins/imageshack-transloader/
Text Domain: imageshack-transloader




License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html


*/


// Includes administration menu... for configurations...
require_once('imst_admin.php');



// No no... you will not like enable this debug in your production enviroment...
// Don't even try, ok? it won't mess with you blog... but the posts will not be stored when debugging! :)
// This is just to debug the plugin... Ok?
$imst_dbg = 0;


/*Retrieve image links from content.*/
function imst_getImages($html){

global $imst_dbg;
	
	$pattern = '/<img[^>]*src=[^"\']?["\']([^"\']*)[\\\\]?["\'][^>]*>/i';	
	
	$matches = array();
	preg_match_all($pattern, $html, $matches);	
	
	$images = array();
	
	foreach($matches[1] as $url){		
		//if image aren't already from Imageshack...		
		$pos = strpos($url, '.imageshack.');
		if($pos === FALSE){		
			$url = str_replace('\\','',$url);						
			
			array_push($images, $url);
			if($imst_dbg){
				print('IMAGE FOUND: ' . $url . '<br>');
			}
			
		}
		else{
			print 'Image already at imageshack: ' . $url;
		}
	}		
	return $images;
}











/*Transload a imagem to imageshack, using the credential provided 
and return the image link at imageshack.*/
function imst_imst_transloadImageshack($imageUrl,$imst_username,$imst_password){	

global $imst_dbg;


	// Option to toglle the transload method used in the first version 
	// of the plugin.. when ImageShack API wasn't used yet...
	// Will probrably be removed soon...
	
	$metodo = 2;	
	
	if($metodo==1){
		$url    = 'http://freedirectlink.com/tools/imageshack_api.php?img=' . $imageUrl;
	}	
	else{				
		// treats the username or passwords empty...
		if(($imst_username=='') or ($imst_password=='')){
			$url_autentication = '';
		}
		else{
			$url_autentication = '&a_username='.$imst_username.'&a_password='.$imst_password;
		}		
		$url = 'http://www.imageshack.us/upload_api.php?url='.$imageUrl.'&key=06BDGLMR76adb5531a84468f681e4895edab9cd7&optimage=resample' . $url_autentication;
		
		// replace the plus sign that is not accepted by imageshack as a parameter...
		$url = str_replace('+','%2B',$url);					
		
		if($imst_dbg){
			print '<br>';
			print '<strong>ImageShack Transload URL. Trying...</strong>';			
			print '<br>';			
			print($url);			
			print '<br>';			
			print('<a target="_blank" href="'.$url.'">Upload this image to ImageShack ... !</a>');			
			print '<br>';			
		}
		
	}	
	
	//Busca a pagina...
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$site = curl_exec($ch);
	curl_close($ch);

	
	if($metodo==1){
		// decode output from API
		$out = json_decode($site);	
		$urlImageshack = $out->image;
	}
	else{		
		$pattern = "/<image_link>([^<]*)<\/image_link>/i";					
		$matches = array();
		preg_match_all($pattern, $site, $matches);
		$urlImageshack = trim($matches[1][0]);
	}	
	
	
	/*
	print $urlImageshack;
	print '<pre>';
	print_r($matches);
	print '</pre>';
	*/
	
	return $urlImageshack;
}







function imst_transloadImages($data){

global $imst_dbg;

	// does not update if saving draft or even on autosaving...	
	if($data['post_status'] != 'publish'){
		return $data;
	}

	// does not update if saving revisions...
	if($data['post_type'] != 'post'){
		return $data;
	}
	
	
	

	// Generates debug about each execution of the plugin...
	if($imst_dbg and 1){	 
		
		$bktrace = debug_backtrace();		
		$bktrace = print_r($bktrace,TRUE);
		
		$bktrace = '<pre>' . $bktrace . '</pre>';
		
		$filedebug = 'debug_imst.html';			
		file_put_contents($filedebug, $bktrace , FILE_APPEND | LOCK_EX);					
	}	
	

	
	
	// make a copu of contents to use as debug...
	if($imst_dbg){	  						
		$content_before = $data['post_content'];		
	} 	
	
	// loads username and password of imageshack...
	$ims_user = get_option('imst_username');
	$ims_pass= get_option('imst_password');			
	
	// retrieve image links from post contents
	$images = imst_getImages($data['post_content']);   	
	
	
	// send images to imageshack and replace links on content...
	foreach($images as $url){
		
  
		// tries some times until give up...
		$max_tries = 5;		
		$tries = $max_tries;
		$urlImageshack = '';		
		
		//send each image do imageshack, using credentiais provided
		while($urlImageshack == ''){		
		
			if(!$tries){
				if($imst_dbg and 1){		
					print('<br>');
					print('For some unknow reason... could not upload this image!');
					echo '<br/>';
					echo '<img src="'.$url.'">';
					echo '<br/>';					
					print('<br>');
				}
				break;
			}						
			// changes the time limit to allow at least 60 seconds per image upload...
			set_time_limit(60);
			$urlImageshack = imst_imst_transloadImageshack($url,$ims_user,$ims_pass);	
			$tries--;
		}
		
	
		if($imst_dbg and 1){		
			print('<br>');			
			
			if($tries){
				print('Image sent to ImageShack using '. ($max_tries - $tries) . ' tries...');			
				print('<br>');			
		
				echo '<table>';
				
				echo '<tr>';		
				echo '<td>';		
				echo  $url;
				echo '<br/>';
				echo '<img src="'.$url.'">';
				echo '<br/>';
				echo '<br/>';
				echo '</td>';		


				echo '<td>';		
				echo '&nbsp;&nbsp;&nbsp;--->&nbsp;&nbsp;&nbsp;';
				echo $urlImageshack;		
				echo '<br/>';
				echo '<img src="'.$urlImageshack.'">';
				echo '<br/>';
				echo '<br/>';
				echo '</td>';		
				
				echo '</tr>';		
				echo '</table>';
			}
			print '<hr>';
		}	
	
	
		//replace each imagem on content with the link of the imageshack 	
		if($urlImageshack != ''){
			$data['post_content'] = str_replace($url,$urlImageshack,$data['post_content']);			
		}
		
		
	}  
	
	// wordpress keeps running... let it finish...
	set_time_limit(60);


	if($imst_dbg and 1){	  						
		if(count($images)){	
			echo '<strong>Content before replacement:</strong>';
			echo '<br>';
			echo htmlentities($content_before);	
			
			echo '<br>';
			echo '<br>';
	
			echo '<strong>Content after replacements:</strong>';
			echo '<br>';
			echo htmlentities($data['post_content']);						
		}	
		else{
			echo 'No imagens were found on post content...';
		}		
		
		if(0){	  									
			print '<pre>';
			$bktrace = debug_backtrace();		
			print_r($bktrace);	
			print '</pre>';									
		} 		
		
		exit();
	}
	

	return $data;  
}

add_filter( 'wp_insert_post_data' , 'imst_transloadImages');



?>