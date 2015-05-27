<?php

namespace sadovojav\image;

use Yii;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ManipulatorInterface;

/**
 * Class Thumbnail
 * @package sadovojav\image
 */
class Thumbnail
{
    private static $image;

    public static $cachePath;
    public static $cacheUrl;
    public static $cacheExpire;
    public static $basePath;
    public static $options;
    public static $placeholder;

    const THUMBNAIL_OUTBOUND = ManipulatorInterface::THUMBNAIL_OUTBOUND;
    const THUMBNAIL_INSET = ManipulatorInterface::THUMBNAIL_INSET;

    const PLACEHOLDER_TYPE_JS = 'js';
    const PLACEHOLDER_TYPE_URL = 'url';

    const FUNCTION_CROP = 'crop';
    const FUNCTION_RESIZE = 'resize';
    const FUNCTION_THUMBNAIL = 'thumbnail';

    /**
     * Creates and caches the image thumbnail and returns <img> tag
     * @param string $file
     * @param array $params
     * @param array $options
     * @return string
     */
    public static function getImg($file, $params, $options = [])
    {
        $cacheFileSrc = self::make($file, $params);

        if (!$cacheFileSrc) {
            if (isset($params['placeholder'])) {
                return self::placeholder($params['placeholder'], $options);
            } else {
                return null;
            }
        }

        return Html::img($cacheFileSrc, $options);
    }

    /**
     * Creates and caches the image thumbnail and returns image url
     * @param string $file
     * @param array $params
     * @return string
     */
    public static function getUrl($file, $params)
    {
        $cacheFileSrc = self::make($file, $params);

        return $cacheFileSrc ? $cacheFileSrc : null;
    }

    /**
     * Make image and save to cache
     * @param string $file
     * @param array $params
     * @return string
     */
    static private function make($file, $params)
    {
        $file = FileHelper::normalizePath(Yii::getAlias(self::$basePath . '/' . $file));

        if (!is_file($file)) {
            return false;
        }

        $cacheFileName = md5($file . serialize($params) . filemtime($file));
        $cacheFileExt = strrchr($file, '.');
        $cacheFileDir = DIRECTORY_SEPARATOR . substr($cacheFileName, 0, 2);
        $cacheFilePath = Yii::getAlias(self::$cachePath) . $cacheFileDir;
        $cacheFile = $cacheFilePath . DIRECTORY_SEPARATOR . $cacheFileName . $cacheFileExt;
        $cacheUrl = str_replace('\\', '/', preg_replace('/^@[a-z]+/', '', self::$cachePath) . $cacheFileDir . DIRECTORY_SEPARATOR
            . $cacheFileName . $cacheFileExt);

        if (file_exists($cacheFile)) {
            if (self::$cacheExpire !== 0 && (time() - filemtime($cacheFile)) > self::$cacheExpire) {
                unlink($cacheFile);
            } else {
                return $cacheUrl;
            }
        }

        if (!is_dir($cacheFilePath)) {
            mkdir($cacheFilePath, 0755, true);
        }

        self::$image = Image::getImagine()->open($file);

        foreach ($params as $key => $value) {
            switch ($key) {
                case self::FUNCTION_THUMBNAIL :
                    self::thumbnail($value);
                    break;
                case self::FUNCTION_RESIZE :
                    self::resize($value);
                    break;
                case self::FUNCTION_CROP :
                    self::crop($value);
                    break;
            }
        }

        self::$image->save($cacheFile, self::$options);

        return $cacheUrl;
    }

