<?php
function writeChecksums($dirPath, $checksumsFile) {
    // Correctly get an array of all files in the directory, excluding directories and files beginning with a period
    $files = glob($dirPath . '/*', GLOB_MARK | GLOB_NOSORT);
    $files = array_filter($files, function($file) {
        return !is_dir($file) && $file[0] !== '.';
    });
    
    // Iterate over each file in the array
    foreach ($files as $file) {
        // Calculate the sha3-512 checksum of the file
        $checksum = hash_file('sha3-512', $file);
        
        // Check if fwrite fails
        if (fwrite($checksumsFile, "$checksum *$file\n") === false) {
            echo "Error writing to checksum file.";
            return;
        }
    }
}

$dirPaths = ['dep', 'OpenCamera'];
$checksumsPath = './sn0.txt';

// Check if the file opens successfully
if ($checksumsFile = fopen($checksumsPath, 'w')) {
    foreach ($dirPaths as $dirPath) {
        writeChecksums($dirPath, $checksumsFile);
    }
    fclose($checksumsFile);
} else {
    echo "Error opening checksum file.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <title>Cam Kave</title>

    <link rel="manifest" href="dep/manifest.json">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Kave">
    <meta name="apple-mobile-web-app-title" content="Kave">
    <meta name="theme-color" content="#000">
    <meta name="msapplication-navbutton-color" content="#000">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-starturl" content="/">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" sizes="512x512" href="dep/favicon.png">
    <link rel="apple-touch-icon" type="image/png" sizes="512x512" href="dep/favicon.png">
    <link rel="icon" type="image/webp" sizes="512x512" href="dep/favicon.webp">
    <link rel="apple-touch-icon" type="image/webp" sizes="512x512" href="dep/favicon.webp">
    <link rel="stylesheet" type="text/css" href="dep/Kave.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.plyr.io/3.7.7/plyr.css" />

    <script type="text/javascript" async src="https://cdn.plyr.io/3.7.7/plyr.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/webtorrent@latest/webtorrent.min.js"></script>
    <script type="text/javascript" src="dep/KaveTorrent.js"></script>

    <base href="./" target="_top">

</head>

<body>
    <div id="overlay">
        <div id="list">
            <span id="media">
                <?php
                $searchPath = 'OpenCamera/';
                $files = scandir($searchPath);
                foreach ($files as $file) {
                    if (!is_dir($file) && $file[0] !== '.') {
                        $pathInfo = pathinfo($file);
                        $extension = strtolower($pathInfo['extension']);
                        $filePath = htmlspecialchars($searchPath . $file); // Sanitize file path for HTML output
                        
                        if ($extension === 'webp') {
                            echo "<img data-media='image' type='image/webp' src='$filePath' loading='lazy' alt='Image'>\n";
                        } elseif ($extension === 'mp4') {
                            echo "<video data-media='video' type='video/mp4' src='$filePath'></video>\n";
                        }
                    }
                }
                ?>
            </span>
            <br>
            <span class="text">
            <?php echo "Last synced: " . date ("F d Y H:i:s", getlastmod()); ?>  UTC.
            </span>
            <br>
            <div id="copyright">
            <?php echo "Kave &copy 2018-".date ("Y", getlastmod()); ?> Alex "Styromaniac" Goven
            <br>
            <img src="dep/MIT.svg">
            <br>
<div id="MIT" class="transition hide">
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
<br>
<br>
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
<br>
<br>
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
</div>
                	</div>
                <div id="lightbox">
                    <div class="bar">
                        <a oncontextmenu="toggleFullScreen()" onclick="window.closeLightbox()">
                            <span class="bat"></span>
                        </a>
                    </div>
                    <div id="content">
                    </div>
                </div>
            </span>
        </div>
    </div>
    <div class="bar">
        <a onClick="window.location.href=window.location.href" oncontextmenu="toggleFullScreen()">
            <span class="bat">
        </a>
    </div>

    <script type="text/javascript" src="dep/Viewplayer.js"></script>
    <script type="text/javascript" src="dep/Fullscreen.js"></script>
    <script type="text/javascript" src="dep/Copyright.js"></script>
    <script type="text/javascript" src="dep/Lightbox.js"></script>

</body>
</html>