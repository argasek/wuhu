<?php
include_once("bootstrap.inc.php");

$data = @file_get_contents(PLUGINREGISTRY);
$oldActivePlugins = unserialize($data);
if (!$oldActivePlugins) $oldActivePlugins = array();

$activePlugins = $oldActivePlugins;

$success = false;
if (isset($_POST["submit"])) {
  $activePlugins = array();
  if (isset($_POST["plugin"])) foreach ($_POST["plugin"] as $dirname => $on) {
    // this leaves room for future activity
    $activePlugins[$dirname] = array();
    $activePlugins[$dirname]["active"] = true;
    $activePlugins[$dirname]["directory"] = $dirname;

    //$entryfile = ADMIN_DIR . "/plugins/" . $dirname . "/plugin.php";
    $path = get_plugin_entry_path($dirname);
    if ($path && file_exists($path)) {
      include_once($path);
      run_hook($dirname . "_activation");
    }
  }
  file_put_contents(PLUGINREGISTRY, serialize($activePlugins));
  $success = true;
}

include_once("header.inc.php");

if (!is_writable(PLUGINREGISTRY)) {
  printf("<div class='error'>Please make sure %s is writable!</div>\n", htmlspecialchars(PLUGINREGISTRY));
}
if ($success)
  printf("<div class='success'>Plugins activated/deactivated</div>\n");


printf("<form action='%s' method='post'>\n", $_SERVER["REQUEST_URI"]);
printf("<ul id='pluginlist'>\n");

$files = array();
$files = array_merge($files, glob(ADMIN_DIR . "/plugins/*.php"));
$files = array_merge($files, glob(ADMIN_DIR . "/plugins/*/", GLOB_ONLYDIR));
usort($files, "strcasecmp");
foreach ($files as $v) {
  if (!preg_match("/\/plugins\/(.*)[\/\.]/", $v, $m))
    continue;

  $name = $m[1];
  $path = get_plugin_entry_path($name);
  if ($path) {
    $pluginDirName = $name;
    $pluginName = $name;
    $pluginDescription = "";

    $f = fopen($path, "rt");
    $data = fread($f, 1024);
    fclose($f);

    if (preg_match("/^Plugin name: (.*)$/im", $data, $m))
      $pluginName = $m[1];
    if (preg_match("/^Description: (.*)$/im", $data, $m))
      $pluginDescription = $m[1];

    $checked = isset($activePlugins[$pluginDirName]) ? " checked='checked'" : "";
    $name = htmlspecialchars($pluginDirName);

    printf("<li>\n");
    printf("  <h3>%s</h3>\n", htmlspecialchars($pluginName));
    printf("  <input type='checkbox' id='plugin-%s' name='plugin[%s]'%s>\n", $name, $name, $checked);
    printf("  <label for='plugin-%s'>%s</label>\n", $name, htmlspecialchars($pluginDescription));
    printf("</li>\n");
  }
}

printf("</ul>\n");
printf("  <input type='submit' name='submit' value='Activate/Deactivate'>\n");
printf("</form>\n");

include_once("footer.inc.php");
