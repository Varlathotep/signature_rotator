<?php
try {
    //Get the config. This is pretty important.
    require_once('config.php');
    if (!extension_loaded('gd')) {
        exit ('The required gd extension has not been loaded. Unable to process your request.');
    }
    //Supress errors. This prevents errors from causing problems with renders. This doesn't effect fatal errors.
    ini_set('display_errors', $err);
    //Includes the most crucial part of this entire system: The compositor. While the compositor is always loaded, it isn't always used.
    require_once($comp);
    //Why the hell did I write an iterative thing as recursive? This is really inefficient. Replace it!
    function /*void*/ recursivelyGetRand(/*string[int]*/ &$files, /*string*/ $randStorage)
    {

        /*int*/ $random = array_rand($files);

        if (count($files) < 2) {
            return 0;
        }
        if (is_file($randStorage))
        {

            if ($random == file_get_contents($randStorage))
            {
                return recursivelyGetRand($files, $randStorage);
            }
            else
            {
                file_put_contents($randStorage, $random);
                return $random;
            }
        }
        else
        {
            file_put_contents($randStorage, $random);
            return $random;
        }

    }
    //
    function get_type($val) {
        $type;
        if (is_resource($val)) {
            $type = get_resource_type($val);
        }
        else if (is_object($val)) {
            $type = get_class($val);
        }
        else {
            $type = gettype($val);
        }
        return $type;
    }
    //
    function /*bool*/ getIsAllowedRefer(/*bool*/ $indepth = false, /*bool*/ $referAllowed, /*string[int]*/ $referList) {

        $allowed = false;
        if ($referAllowed) {
            $allowed = true;
        }
        else if($indepth && array_key_exists('HTTP_REFERER', $_SERVER)) {
            foreach ($referList as /*string*/ $referer) {
                if (strpos($referer, parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) !== false) {
                    $allowed = true;
                }
            }
        }
        else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $allowed = ($referAllowed || (isset($_SERVER['HTTP_REFERER']) && in_array(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST), $referList)));
        }
        else {
            $allowed = true;
        }
        return $allowed;
        
    }
    /*string*/ $mode = null;
    if (array_key_exists($modeQ, $_GET)) {
        $mode = $_GET[$modeQ];
    }
    //NOTE: imagick is currently required for signature generation. A separate signature rotator is included that won't composite the image.
    if ($mode == 'signature' && extension_loaded('imagick')) {
        //Determines if the number.txt file, used to maintain the previous index, exists and, if so, loads the number, otherwise, loads 0.
        if (is_file($numStorage)) {
            /*int*/ $generatorNumber = file_get_contents($numStorage);
        }
        else {
            /*int*/ $generatorNumber = 0;
        }
        //Sets the user variable using the query string.
        /*string*/ $user = $_GET[$userQ];
        //Determines if the usr string contains any pathing characters and, if so, exits the code with an error message.
        if (strpos($user, '..') === false && strpos($user, '.') === false && strpos($user, '/') === false) {
            /*string*/ $files = scandir($user);
        }
        else {
            exit ('It\'s rude to try doing code injection :< Don\'t be rude :<');
        }
        //Iterates over the files loaded by the scandir and removes any nodes that are directories or pathing characters.
        foreach ($files as $node => $file) {
            if ($file === '..' || $file === '.' || is_dir($user.'/'.$file)) {
                unset ($files[$node]);
            }
        }
        //Reindexes the files array, gets a random index using the overkill function, and loads the file name.
        $files = array_values($files);
        $filesIndex = recursivelyGetRand($files, $randStorage);
        $file = $files[$filesIndex];
        //Determines if a specific file is being requested and, if so, loads that file.
        if (isset($_GET[$imgQ])) {
            $file = str_replace('/', '', str_replace('..', '', $_GET[$imgQ]));
        }
        //Instantiates the compositor (the magic is about to start.)
        /*compositor*/ $compositor = new compositor();
        //Sets the working output directory, image directory, data directory, and loads the base image.
        $compositor->setOutputDirectory($user.$outDir);
        $compositor->setImageDirectory($user.$imgDir);
        $compositor->setDataDirectory($user.$datDir);
        $compositor->setImageBase($file);
        $compositor->setFilename($file);
        //Creates the output path and loads the image.
        /*string*/ $path = $compositor->getOutputPath();
        /*byte*/ $image = file_get_contents($path);
        //Determines if the user has been referred to the signature.
        if (getIsAllowedRefer($indepthRefererScan, $referAllowed, $referList)) {    
            //Determines if an image exists within the cache time and, if not, begins compiling the image and writes the image to its output folder.    
            if ($image === false || (filemtime($path) + $compositor->cacheTime()) < time())
            {   

                $compositor->generateLayerInformation();
                $image = $compositor->generateImage();
                file_put_contents($path, $image);

            }

        }
        else if (!$referAllowed && isset($_SERVER['HTTP_REFERER']))
        {

            $compositor->generateLayerInformation();
            $image = $compositor->generateImage();
            /*ImagickDraw*/ $drawer = new ImagickDraw();
            $drawer->setFillColor(new ImagickPixel('#00ff00'));
            $height = $image->getImageHeight();
            while($height > 0) {
                $drawer->annotation(0, $height, 'Referer not allowed referer not allowed referer not allowed referer not allowed referer not allowed referer not allowed referer not allowed referer not allowed referer not allowed referer not allow referer not allowed');
                $height -= 10;
            }
            $drawer->setFillColor(new ImagickPixel('#000000'));
            $drawer->setFontSize(20);
            $drawer->annotation(10, $image->getImageHeight() - 60, parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST));
            $drawer->annotation(10, $image->getImageHeight() - 40, 'Using a referrer is bad!');
            $drawer->annotation(10, $image->getImageHeight() - 20, "Please don't refer to this image \nwithout my permission! :<");

            $image->drawImage($drawer);
        }
        else if ($debugger) {
            //This is debug information. When config.php is created, add the ability to remove this.
            $start = microtime(true);
            $compositor->generateLayerInformation();
            $image = $compositor->generateImage();
            $end = microtime(true);
            file_put_contents($path, $image);

            $generationTime = $end - $start;
            /*ImagickDraw*/ $drawer = new ImagickDraw();
            $drawer->annotation(10,$image->getImageHeight() - 10,'Generation Time:' . $generationTime);
            $drawer->annotation(10,$image->getImageHeight() - 20,'Generation Number: ' . $generatorNumber);
            $drawer->annotation(10,$image->getImageHeight() - 30,'Debug Information: ');

            $image->drawImage($drawer);

        }
            //Var_dump the shit out of compositor!
        if ($compositor->debug) {

            $compositor->var_dump();
            exit;

        }
        //Updates the generation count text document.
        file_put_contents($numStorage, ++$generatorNumber);
        //Sets the header, gets the extension and outputs the image to the browser.
        header ('Cache-Control: '.$caching);
        header ('Content-Type: image/'.$compositor->getOutputExtension());
        echo $image;
    }
    else if ($mode == 'signature' && !extension_loaded('imagick') && extension_loaded('gd')) {
            //Refactor the fuck out of this shit, aye? We ain't need no repetitious code in our system!
        if (getIsAllowedRefer($indepthRefererScan, $referAllowed, $referList)) { 
                //Determines if the number.txt file, used to maintain the previous index, exists and, if so, loads the number, otherwise, loads 0.
            if (is_file($numStorage)) {
                /*int*/ $generatorNumber = file_get_contents($numStorage);
            }
            else {
                /*int*/ $generatorNumber = 0;
            }
            //Sets the user variable using the query string.
            /*string*/ $user = $_GET[$userQ];
            //Determines if the usr string contains any pathing characters and, if so, exits the code with an error message.
            if (strpos($user, '..') === false && strpos($user, '.') === false && strpos($user, '/') === false) {
                /*string*/ $files = scandir($user);
            }
            else {
                exit ('It\'s rude to try doing code injection :< Don\'t be rude :<');
            }
            //Iterates over the files loaded by the scandir and removes any nodes that are directories or pathing characters.
            foreach ($files as $node => $file) {
                if ($file === '..' || $file === '.' || is_dir($user.'/'.$file)) {
                    unset ($files[$node]);
                }
            }
            //Reindexes the files array, gets a random index using the overkill function, and loads the file name.
            $files = array_values($files);
            $filesIndex = recursivelyGetRand($files, $randStorage);
            $file = $files[$filesIndex];
            //Determines if a specific file is being requested and, if so, loads that file.
            if (isset($_GET[$imgQ])) {
                $file = str_replace('/', '', str_replace('..', '', $_GET[$imgQ]));   
            }
            /*string*/ $path = $user.$imgDir.$file;
            /*byte[int]*/ $image = file_get_contents($path);
            /*mixed[mixed]*/ $imageDetails = getimagesize($path);
            /*string*/ $imageExtension = null;
                //Determines which extension is needed for the image. This is cruicial I guess?
            if ($imageDetails[2] == IMAGETYPE_GIF) {
                $imageExtension = '.gif';
            }
            else if ($imageDetails[2] == IMAGETYPE_JPEG) {
                $imageExtension = '.jpg';
            }
            else if ($imageDetails[2] == IMAGETYPE_PNG) {
                $imageExtension = '.png';
            }
            else if ($imageDetails[2] == IMAGETYPE_BMP) {
                $imageExtension = '.bmp';
            }
            //Determines if the extension is set or not and, if so, creates the image.
            if ($imageExtension !== null) {
                header ('Cache-Control: '.$caching);
                header ('Content-Type: image/'.str_replace('.', '', $imageExtension));
                echo  $image;
            }

        }
    }
    else if ($mode == 'manager') {
        //This isn't included in the repo currently. One day, maybe. But not today.
           //require_once('manager.php');
    }
    else if ($mode == 'info') {
        phpinfo();
    }
}
catch(exception $e) {
    //Add some actual exception handling.
}