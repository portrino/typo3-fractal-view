<?php

namespace Portrino\Typo3FractalView\Tests;

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
use League\Fractal\Serializer\ArraySerializer;
use Portrino\Typo3FractalView\Mvc\View\FractalView;
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
 * Class FractalViewTest
 * @package Portrino\Typo3FractalView\Tests
 */
class FractalViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FractalView
     */
    protected $view;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Manager
     */
    protected $fractalManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->view = $this->getMock(
            FractalView::class,
            [
                'setIncludesFromRequest',
                'setExcludesFromRequest'
            ]
        );

        $this->view
            ->expects(static::once())
            ->method('setIncludesFromRequest');


        $this->view
            ->expects(static::once())
            ->method('setExcludesFromRequest');

        $this->objectManager = $this->getMock(
            ObjectManagerInterface::class
        );
        $this->objectManager->expects(static::any())
            ->method('get')
            ->willReturnMap([
                [BookTransformer::class, new BookTransformer()],
                [AuthorTransformer::class, new AuthorTransformer()],
                [InvalidTransformer::class, new InvalidTransformer()],
                [BookWithAuthorRelationTransformer::class, new BookWithAuthorRelationTransformer()]
            ]);
        $this->view->injectObjectManager($this->objectManager);

        $this->fractalManager = new Manager();
        $this->fractalManager->setSerializer(new ArraySerializer());

        $this->view->injectFractalManager($this->fractalManager);

        $this->controllerContext = $this->getMock(
            ControllerContext::class
        );
        $this->response = $this->getMock(
            Response::class
        );
        $this->controllerContext
            ->expects(static::any())
            ->method('getResponse')
            ->will(static::returnValue($this->response));

        $this->view->setControllerContext($this->controllerContext);
    }

    /**
     * @test
     */
    public function renderSingleVariableTest()
    {
        $book = new Book(1, 'A Song of Ice and Fire', '1996');
        $configuration = [
            'book' => BookTransformer::class
        ];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book']);
        $actualJson = $this->view->render();

        // rendering via pure fractal
        $resource = new Item($book, new BookTransformer);
        $expectedjson = json_encode($this->fractalManager->createData($resource)->toArray());

        static::assertEquals($expectedjson, $actualJson);
    }

    /**
     * @test
     */
    public function renderMultipleVariablesTest()
    {
        $book = new Book(1, 'A Song of Ice and Fire', '1996');
        $author = new Author(1, 'George Raymond Richard Martin');

        $configuration = [
            'author' => AuthorTransformer::class,
            'book' => BookTransformer::class
        ];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('author', $author);
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book', 'author']);
        $actualJson = $this->view->render();

        // rendering via pure fractal
        $bookResource = new Item($book, new BookTransformer);
        $authorResource = new Item($author, new AuthorTransformer());

        $bookArray = $this->fractalManager->createData($bookResource)->toArray();
        $authorArray = $this->fractalManager->createData($authorResource)->toArray();

        $expectedjson = json_encode(['author' => $authorArray, 'book' => $bookArray]);
        static::assertEquals($expectedjson, $actualJson);
    }

    /**
     * @test
     */
    public function renderCollectionTest()
    {
        $books = [
            new Book(1, 'A Song of Ice and Fire 1', '1996'),
            new Book(2, 'A Song of Ice and Fire 2', '1997'),
            new Book(3, 'A Song of Ice and Fire 3', '1998')
        ];

        $configuration = [
            'books' => BookTransformer::class
        ];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('books', $books);
        $this->view->setVariablesToRender(['books']);
        $actualJson = $this->view->render();

        // rendering via pure fractal
        $fractal = new Manager();
        $bookResource = new Collection($books, new BookTransformer);

        $booksArray = $fractal->createData($bookResource)->toArray();

        $expectedjson = json_encode($booksArray);
        static::assertEquals($expectedjson, $actualJson);
    }

    /**
     * @test
     */
    public function renderValueTest()
    {
        $value = 'foo';
        $configuration = [];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('value', $value);
        $this->view->setVariablesToRender(['value']);
        $actualJson = $this->view->render();

        static::assertEquals('["foo"]', $actualJson);
    }

    /**
     * @test
     */
    public function renderWithIncludeTest()
    {
        $book = new BookWithAuthorRelation(1, 'A Song of Ice and Fire', '1996');
        $book->author = new Author(1, 'George Raymond Richard Martin');

        $configuration = [
            'book' => BookWithAuthorRelationTransformer::class
        ];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->setIncludes('author');
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book']);
        $actualJson = $this->view->render();

        // rendering via pure fractal
        $bookResource = new Item($book, new BookWithAuthorRelationTransformer());
        $this->fractalManager->parseIncludes('author');
        $bookArray = $this->fractalManager->createData($bookResource)->toArray();

        $expectedjson = json_encode($bookArray);
        static::assertEquals($expectedjson, $actualJson);
    }

    /**
     * @test
     */
    public function renderWithoutIncludeTest()
    {
        $book = new BookWithAuthorRelation(1, 'A Song of Ice and Fire', '1996');
        $book->author = new Author(1, 'George Raymond Richard Martin');

        $configuration = [
            'book' => BookWithAuthorRelationTransformer::class
        ];

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book']);
        $actualJson = $this->view->render();

        // rendering via pure fractal
        $bookResource = new Item($book, new BookWithAuthorRelationTransformer());
        $bookArray = $this->fractalManager->createData($bookResource)->toArray();

        $expectedjson = json_encode($bookArray);
        static::assertEquals($expectedjson, $actualJson);
    }

    /**
     * @test
     */
    public function getTransformerThrowsExceptionWhenTransformerIsInvalidTest()
    {
        $book = new Book(1, 'A Song of Ice and Fire', '1996');
        $configuration = [
            'book' => InvalidTransformer::class
        ];

        $this->setExpectedException(
            'InvalidArgumentException',
            'Argument $transformerClassName should extend League\Fractal\TransformerAbstract'
        );

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book']);
        $this->view->render();
    }
}
