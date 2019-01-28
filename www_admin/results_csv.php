<?
error_reporting(E_ALL ^ E_NOTICE);
include_once("bootstrap.inc.php");

$encoding = "utf-8";


header("Content-Type: text/plain; charset=" . $encoding);
loadPlugins();

$voter = SpawnVotingSystem();

$places = isset($_GET['places']) ? (int) $_GET['places'] : 5;

if (!$voter)
  die("VOTING SYSTEM ERROR");

$out = fopen('php://output', 'w');
$c = SQLLib::selectRows("select * from compos order by start,id");
foreach ($c as $compo) {

  $query = new SQLSelect();
  $query->AddTable("compoentries");
  $query->AddWhere(sprintf_esc("compoid=%d", $compo->id));
  $query->AddOrder("playingorder");
  run_hook("admin_results_dbquery", array("query" => &$query));
  $entries = SQLLib::selectRows($query->GetQuery());

  global $results;
  $results = array();
  $results = $voter->CreateResultsFromVotes($compo, $entries);
  run_hook("voting_resultscreated_presort", array("results" => &$results));
  arsort($results);
  $n = 1;

  foreach ($results as $k => $v) {
    $e = SQLLib::selectRow(sprintf_esc("select * from compoentries where id = %d", $k));
    // Compo, place, prod, group
    //$line = sprintf("%s,%s,%s\n", $compo->name, $n, trim($e->title), trim($e->author));
    $line = array($compo->name, $n, trim($e->title), trim($e->author));
    fputcsv($out, $line);

    $n++;
    if ($n > $places) break;
  }
}
fclose($out);
