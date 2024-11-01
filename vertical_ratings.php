<?php
/*
Plugin Name: * vertical ratings
Plugin URI: http://digidownload.nl
Description: Who says rating bars have to be horizontal ? Who says ratings can only be measured with stars ? Finally ! A rating plugin for AUTHORS. Lets your visitors love your authors with ajax hearts and rating system. Show if authors are trustworthy, good reads, professionals in their fields or if they are a bit shady. See it in use at <a href='http://digidownload.nl'>digidownload.nl</a>
Author: pete scheepens
Author URI: http://digidownload.nl
Version: 1.7
Contact developer Pete Scheepens at info-at-portaljumper-dot-com
 */
 
add_action("wp_ajax_vr_vote", "vr_vote_func");
add_action("wp_ajax_nopriv_vr_vote", "vr_vote_func");

// create/update database tables
register_activation_hook(__FILE__,'vr_db_init');

// create database
function vr_db_init() {
global $wpdb;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql2 = "CREATE TABLE " .$wpdb->prefix . "vertical_author_ratings (
			`id` int(11) NOT NULL auto_increment,
			`author_id` int(11) NOT NULL,
			`ip_address` varchar(50) NOT NULL,
			`rating` int(11) NOT NULL,
			`timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`), UNIQUE ( `author_id` , `ip_address`)			
		) AUTO_INCREMENT=1 ;";	
		
	dbDelta($sql2);	

vr_check_options();
}

// fill options with default values
function vr_check_options() {
$checkoptions = get_option('vertical_author_ratings_opts','');
   // fill defaults if empty
if (empty($checkoptions))
   {
   $aloptions['location'] = "tl";
   $aloptions['amount'] = "6";
   $aloptions['image'] = "stars";
   $aloptions["title"] = '';
   $aloptions["single"] = 'no';
   $aloptions['bandb'] = 'both';
   $aloptions['trans_author'] = 'Author';
   $aloptions['trans_rating'] = 'Rating :';
   $aloptions['trans_votes'] = 'vote(s)';
   $aloptions['trans_yav'] = 'You have already voted!';
   update_option('vertical_author_ratings_opts', $aloptions);	
   }
}


// load css & js
function vr_moomoo_load() {
wp_enqueue_script('vrrating',plugins_url('/jquery/vrrating.jquery.js', __FILE__));
wp_enqueue_style('vr_css',plugins_url('/jquery/vrrating.jquery.css', __FILE__)); 
} 

function vr_jc_load() {	
  wp_enqueue_script('jquery');
}  

add_action('wp_enqueue_scripts', 'vr_jc_load'); 
add_action('wp_footer', 'vr_moomoo_load');

// insert the div block
add_filter('the_content', 'show_vr');

function show_vr ($content) {

$singlecheck = 'if (is_single() ) ';
$aloptions = get_option('vertical_author_ratings_opts');
if ($aloptions["single"] == "yes") $show = 1; else $show = '';
if ($aloptions['location'] == 'tl')  $display = "<div style='float:left;'> " . return_vr('0','left',$aloptions['title'] , $show ,$aloptions['bandb'] ) . "</div>$content";
elseif ($aloptions['location'] == 'tr')  $display = "<div style='float:right;'> " . return_vr('0','left',$aloptions['title'] , $show , $aloptions['bandb']) . "</div>$content";
else $display = $content;
return $display;
}

// insert the dynamic jq code
add_action('wp_footer', 'vr_jq');

// admin options
add_action('admin_menu', 'vertical_author_ratings_menu');
function vertical_author_ratings_menu() {
add_options_page('vertical ratings', 'vertical ratings', 'manage_options', 'vertical_author_ratings_lovers','vertical_author_ratings_options_page');
}

