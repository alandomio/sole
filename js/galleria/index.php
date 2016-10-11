<!doctype html>
<html>
    <head>
        <script src="jquery-1.7.min.js"></script>
        <script src="galleria-1.2.6.min.js"></script>
    </head>
    <body>
        <div id="gallery">
            <img src="photo1.jpg">
            <img src="photo2.jpg">
            <img src="photo3.jpg">
        </div>
        <script>
            Galleria.loadTheme('themes/classic/galleria.classic.min.js');
            $("#gallery").galleria({
                width: 500,
                height: 500
            });
        </script>
    </body>
</html>