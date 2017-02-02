<?php
/*
Plugin Name: Time limited Links with Fallback
Plugin URI: https://github.com/chesterrush/yourls-Time-Limit-Link/
Description: Transmit a Link with Fallback link and set a global valide time limited in minutes 
Version: 1.0
Author: Stefan Mies
Author URI: http://stefan-mies.com/
*/


// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'redirect_shorturl', 'stm_redirect_shorturl' );
function stm_redirect_shorturl($args ){
	#print_r($args);
	
	$stm_time_limit = yourls_get_option( 'stm_time_limit' );
	$stm_time_limit_fallback = yourls_get_option( 'stm_time_limit_fallback' );
	
	$stm_time_limit_no_forward = yourls_get_option( 'stm_time_limit_no_forward' );
	
	
	
	
	
	if(isset($stm_time_limit) && !empty($stm_time_limit)){
	
		
		$con_stm=new mysqli(YOURLS_DB_HOST, YOURLS_DB_USER , YOURLS_DB_PASS, YOURLS_DB_NAME);
		if($stm_con->connect_errno > 0){
			die('Unable to connect to database [' . $con_stm->connect_error . ']');
		}
		
		$stm_keyword = $args[1];
		$stm_sql = "SELECT keyword, NOW( ) , timestamp FROM `" . YOURLS_DB_PREFIX . "url` WHERE timestamp >= NOW( ) - INTERVAL " . $stm_time_limit . " MINUTE AND keyword = '" . $stm_keyword  . "' LIMIT 0 , 1";
		if(!$stm_result = $con_stm->query($stm_sql)){
			die('There was an error running the query [' . $con_stm->error . ']');
		}
		#while($row = $stm_result->fetch_assoc()){
		#	$stm_time_short_link = $row['timestamp'] . '<br />';
		#}
		
		$time_limit_link_valide = $stm_result->num_rows;
		
		if($time_limit_link_valide <= 0){
			// No Valide Link
			$stm_sql = "SELECT content FROM `" . YOURLS_DB_PREFIX . "link_info` WHERE `key` = '" . $stm_keyword  . "' LIMIT 0 , 1";
			echo $stm_sql;
			if(!$stm_result = $con_stm->query($stm_sql)){
				die('There was an error running the query [' . $con_stm->error . ']');
			}
			while($row = $stm_result->fetch_assoc()){
				$stm_time_link_content = $row['content'];
			}
			
			$stm_arr_content = json_decode($stm_time_link_content,true);	
						
			$stm_url = $stm_arr_content['referrer'];
			
			if(isset($stm_url) && !empty($stm_url)){
				
				/*if($stm_time_limit_no_forward == "on"){
					$stm_ch = curl_init();
					curl_setopt( $stm_ch, CURLOPT_URL, $stm_url );
					curl_setopt( $stm_ch, CURLOPT_HEADER, 0 );            // No header in the result
					curl_setopt( $stm_ch, CURLOPT_RETURNTRANSFER, true ); // Return, do not echo result
					curl_setopt( $stm_ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt( $stm_ch, CURLOPT_POST, 1 );              // This is a POST request
					curl_setopt( $stm_ch, CURLOPT_POSTFIELDS, array(      // Data to POST
							'key'      => stm_keyword
						) );

					// Fetch and return content
					curl_exec($stm_ch);
					curl_close($stm_ch);
					
					exit();
				
				}else{*/
					yourls_redirect( $stm_url , 301 ); 
				#}/*part of stm_time_limit_no_forward == On */
				
			}else{
				if(isset($stm_time_limit_fallback) && !empty($stm_time_limit_fallback)){
					yourls_redirect( $stm_time_limit_fallback , 301 ); 
				}
			}
		}else{
			if($stm_time_limit_no_forward == "on"){
				
					$stm_url = $args[0];
				
					$stm_ch = curl_init();
					curl_setopt( $stm_ch, CURLOPT_URL, $stm_url );
					curl_setopt( $stm_ch, CURLOPT_HEADER, 0 );            // No header in the result
					curl_setopt( $stm_ch, CURLOPT_RETURNTRANSFER, true ); // Return, do not echo result
					curl_setopt( $stm_ch, CURLOPT_POST, 1 );              // This is a POST request
					curl_setopt( $stm_ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt( $stm_ch, CURLOPT_POSTFIELDS, array(      // Data to POST
							'key'      => stm_keyword
						) );

					// Fetch and return content
					$output = curl_exec($stm_ch);
					$content_type = curl_getinfo( $stm_ch, CURLINFO_CONTENT_TYPE );
					curl_close($stm_ch);
					header( 'Content-Type: '.$content_type );
					echo $output;
					exit();
			}
		}
	}
}


yourls_add_action( 'pre_api_output', 'stm_get_call' );
function stm_get_call( $args  ) {
	
	
	// Create MySQL Connection
		$con_stm=new mysqli(YOURLS_DB_HOST, YOURLS_DB_USER , YOURLS_DB_PASS, YOURLS_DB_NAME);
	
		if($stm_con->connect_errno > 0){
			die('Unable to connect to database [' . $con_stm->connect_error . ']');
		}
		
		
	// Check Info Table exists and create table if not exists
		$stm_sql = "SHOW TABLES LIKE '" . YOURLS_DB_PREFIX . "link_info'";
		if(!$stm_result = $con_stm->query($stm_sql)){
			die('There was an error running the query [' . $con_stm->error . ']');
		}
		
		$table_exists = $stm_result->num_rows > 0;
		
		#$stm_result->free();
		
		if(!$table_exists){
			$stm_sql = "CREATE TABLE IF NOT EXISTS `" . YOURLS_DB_PREFIX . "link_info` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`key` varchar(200) NOT NULL,
				`content` longtext NOT NULL,
				`type` varchar(4) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `key` (`key`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		
			if(!$stm_result = $con_stm->query($stm_sql)){
				die('There was an error running the query [' . $con_stm->error . ']');
			}
			
			#$stm_result->free();
		}
	
		
		#echo "<h1>_REQUEST</h1>";
		#print_r($_REQUEST);
		
		#echo "<h1>Args</h1>";
		#print_r($args );
		
		
		$stm_json_string = stripslashes($_REQUEST['content']);
		$stm_json_string = mysqli_real_escape_string($con_stm,$stm_json_string);
		
		#echo "<<--- <br>\n";
		$stm_sql = "INSERT INTO " . YOURLS_DB_PREFIX . "link_info (`id`, `key`, `content`, `type`) VALUES (NULL, '" . $args[1]['url']['keyword'] . "', '" . $stm_json_string . "', 'json');";
		if(!$stm_result = $con_stm->query($stm_sql)){
				die('There was an error running the query [' . $con_stm->error . ']');
		}
		
		$con_stm->close();
		
}



// Register our plugin config page
yourls_add_action( 'plugins_loaded', 'stm_config_add_page_timelimit' );
function stm_config_add_page_timelimit() {
        yourls_register_plugin_page( 'time_limited_link_config', 'Time limited URL Plugin Config', 'stm_config_do_page_timelimit' );
        // parameters: page slug, page title, and function that will display the page itself
}

// Display config page
function stm_config_do_page_timelimit() {

// Check if a form was submitted
        if( isset($_POST[ 'submit' ]) ) {
          if( isset( $_POST['stm_time_limit'] ) )
            stm_config_update_option_timelimit( $_POST['stm_time_limit'], "stm_time_limit" );

          if( isset( $_POST['stm_time_limit_fallback'] ) )
            stm_config_update_option_timelimit($_POST['stm_time_limit_fallback'],"stm_time_limit_fallback");

          if( isset( $_POST['stm_time_limit_no_forward'] ) && !empty( $_POST['stm_time_limit_no_forward'] ) ){
            stm_config_update_option_timelimit("on","stm_time_limit_no_forward");
          }else{
            stm_config_update_option_timelimit("off","stm_time_limit_no_forward");
          }
        }
			
	// Create MySQL Connection
		$con_stm=new mysqli(YOURLS_DB_HOST, YOURLS_DB_USER , YOURLS_DB_PASS, YOURLS_DB_NAME);
	
		if($stm_con->connect_errno > 0){
			die('Unable to connect to database [' . $con_stm->connect_error . ']');
		}
		
		
	// Check Info Table exists and create table if not exists
		$stm_sql = "SHOW TABLES LIKE '" . YOURLS_DB_PREFIX . "link_info'";
		if(!$stm_result = $con_stm->query($stm_sql)){
			die('There was an error running the query [' . $con_stm->error . ']');
		}
		
		$table_exists = $stm_result->num_rows > 0;
		
		#$stm_result->free();
		
		if(!$table_exists){
			$stm_sql = "CREATE TABLE IF NOT EXISTS `" . YOURLS_DB_PREFIX . "link_info` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`key` varchar(200) NOT NULL,
				`content` longtext NOT NULL,
				`type` varchar(4) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `key` (`key`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		
			if(!$stm_result = $con_stm->query($stm_sql)){
				die('There was an error running the query [' . $con_stm->error . ']');
			}
			
			#$stm_result->free();
		}
	
	
        // Get value from database
        $stm_time_limit = yourls_get_option( 'stm_time_limit' );
		$stm_time_limit_fallback = yourls_get_option( 'stm_time_limit_fallback' );
		$stm_time_limit_no_forward = yourls_get_option( 'stm_time_limit_no_forward' );
		
		if($stm_time_limit_no_forward == "on"){
			$stm_time_limit_no_forward = "checked";
		}
		
		#print_r($_POST);

        echo <<<HTML
                <h2>Time Limit Plugin Config</h2>
                <p>Config time limite for your links. Important: It will be necessary that all links will be unique.  </p>
                <form method="post">
                <p><label for="stm_time_limit_fallback">URL to fallback to</label> <input type="text" id="stm_time_limit_fallback" name="stm_time_limit_fallback" value="$stm_time_limit_fallback" size="40" /></p>
                                <p><label for="stm_time_limit">Links valid for (minutes)</label> <input type="text" id="stm_time_limit" name="stm_time_limit" value="$stm_time_limit" size="40" /></p>
                                <p><label for="stm_time_limit_no_forward">Use Yourls service as Proxy for valid links</label> <input type="checkbox" id="stm_time_limit_no_forward" name="stm_time_limit_no_forward" size="40" $stm_time_limit_no_forward /></p>

                <p><input type="submit" name="submit" value="Update value" /></p>
                </form>
HTML;
}

// Update option in database
function stm_config_update_option_timelimit($tmp_value, $tmp_key ) {
        $in = $tmp_value;

        if( $in ) {
                // Validate test_option. ALWAYS validate and sanitize user input.
                // Here, we want an string
                $in = strval( $in);

                // Update value in database
                yourls_update_option( $tmp_key, $in );
        }
}