    /**
     * Image placeholder
     * @param array $params
     * @param array $options
     * @return null|string
     */
    public static function placeholder(array $params, $options = [])
    {
        $placeholder = null;

        $width = (isset($params['width']) && is_numeric($params['width'])) ? $params['width'] : null;
        $height = (isset($params['height']) && is_numeric($params['height'])) ? $params['height'] : null;

        if (is_null($width) || is_null($height)) {
            throw new Exception('Wrong placeholder width or height');
        }

        if (isset($options['backgroundColor']) && self::checkHexColor($options['backgroundColor'])) {
            $backgroundColor = $options['backgroundColor'];
        } else {
            $backgroundColor = self::$placeholder['backgroundColor'];
        }

        if (isset($options['textColor']) && self::checkHexColor($options['textColor'])) {
            $textColor = $options['textColor'];
        } else {
            $textColor = self::$placeholder['textColor'];
        }

        $text = !empty($params['text']) ? $params['text'] : self::$placeholder['text'];

        if (self::$placeholder['type'] == self::PLACEHOLDER_TYPE_URL) {
            $placeholder = self::urlPlaceholder($width, $height, $text, $backgroundColor, $textColor, $options);
        } elseif (self::$placeholder['type'] == self::PLACEHOLDER_TYPE_JS) {
            $placeholder = self::jsPlaceholder($width, $height, $text, $backgroundColor, $textColor, $options);
        }

        return $placeholder;
    }

    /**
     * Return url placeholder image
     * @param integer $width
     * @param integer $height
     * @param string $text
     * @param string $backgroundColor
     * @param string $textColor
     * @param array $options
     * @return string
     */
    static private function urlPlaceholder($width, $height, $text, $backgroundColor, $textColor, $options)
    {
        $src = 'http://placehold.it/' . $width . 'x' . $height . '/' . str_replace('#', '', $backgroundColor) . '/' .
            str_replace('#', '', $textColor) . '&text=' . $text;

        return Html::img($src, $options);
    }

    /**
     * Return js placeholder image
     * @param integer $width
     * @param integer $height
     * @param string $text
     * @param string $backgroundColor
     * @param string $textColor
     * @param array $options
     * @return string
     */
    static private function jsPlaceholder($width, $height, $text, $backgroundColor, $textColor, $options)
    {
        $src = 'holder.js/' . $width . 'x' . $height . '/' . $backgroundColor . ':' . $textColor . '/text:' . $text;

        return Html::img('', array_merge($options, ['data-src' => $src]));
    }

    /**
     * Check hex color
     * @param string $hex
     * @return int
     */
    static private function checkHexColor($hex)
    {
        return preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $hex);
    }

    /**
     * Crop image
     * @param array $params
     */
    static private function crop(array $params)
    {
        $x = (isset($params['x']) && is_numeric($params['x'])) ? $params['x'] : 0;
        $y = (isset($params['y']) && is_numeric($params['y'])) ? $params['y'] : 0;
        $width = (isset($params['width']) && is_numeric($params['width'])) ? $params['width'] : null;
        $height = (isset($params['height']) && is_numeric($params['height'])) ? $params['height'] : null;

        if (is_null($width) || is_null($height)) {
            throw new Exception('Wrong crop width or height');
        }

        self::$image->crop(new Point($x, $y), new Box($width, $height));
    }

    /**
     * Resize image
     * @param array $params
     */
    static private function resize(array $params)
    {
        $width = (isset($params['width']) && is_numeric($params['width'])) ? $params['width'] : null;
        $height = (isset($params['height']) && is_numeric($params['height'])) ? $params['height'] : null;

        if (!is_null($width) && !is_null($height)) {
            self::$image->resize(self::$image->getSize()->widen($width), self::$image->getSize()->heighten($height));
        } elseif (!is_null($width)) {
            self::$image->resize(self::$image->getSize()->widen($width));
        } elseif (!is_null($height)) {
            self::$image->resize(self::$image->getSize()->heighten($height));
        } else {
            throw new Exception('Wrong resize width or height');
        }
    }

    /**
     * Make thumbnail image
     * @param array $params
     */
    static private function thumbnail(array $params)
    {
        $mode = isset($params['mode']) ? $params['mode'] : self::THUMBNAIL_OUTBOUND;
        $width = (isset($params['width']) && is_numeric($params['width'])) ? $params['width'] : null;
        $height = (isset($params['height']) && is_numeric($params['height'])) ? $params['height'] : null;

        if (is_null($width) || is_null($height)) {
            throw new Exception('Wrong thumbnail width or height');
        }

        self::$image = self::$image->thumbnail(new Box($width, $height), $mode);
    }
}
