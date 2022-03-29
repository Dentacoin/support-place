<?php

namespace App\Helpers;

use Image;

class GeneralHelper {

    public static function checkFile($file, $allowedExtensions, $allowedMimetypes) {

        if(is_string($file)) { //for base64
            //checking file mimetype
            
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $file, FILEINFO_MIME_TYPE);
            
            if (!in_array($mime_type, $allowedMimetypes)) {
                return [
                    'error' => 'Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }
        } else {
            // if contains php tag
            if( strpos(file_get_contents($file),'<?php') !== false) {
                // do stuff
                return [
                    'error' => 'Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }
            
            //checking file extension
            if (!in_array(strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)), $allowedExtensions)) {
                return [
                    'error' => 'Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }

            //checking file mimetype
            if (!in_array($file->getMimeType(), $allowedMimetypes)) {
                return [
                    'error' => 'Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }

            //checking if error in file
            if ($file->getError()) {
                return [
                    'error' => 'There is error with one or more of the files, please try with other files. Please try again.'
                ];
            }
        }

        $img = Image::make( $file )->orientate();

        //checking if file has height & width
        if ($img->height() > 1 && $img->width() > 1) {
        } else {
            return [
                'error' => 'There is error with one or more of the files, please try with other files. Please try again.'
            ];
        }

        //checking file mimetype
        if (!in_array($img->mime(), $allowedMimetypes)) {
            return [
                'error' => 'Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
            ];
        }

        return [
            'success' => true
        ];
    }
}