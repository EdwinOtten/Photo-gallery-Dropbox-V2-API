<?php
require 'dataprovider.php';
/* @var $gallery Album */
// var_dump($gallery);die;
?>



<html>
<head>
    <?php echo file_get_contents('templates/scripts.html'); ?>
    <?php echo file_get_contents('templates/photoswipe-scripts.html'); ?>
    <?php echo file_get_contents('templates/stylesheets.html'); ?>
</head>

<body>

<h1><?php echo $gallery->name; ?></h1>

<!-- Root element of thumbnail grid -->
<div class="col-sm-12 panel" id="thumbnails">

<?php
foreach ($gallery->items as $item)
{
    if ($item->type == 'album') 
    {
        /* @var $item Album */
        echo '
    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
        <a href="#" class="thumbnail">
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
