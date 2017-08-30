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
use League\Fractal\Resource\Item;
use Portrino\Typo3FractalView\Mvc\View\FractalView;
use Portrino\Typo3FractalView\Tests\Model\Author;
use Portrino\Typo3FractalView\Tests\Model\Book;
use Portrino\Typo3FractalView\Tests\Transformer\AuthorTransformer;
use Portrino\Typo3FractalView\Tests\Transformer\BookTransformer;
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
        $this->view = new FractalView();

        $this->objectManager = $this->getMock(
            ObjectManagerInterface::class
        );
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [BookTransformer::class, new BookTransformer()],
                [AuthorTransformer::class, new AuthorTransformer()],
                [InvalidTransformer::class, new InvalidTransformer()]
            ]);
        $this->view->injectObjectManager($this->objectManager);

        $this->fractalManager = new Manager();
        $this->view->injectFractalManager($this->fractalManager);

        $this->controllerContext = $this->getMock(
            ControllerContext::class
        );
        $this->response = $this->getMock(
            Response::class
        );
        $this->controllerContext
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));

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
        $fractal = new Manager();
        $resource = new Item($book, new BookTransformer);
        $expectedjson = json_encode($fractal->createData($resource)->toArray()['data']);

        $this->assertEquals($expectedjson, $actualJson);
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
        $fractal = new Manager();
        $bookResource = new Item($book, new BookTransformer);
        $authorResource = new Item($author, new AuthorTransformer());

        $bookArray = $fractal->createData($bookResource)->toArray()['data'];
        $authorArray = $fractal->createData($authorResource)->toArray()['data'];

        $expectedjson = json_encode(['author' => $authorArray, 'book' => $bookArray]);
        $this->assertEquals($expectedjson, $actualJson);
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

        $this->setExpectedException('InvalidArgumentException', 'Argument $transformerClassName should extend League\Fractal\TransformerAbstract');

        // rendering via fractal view
        $this->view->setConfiguration($configuration);
        $this->view->assign('book', $book);
        $this->view->setVariablesToRender(['book']);
        $this->view->render();
    }
}
