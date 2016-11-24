<?php
//
function add_theme_menu_item() {
  add_menu_page("Plugin Final One", "Plugin Final One", "manage_options", "plugin-final-one", "theme_settings_page", null, 99);
}

add_action("admin_menu", "add_theme_menu_item");


function theme_settings_page() {
  // require_once( plugin_dir_path( __FILE__ ) . 'default-settings.php');
  ?>
    <div class="wrap">
    <h1>Theme Panel</h1>
    <form method="post" action="options.php">
        <?php
          settings_fields("section");
          do_settings_sections("theme-options");
          submit_button();
        ?>
        </div>
    </form>
  </div>
<?php
}

function display_page_for_blocked_user() {
	?>
    	<input style="width:100%;line-height:4em" type="text" name="page_for_blocked_user" id="page_for_blocked_user" value="<?php echo get_option('page_for_blocked_user'); ?>" />
      <br/><span style="font-size:13px;font-style:italic;">* Insert page name for error redirect page for blocked user</span>
      <br/><span style="font-size:13px;font-style:italic;"></span>
    <?php
}

function display_max_user_atempt_hints() {
	?>
    	<input style="width:100%;line-height:4em" type="number" name="max_user_atempt_hints" id="max_user_atempt_hints" value="<?php echo get_option('max_user_atempt_hints'); ?>" />
      <br/><span style="font-size:13px;font-style:italic;">* Maximum atempts before loged is disabled for xxx minutes.</span>
      <br/><span style="font-size:13px;font-style:italic;"></span>
    <?php
}

function display_disable_user_login_time() {
	?>
    	<input style="width:100%;line-height:4em" type="number" name="disable_user_login_time" id="disable_user_login_time" value="<?php echo get_option('disable_user_login_time'); ?>" />
      <br/><span style="font-size:13px;font-style:italic;">* Here are setting for how long in minutes user is disabled to login.</span>
      <br/><span style="font-size:13px;font-style:italic;"></span>
    <?php
}

function display_blacklist_username_list() {
  // wp_editor( ' ', 'blacklist_username_list' );
  ?>
    	<input style="width:100%;line-height:4em" type="text" name="blacklist_username_list" id="blacklist_username_list" value="<?php echo get_option('blacklist_username_list'); ?>" />
      <br/><span style="font-size:13px;font-style:italic;">* Insert here blacklist username for block login</span>
      <br/><span style="font-size:13px;font-style:italic;">* Insert username and separated by commas</span>
    <?php
}

function display_theme_panel_fields() {
	add_settings_section("section", "All Settings", null, "theme-options");

  add_settings_field("page_for_blocked_user", "Page for blocked user redirect to", "display_page_for_blocked_user", "theme-options", "section");
  register_setting("section", "page_for_blocked_user");

  add_settings_field("max_user_atempt_hints", "Max number of hints befor time blocked", "display_max_user_atempt_hints", "theme-options", "section");
  register_setting("section", "max_user_atempt_hints");

  add_settings_field("disable_user_login_time", "Time for login disable in minutes", "display_disable_user_login_time", "theme-options", "section");
  register_setting("section", "disable_user_login_time");

    add_settings_field("blacklist_username_list", "Add blaclist username here", "display_blacklist_username_list", "theme-options", "section");
    register_setting("section", "blacklist_username_list");

}

add_action("admin_init", "display_theme_panel_fields");


function displayCountryName() {
  $mysqli = database_connection();

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
