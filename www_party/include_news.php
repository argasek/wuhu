<?
if (!defined("ADMIN_DIR")) exit();
?>
<dl class="text-container">
<?
$news = SQLLib::selectRows("select * from intranet_news order by `date` desc");
foreach($news as $n) {
?>
<dt><h4><?=date("Y-m-d",strtotime($n->date))?> - <?=_html($n->eng_title)?></h4></dt>
<dd><?=$n->eng_body?></dd>
<?
}
?>
</dl>