<?php

/**
 *  Composites an image using the imagick library and a .csv file.
 *
 *  @author B. Wofter
 *  @copyright 2015/02/06 B. Wofter
 */
class compositor
{
    //Base configuration data start:
    /*int*/ private $cacheTimeModifier = 1;
    /*string[string]*/ private $propertyDefinitions = array('font' => '-FONT',
                                                            'fontSize' => '-FONT-SIZE',
                                                            'fontColor' => '-COLOR',
                                                            'strokeColor' => '-STROKE-COLOR',
                                                            'strokeWidth' => '-STROKE-WIDTH',
                                                            'strokeOpacity' => '-STROKE-OPACITY',
                                                            'xCoOrd' => '-X-COORD',
                                                            'yCoOrd' => '-Y-COORD',
                                                            'opacity' => '-OPACITY',
                                                            'blendMode' => '-BLEND-MODE',
                                                            'type' => '-TYPE',
                                                            'fillOverStroke' => '-FILL-OVER-STROKE',
                                                            'strokeBlendMode' => '-STROKE-BLEND-MODE');
    /*string*/ private $outputDirectory = 'output/';
    /*string*/ private $outputExtension = 'png';
    /*string*/ private $outputType = 'png32';
    /*string*/ private $imageDirectory = 'image/';
    /*string*/ private $imageBase = 'base';
    /*string*/ private $imageBaseExtension = 'png';
    /*string*/ private $dataDirectory = 'data/';
    /*string*/ private $dataExtension = 'tsv';
    /*string*/ private $dataSeparator = '~';
    /*string*/ private $fontDirectory = 'font/font/';
    /*string*/ private $filename;
    /*string*/ public $debug = false;
    /*string[int][string]*/ private $data;
    /*string[int][string]*/ private $properties;
    /*string[int]*/ public $exceptions = array();

    //Getters start:
    public function /*void*/ setCacheTimeModifier(/*int*/ $cache)
    {

        $this->cacheTimeModifier = $cache;

    }

    public function /*void*/ setOutputDirectory(/*string*/ $directory)
    {

        $this->outputDirectory = $directory;

    }

    public function /*void*/ setOutputExtension(/*string*/ $extension)
    {

        $this->outputExtension = $extension;

    }

    public function /*void*/ setOutputType(/*string*/ $type)
    {

        $this->outputType = $type;

    }

    public function /*void*/ setImageDirectory(/*string*/ $directory)
    {

        $this->imageDirectory = $directory;

    }

    public function /*void*/ setImageBase(/*string*/ $image)
    {

        $this->imageBase = $image;

    }

    public function /*void*/ setImageBaseExtension(/*string*/ $extension)
    {

        $this->imageBaseExtension = $extension;

    }

    public function /*void*/ setDataDirectory(/*string*/ $directory)
    {

        $this->dataDirectory = $directory;

    }

    public function /*void*/ setDataExtension(/*string*/ $extension)
    {

        $this->dataExtension = $extension;

    }

    public function /*void*/ setDataSeparator(/*string*/ $separator)
    {

        $this->dataSeparator = $separator;

    }

    public function /*void*/ setFontDirectory(/*string*/ $directory)
    {

        $this->fontDirectory = $directory;

    }

    public function /*void*/ setFilename(/*string*/ $filename)
    {

        $this->filename = $filename;

    }

    public function /*string*/ getOutputExtension()
    {

        return $this->outputExtension;

    }

    public function /*string*/ getOutputPath()
    {

        return $this->outputDirectory.$this->filename.'.'.$this->outputExtension;

    }

    public function /*string*/ getDataPath()
    {

        return $this->dataDirectory.$this->filename.'.'.$this->dataExtension;

    }

    public function /*string*/ getBaseImagePath()
    {

        if (strpos($this->imageBase,$this->imageBaseExtension) !== false)
            return $this->imageDirectory.$this->imageBase;
        else
            return $this->imageDirectory.$this->imageBase.'.'.$this->imageBaseExtension;

    }

