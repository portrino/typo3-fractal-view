<?php

namespace Portrino\Typo3FractalView\Tests\Serializer;

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

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use PHPUnit_Framework_TestCase;
use Portrino\Typo3FractalView\Mvc\View\FractalView;
use Portrino\Typo3FractalView\Serializer\ArraySerializer;
use Portrino\Typo3FractalView\Tests\Model\Author;
use Portrino\Typo3FractalView\Tests\Model\Book;
use Portrino\Typo3FractalView\Tests\Model\BookWithAuthorRelation;
use Portrino\Typo3FractalView\Tests\Transformer\AuthorTransformer;
use Portrino\Typo3FractalView\Tests\Transformer\BookTransformer;
use Portrino\Typo3FractalView\Tests\Transformer\BookWithAuthorRelationTransformer;
use Portrino\Typo3FractalView\Tests\Transformer\InvalidTransformer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class ArraySerializerTest
 * @package Portrino\Typo3FractalView\Tests\Serializer
 */
class ArraySerializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArraySerializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = new ArraySerializer();
    }

    /**
     * @test
     */
    public function collectionTest()
    {
        $data = [
            'foo' => 'bar',
            '123' => '456'
        ];

        $actual = $this->serializer->collection('data', $data);

        $this->assertArrayNotHasKey('data', $actual);
        $this->assertArrayHasKey('foo', $actual);
        $this->assertArrayHasKey('123', $actual);
    }
}
