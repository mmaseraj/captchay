<?php
header ( 'content-type: text/html; charset=utf-8' );

session_start ();
require 'class.captchay.php';

$captcha = new Captchay ( 305, 60 );
$captcha->setConfig ( 'canvas', array (
    'border-style' => 'solid',
    'border-color' => '#e1e1e1'
) );



if (isset ( $_POST[ 'submit_btn' ] )) {
  if ($captcha->isValid ()) {
    echo 'valid captchay string';
  } else {
    echo '<div style="color: red">not valid captchay string</div>';
  }
}
$captcha->create ();
$captchay_html = $captcha->output ();
?>

<form method="post">
  <?php echo $captchay_html ?>
  <br />
  <input type="submit" name="submit_btn" value="Submit" />
</form>

<?php
/*
* End of file index.php
*/