    //Constructors/Desctructors start:
    /**
     *  @param  string          $filename               false
     *  @param  int             $cacheTimeModifier      false
     *  @param  string          $outputDirectory        false
     *  @param  string          $outputExtension        false
     *  @param  string          $outputType             false
     *  @param  string          $imageDirectory         false
     *  @param  string          $imageBase              false
     *  @param  string          $dataDirectory          false
     *  @param  string          $dataExtension          false
     *  @param  string[string]  $propertyDefinitions    false
     *
     *  @return void
     */
    public function /*void*/ __construct(/*string*/ $filename = false, /*int*/ $cacheTimeModifier = false,
                                /*string*/ $outputDirectory = false, /*string*/ $outputExtension = false,
                                /*string*/ $outputType = false, /*string*/ $imageDirectory = false,
                                /*string*/ $imageBase = false, /*string*/ $imageBaseExtension = false,
                                /*string*/ $dataDirectory = false, /*string*/ $dataExtension = false,
                                /*string[string]*/ $propertyDefinitions = false)
    {

        /*NOTE: This only works in some circumstances with non-default configuration of webservers.
                    Replace with a solution that works best for you (ex.: $_GET or $_POST)
         */
        $file = explode('/', substr($_SERVER['REQUEST_URI'], 1));

        if ($filename)
            $this->filename = $filename;
        else
        {

            $this->filename = $file[0];

        }
        if (isset($file[1]) || isset($_GET['debug']) && $_GET['debug'] === '1')
        {

            $this->debug = true;

        }

        if ($cacheTimeModifier)
            $this->cacheTimeModifier = $cacheTimeModifier;
        if ($propertyDefinitions)
            $this->propertyDefinitions = $propertyDefinitions;
        if ($outputDirectory)
            $this->outputDirectory = $outputDirectory;
        if ($outputExtension)
            $this->outputExtension = $outputExtension;
        if ($outputType)
            $this->outputType = $outputType;
        if ($imageDirectory)
            $this->imageDirectory = $imageDirectory;
        if ($imageBase)
            $this->imageBase = $imageBase;
        if ($imageBaseExtension)
            $this->imageBaseExtension;
        if ($dataDirectory)
            $this->dataDirectory = $dataDirectory;
        if ($dataExtension)
            $this->dataExtension = $dataExtension;

    }

    //Functions start:
    /**
     *  If you can't figure out what this does you probably shouldn't be looking at it.
     *
     *  @param  boolean     $ignore     Determines if to ignore the filesystem cache.
     *
     *  @return int
     */
    public function /*int*/ cacheTime($ignore = false)
    {
    //This should be 3600 seconds, or 1 hour, after the file's last modified time. Modifying cacheTimeModifier should allow sub times of 1 hour.
        if ($ignore)
            return 0;
        else
            return (filemtime($this->getOutputPath()) + ($this->cacheTimeModifier * (60 * 60)));

    }

    /**
     *  Assists in the final generation of the image by accepting the $data and $properties parameters.
     *
     *  @return imagick
     */
    public function /*imagick*/ generateImage()
    {

        try
        {

            /*imagick*/ $image = new Imagick();
            /*imagickdraw*/ $drawer = new ImagickDraw();

            $image->readImage($this->getBaseImagePath());

            $image->setImageFormat($this->outputType);
            $image->setBackgroundColor(new ImagickPixel('transparent'));

        }
        catch (ImagickException $exception)
        {

            $this->exceptions[] = $exception;

        }

        if (count($this->exceptions) == 0)
            foreach ($this->data as $index => $row)
                $this->processLayers($row, $this->properties[$index], $drawer, $image);

        return $image;

    }

    /**
     *  Generates the information needed for the final image output.
     *
     *  @return void
     */
    public function /*void*/ generateLayerInformation()
    {

        /*string*/ $path = $this->getDataPath();

        if (file_exists($path))
        {

            /*string[]*/ $lines = explode("\n", file_get_contents($path));
            /*string[]*/ $header = explode($this->dataSeparator, array_shift($lines));

            foreach ($lines as $line)
                if (strlen(trim($line)) != 0)
                {

                    /*string[]*/ $fields = explode($this->dataSeparator, $line);

                    /*string[string]*/ $results = array();

                    foreach ($fields as $index => $value)
                        $results[$header[$index]] = $value;

                    $this->data[] = $results;

                }

        }

        $this->siftArray();

        if (count($this->data) <= 0)
        {

            $this->data = array(array('TEXT' => "An error has occured during generation.\nNo data read from data file.\n" . $path));

        }

    }

