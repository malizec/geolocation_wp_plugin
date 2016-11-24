<?php

function display_page_for_blocked_user() {
	?>
    	<input type="text" name="page_for_blocked_user" id="page_for_blocked_user" value="<?php echo get_option('page_for_blocked_user'); ?>" />
    <?php
}

function display_max_user_atempt_hints() {
	?>
    	<input type="number" name="max_user_atempt_hints" id="max_user_atempt_hints" value="<?php echo get_option('max_user_atempt_hints'); ?>" />
    <?php
}

function display_disable_user_login_time() {
	?>
    	<input type="number" name="disable_user_login_time" id="disable_user_login_time" value="<?php echo get_option('disable_user_login_time'); ?>" />
    <?php
}

function sandbox_checkbox_element_callback() {

    $options = get_option( 'sandbox_theme_input_examples' );

    $html = '<input type="checkbox" id="checkbox_example" name="sandbox_theme_input_examples[checkbox_example]" value="1"' . checked( 1, $options['checkbox_example'], false ) . '/>';
    $html .= '<label for="checkbox_example">This is an example of a checkbox</label>';

    echo $html;

}

function display_theme_panel_fields() {
	add_settings_section("section", "All Settings", null, "theme-options");

  add_settings_field("page_for_blocked_user", "Page for blocked user redirect to", "display_page_for_blocked_user", "theme-options", "section");
  register_setting("section", "page_for_blocked_user");

  add_settings_field("max_user_atempt_hints", "Max number of hints befor time blocked", "display_max_user_atempt_hints", "theme-options", "section");
  register_setting("section", "max_user_atempt_hints");

  add_settings_field("disable_user_login_time", "Time for login disable in minutes", "display_disable_user_login_time", "theme-options", "section");
  register_setting("section", "disable_user_login_time");

  add_settings_field( 'checkbox_example','Checkbox Element','sandbox_checkbox_element_callback', "theme-options", "section" );
  register_setting("section", "sandbox_checkbox_element_callback");


}

add_action("admin_init", "display_theme_panel_fields");


function displayCountryName() {
	$mysqli = new mysqli("localhost", "root", "root", "ip2location");
  /* check connection */
  if ($mysqli->connect_errno) {
      printf("Connect failed: %s\n", $mysqli->connect_error);
      exit();
  }
  $query = "SELECT country_name FROM `ip2location_db1` GROUP BY country_name ";
  $result = $mysqli->query($query);
  /* associative array */

  $options = get_option( 'myoption' );
  while ($row = $result->fetch_assoc()) {
    echo '<input type="checkbox" name="blocked_country_names[]" value="'.$row['country_name'].'" style="width:auto">'.$row['country_name'];
  }
  /* free result set */
  $result->free();
}
