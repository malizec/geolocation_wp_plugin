<?php

function add_subpage() {
  add_submenu_page( 'plugin-final-one', 'Page for block ip by country name', 'Country Blocking','manage_options', 'country-blocking', 'display_my_second_options_page');
}


add_action("admin_menu", "add_subpage");


function display_my_second_options_page() {
  ?>
    <div class="wrap">
    <h1>Country Blocking</h1>
    <form method="post" action="options.php">
        <?php
          settings_fields("new_section");
          do_settings_sections("new_options");
          submit_button();
        ?>
        </div>
    </form>
  </div>
<?php
}

function register_options_for_page() {
	add_settings_section("new_section", "Settings about Country Blocking", null, "new_options");

  add_settings_field("countru_names_blocked_ip", "Select coutries for block IP", "displayCountryNameCheckbox", "new_options", "new_section");
  register_setting("new_section", "blocked_country_names");

}

add_action("admin_init", "register_options_for_page");

function dysplay_country_name_for_blocked_ip() {
	?>
    	<input type="text" name="country_name_for_blocked_ip" id="country_name_for_blocked_ip" value="<?php echo get_option('country_name_for_blocked_ip'); ?>" />
    <?php
}

function displayCountryNameCheckbox() {
  $mysqli = database_connection();

  /* check connection */
  if ($mysqli->connect_errno) {
      printf("Connect failed: %s\n", $mysqli->connect_error);
      exit();
  }
  $query = "SELECT country_name FROM `ip2location_db1` GROUP BY country_name ";
  $result = $mysqli->query($query);
  /* associative array */

  // Display what country name is in list

  $options = get_option( 'blocked_country_names' );

  $o = '';
  $o .= '<h2>Selected countries:</h2>';
  $o .= '<hr/>';
  foreach ($options as $country_name=>$value) {
    $o .= ' *<div style="display:inline-block;margin:0 10px 0 0">'.trim($country_name).'</div>';
  }

  $o .= '<hr/>';
  $o .= '<h2>Select other countries</h2>';
  $o .= '<hr/>';


  while ($row = $result->fetch_assoc()) {
    $o .= '<div style="width:280px;display:inline-block;line-height:1.5em">';
    $o .= '<input name="blocked_country_names['.$row['country_name'].']" type="checkbox" value="1" ';
    if ( isset($options[$row['country_name']]) ) {
      $o .= " checked='checked' ";
    }
    $o .= ' />';
    $o .= $row['country_name'];
    $o .= '</div>';
  }

  echo $o;

  /* free result set */
  $result->free();

}