    /**
     * Determines which imagick composite value to use based off of the string present in the CSV.
     *
     * @param  string   $compositeString   A string containing the name of a composite type.
     *
     * @return int
     */
    public function /*int*/ getCompositeValue(/*string*/ $compositeString)
    {

        switch ($compositeString)
        {
            case 'burn':
                return imagick::COMPOSITE_COLORBURN;
            case 'add':
                return imagick::COMPOSITE_ADD;
            case 'atop':
                return imagick::COMPOSITE_ATOP;
            case 'blend':
                return imagick::COMPOSITE_BLEND;
            case 'bumpmap':
                return imagick::COMPOSITE_BUMPMAP;
            case 'clear':
                return imagick::COMPOSITE_CLEAR;
            case 'dodge':
                return imagick::COMPOSITE_COLORDODGE;
            case 'colorize':
                return imagick::COMPOSITE_COLORIZE;
            case 'black':
                return imagick::COMPOSITE_COPYBLACK;
            case 'blue':
                return imagick::COMPOSITE_COPYBLUE;
            case 'copy':
                return imagick::COMPOSITE_COPY;
            case 'cyan':
                return imagick::COMPOSITE_COPYCYAN;
            case 'green':
                return imagick::COMPOSITE_COPYGREEN;
            case 'magenta':
                return imagick::COMPOSITE_COPYMAGENTA;
            case 'opacity':
                return imagick::COMPOSITE_COPYOPACITY;
            case 'red':
                return imagick::COMPOSITE_COPYRED;
            case 'yellow':
                return imagick::COMPOSITE_COPYYELLOW;
            case 'darken':
                return imagick::COMPOSITE_DARKEN;
            case 'destination_atop':
                return imagick::COMPOSITE_DSTATOP;
            case 'destination':
                return imagick::COMPOSITE_DST;
            case 'destination_in':
                return imagick::COMPOSITE_DSTIN;
            case 'destination_outer':
                return imagick::COMPOSITE_DSTOUT;
            case 'destination_over':
                return imagick::COMPOSITE_DSTOVER;
            case 'difference':
                return imagick::COMPOSITE_DIFFERENCE;
            case 'display':
                return imagick::COMPOSITE_DISPLAY;
            case 'dissolve':
                return imagick::COMPOSITE_DISSOLVE;
            case 'exclusion':
                return imagick::COMPOSITE_EXCLUSION;
            case 'hardlight':
                return imagick::COMPOSITE_HARDLIGHT;
            case 'hue':
                return imagick::COMPOSITE_HUE;
            case 'lighten':
                return imagick::COMPOSITE_LIGHTEN;
            case 'luminize':
                return imagick::COMPOSITE_LUMINIZE;
            case 'minus':
                return imagick::COMPOSITE_MINUS;
            case 'modulate':
                return imagick::COMPOSITE_MODULATE;
            case 'multiply':
                return imagick::COMPOSITE_MULTIPLY;
            case 'out':
                return imagick::COMPOSITE_OUT;
            case 'over':
                return imagick::COMPOSITE_OVER;
            case 'plus':
                return imagick::COMPOSITE_PLUS;
            case 'replace':
                return imagick::COMPOSITE_REPLACE;
            case 'saturate':
                return imagick::COMPOSITE_SATURATE;
            case 'screen':
                return imagick::COMPOSITE_SCREEN;
            case 'softlight':
                return imagick::COMPOSITE_SOFTLIGHT;
            case 'source_atop':
                return imagick::COMPOSITE_SRCATOP;
            case 'source':
                return imagick::COMPOSITE_SRC;
            case 'source_in':
                return imagick::COMPOSITE_SRCIN;
            case 'source_out':
                return imagick::COMPOSITE_SRCOUT;
            case 'source_over':
                return imagick::COMPOSITE_SRCOVER;
            case 'subtract':
                return imagick::COMPOSITE_SUBTRACT;
            case 'threshold':
                return imagick::COMPOSITE_THRESHOLD;
            case 'xor':
                return imagick::COMPOSITE_XOR;
            default:
                return imagick::COMPOSITE_DEFAULT;
        }

    }

