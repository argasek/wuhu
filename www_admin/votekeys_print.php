<?php
include_once("bootstrap.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title></title>
  <meta charset="utf-8">
  <style type="text/css">
    body {
      font-family: arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    ul {
      margin: 0;
      padding: 0;
    }

    li {
      list-style: none;
      padding: 25px 15px;
      border: 1px dotted #ccc;
      text-align: center;
      font-size: 130%;
      letter-spacing: 2px;
    }

    .votekeys li {
      float: left;
      width: 25%;
    }

    .votekey-reserved {
      font-family: "Courier New", sans-serif;
    }

    .votekey-reserved:before, .votekey-reserved:after {
      content: '*';
    }

    <?=($settings["votekeys_css"] ?: "")?>
  </style>
</head>
<body>
<?php
$s = SQLLib::selectRows("select * from votekeys");
if (count($s) > 0) {
  printf("<ul class='votekeys'>");
  $n = 1;
  $format = $settings["votekeys_format"] ?: "{%VOTEKEY%}";
  foreach ($s as $t) {
    $reserved = '';
    if (isset($settings["reserved_votekeys"])) {
      $reserved = ($n <= $settings["reserved_votekeys"]) ? "class='votekey-reserved'" : '';
    }
    printf("  <li %s>%s</li>", $reserved, str_replace("{%VOTEKEY%}", $t->votekey, $format));
    $n++;
  }
  printf("</ul>");
} else {
  printf("<p>No votekeys generated yet!</p>");
}

?>
</body>
</html>