function vertical_author_ratings_options_page() {
?>
<style>
.abox {padding:1%;-moz-border-radius:10px;-webkit-border-radius:10px;border-radius:10px;margin:2%;border:1px solid #CCC;background:white;}
.ablur {padding:1%;-webkit-box-shadow:0 0 1em hsla(0, 0%, 0%, 1.0);-moz-box-shadow:0 0 1em hsla(0, 0%, 0%, 1.0);margin:2%;box-shadow:0 0 1em hsla(0, 0%, 0%, 1.0);}
</style>
<div class="abox ablur" style="float:left;width:54%;text-align:center">
<?PHP
if (!empty($_POST) && wp_verify_nonce($_POST['vr_filed'],'vertical_author_ratings_form_submit') )
   {
      $aloptions['location'] = $_POST['location'];
      $aloptions['amount'] = $_POST['amount'];
      $aloptions['image'] = $_POST['image'];
      $aloptions['title'] = $_POST['title'];
      $aloptions['single'] = $_POST['single'];
     $aloptions['bandb'] = $_POST['bandb'];
      $aloptions['trans_author'] = $_POST['trans_author'];
      $aloptions['trans_rating'] = $_POST['trans_rating'];
      $aloptions['trans_votes'] = $_POST['trans_votes'];
      $aloptions['trans_yav'] = $_POST['trans_yav'];
      update_option('vertical_author_ratings_opts', $aloptions);	
     
      echo "<div style='background-color:yellow;text-align:center'><h2>VR settings were saved !</h2></div>";
   
   }
   
$aloptions = get_option('vertical_author_ratings_opts');
//  print_R($aloptions);
?>
<h1>vertical_author_ratings Settings Menu (free version)</h1>
   A vertical rating system for WordPress<br/><br/>
   
<form method="post">

<strong>WHAT do you want your visitors to rate ?</strong><br/>
<small>(Premium option)</small><br />
<select>
<option SELECTED>Authors</option>
<option disabled>posts and pages</option>
</select>
 <br/><br/>
<strong>WHERE shall we show the rating blocks ?</strong><br/><small>when unchecked the ratings only show on single posts<br>when checked the ratings also show on the main pages.</small>
   <br />
   <input type="checkbox" name="single" value="yes" <?PHP if ($aloptions['single'] == 'yes') echo 'checked'; ?> > Show ratings on all posts/pages<br /><br />

<strong>WHERE should the rating block show up (in relation to your post content) ?</strong><br/>  
  <input type="radio" name="location" value="ad" <?PHP if ($aloptions['location'] == 'ad') echo 'checked'; ?> > -DISABLED<br />
<input type="radio" name="location" value="tl" <?PHP if ($aloptions['location'] == 'tl') echo 'checked'; ?> > -left of content<br />
<input type="radio" name="location" disabled > -right of content (premium)<br />
 <br/><br/>
   <strong>HOW many items would you like to stack in the rating bar ? (1-30)</strong><br/><small>Regardless of how many items you choose, the total value is always 100%.<br/> so with 3 items 33% would fill 1 item. with 6 items 33% would fill 2 items etc.</small>
   <br />
   <input type="number" name="amount" min="1" max="30" value="<?PHP echo $aloptions['amount'] ; ?>" >
<br/><br/>
<strong>WHICH item do you want to show in the rating bar ?</strong><br/><small>More options in the PREMIUM version.</small>
   <br />
   <select name="image">
      <option value="-" disabled="disabled"> - choose one - </option>
      <option value="stars" <?PHP if ($aloptions['image'] == 'stars') echo 'selected'; ?> >Stars (medium)</option>
      <option value="hearts" <?PHP if ($aloptions['image'] == 'hearts') echo 'selected'; ?> disabled>Hearts</option>
      <option value="arrows" <?PHP if ($aloptions['image'] == 'arrows') echo 'selected'; ?> disabled>arrows</option>
      <option value="balls" <?PHP if ($aloptions['image'] == 'balls') echo 'selected'; ?>>balls</option>
      <option value="thermometer2" <?PHP if ($aloptions['image'] == 'thermometer2') echo 'selected'; ?>>thermometer (72 x 160 px )</option>
      <option value="lamppostd" <?PHP if ($aloptions['image'] == 'lamppostd') echo 'selected'; ?> disabled>lamppost (89 x 250 px )</option>
   </select>
<br/><br/>
<strong>WHAT do you want to show above the rating bar ?</strong><br/><small>More options in the PREMIUM version.</small>
   <br />
   <select name="title">
      <option value="-" disabled="disabled"> - choose one - </option>
      <option value="nothing" <?PHP if ($aloptions['title'] == 'nothing') echo 'selected'; ?> >nothing</option>
      <option value="user_nicename" <?PHP if ($aloptions['title'] == 'user_nicename') echo 'selected'; ?> >author nicename</option>
      <option value="user_login" <?PHP if ($aloptions['title'] == 'user_login') echo 'selected'; ?> >author login name (always available *)</option>
      <option value="user_email" <?PHP if ($aloptions['title'] == 'user_email') echo 'selected'; ?> disabled>author e-mail</option>
      <option value="user_url" <?PHP if ($aloptions['title'] == 'user_url') echo 'selected'; ?> disabled>author url</option>
      <option value="nickname" <?PHP if ($aloptions['title'] == 'nickname') echo 'selected'; ?> disabled>author nickname</option>
      <option value="first_name" <?PHP if ($aloptions['title'] == 'first_name') echo 'selected'; ?> disabled>author first name</option>
      <option value="last_name" <?PHP if ($aloptions['title'] == 'last_name') echo 'selected'; ?> disabled>author last name</option>
   </select>   
   
   <br/><br/>
   <strong>Box 'n borders (</strong><br><small>see sidebar for examples.</small>
   <br/>
<input type="radio" name="bandb" value="none" <?PHP if ($aloptions['bandb'] == 'none') echo 'checked'; ?> > -None<br />
<input type="radio" name="bandb" value="box" <?PHP if ($aloptions['bandb'] == 'box') echo 'checked'; ?> > -boxed<br />
<input type="radio" name="bandb" value="blur" <?PHP if ($aloptions['bandb'] == 'blur') echo 'checked'; ?> > -shadow<br />
<input type="radio" name="bandb" value="both" <?PHP if ($aloptions['bandb'] == 'both') echo 'checked'; ?> disabled > -box and shadow (premium)<br />
  <br/> <br/>
  <strong>Translation</strong><br><small>Sorry, premium only</small>
<br>
 Author : <input type="text" name="trans_author" value="<?PHP echo $aloptions['trans_author']; ?>" > (shows above the rating bar)<br>
  Rating : <input type="text" name="trans_rating" value="<?PHP echo $aloptions['trans_rating']; ?>" > below the rating bar : Rating 73% - 3 vote(s)<br>
  vote(s) : <input type="text" name="trans_votes" value="<?PHP echo $aloptions['trans_votes']; ?>" > below the rating bar : Rating 73% - 3 vote(s)<br>
  you already voted : <input type="text" name="trans_yav" value="<?PHP echo $aloptions['trans_yav']; ?>" > shows on double vote<br><br>
      <?php wp_nonce_field('vertical_author_ratings_form_submit','vr_filed'); ?>
<input type="submit" value="submit changes" style="background-color:yellow">
</form>
<br><br />
<iframe width="420" height="315" src="http://www.youtube.com/embed/kOzj7Qzlavc" frameborder="0" allowfullscreen></iframe>
</div>


<div class="abox ablur" style="float:right;width:30%;text-align:center">
   <h1>Vertical ratings</h1>
   Because horizontal with stars has been done before :-)<br>
<br>Box 'n Borders css options :<br/>   
<?php
echo "<img src='" .plugins_url( 'screenshot-3.jpg' , __FILE__ ). "' > ";
?>  

<br/><br/>
<a href="http://digidownload.nl/author/admin/" title="see it in action">Other versions</a> (takes you to digidownload.nl)<br>
<a href="mailto:info@portaljumper.com">Share your image !</a> (want to showcase your own rating image ? Create a .PNG with a transparant layer and mail it to us. We may include it in our next version !)
<br>-<br>This plugin also has a PREMIUM version with even more options, settings and hardened code ! visit digidownload.nl for more info.
<br/> <br />Coding & style by: Pete Scheepens
 <br />
<h2>our other free plugins on wordpress.org</h2> 
Author love - an author rating system<br>
<?php
echo "<img src='" .plugins_url( 'screenshot-10.jpg' , __FILE__ ). "' > ";
?>    
</div>
<div style="clear:both"></div>";
<?PHP
}