    /**
     *  Generates the final output image using the information from $data and $properties
     *  and pushes this information to the $drawer and $image references.
     *
     *  @param  string[int][string] $data           An array containing strings indexed by an int and a string.
     *  @param  string[int][string] $properties     An array containing strings indexed by an int and a string.
     *  @param  imagickdraw $drawer                 A reference to an imagickdraw object used as the composite component.
     *  @param  imagick     $image                  A reference to an imagick object used to contain the final image.
     *
     *  @return void
     */
    public function /*void*/ processLayers(/*string[string]*/ $data, /*string[string]*/ $properties,
                                           /*imagickdraw*/ &$drawer, /*imagick*/ &$image)
    {

        /*int[string]*/ $dimensions = $image->getImageGeometry();

        /*int*/ $baseX = 2;
        /*int*/ $baseY = 16;

        foreach ($data as $key => $value)
        {

            try
            {

                //Rewrite into modular functions using the keys.
                if ($properties[$key.$this->propertyDefinitions['type']] == 'text')
                {

                    /*imagick*/ $base = new imagick();
                    $base->newPseudoImage($dimensions['width'], $dimensions['height'], 'canvas:transparent');
                    //Sets the font if a text layer is found, defaulting to Helvetica-Narrow if no font is set.
                    if (isset($properties[$key.$this->propertyDefinitions['font']]))
                        $drawer->setFont($this->fontDirectory.$properties[$key.$this->propertyDefinitions['font']]);
                    else
                        $drawer->setFont('Helvetica-Narrow');
                    //Sets the font size if a text layer is found, defaulting to 18pt if no font size is set.
                    if (isset($properties[$key.$this->propertyDefinitions['fontSize']]))
                        $drawer->setFontSize($properties[$key.$this->propertyDefinitions['fontSize']]);
                    else
                        $drawer->setFontSize(18);
                    //Sets the font color if a text layer is found, defaulting to #0050AF if no color is set.
                    if (isset($properties[$key.$this->propertyDefinitions['fontColor']]))
                        $drawer->setFillColor(new ImagickPixel($properties[$key.$this->propertyDefinitions['fontColor']]));
                    else
                        $drawer->setFillColor(new ImagickPixel('#0050AF'));
                    //Sets the stroke color if a text layer is found, defaulting to #FFFFFF if no color is set.
                    if (isset($properties[$key.$this->propertyDefinitions['strokeColor']]))
                        $drawer->setStrokeColor(new ImagickPixel($properties[$key.$this->propertyDefinitions['strokeColor']]));
                    else
                        $drawer->setStrokeColor(new ImagickPixel('#FFFFFF'));
                    //Sets the stroke width if a text layer is found, defaulting to 0 if no width is set.
                    if (isset($properties[$key.$this->propertyDefinitions['strokeWidth']]))
                        $drawer->setStrokeWidth($properties[$key.$this->propertyDefinitions['strokeWidth']]);
                    else
                        $drawer->setStrokeWidth(0);
                    //Sets the stroke opacity if a text layer is found, defaulting to 1.0 if no opacity is set.
                    if (isset($properties[$key.$this->propertyDefinitions['strokeOpacity']]))
                        $drawer->setStrokeOpacity($properties[$key.$this->propertyDefinitions['strokeOpacity']]);
                    else
                        $drawer->setStrokeOpacity(1.0);
                    //Sets the text opacity if a text layer is found, defaulting to 1.0 if no opacity is set.
                    if (isset($properties[$key.$this->propertyDefinitions['opacity']]))
                        $drawer->setFillOpacity($properties[$key.$this->propertyDefinitions['opacity']]);
                    else
                        $drawer->setFillOpacity(1.0);
                    //Sets the stroke and text antialias to on.
                    $drawer->setStrokeAntialias(true);
                    $drawer->setTextAntialias(true);
                    //Sets the X coord if a text layer is found, defaulting to $baseX's value if X is not set.
                    if (isset($properties[$key.$this->propertyDefinitions['xCoOrd']]))
                        $x = $properties[$key.$this->propertyDefinitions['xCoOrd']];
                    else
                        $x = $baseX;
                     //Sets the Y coord if a text layer is found, defaulting to $baseY's value if Y is not set.
                    if (isset($properties[$key.$this->propertyDefinitions['yCoOrd']]))
                        $y = $properties[$key.$this->propertyDefinitions['yCoOrd']];
                    else
                        $y = $baseY;
                    //Creates the annotation.
                    $drawer->annotation($x, $y, $value);
                    //Makes the text overlay the stroke if enabled.
                    if ($properties[$key.$this->propertyDefinitions['fillOverStroke']] == '1')
                    {

                        $drawer->setStrokeColor(new ImagickPixel('none'));
                        $drawer->setStrokeAntialias(true);
                        $drawer->setTextAntialias(true);

                        $drawer->annotation($x, $y, $value);

                    }
                    //Adjusts the baseY by the height of the text or by 20 if no height was specified.
                    if (isset($properties[$key.$this->propertyDefinitions['fontSize']]))
                        $baseY += $properties[$key.$this->propertyDefinitions['fontSize']] + 2;
                    else
                        $baseY += 20;
                    //Creates the text image.
                    $base->drawImage($drawer);
                    //Resolves the composition blending mode string.
                    $composition = $this->getCompositeValue($properties[$key.$this->propertyDefinitions['blendMode']]);
                    //Composes the text onto the base image.
                    $image->compositeImage($base, $composition, 0, 0);
                    $drawer->clear();

                }
                else if ($properties[$key.$this->propertyDefinitions['type']] == 'image')
                {

                    /*imagick*/ $base = new imagick($value);
                    $base->setImageFormat('png32');
                    //Sets the X coordinate if an image layer is found, defaulting to 0 if not set.
                    if (isset($properties[$key.$this->propertyDefinitions['xCoOrd']]))
                        $x = $properties[$key.$this->propertyDefinitions['xCoOrd']];
                    else
                        $x = 0;
                    //Sets the Y coordinate if an image layer is found, defaulting to 0 if not set.
                    if (isset($properties[$key.$this->propertyDefinitions['yCoOrd']]))
                        $y = $properties[$key.$this->propertyDefinitions['yCoOrd']];
                    else
                        $y = 0;

                    //This works but artifacts are left around the stroke. Figure out something to do with that.
                    if (isset($properties[$key.$this->propertyDefinitions['strokeWidth']]))
                    {
                        /*Creates a copy of the image, insures its format is png32, makes the image equal to the alpha channel,
                          blurs the image, thresholds the image, edges the image, inverts the image, creates a new alpha channel,
                          then creates an alpha channel based off of the image itself.
                        */
                        $stroke = clone $base;
                        $stroke->setImageFormat('png32');
                        $stroke->separateImageChannel(Imagick::CHANNEL_ALPHA);
                        $stroke->blurImage(1, $properties[$key.$this->propertyDefinitions['strokeWidth']]);
                        $stroke->whiteThresholdImage('#F8F8F8');
                        $stroke->edgeImage($properties[$key.$this->propertyDefinitions['strokeWidth']]);
                        $stroke->negateImage(false);
                        $stroke->setImageAlphaChannel(Imagick::ALPHACHANNEL_OPAQUE);
                        $stroke->setImageClipMask(clone $stroke);
                        //Sets the stroke color if an image layer is found, defaulting to #FFFFFF if not set.
                        if (isset($properties[$key.$this->propertyDefinitions['strokeColor']]))
                            $stroke->colorizeImage($properties[$key.$this->propertyDefinitions['strokeColor']], 1);
                        else
                            $stroke->colorizeImage(new ImagickPixel('#FFFFFF'), 1);
                        //DO NOT USE THIS PARAMETER. It currently ruins the alpha channel.
                        if (isset($properties[$key.$this->propertyDefinitions['strokeOpacity']]))
                            $stroke->setImageOpacity($properties[$key.$this->propertyDefinitions['strokeOpacity']]);

                    }

                    //Currently not working.
                    /*if (isset($properties[$key.$this->propertyDefinitions['opacity']]))
                        $base->setImageOpacity($properties[$key.$this->propertyDefinitions['opacity']]);*/
                    //Composites the stroke onto the base image if it exists.
                    if (isset($stroke))
                    {

                        $composition = $this->getCompositeValue($properties[$key.$this->propertyDefinitions['strokeBlendMode']]);
                        $image->compositeImage($stroke, $composition, $x, $y);

                    }
                    //Sets the composite blend mode if an image layer is found.
                    $composition = $this->getCompositeValue($properties[$key.$this->propertyDefinitions['blendMode']]);
                    //Composites the image onto the base image.
                    $image->compositeImage($base, $composition, $x, $y);

                }
                else
                {

                    //Fall out to some sort of error/catch all type here.

                }

            }
            catch (ImagickException $exception)
            {

                $this->exceptions[] = $exception;

            }

        }

    }

    /**
     *  Runs through the data array to create the properties array.
     *
     *  @return void
     */
    public function /*void*/ siftArray()
    {

        $this->propertyDefinitions;

        foreach ($this->data as $index => $row)
            foreach ($row as $header => $field)
                foreach ($this->propertyDefinitions as $definition)
                    if (strpos($header, $definition))
                    {

                        $this->properties[$index][$header] = $field;
                        unset($this->data[$index][$header]);

                    }

    }

}
