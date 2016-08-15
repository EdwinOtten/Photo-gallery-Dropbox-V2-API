<html>
<head>
    <?php echo file_get_contents('templates/scripts.html'); ?>
    <?php echo file_get_contents('templates/photoswipe-scripts.html'); ?>
    <?php echo file_get_contents('templates/stylesheets.html'); ?>
</head>

<body>

<h1>My example album</h1>

<!-- Root element of thumbnail grid -->
<div class="col-sm-12 panel" id="thumbnails">

    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
        <a href="#" class="thumbnail" data-size="883x589" data-fullres-uri="images/full1.jpg">
            <div class="frontpage_square">
                <img src="images/thumb1.jpg" class="img img-responsive full-width" />
            </div>
            <div class="caption">
                <p>Some kind of castle</p>
            </div>
        </a>
    </div>

    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
        <a href="#" class="thumbnail" data-size="928x580" data-fullres-uri="images/full2.jpg">
            <div class="frontpage_square">
                <img src="images/thumb2.jpg" class="img img-responsive full-width" />
            </div>
            <div class="caption">
                <p>Snowy mountain top</p>
            </div>
        </a>
    </div>

</div>


<?php echo file_get_contents('templates/photoswipe-body.html'); ?>

</body>
</html>