///////////////////////////////////////////////////////////// FUNCTIONS //////////////////////////////////////////////////////////////////

// response to ajax call
function vr_vote_func() {
$nonce=$_REQUEST['nonce'];
   if (! wp_verify_nonce($nonce, 'vr_vote') ) {
   $aResponse['server'] = 'Security violation';
   echo json_encode($aResponse);
   die();
   };
$id = intval($_POST['idBox']);
$rate = floatval($_POST['rate']);
$ip = $_POST['IP'];
global $wpdb;
if (!$wpdb->query($wpdb->prepare( "INSERT INTO " .$wpdb->prefix . "vertical_author_ratings (author_id,ip_address,rating) VALUES ('$id','$ip','$rate') " )) )
$W = "YOU<br>ALREADY<br>VOTED !"; else $W = "thanks<br>for<br>voting.";
$aResponse['type'] = 'success';
$aResponse['server'] = $W;
echo json_encode($aResponse);
die();
}   

// create dynamic JS in footer
function vr_jq($content) {
 $width = 23;
 $height = 20;
$aloptions = get_option('vertical_author_ratings_opts');
if ($aloptions['image'] == 'hearts') $images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/heart.png' , __FILE__ ) . "'";
  if ($aloptions['image'] == "stars") $images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/star.png' , __FILE__ ) . "'";
   if ($aloptions['image'] == "arrows") $images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/arrow.png' , __FILE__ ) . "'";
   if ($aloptions['image'] == "balls") $images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/ball.png' , __FILE__ ) . "'";
   if ($aloptions['image'] == "thermometer2") {$images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/thermometer2.png' , __FILE__ ) . "'";$width=72;$height=160;$aloptions['amount']=1;}
   if ($aloptions['image'] == "lamppostd") {$images = ",bigStarsPath:'" . plugins_url( 'jquery/icons/lamppostd.png' , __FILE__ ) . "'";$width=89;$height=250;$aloptions['amount']=1;}
   $link = admin_url('admin-ajax.php?action=vr_vote');
   $nonce= wp_create_nonce  ('vr_vote');
  
  echo '
<script type="text/javascript" src="' .plugins_url( 'jquery/vrrating.jquery.js' , __FILE__ ). '">
</script>
<script type="text/javascript">
jQuery(document).ready(function(){jQuery(".vertical_author_ratings").vrrating({length:' . $aloptions['amount'] . ',url:\''. $link . '\',width:'. $width . ',heigth:'. $height . ',decimalLength:1' . $images . ',nonce:\''. $nonce . '\',phpPath:\''. $link . '\'}); });
</script>';
}


// render rating block
function render_vertical_author_ratings( $author_id="FALSE" )
{
global $wpdb;
if (empty($author_id) || $author_id == "FALSE") $author_id = get_the_author_meta('ID'); 
$average_rating = $wpdb->get_var( $wpdb->prepare(" SELECT AVG(rating) FROM " .$wpdb->prefix . "vertical_author_ratings WHERE author_id='$author_id' ") );
$average_rating = number_format($average_rating);
$count_rating = $wpdb->get_var( $wpdb->prepare(" SELECT COUNT(rating) FROM " .$wpdb->prefix . "vertical_author_ratings WHERE author_id='$author_id' ") );
$visitor_ip = str_replace(".","",vr_getIp() );
$path = plugins_url();
$randid = microtime(TRUE);
$id = $average_rating . "_" . $author_id . "|" . $visitor_ip . "|" . $path . "|" . $randid ;
  echo "<div style='margin:3px;background-color:white'>";
   echo "<div style='height:24px;font-size:12px;overflow:hidden;background-color:white'>Author:<br>$author_info<</div>";
echo "<div class='vertical_author_ratings' id='$id' style='text-align:center;margin:1px auto'></div>";
echo "<div class='vr_serverResponse' style='font-size:12px;text-align:center'><p>rating: $average_rating% - $count_rating votes</p></div>";
  echo "</div>";
}

function return_vr( $author_id="FALSE", $pos="left" ,$author_info="",$single="no", $class="none" )
{
$data = "";
global $wpdb;
if (empty($author_id) || $author_id == "FALSE") $author_id = get_the_author_meta('ID');
$author_info = get_the_author_meta('user_nicename'); 
$average_rating = $wpdb->get_var( $wpdb->prepare(" SELECT AVG(rating) FROM " .$wpdb->prefix . "vertical_author_ratings WHERE author_id='$author_id' ") );
$average_rating = number_format($average_rating);
$count_rating = $wpdb->get_var( $wpdb->prepare(" SELECT COUNT(rating) FROM " .$wpdb->prefix . "vertical_author_ratings WHERE author_id='$author_id' ") );
$visitor_ip = str_replace(".","",vr_getIp() );
$path = plugins_url();
if ($class == "none") $class = '';
if ($class == "both") $class = "box blur";
$randid = microtime(TRUE);
$id = $average_rating . "_" . $author_id . "|" . $visitor_ip . "|" . $path . "|" . $randid ;
  
if ($single == "no" || empty($single))
{
 if (is_single() )  {
   $data .= "<div class='$class'>";
      if (!empty($author_info)) $data .= "<div style='font-size:12px;text-align:center'>Author:<br>$author_info</div>";
    $data .= "<div class='vertical_author_ratings' id='$id' style='text-align:center;margin:1px auto'></div>";
   $data .= "<div class='vr_serverResponse' style='font-size:12px;text-align:center'><p>rating: $average_rating%<br/>$count_rating votes</p></div>";
   $data .= "</div>";
      }
 else $data = '';
}
else
{
   $data .= "<div class='$class'>";
      if (!empty($author_info)) $data .= "<div style='font-size:12px;text-align:center'>Author:<br>$author_info</div>";
   $data .= "<div class='vertical_author_ratings' id='$id' style='text-align:center;margin:1px auto'></div>";
  $data .= "<div class='vr_serverResponse' style='font-size:12px;text-align:center'><p>rating: $average_rating%<br/>$count_rating votes</p></div>";
   $data .= "</div>";
}
return $data;
}


function vr_getIp() {
    $ip = $_SERVER['REMOTE_ADDR'];
 
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
 
    return $ip;
}
?>
