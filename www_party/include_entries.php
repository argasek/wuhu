<div class="form-container">
<?
if (!defined("ADMIN_DIR")) exit();

global $settings;
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
  $data["id"] = $_POST["entryid"];
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
if ($_POST["entryid"]) {
  $msg = "";
  $id = perform($msg);
  if ($id) {
    echo "<div class='alert alert-dismissible alert-success'>Update successful!</div>";
  } else {
    echo "<div class='alert alert-dismissible alert-danger'>Error: ".$msg."</div>";
  }
}
global $page;
if ($_GET["id"]) {
  $entry = SQLLib::selectRow(sprintf_esc("select * from compoentries where id=%d",$_GET["id"]));
  if ($entry->userid != $_SESSION["logindata"]->id)
    die("nice try.");
    
  $compo = get_compo($entry->compoid);

  $filedir = get_compoentry_dir_path( $entry );
  if (!$filedir)
    die("Unable to find compo entry dir!");    

  if ($_GET["select"]) {
    $lock = new OpLock();
    $fn = basename($_GET["select"]);
    if (file_exists($filedir . $fn)) {
      $upload = array(
        "filename" => $fn,
      );
      SQLLib::UpdateRow("compoentries",$upload,"id=".(int)$_GET["id"]);
      header( "Location: ".build_url($page,array("id"=>(int)$_GET["id"])) );
      exit();
    }
  }

  if ($_GET["delete"]) {
    $lock = new OpLock();
    $fn = basename($_GET["delete"]);
    if (file_exists($filedir . $fn)) {
      unlink($filedir . $fn);
      header( "Location: ".build_url($page,array("id"=>(int)$_GET["id"])) );
      exit();
    }
  }

?>
<form action="<?=build_url($page,array("id"=>(int)$_GET["id"])) ?>" method="post" enctype="multipart/form-data">
<div class='form-group'>
  <label for="title">Entry title:</label>
  <input class="form-control" id="title" name="title" type="text" value="<?=_html($entry->title)?>" required='yes'/>
</div>
<div class='form-group'>
  <label for="author">Author:</label>
  <input class="form-control" id="author" name="author" type="text" value="<?=_html($entry->author)?>"/>
</div>
<div class='form-group'>
  <label for="comment">Comment: <small>(this will be shown on the compo slide)</small></label>
  <textarea class="form-control" id="comment" name="comment" rows="4"><?=_html($entry->comment)?></textarea>
</div>
<div class='form-group'>
  <label for="orgacomment">Comment for the organizers: <small>(this will NOT be shown anywhere)</small></label>
  <textarea id="orgacomment" class="form-control" name="orgacomment" rows="4"><?=_html($entry->orgacomment)?></textarea>
</div>

<div class='form-group'>
  <label>Screenshot: (JPG, GIF or PNG!)</label>
  <div class="panel">
    <div class="panel-body">
      <img id='screenshot' src='screenshot.php?id=<?=(int)$_GET["id"]?>&amp;show=thumb' alt='thumb' class="d-block max-w-100"/>
    </div>
  </div>

</div>

<?php
  $a = glob($filedir . "*");
?>

<div class='form-group'>
  <label>Uploaded files</label>
<?
  foreach ($a as $v)
  {
    $v = basename($v);
?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><?=$v?></h4>
      </div>
      <div class="panel-body">
        <?
        if ($v == $entry->filename) {
          echo "<i>Currently selected file</i>";
        } else {
          printf("<a class='btn btn-primary' href='%s&amp;select=%s'>Select this file</a>\n",$_SERVER["REQUEST_URI"],rawurlencode($v));
          printf("<a class='btn btn-danger' href='%s&amp;delete=%s' class='deletefile'>Delete this file</a>\n",$_SERVER["REQUEST_URI"],rawurlencode($v));
        }
        ?>
      </div>
    </div>
<?
  }
?>
</div>

  <?if (count($a)>1) {?>
    <div class='alert alert-dismissible alert-warning'>
      <strong>Warning:</strong> having only a <u>SINGLE</u> version of uploaded production file decreases the chances of having the wrong version played!
    </div>
  <?}?>


  <div class="form-group">
  <label for='entryfile'>Upload a new file:</label>
  <span class="control-fileupload">
    <label for='entryfile'>File:</label>
    <input class="form-control" id='entryfile' name="entryfile" type="file" />
  </span>
  <p class="help-block">
    (max. <?=ini_get("upload_max_filesize")?> - if you want to upload
    a bigger file, just upload a dummy text file here and ask the organizers!)
  </p>
</div>
<div class="form-group">
  <label for='entryfile'>Update screenshot:</label>
  <span class="control-fileupload">
    <label for='screenshot'>Image: <small>(optional - JPG, GIF or PNG!)</small></label>
    <input class="form-control" id='screenshot' name="screenshot" type="file" accept="image/*" />
  </span>
</div>

<div class="form-group">
  <input name="entryid" type='hidden' value="<?=(int)$_GET["id"]?>" />
  <button class="btn btn-primary btn-default" type="submit" value="Go!">Update your entry</button>
</div>

</form>
</div>
<?
} else {
  $entries = SQLLib::selectRows(sprintf_esc("select * from compoentries where userid=%d",get_user_id()));
  echo "<table class='entrylist'>";
  echo "<tr>";
  echo "  <th>#</th>";
  echo "  <th>Screenshot</th>";
  echo "  <th>Compo</th>";
  echo "  <th>Title</th>";
  echo "  <th>Author</th>";
  echo "  <th>Options</th>";
  run_hook("editentries_endheader");
  echo "</tr>";
  global $entry;
  foreach ($entries as $entry) 
  {
    $compo = get_compo( $entry->compoid );
    echo "<tr>";
    printf("<td>#%d</td>",$entry->id);
    printf("<td><a href='screenshot.php?id=%d' target='_blank'><img src='screenshot.php?id=%d&amp;show=thumb'/></a></td>",$entry->id,$entry->id );
    printf("<td>%s</td>",_html($compo->name) );
    printf("<td>%s</td>",_html($entry->title) );
    printf("<td>%s</td>",_html($entry->author) );
    $compo = get_compo( $entry->compoid );
    if ($compo->uploadopen || $compo->updateopen)
      printf("<td><a href='%s&amp;id=%d'>Edit entry</a></td>",$_SERVER["REQUEST_URI"],$entry->id );
    else
      printf("<td>&nbsp;</td>" );
    run_hook("editentries_endrow",array("entry"=>$entry));
    echo "</tr>";
  }
  echo "</table>";
}
?>