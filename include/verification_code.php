<?php
/**
 * Script para la generaci髇 de CAPTCHAS
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 * 
 * 验证码处理类
 * 整理By: Wilson
 *
 */
//$code = new _08_Verification_Code();



// OPTIONAL Change configuration...
//$code->wordsFile = 'words/es.php';
//$code->varname = 'secretword';
//$code->imageFormat = 'png';
//$code->lineWidth = 3;
//$code->scale = 3; $code->blur = true;
//$code->resourcesPath = "/var/cool-php-captcha/resources";

// OPTIONAL Simple autodetect language example
/*
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array('en', 'es');
    $lang  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($lang, $langs)) {
        $code->wordsFile = "words/$lang.php";
    }
}
*/



// Image generation
//$code->CreateImage();



class _08_Verification_Code
{

    /** Width of the image */
    public $width  = 60;

    /** Height of the image */
    public $height = 25;
    
    // 开始坐标，设置该值为字符位置
    public $startPos = 10;
    
    // 使用COOKIE或是SESSION验证，0为COOKIE，1为COOKIE与SESSION，该SESSION只为用解决跨域，COOKIE只为兼容之前方法
    public $cookieAndSession = 1;
    
    public $randString = '';

    /** Dictionary word file (empty for random text) */
    //public $wordsFile = 'words/en.php';
    public $wordsFile = '';

    /**
     * Path for resource files (fonts, words, etc.)
     *
     * "resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     */
    public $resourcesPath = '';

    /** Min word length (for non-dictionary random text generation) */
    public $minWordLength = 4;

    /**
     * Max word length (for non-dictionary random text generation)
     * 
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
    public $maxWordLength = 4;

    /** Sessionname to store the original text */
    public $varname = '_08CMS_';

    /** Background color in RGB-array */
    public $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB-array */
    public $colors = array(
        array(27,78,181), // blue
        array(22,163,35), // green
        array(214,36,7),  // red
    );

    /** Shadow color in RGB-array or null */
    public $shadowColor = null; //array(0, 0, 0);

    /** Horizontal line through the text */
    public $lineWidth = 0;

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public $fonts = array(
        'AngelicWar' => array('spacing' => -1, 'minSize' => 15, 'maxSize' => 16, 'font' => '_08_AngelicWar.ttf'),
        'Avant_M' => array('spacing' => -1, 'minSize' => 15, 'maxSize' => 16, 'font' => '_08_Avant_M.Ttf'),
    );

    /** Wave configuracion in X and Y axes */
    public $Yperiod    = 1;
    public $Yamplitude = 1;
    public $Xperiod    = 1;
    public $Xamplitude = 1;

    /** letter rotation clockwise */
    public $maxRotation = 4;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    public $scale = 3;

    /** 
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public $blur = false;

    /** Debug? */
    public $debug = false;
    
    /** Image format: jpeg or png */
    public $imageFormat = 'png';


    /** GD image */
    public $im;
    
    public function __construct($config = array()) {
        $this->resourcesPath = M_ROOT . 'images';
        $font_path = $this->resourcesPath . DIRECTORY_SEPARATOR . 'fonts';
        _08_FileSystemPath::checkPath($font_path, true);
        
        foreach(array('width', 'height', 'varname', 'randString') as $key)
        {
            if ( isset($config[$key]) )
            {
                $this->$key = $config[$key];
            }
        }
        if ( empty($this->randString) )
        {
            $maxSize = 15;
        }
        else
        {
            $maxSize = floor($this->width / strlen($this->randString) - 2);
            $this->startPos = ceil(($maxSize - 15) / 2);
            #echo $this->startPos;exit;
            
            foreach($this->fonts as &$font)
            {
                $font['maxSize'] = $maxSize;
                $font['minSize'] = $maxSize - rand(2, 5);
            }
        }
        
        if ( is_dir($font_path) )
        {
            $dir = new DirectoryIterator($font_path);
            $files = array();
            foreach($dir as $file)
            {
                if ( !$file->isDot() && @$file->isFile() )
                {
                    $filename = $file->getFilename();
                    $filekey = preg_replace('/[^\w]/', '', $filename);
                    if ( (false === strpos($filename, '_08')) && (preg_match('/(\.tt[f|c])$/i', $filename)) )
                    {                      
                        $files[$filekey] = array('spacing' => 11, 'minSize' => $maxSize - rand(2, 5), 'maxSize' => $maxSize, 'font' => $filename);
                    }
                }
            }
        }
        
        
        if ( !empty($files) )
        {
            $this->fonts += $files;
        }        
    }

