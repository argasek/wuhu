<div class="form-container">
<?php
if (!defined("ADMIN_DIR")) exit();

$wasError = false;

if ($_POST["nickname"]) {
  global $userdata;
  $userdata = array(
    "group"=> ($_POST["group"]),
  );
  if ($_POST["nickname"])
    $userdata["nickname"] = $_POST["nickname"];
  run_hook("profile_processdata",array("data"=>&$userdata));
  if ($_POST["password"]) {
    if ($_POST["password"]!=$_POST["password2"]) {
      echo "<div class='alert alert-dismissible alert-danger'>Passwords don't match!</div>";
      $wasError = true;
    } else {
      $userdata["password"] = hashPassword($_POST["password"]);
    }
  }
  if ($_POST["email"] && strlen(trim($_POST["email"])) > 0) {
    if (!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)) {
      echo "<div class='alert alert-dismissible alert-danger'>This email address is invalid!</div>";
      $wasError = true;
    } else {
      $userdata["email"] = trim($_POST["email"]);
    }
  } else {
    $userdata["email"] = null;
  }

  SQLLib::UpdateRow("users",$userdata,sprintf_esc("id='%d'",get_user_id()));
  if ($wasError === false) {
    echo "<div class='alert alert-dismissible alert-success'>Profile editing successful!</div>";
  } else {
    echo "<div class='alert alert-dismissible alert-warning'>Profile editing partially successful, check for errors!</div>";
  }
}
global $user;
$user = SQLLib::selectRow(sprintf_esc("select * from users where id='%d'",get_user_id()));
global $page;
?>
<form action="<?=build_url("ProfileEdit")?>" method="post" id='profileForm' class="text-container">
<div id="profile">
<div class="form-group">
  <label>Username:</label>
  <p class="form-control" >
  <b><?=_html($user->username)?></b>
  </p>
</div>
<div class="form-group">
  <label for="email">Email: <small>(required only if you want to collect a prize!)</small></label>
  <input id="email" name="email" type="email" value="<?=_html($user->email)?>" class="form-control" />
</div>
<div class="form-group">
  <label for="password">New password: (only if you want to change it)</label>
  <input name="password" type="password" id="password" class="form-control" />
</div>
<div class="form-group">
  <label for="password2">New password again:</label>
  <input name="password2" type="password" id="password2" class="form-control" />
</div>
<div class="form-group">
  <label for="nickname">Nick/Handle:</label>
  <input name="nickname" type="text" id="nickname" value="<?=_html($user->nickname)?>" class="form-control" required='yes' />
</div>
<div class="form-group">
  <label for="group">Group: (if any)</label>
  <input name="group" type="text" id="group" value="<?=_html($user->group)?>" class="form-control" />
</div>
<?
run_hook("profile_endform");
?>
<div id='regsubmit' class="form-group">
  <button class="btn btn-primary btn-default" type="submit" value="Go!">Update profile</button>
</div>
</div>
</form>
</div>
