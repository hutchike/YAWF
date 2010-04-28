<?= $parent ? HTML::link("$path?folder=$parent", "&lt;&lt; Back to <b>$parent</b> folder") : '' ?>

<p>YAWF framework code in <b><?= $folder ?></b> folder:</p>

<?
$items = '';
foreach ($files as $file)
{
    if (preg_match('/\.\w+$/', $file))
    {
        $items .= HTML::list_item(HTML::link("download/code.php?$file", $file, array('target' => 'code_frame')));
    }
    elseif (preg_match('/^\w/', $file))
    {
        $items .= HTML::list_item(HTML::link("$path?folder=$file", $file));
    }
}
print HTML::bullet_list($items);
?>

<iframe id="code_frame" name="code_frame"></iframe>
