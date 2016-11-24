<?php
/**
* Plugin Name:        Plugin Final One
* Plugin URI:         https://malizec.netne.net
* Author:             Nenad Cvetkovic
* Author URI:         https://www.facebook.com/nenadcv
* Description:        This is text about this plugin and his functionality
* Version:            0.0.1
* License:            GPL-2.0+
* Text Domain:        restricted-place-on-web
**/

// Define development
// define('DEVELOPMENT', false);
define('DEVELOPMENT', true);
define('ONLINE', false);

// Default error page is 404
$page_for_blocked_user = get_option('page_for_blocked_user');

// Max login user hints
$max_user_atempt_hints    = get_option('max_user_atempt_hints');

// Disable user to login
$disable_user_login_time  = get_option('disable_user_login_time');   // minutes

if ( DEVELOPMENT == true ) {
  // PHP Error checking
  error_reporting(E_ALL);
  ini_set("display_errors", 1);

}

function database_connection() {
  if ( ONLINE == false) {
    return new mysqli("localhost", "root", "root", "ip2location");
  } else {
    return new mysqli("mysql443.loopia.se", "nenad@w29677", "nenadmashni1", "webexclusives_com_db_2");
  }
}

// Add styles for this plugin
// wp_enqueue_style( $handle, $src, $deps, $ver, $media );
function wpdocs_theme_name_scripts() {
    wp_enqueue_style( 'style-name', plugin_dir_path( __FILE__ ).'css/plugin-final-one.css' );
    // wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );

// check if session has started
if ( !session_id() ) @session_start();

if ( DEVELOPMENT == true ) {
  echo '<pre>';
    var_dump($_SESSION);
    print_r($_SESSION['error_login_data']);
  echo '</pre>';
  echo $page_for_blocked_user;
  echo $max_user_atempt_hints;
  echo $disable_user_login_time;
}

// custom authenticate function to check ip,username... before wp authenticate
add_action( 'wp_authenticate' , 'check_custom_authentication' );

function check_custom_authentication ($username) {
  global $page_for_blocked_user;
  global $page;
  global $msg;
  global $disable_user_login_time;
  global $max_user_atempt_hints;

  // set first login atempt
  if ( !isset($_SESSION['first_login_time']) ){ $_SESSION['first_login_time'] = time(); }


  // Cache the user data for authentication
  $user_real_ip = getUserRealIpAdress();
  $user_ip = Dot2LongIP($user_real_ip);
  $country_name = returnCountryForIp($user_ip);
  $error['count'] = 0;
  $error['meessage'] = 'Errors are:';
  $login_data['username'] = $username;
  if (isset($_POST['pwd']) ) {
    $login_data['password'] = $_POST['pwd'];
  }

  // Blacklist data
  // $blacklist['blacklist_usernames'] = explode(',',trim(get_option( 'blacklist_username_list' ))); // return array from db
  $blacklist['blacklist_usernames'] = array('nenad','user','administrator');
  $blacklist['blacklist_ip'] = array('21307064331');
  $blacklist['blacklist_real_ip'] = array('127.0.0.11');
  $blacklist['blocked_users'] = array('adminuser');

  // check for blacklist ip
  $new_array = array();
  if ( get_option('blocked_country_names') ) {
    $country_array = get_option('blocked_country_names');
    foreach ($country_array as $key => $value) {
      array_push($new_array,$key);
    }
  }
  if ( DEVELOPMENT == false ) {
    echo $user_real_ip;
    echo '<br/>';
    echo $user_ip;
    echo '<br/>';

    echo returnCountryForIp($user_ip);
    echo '<br/>';

    // print_r($blacklist_ip_array); // dont use this or it will crach your page :=D

  }

  $blacklist_ip_array = returnIpValuesForCountryName($new_array);
  if ( $blacklist_ip_array != null ) {
    for ( $i=0; $i<count($blacklist_ip_array); $i++) {
      $items = explode('-',$blacklist_ip_array[$i]);
      // $items[0] == ip_from
      // $items[1] == ip_to
      if ( ($items[0]<=$user_ip) && ($user_ip<=$items[1]) ) {
        echo 'Nasli smo lopova. Nalazi se od '.long2ip($items[0]) . ' do '.long2ip($items[1]).' i nalazi se na mestu  br '.$i.'<br/>';
        send_mail_to_admin(array($user_real_ip,$user_ip), true);
        redirectBlockedUser($page_for_blocked_user);
      }
    }
  } elseif ( in_array($user_real_ip, $blacklist['blacklist_real_ip']) || in_array($user_ip, $blacklist['blacklist_ip']) ) {
    // Check if user ip is blocke or is on blacklist
  } elseif ( $username ){   // check is username or ip is on blacklist
    // create session for first time login time and date
    add_user_hints_login();
    if ( in_array($username, $blacklist['blacklist_usernames']) ) {
      send_mail_to_admin(array($user_real_ip,$user_ip,$username,$login_data['password']), true);
      check_user_login_hints($disable_user_login_time,$max_user_atempt_hints);
      $blacklist_user_error_message = '';
      $blacklist_user_error_message .= $max_user_atempt_hints.'Pogresna kombinacija username/password. Imate jos '.(5-$_SESSION['error_login_data']['login_hints']).' pokusajaa.';
      $blacklist_user_error_message .= $max_user_atempt_hints.'Pogresna kombinacija username/password. Imate jos '.(12-$_SESSION['error_login_data']['login_hints']).' pokusajaa.';
      redirectWithErrorMessage('wp-login', $blacklist_user_error_message);
    } else {
      //  username is not in blacklist array,  continue if everthing is ok continue to login page
      check_user_login_hints($disable_user_login_time,$max_user_atempt_hints);
      redirectWithErrorMessage('wp-login','Else'.$_SESSION['error_login_data']['login_hints'].$disable_user_login_time.$max_user_atempt_hints);
    }
  }
}

// add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );
//
// function my_login_redirect( $redirect_to, $request, $user ) {
// 	//is there a user to check?
// 	global $user;
// 	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
// 		//check for admins
// 		if ( in_array( 'administrator', $user->roles ) ) {
// 			// redirect them to the default place
// 			return $redirect_to;
// 		} else {
// 			return home_url();
// 		}
// 	} else {
// 		return $redirect_to;
// 	}
// }

add_action( 'wp_login_failed', 'custom_login_fail' ); // hook failed login

if( ! function_exists( 'custom_login_fail' ) ) {
    function custom_login_fail( $username ) {
      check_user_login_hints($disable_user_login_time,$max_user_atempt_hints);
      set_error_session_data();
      //  username is not in blacklist array,  continue if everthing is ok continue to login page
      add_user_hints_login();
      $blacklist_user_error_message .= 'Pogresna kombinacija username/password. Imate jos '.(3-$_SESSION['error_login_data']['login_hints']).' pokusajaaa.';
      $blacklist_user_error_message .= 'Pogresna kombinacija username/password. Imate jos '.(3-$_SESSION['error_login_data']['login_hints']).' pokusajaaa.';
      redirectWithErrorMessage('wp-login', $blacklist_user_error_message);
    }
}

function set_error_session_data(){
  $user_real_ip = getUserRealIpAdress();
  $user_ip = Dot2LongIP($user_real_ip);
  $country_name = returnCountryForIp($user_ip);

  $_SESSION['error_login_data']['user_ip'] = $user_ip;
  $_SESSION['error_login_data']['user_real_ip'] = $user_real_ip;
  $_SESSION['error_login_data']['user_country'] = $country_name;
}

function add_user_hints_login(){
  if ( !isset($_SESSION['error_login_data']['login_hints']) ) {
    $_SESSION['error_login_data']['login_hints'] = 1;
  } else {
    $_SESSION['error_login_data']['login_hints']++;
  }
}

function reset_user_hints_login(){
  // reset user login hints
  $_SESSION['error_login_data']['login_hints'] = 1;
  // reset user login time
  $_SESSION['first_login_time'] = time();
}

function check_user_login_hints($disable_login_time,$max_hints){
  if ( isset($_SESSION['error_login_data']['login_hints']) && ($_SESSION['error_login_data']['login_hints'] >= $max_hints) ) {
    if ( isset($_SESSION['first_login_time']) && ( $_SESSION['first_login_time']+$disable_login_time*60 > time() ) ) { // set time for login disabled 1*60=1min
      $new_time = $_SESSION['first_login_time']+$disable_login_time*60;
      $blocked_message = '';
      // $blocked_message .= 'The time has not yet passed; session time:'.$_SESSION['first_login_time'] .' and time:'.time() .'and '. $new_time;
      $blocked_message .= 'Vase logovanje je onemoguceno na narednih '.$disable_login_time.' minuta. Pokusajte nakon tog vremena.';
      // $blocked_message .=.$disable_user_login_time.'='.$max_user_atempt_hints;
      redirectWithErrorMessage('wp-login',$blocked_message);
      die();
    } else {
      reset_user_hints_login();
    }
  }
}

if (isset($_GET['login']) && $_GET['login']=='failed' ) {
  if ( isset($_GET['msg']) ) {
    // echo urldecode($_GET['msg']);
    $error_message = urldecode($_GET['msg']);
    display_get_error_message($error_message);
  }
}


// *************************************** //
//           Options Main Page              //
// *************************************** //

require_once( plugin_dir_path( __FILE__ ) . 'options.php');


// *************************************** //
//           Options Submenu Page              //
// *************************************** //

 require_once( plugin_dir_path( __FILE__ ) . 'second_options_page.php');


// *************************************** //
//           Functions place               //
// *************************************** //

// Create black list database table
function createDbTableForBlackList($blackListDataName) {
  global $wpdb;

  $table_name = $wpdb->prefix . $blackListDataName;

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    name tinytext NOT NULL,
    text text NOT NULL,
    url varchar(55) DEFAULT '' NOT NULL,
    UNIQUE KEY id (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}

function returnIpValuesForCountryName($country_array=null){
  if ( empty($country_array) || $country_array==null ) {
    return null;
  } else {
    $mysqli = database_connection();

    /* check connection */
    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $query = "SELECT ip_from,ip_to FROM `ip2location_db1` WHERE ";
    for ($i=0; $i <count($country_array) ; $i++) {
      # code...
      if ( $i == 0  ) {
        $query .= " country_name='$country_array[$i]' ";
      } else {
        $query .= "OR country_name='$country_array[$i]' ";
      }
    }

    // $country_str = implode(',', $country_array); // returns 1,2,3,4,5
    // $query = "SELECT ip_from,ip_to FROM `ip2location_db1` WHERE country_name IN ({$country_str})";

    // echo $query;

    $result = $mysqli->query($query);

    $blacklist_ip_array = array();
    /* associative array */
    while ($row = $result->fetch_assoc()) {
      array_push($blacklist_ip_array, $row['ip_from'].'-'.$row['ip_to']);
    }
    /* free result set */
    $result->free();
    return $blacklist_ip_array;
  }

}

// return black list data from database table
function returnBlackListData($data){
  // return array data
  $array_data = array('nenadcv','username','admin');

  return $array_data;
}


// IP real ip adress
function  check_black_ip_list($ipaddress){
  $realipaddress = getUserRealIpAdress();

  return false;
}

// Function to get the client IP address
function getUserRealIpAdress() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if(getenv('HTTP_FORWARDED')) {
       $ipaddress = getenv('HTTP_FORWARDED');
    } else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return  $ipaddress;
}

// convert real ip adress to ip for checking
function Dot2LongIP ($IPaddr) {
 if ($IPaddr == "") {
   return 0;
 } else {
   $ips = explode(".", $IPaddr);
   return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
 }
}

//
function returnStateForIp2($user_ip){
  // 1st Method - Declaring $wpdb as global and using it to execute an SQL query statement that returns a PHP object
  global $wpdb;
  $results = $wpdb->get_results( 'SELECT * FROM wp_options WHERE option_id = 1', OBJECT );
  print_r($results);
}

// Return ip adress and State name
function returnStateForIp($user_ip) {
  $mysqli = database_connection();

  /* check connection */
  if ($mysqli->connect_errno) {
      printf("Connect failed: %s\n", $mysqli->connect_error);
      exit();
  }
  echo $user_ip;
  // $query = "SELECT * FROM `ip2location_db1` WHERE country_name='Zimbabwe' OR country_name='Serbia'";
  // $query = "SELECT * FROM `ip2location_db1` WHERE $user_ip BETWEEN ip_from AND ip_to";
  $query = "SELECT * FROM `ip2location_db1` WHERE $user_ip>ip_from AND $user_ip<ip_to ";
  $result = $mysqli->query($query);

  /* associative array */
  while ($row = $result->fetch_assoc()) {
    echo '<div style="background-color:#e3e3e3l;color:#fff">'.$row['geo_id'].'-'.$row['ip_from'].'-'.$row['ip_to'].'-'.$row['country_code'].'-'.$row['country_name'].'</div>';
    $high_ip = $row['ip_to'];
    $low_ip = $row['ip_from'];

    if ($user_ip<=$high_ip && $user_ip<=$low_ip ) {
      echo '<div style="color:red">'.$ip.' in range</div>';
    } else {
      echo '<div style="color:red">'.$ip.' not range</div>';
    }
  }
  /* free result set */
  $result->free();
}

function returnCountryForIp($user_ip){
  $mysqli = database_connection();

  $country_name = 'Country Name is : ';

  /* check connection */
  if ($mysqli->connect_errno) {
      printf("Connect failed: %s\n", $mysqli->connect_error);
      exit();
  }
  if ( $user_ip == '2130706433') {
    $country_name = 'Localhost';
  } else {
    $query = "SELECT * FROM `ip2location_db1` WHERE $user_ip>ip_from AND $user_ip<ip_to ";
    $result = $mysqli->query($query);

    /* associative array */
    while ($row = $result->fetch_assoc()) {
      $country_name .= $row['country_name'];
    }
    /* free result set */
    $result->free();
  }

  return $country_name;
}

// Send mail to wp admin for login atempt
function send_mail_to_admin($data,$status) {
  $to      = 'nenadcv@gmail.com';
  $subject = 'the subject';
  $message = 'Login atempts datra are: <br/>'.serialize($data);
  $headers = 'From: nenadcv@gmail.com' . "\r\n" .
      'Reply-To: nenadcv@gmail.com' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();

  if ( $status == true ) {
    if ( mail($to, $subject, $message, $headers) ) {
      return true;
    } else {
      return false;
    }
  }
}

// Error message
if ( isset($_SESSION['error']) ) {
  $error = $_SESSION['error'];
  unset($_SESSION['error']);
  display_session_error_action();
}


// Jquery event to hide wp login form or display error message before wp login form
function display_session_error_action() {
  echo add_jquery_files()
  ?>

  <!-- Add error section before wp login form -->
  <script type="text/javascript">
    $(document).ready(function(){
      $("#loginform").before('<div id="login_error"><?php render_error_message($error); ?></div>');
      // remove login form for viewing
     //  $("#loginform").remove();
    });
  </script>
  <?php
}

// Jquery event to hide wp login form or display error message before wp login form
function display_get_error_message($msg) {
  $msg;
  echo add_jquery_files()
  ?>
  <!-- Add error section before wp login form -->
  <script type="text/javascript">
    $(document).ready(function(){
      $("#loginform").before('<div id="login_error"><?php echo urldecode($_GET['msg']); ?></div>');
      // remove login form for viewing
     //  $("#loginform").remove();
    });
  </script>
  <?php
}

function add_jquery_files() {
  $o = '';
  // add jquery files
  $o .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
  $o .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>';
  $o .= '<script type="text/javascript" src="/javascript/jquery.mousewheel.min.3.0.6.js"></script>';
  $o .= '<link rel="stylesheet" href="/css/jquery.sbscroller.css" />';
  return $o;
}

// Display error message before wp login form
function render_error_message($error){
  echo 'Error<br/>';
}

// Error redirect
function redirectWithErrorMessage($page='wp-login', $msg='Login error') {
  $redirect_url = home_url( $page.'.php?login=failed&msg='.urlencode($msg));
  wp_redirect( $redirect_url );
  exit;
}

// redirect blocked user to error page or 404 page
function redirectBlockedUser($pageForBlockedUser = 'error'){
  $pageForBlockedUser = $pageForBlockedUser;
  if ( (strpos($_SERVER['REQUEST_URI'],'wp-login.php') !== false) ||  (preg_match('/wp-login.php/',$_SERVER['REQUEST_URI']))) {
      $redirect_url = home_url( $pageForBlockedUser.'.php?login=failed&time='.date("Y-m-d-H-i-s") );
      wp_redirect( $redirect_url );
      exit;
  }
}
