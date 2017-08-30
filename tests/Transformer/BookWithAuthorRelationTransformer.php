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

use League\Fractal\Resource\Item;
use League\Fractal\Resource\Primitive;
use League\Fractal\TransformerAbstract;
use Portrino\Typo3FractalView\Tests\Model\BookWithAuthorRelation;

/**
 * Class BookWithAuthorRelationTransformer
 * @package Portrino\Typo3FractalView\Tests\Transformer
 */
class BookWithAuthorRelationTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'author'
    ];

    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'yr'
    ];

    /**
     * @param BookWithAuthorRelation $book
     * @return array
     */
    public function transform(BookWithAuthorRelation $book)
    {
        return [
            'id' => (int)$book->id,
            'title' => $book->title,
            'year' => (int)$book->yr,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => '/books/' . $book->id,
                ]
            ],
        ];
    }

    /**
     * @param BookWithAuthorRelation $book
     * @return Primitive
     */
    public function includeYr(BookWithAuthorRelation $book)
    {
        return new Primitive($book->yr);
    }

    /**
     * @param BookWithAuthorRelation $book
     * @return Item
     */
    public function includeAuthor(BookWithAuthorRelation $book)
    {
        $author = $book->author;

        return $this->item($author, new AuthorTransformer);
    }
}
