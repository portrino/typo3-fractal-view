<?php

namespace Portrino\Typo3FractalView\Tests\Transformer;

/*
 * This file is part of the TYPO3 Fractal View project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

use Portrino\Typo3FractalView\Tests\Model\Book;

/**
 * Class InvalidTransformer
 * @package Portrino\Typo3FractalView\Tests\Transformer
 */
class InvalidTransformer
{
    /**
     * @param Book $book
     * @return array
     */
    public function transform(Book $book)
    {
        return [
            'id' => (int)$book->id,
            'name' => $book->title
        ];
    }
}
