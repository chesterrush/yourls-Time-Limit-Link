<?php
///////////////////////////////////////////////////////////////////
/////////////////////////// CONFIGURATION /////////////////////////
///////////////////////////////////////////////////////////////////


// EDIT THIS: your auth parameters
$username = '';
$password = '';
$v_hash = time() . rand(10000 , 90000);

// EDIT THIS: the query parameters
$url     = 'https://www.stefan-mies.com/?' . $v_hash; // URL to shrink
$keyword = $v_hash;  // optional keyword
$title   = $v_hash;   // optional, if omitted YOURLS will lookup title with an HTTP request
$format  = 'json';   // output format: 'json', 'xml' or 'simple'

// EDIT THIS: the URL of the API file
$api_url = 'http://---------URL----------/yourls-api.php';


///////////////////////////////////////////////////////////////////
///////////////////////////// SCRIPTS /////////////////////////////
///////////////////////////////////////////////////////////////////
$content = array(); 

$content['referrer'] = "http://www.artegic.de"; 
$content = json_encode($content);

// Init the CURL session
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $api_url );
curl_setopt( $ch, CURLOPT_HEADER, 0 );            // No header in the result
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // Return, do not echo result
curl_setopt( $ch, CURLOPT_POST, 1 );              // This is a POST request
curl_setopt( $ch, CURLOPT_POSTFIELDS, array(      // Data to POST
        'url'      => $url,
        'keyword'  => $keyword,
        'title'    => $title,
        'format'   => $format,
        'action'   => 'shorturl',
        'username' => $username,
        'password' => $password,
		'content'  => $content
    ) );

// Fetch and return content
$data = curl_exec($ch);
curl_close($ch);

// Do something with the result. Here, we just echo it.
#echo $data;

$arr_content = json_decode($data,true);
print_r($data );

if($arr_content['status'] === "success"){
	echo $arr_content['shorturl'];	
}else{
	echo $url .  "<- No";
}


?>
