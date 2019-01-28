<?
if (!defined("ADMIN_DIR")) exit();
?>

<?
$news = SQLLib::selectRows("select * from intranet_news order by `date` desc");
if (count($news) > 0) {
?><dl class="text-container"><?
}
foreach($news as $n) {
?>
<dt><h4><?=date("Y-m-d",strtotime($n->date))?> - <?=_html($n->eng_title)?></h4></dt>
<dd><?=$n->eng_body?></dd>
<?
}

if (count($news) > 0) {
  ?></dl><?
}
?>
