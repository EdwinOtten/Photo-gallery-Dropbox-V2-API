<html>
<head>
    <?php echo file_get_contents('templates/scripts.html'); ?>
    <?php echo file_get_contents('templates/stylesheets.html'); ?>
</head>

<body>

<h1>My photo gallery</h1>

<!-- Root element of thumbnail grid -->
<div class="col-sm-12 panel" id="thumbnails">

    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
        <a href="album.php" class="thumbnail">
            <div class="frontpage_square">
                <img src="images/thumb1.jpg" class="img img-responsive full-width" />
            </div>
            <div class="caption">
                <p>My example album</p>
            </div>
        </a>
    </div>

</div>

</body>
</html>