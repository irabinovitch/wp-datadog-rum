<?php
/**
 * Plugin Name: Datadog RUM
 * Plugin URI: http://www.fonz.net/blog/datadog-rum-wordpress/
 * Description: Enable Datadog RUM on your wordpress blog
 * Version: 0.1
 * Author: Ilan Rabinovitch
 * Author URI: http://www.fonz.net
 */

function add_DatadogRUM() {
  // validate values
  if (is_user_logged_in()) { 
    global $current_user;
	$current_user = wp_get_current_user();
  };
?>
<script
    src="https://www.datadoghq-browser-agent.com/datadog-rum-us.js"
    type="text/javascript">
</script>
<script>
    window.DD_RUM && window.DD_RUM.init({
        clientToken: '<?php echo get_option('datadog_rum_client_token'); ?>',
        applicationId: '<?php echo get_option('datadog_rum_app_id'); ?>',
        sampleRate: <?php echo get_option('datadog_rum_sample_rate', 100); ?>,
        trackInteractions: <?php echo get_option('datadog_rum_track_interactions', 'true'); ?>
    });
</script>
<script>
	window.DD_RUM && window.DD_RUM.addRumGlobalContext('usr', {
	  logged_in: <?php if (is_user_logged_in() ) { echo "true"; } else { echo "false"; } ?>,
      <?php if (is_user_logged_in()) { 
	           echo 'id:'.$current_user->ID.',';
			   echo 'login: "'.$current_user->user_login.'",';
   	           echo 'email: "'.$current_user->user_email.'",'; 
   	           echo 'name: "'.$current_user->display_name.'",'; 
		   }	   
	  ?>
  	});
</script>
<?php
}

function print_DatadogRUM_management() {
  $clientToken = $applicationId = $sampleRate = $trackInteractions = $site = $website = "";
  $rumErrors = array();
  
  if (isset($_POST['submit'])) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to manage options for this blog.'));
        }
  $clientToken = trim($_POST['datadog_rum_client_token']);
  if (strlen($clientToken) >= 35 && preg_match("/^pub/", $clientToken)) {
      update_option('datadog_rum_client_token', $clientToken);
  } else {
	array_push($rumErrors, 'Invalid clientToken.');
  }
  
  $applicationId = trim($_POST['datadog_rum_app_id']);
  if (strlen($applicationId) > 35 && substr_count($applicationId,"-") >= 4) {
        update_option('datadog_rum_app_id', $applicationId);
  } else {
	array_push($rumErrors, 'Invalid applicationId.');
  }
  
  $sampleRate = trim($_POST['datadog_rum_sample_rate']);
  if ( filter_var($sampleRate, FILTER_VALIDATE_INT) && $sampleRate >= 0) {
      update_option('datadog_rum_sample_rate', $sampleRate);
  } else {
    array_push($rumErrors, 'sampleRate must be an integer between 0 and 100.');
  }
  
  $site = trim($_POST['datadog_rum_site']);
  update_option('datadog_rum_site', $site);
  $trackInteractions = trim($_POST['datadog_rum_track_interactions']);
  update_option('datadog_rum_track_interactions', $trackInteractions);
  
  if (sizeof($rumErrors) == 0) {
  ?> 
  <div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>
<?php
  } 
  if (sizeof($rumErrors) > 0) {
?>
  <div id="message" class="error fade"><p><strong><?php foreach($rumErrors as $key=>$value) { echo "Error: ".$value." <br/>"; } ?></strong></p></div>
?>
<?php	  
}
}
?>
<div class="wrap">
    <img src="<?php echo plugin_dir_url("/", __FILE__) . trim(dirname(plugin_basename(__FILE__)), '/'); ?>/datadog.svg" width="100" alt="Datadog" />
    <h2>Datadog RUM</h2>
	<p>Create a <a href="https://app.datadoghq.com/rum/list/">RUM application</a> in Datadog and enter its settings below.  If you do not yet have an account you can sign up for a <a href="https://www.datadoghq.com/free-datadog-trial/">free trial</a>.</p></p>
    <form method="post" action="">
  <b>Datadog clientToken</b>
  <input name="datadog_rum_client_token" type="text" id="datadog_rum_client_token" value="<?php echo get_option('datadog_rum_client_token'); ?>" maxlength="40"  size="40" placeholder="e.g. pube12345667890" /><br/>
        <b>Datadog RUM applicationId</b>
        <input name="datadog_rum_app_id" type="text" id="datadog_rum_app_id" value="<?php echo get_option('datadog_rum_app_id'); ?>" maxlength="40" size="40" placeholder="e.g. foo-bar-baz-buzz" /><br/>
  <b>Percentage of sessions to track</b> (eg 100 for all, 0 for none)</b>
  <input name="datadog_rum_sample_rate" type="text" id="datadog_rum_sample_rate" value="<?php echo get_option('datadog_rum_sample_rate', '100'); ?>" size="3" maxlength="3"/>
  <br/>
  <!-- TODO: add other option for alternate sites/intakes-->
    <b><label for="datadog_rum_site">Datadog Site</label></b>
        <select id="datadog_rum_site" name="datadog_rum_site">
          <option value="us" <?php if (get_option('datadog_rum_site', 'us') == "us") { echo "selected"; } ?> >US</option>
          <option value="eu" <?php if (get_option('datadog_rum_site') == "eu") { echo "selected"; } ?> >EU</option>
        </select>
    <br/>
    <b><label for="datadog_rum_track_interactions">Track Interactions</label></b>
        <select id="datadog_rum_track_interactions" name="datadog_rum_track_interactions">
          <option value="true" <?php if (get_option('datadog_rum_track_interactions', 'true') == "us") { echo "selected"; } ?> >True</option>
          <option value="false" <?php if (get_option('datadog_rum_track_interactions') == "false") { echo "selected"; } ?> >False</option>
        </select>
     <br/>
    <input type="submit" name="submit" value="<?php esc_attr_e('Save Changes') ?>" />
    </form>
</div>
<?php
}

function DatadogRUM_config()
{
    if ( function_exists('add_submenu_page') ) {
        add_submenu_page('plugins.php', __('Datadog Real User Monitoring', 'datadog-rum'), __('Datadog Real User Monitoring'), 'manage_options', 'datadog-rum-config', 'print_DatadogRUM_management');
    }
}

function add_DatadogRUM_action_links( $links )
{
    return array_merge(array('settings' => '<a href="' . get_bloginfo( 'wpurl') . '/wp-admin/plugins.php?page=datadog-rum-config">Settings</a>'), $links);
}

add_action('wp_head', 'add_DatadogRUM');

if(is_admin()) {
	add_action( 'admin_head', 'add_DatadogRUM' );
    load_plugin_textdomain('datadog-rum', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n');
    add_action('admin_menu', 'DatadogRUM_config');
    add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'add_DatadogRUM_action_links');
}
?>
