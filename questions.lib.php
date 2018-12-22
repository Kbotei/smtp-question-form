<?php
// Dont edit from here....
$host = str_replace('www.', '', $_SERVER["SERVER_NAME"]);
$tmp = explode('.', $_SERVER['SERVER_NAME']);
$css = $tmp[1];

$r = (isset($_POST['r'])) ? $_POST['r'] : $_SERVER['HTTP_REFERER'];
$r = (!strstr($r, 'questions.php')) ? $r : 'http://'.$_SERVER["HTTP_HOST"];
$r = (empty($r)) ? 'http://'.$_SERVER["HTTP_HOST"] : $r;


function display_form($error = '')
{
	global $r, $css;
	$data = '<form action="questions.php" method="post">
  	<div class="row" style="text-align:center;padding-bottom:10px;">
      <span class="header">Send your Question or Comment.</span><br />
	  '.$error.'<br />
    </div>
    <div class="row">
      <span class="label">Email:</span><span class="formw"><input type="text" name="email2" size="30" tabindex="1" value="'.stripslashes($_POST['email2']).'" /></span>
    </div>
    <div class="row">
      <div class="label">Question?:</div>
	  <div class="formw">
        <textarea cols="48" rows="10" name="question" tabindex="2">'.stripslashes($_POST['question']).'</textarea>
      </div>
    </div>
	<div class="spacer">
 	&nbsp;
	</div>
	<div class="row" style="text-align:center;">
      <div class="footer">
        <input type="submit" name="submit" value="Submit"> &nbsp;&nbsp; <input type="Reset">
      </div>
    </div>
  <div class="spacer">&nbsp;</div>
  <input type="hidden" name="r" value="'.$r.'" />
  <input type="hidden" name="email" value="" />
 </form>';
	return $data;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" href="questions_<?php echo $css; ?>.css" type="text/css" media="screen" />
	<title><?php echo ucfirst($host); ?> - Question Form</title>
</head>
<body>
<br />
<div class="table" style="text-align:center;">
<?php

if (!isset($_POST['submit']))
{ 
	echo display_form();
}
elseif (isset($_POST['submit']) && empty($_POST['email']))
{
	if(empty($_POST['email2']) or empty($_POST['question']))
	{
		echo display_form('You must fill in both the Email and Question fields.');
		exit;
	}
	
	$email = trim($_POST['email2']);
	$question = (!get_magic_quotes_gpc()) ? addslashes($_POST['question']) : $_POST['question'];
	
	$match = 0;
	$match = preg_match('/\[url(.*?)\](.*?)\[\/url\]/i', $question);
	$match = ($match != 1) ? preg_match('/<a(.*?)>(.*?)<\/a>/i', $question) : $match;
	
	if ($match > 0)
	{
		echo display_form('HTML/BBCODE is not allowed. Please try again.');
		exit;
	}
	
	include('Mail.php');

	$msg = 'This message was sent from the '.ucfirst($host).' question form.'."\r\n\r\n";
	$msg .= 'Email Address: '.$email."\r\n\r\n";
	$msg .= 'Question/Comment: '."\r\n";
	$msg .= "----------------------------------------\r\n";
	$msg .= $question."\r\n----------------------------------------\r\n\r\n";
	$msg .= 'http://'.$_SERVER["HTTP_HOST"];

	// To here...
	// You can edit these variables.

	$recipients = 'webmaster@'.$host;

	$headers['From']    = 'webmaster@'.$host;
	$headers['To']      = 'webmaster@'.$host;
	$headers['Subject'] = ucfirst($host).' Question Submition Form';

	// Dont edit after this point.
	$body = $msg;

	$params['host'] = 'mail.'.$host;
	$params['auth'] = true;
	$params['username'] = 'webmaster@'.$host;
	//TODO: Secure the password better
	$params['password'] = 'PASSWORD';

	// Create the mail object using the Mail::factory method
	$mail_object =& Mail::factory('smtp', $params);

	$status = $mail_object->send($recipients, $headers, $body);

	if (!PEAR::isError($status))
	{
	?>
		<meta http-equiv="Refresh" content="5;url=<?php echo $r; ?>">
		Thank you. Your submission has been processed.<br />
	    <a href="<?php echo $r; ?>">You are now being returned to <?php echo $r; ?><br />
		If your browser does not redirect you click here.</a>
	<?php
	}
	else
	{
	?>
	    There was an error when trying to prossess your submission.<br />
		Press the back button or <a href="javascript:history.back();">click here</a> to try again.<br />
	<?php
	}
}
else
{
	header("Location: http://".$_SERVER["HTTP_HOST"]);
}
?>
</div>
</body>
</html>