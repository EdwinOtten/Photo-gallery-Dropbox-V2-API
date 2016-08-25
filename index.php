<?php
require 'dataprovider.php';
/* @var $album Album */
$album = getAlbumTree();

if (isset($_GET['path']))
{
    $subAlbum = $album->getAlbumForPath(urldecode($_GET['path']));
    if ($subAlbum instanceof Album)
        $album = $subAlbum;
}


?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <?php echo file_get_contents('templates/scripts.html'); ?>
    <?php echo file_get_contents('templates/photoswipe-scripts.html'); ?>
    <?php echo file_get_contents('templates/stylesheets.html'); ?>
</head>

<body>

<h1><a href="?path=<?php echo urlencode(getParentPath($album->path)); ?>">&#8679;</a> <?php echo $album->name; ?></h1>

<!-- Root element of thumbnail grid -->
<div class="col-sm-12 panel" id="thumbnails">

<?php
foreach ($album->items as $item)
{
    if ($item->type == 'album') 
    {
        /* @var $item Album */
        echo '
    <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 col-xl-1">
        <a href="?path='.urlencode($item->path).'" class="thumbnail">
            <div class="frontpage_square">
                <img src="images.php?path='.urlencode($item->getImageForThumbnail()->path).'&thumbnail=1" class="img img-responsive full-width" />
            </div>
            <div class="caption">
                <p>'.$item->name.'</p>
            </div>
        </a>
    </div>';
    }
    else if ($item->type == 'image')
    {
        /* @var $item AlbumImage */
        echo '
    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
        <a href="#1" class="thumbnail clickable" data-size="'.$item->width.'x'.$item->height.'" data-fullres-uri="images.php?path='.urlencode($item->path).'">
            <div class="frontpage_square">
                <img src="images.php?path='.urlencode($item->path).'&thumbnail=1" class="img img-responsive full-width" />
            </div>
            <div class="caption">
                <p>'.$item->name.'</p>
            </div>
        </a>
    </div>';
    }
}
?>

</div>


<?php echo file_get_contents('templates/photoswipe-body.html'); ?>

</body>
</html>
