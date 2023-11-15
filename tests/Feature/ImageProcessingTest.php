<?php

use Intervention\Image\Facades\Image;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{

    public function test_image_file_can_be_resized()
    {
        $img = Image::make(file_get_contents('https://source.unsplash.com/user/c_v_r/800x800'))
            ->resize(600, 700, function ($constraint) {
                // resize the image so that the largest side fits within the limit; the smaller
                // side will be scaled to maintain the original aspect ratio
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('webp', 80);
        $this->assertTrue((bool) $img);
    }
}
