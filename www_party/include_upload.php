<div class="form-container">
<?php
if (!defined("ADMIN_DIR")) exit();

global $settings;
include_once(ADMIN_DIR . "/thumbnail.inc.php");

function perform(&$msg) 
{
  global $settings;
  if (!is_user_logged_in()) {
    $msg = "You got logged out :(";
    return 0;
  }
  $data = array();
  $meta = array("title","author","comment","orgacomment");
  foreach($meta as $m) $data[$m] = $_POST[$m];
  $data["compoID"] = $_POST["compo"];
  $data["userID"] = get_user_id();
  $data["localScreenshotFile"] = $_FILES['screenshot']['tmp_name'];
  $data["localFileName"] = $_FILES['entryfile']['tmp_name'];
  $data["originalFileName"] = $_FILES['entryfile']['name'];
  if (handleUploadedRelease($data,$out))
  {
    return $out["entryID"];
  }

  $msg = $out["error"];
  return 0;
} 
if ($_POST) {
  $msg = "";
  $id = perform($msg);
  if ($id) {
    echo "<div class='alert alert-dismissible alert-success'>Upload successful! Your entry number is <b>".$id."</b>.</div>";
  } else {
    echo "<div class='alert alert-dismissible alert-danger'>".$msg."</div>";
  }
}

$s = SQLLib::selectRows("select * from compos where uploadopen>0 order by start");
if ($s) {
global $page;
?>
<form action="<?=$_SERVER["REQUEST_URI"]?>" method="post" enctype="multipart/form-data" id='uploadEntryForm'>

<div class="form-group">
  <label for="compoSelect">Please select a compo:</label>
  <select id="compoSelect" class="form-control" name="compo">
<?php
foreach($s as $t)
  printf("  <option value='%d'%s>%s</option>\n",$t->id,$t->id==$_POST["compo"] ? ' selected="selected"' : "",$t->name);
?>  
  </select>
</div>
<div class="form-group">
  <label for='title'>Title:</label>
  <input class="form-control" id='title' name="title" type="text" value="<?=_html($_POST["title"])?>" required='required'/>
</div>
<div class="form-group">
  <label for='author'>Author:</label>
  <input class="form-control" id='author' name="author" type="text" value="<?=_html($_POST["author"])?>"/>
</div>
<div class="form-group">
  <label for="comment">Comment: <small>(this will be shown on the compo slide)</small></label>
  <textarea class="form-control" name="comment" rows="4"><?=_html($_POST["comment"])?></textarea>
</div>
<div class="form-group">
  <label for='orgacomment'>Comment for the organizers: <small>(this will NOT be shown anywhere)</small></label>
  <textarea class="form-control" name="orgacomment" id="orgacomment" rows="4"><?=_html($_POST["orgacomment"])?></textarea>
</div>
<div class="form-group">
  <label for='entryfile'>Uploaded file:</label>
  <span class="control-fileupload">
    <label for='entryfile'>File:</label>
    <input class="form-control" id='entryfile' name="entryfile" type="file" required='required' />
  </span>
  <p class="help-block">
    (max. <?=ini_get("upload_max_filesize")?> - if you want to upload
    a bigger file, just upload a dummy text file here and ask the organizers!)
  </p>
</div>
<div class="form-group">
  <label for='entryfile'>Screenshot file:</label>
  <span class="control-fileupload">
    <label for='screenshot'>Image: <small>(optional - JPG, GIF or PNG!)</small></label>
    <input class="form-control" id='screenshot' name="screenshot" type="file" accept="image/*" />
  </span>
</div>
<div class="form-group">
  <button class="btn btn-primary btn-default" type="submit" value="Go!">Upload entry</button>
</div>
</form>
<?php
} else echo "Sorry, all deadlines are closed!";
?>
</div>