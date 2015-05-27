# Yii2 image thumbnail

Create image thumbnails use Imagine. Thumbnail created and cached automatically.
It allows you to create placeholder with service [http://placehold.it/](http://placehold.it/) or holder.js.

#### Features:
- Easy to use
- Use Imagine
- Automaticly thumbnails caching
- Cache sorting to subdirectories
- Use placehold.it & holder.js

## Installation

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run ```php composer.phar require sadovojav/yii2-image-thumbnail "dev-master"```

or add ```"sadovojav/yii2-image-thumbnail": "dev-master"``` to the require section of your ```composer.json```

### Config

Attach the component in your config file:

```php

'bootstrap' => [
    'easyThumbnail',
],

'components'=>[
    'easyThumbnail' => [
        'class' => 'sadovojav\image\EasyThumbnail',
        //'cachePath' => '@webroot/assets/thumbnails',
        //'basePath' => '@webroot',
        //'cacheExpire' => 0,
        'placeholder' => [
            'type' => sadovojav\image\Thumbnail::PLACEHOLDER_TYPE_URL,
            'backgroundColor' => '#ececec',
            'textColor' => '#999',
            'text' => 'Ooops!'
        ],
        'options' => [
            'quality' => 75
        ]
    ],
],
```
#### Parameters
- string `basePath` = `@webroot` - Base path
- string `cachePath` = `@webroot/assets/thumbnails` - Cache path alias
- integer `cacheExpire` = `86400` - Cache expire time
- array `placeholder` - Placeholder parametrs
- array `options` - Image save options

> placeholder type
- 1. js - holder.js
- 2. url - get placeholder by url

## Using

### Get cache image
```php
echo \sadovojav\image\Thumb::getImg($file, $params, $options);
```
This method returns Html::img()

#### Parameters
- string `$file` required - Image file path
- array `$params` - Image manipulation methods. See Methods
- array `$options` - options for Html::img()

### Get cache image url
```php
echo \sadovojav\image\Thumb::getUrl($file, $params);
```
This method returns cache image url

#### Parameters
- string `$file` required - Image file path
- array `$params` - Image manipulation methods. See Methods

## Method

### Resize
```php
'resize' => [
    'width' => 320,
    'height' => 200
]
```
#### Parameters
- integer `$width` required - New width
- integer `$height` required - New height

### Crop
```php
'crop' => [
    'width' => 250,
    'height' => 200,
//    'x' => $x,
//    'y' => $y
]
```
#### Parameters
- integer `$width` required - New width
- integer `$height` required - New height
- integer `$x` = `0` - X start crop position
- integer `$y` = `0` - Y start crop position

### Thumbnail
```php
'thumbnail' => [
    'width' => 450,
    'height' => 250,
//    'mode' => \sadovojav\image\Thumbnail:THUMBNAIL_OUTBOUND
]
```
#### Parameters
- integer `$width` required - New width
- integer `$height` required - New height
- string `$mode` = `THUMBNAIL_OUTBOUND` - Thumbnail mode `THUMBNAIL_OUTBOUND` or `THUMBNAIL_INSET`

### Placeholder
This param return image placeholder if image file doesn't exist.
```php
'placeholder' => [
    'width' => 450,
    'height' => 250,
//    'backgroundColor' => '#ececec',
//    'textColor' => '#999'
//    'text' => 'No image'
]
```
#### Parameters
- integer `$width` required - Placeholder image width
- integer `$height` required - Placeholder image height
- string `$backgroundColor` = `#ececec` - Background color
- string `$textColor` = `#999` - Text color
- string `$text` = `Ooops!` - Text

## Author

[Aleksandr Sadovoj](https://github.com/sadovojav/), e-mail: [sadovojav@gmail.com](mailto:sadovojav@gmail.com)