    public function CreateImage() {
        global $mcharset;
        $ini = microtime(true);

        /** Initialization */
        $this->ImageAllocate();
        
        /** Text insertion */
        if ( empty($this->randString) )
        {
            $text = $this->GetCaptchaText();
        }
        else
        {
        	$text = $this->randString;
        }
        
        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $this->WriteText($text, $fontcfg);
        if ( $this->cookieAndSession )
        {
            # 用于解决跨域
            $_SESSION[$this->varname] = authcode(time()."\t".$text, 'ENCODE');
            # 用于兼容之前的方法
            msetcookie($this->varname, authcode(time()."\t".$text, 'ENCODE'));
        }
        else
        {
            msetcookie($this->varname, authcode(time()."\t".$text, 'ENCODE'));
        }
        

        /** Transformations */
        if (!empty($this->lineWidth)) {
            $this->WriteLine();
        }
        $this->WaveImage();
        
        if ($this->blur && function_exists('imagefilter')) {
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->ReduceImage();


        if ($this->debug) {
            $string = "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms";
            imagestring($this->im, 1, 1, $this->height-8,
                "$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
                $this->GdFgColor
            );
        } else if ($this->width >= 100) {
            $string = mb_convert_encoding('点击换一换','html-entities',$mcharset);
            $fontfile = $this->resourcesPath. DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'simsun.ttc';
            @imagettftext($this->im, 10, 0, $this->width / 2 - 40, $this->height - 2, $this->GdFgColor, $fontfile, $string);
        }

        
        // 增加杂点与边框
        $this->AddNoise();

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
        if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
            $this->GdShadowColor = imagecolorallocate($this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
        }
    }
    
    /**
     * Text generation
     *
     * @return string Text
     */
    protected function GetCaptchaText() {
        $text = $this->GetDictionaryCaptchaText();
        if (!$text) {
            $text = $this->GetRandomCaptchaText(4);
        }
        return $text;
    }

    /**
     * Random text generation
     *
     * @return string Text
     */
    protected function GetRandomCaptchaText($length = null) {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $vocals = "08CMS";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++) {
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1);
            } else {
                $text .= substr($words, mt_rand(0, strlen($words)), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }

    /**
     * Random dictionary word generation
     *
     * @param boolean $extended Add extended "fake" words
     * @return string Word
     */
    function GetDictionaryCaptchaText($extended = false) {
        if (empty($this->wordsFile)) {
            return false;
        }

        // Full path of words file
        if (substr($this->wordsFile, 0, 1) == '/') {
            $wordsfile = $this->wordsFile;
        } else {
            $wordsfile = $this->resourcesPath.'/'.$this->wordsFile;
        }

        if (!file_exists($wordsfile)) {
            return false;
        }

        $fp     = fopen($wordsfile, "r");
        $length = strlen(fgets($fp));
        if (!$length) {
            return false;
        }
        $line   = rand(1, (filesize($wordsfile)/$length)-2);
        if (fseek($fp, $length*$line) == -1) {
            return false;
        }
        $text = trim(fgets($fp));
        fclose($fp);


        /** Change ramdom volcals */
        if ($extended) {
            $text   = preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
            $vocals = array('0', '8', 'C', 'M', 'S');
            foreach ($text as $i => $char) {
                if (mt_rand(0, 1) && in_array($char, $vocals)) {
                    $text[$i] = $vocals[mt_rand(0, 4)];
                }
            }
            $text = implode('', $text);
        }

        return $text;
    }

    /**
     * Horizontal line insertion
     */
    protected function WriteLine() {
        $x1 = $this->width*$this->scale*.15;
        $x2 = $this->textFinalX;
        $y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $width = $this->lineWidth/2*$this->scale;

        for ($i = $width*-1; $i <= $width; $i++) {
            imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->GdFgColor);
        }
    }

    /**
     * Text insertion
     */
    protected function WriteText($text, $fontcfg = array()) {
        if (empty($fontcfg)) {
            // Select the font configuration
            $fontcfg  = $this->fonts[array_rand($this->fonts)];
        }

        // Full path of font file
        $fontfile = $this->resourcesPath. DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1.0+($lettersMissing*0.09);

        // Text generation (char by char)
        $x      = $this->startPos * $this->scale;
        $y      = round(($this->height*27/35)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->maxRotation*-3, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);

            if ($this->shadowColor) {
                $coords = @imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->GdShadowColor, $fontfile, $letter);
            }
            $coords = @imagettftext($this->im, $fontsize, $degree,
                $x, $y,
                $this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }

        $this->textFinalX = $x;
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
        // Reduzco el tama駉 de la imagen
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
        if ($this->imageFormat == 'png' && function_exists('imagepng')) {
            header("Content-type: image/png");
            imagepng($this->im);
        } else {
            header("Content-type: image/jpeg");
            imagejpeg($this->im, null, 80);
        }
    }
    
    /**
     * 增加杂点与边框
     */
    protected function AddNoise()
    {
        # 增加边框
        $border = imagecolorallocate($this->im,183,216,239);
        imagerectangle($this->im,0,0,$this->width - 1,$this->height - 1,$border);
        for($i=1; $i<=20;$i++)
        {
    		$dot = imagecolorallocate($this->im,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255));
    		imagesetpixel($this->im,mt_rand(2,$this->width-2), mt_rand(2,$this->height-2),$dot);
        }
        for($i=1; $i<=10;$i++)
        {
    		imageString(
                $this->im,
                1,
                $i*$this->width/12+mt_rand(1,3),
                mt_rand(1,13),
                '.',
                imageColorAllocate($this->im,mt_rand(150,255), mt_rand(150,255), mt_rand(150,255))
            );
    	}
    }

    /**
     * Cleanup
     */
    protected function Cleanup() {
        imagedestroy($this->im);
    }
}
