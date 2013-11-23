<?php
/**
 * SimpleCaptcha class
 */
class captcha {

    /** Width of the image */
    public $width  = 180;

    /** Height of the image */
    public $height = 50;

    /** Dictionary word file (empty for randnom text) */
    public $wordsFile = '';

    /** Min word length (for non-dictionary random text generation) */
    public $minWordLength = 5;

    /**
     * Max word length (for non-dictionary random text generation)
     * 
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
    public $maxWordLength = 6;

    /** Background color in RGB-array */
    public $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB-array */
    public $colors = array(
        array(27, 78, 181), // blue
        array(22, 163, 35), // green
        array(214, 36, 7),  // red
    );

    /** Shadow color in RGB-array or false */
    public $shadowColor = array(120, 120, 120);

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public $fonts = array(
        'comic'  => array('spacing' =>4,'minSize' => 20, 'maxSize' => 30, 'font' => 'comic.ttf'),
        'courbi' => array('spacing' =>2,'minSize' => 20, 'maxSize' => 26, 'font' => 'courbi.ttf'),
        'arial'  => array('spacing' =>4,'minSize' => 20, 'maxSize' => 34, 'font' => 'arial.ttf'),
        'times'  => array('spacing' =>2,'minSize' => 20, 'maxSize' => 34, 'font' => 'times.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    public $Yperiod    = 10;
    public $Yamplitude = 1;
    public $Xperiod    = 10;
    public $Xamplitude = 1;

    /** letter rotation clockwise */
    public $maxRotation = 8;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    public $scale = 3;

    /** 
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public $blur = true;

    /** Debug? */
    public $debug = false;
    
    /** Image format: jpeg or png */
    public $imageFormat = 'png';


    /** GD image */
    public $im;

    /** Sessionname to store the original text */
    public static $session_var = '__captcha__';

    /**
     * Draw captcha image
     */
    public static function create() {
        $captcha = new Captcha();
        $captcha->CreateImage();
    }

    /**
     * Check captcha value
     */
    public static function check($captcha_value) {
        $result = isset($_SESSION[self::$session_var]) &&
            !strcasecmp($captcha_value, $_SESSION[self::$session_var]);
        unset($_SESSION[self::$session_var]);
        return $result;
    }

    /**
     * Creates the image
     */
    public function CreateImage() {
        $ini = microtime(true);

        /** Initialization */
        $this->ImageAllocate();
        
        /** Text insertion */
        $text     = $this->GetCaptchaText();

        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $this->WriteText($text, $fontcfg);

        $_SESSION[self::$session_var] = $text;

        /** Transformations */
        $this->WaveImage();
        if ($this->blur) {
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->ReduceImage();


        if ($this->debug) {
            imagestring($this->im, 1, 1, $this->height-8,
                "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
                $this->GdFgColor
            );
        }

        /** Output */
        $this->WriteImage();
        $this->Cleanup();
    }

    /**
     * Creates the image resources
     */
    protected function ImageAllocate() {
        // Cleanup
        if (!empty($this->im)) {
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);
        
        // Background color
        $this->GdBgColor = imagecolorallocate($this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

        // Foreground color
        $color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadowColor)) {
            $this->GdShadowColor = imagecolorallocate($this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
        }
    }

    /**
     * Random text generation
     *
     * @return string Text
     */
    protected function GetCaptchaText($length = null) {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstvwyz";
        $vocals = "aeiou";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++) {
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1);
            } else {
                $text .= substr($words, mt_rand(0, 22), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }

   /**
     * Text insertion
     */
    protected function WriteText($text, $fontcfg = array()) {
        if (empty($fontcfg)) {
            // Select the font configuration
            $fontcfg  = $this->fonts[array_rand($this->fonts)];
        }
        $fontfile = APP_DIR.'/include/fonts/'.$fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);
        
        $bbox = imageftbbox($fontcfg['maxSize']*$this->scale*$fontSizefactor, 0, $fontfile, $text);
        $text_width = $bbox[2] - $bbox[0];
        
        // Text generation (char by char)
        $x      = round(($this->width*$this->scale - $text_width)/2);
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);
            
            if ($this->shadowColor) {
                $coords = imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->GdShadowColor, $fontfile, $letter);
            }
            $coords = imagettftext($this->im, $fontsize, $degree,
                $x, $y,
                $this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }
    }
    /**
     * Wave filter
     */
    protected function WaveImage() {
        // X-axis wave generation
        $xp = $this->scale*$this->Xperiod*rand(1,3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for ($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }
    /**
     * Reduce the image to the final size
     */
    protected function ReduceImage() {
        // Reduzco el tama�o de la imagen
        $imResampled = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }
    /**
     * File generation
     */
    protected function WriteImage() {
        if ($this->imageFormat == 'png') {
            header("Content-type: image/png");
            ob_clean();
            imagepng($this->im);
        } else {
            header("Content-type: image/jpeg");
            ob_clean();
            imagejpeg($this->im, null, 90);
        }
    }
    /**
     * Cleanup
     */
    protected function Cleanup() {
        imagedestroy($this->im);
    }
}


