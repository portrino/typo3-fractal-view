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
use Portrino\Typo3FractalView\Tests\Model\Book;
use Portrino\Typo3FractalView\Tests\Transformer\BookTransformer;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

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

    protected function setUp()
    {
        $this->view = $this->getMock(FractalView::class, ['getTransformer']);
        $this->view->expects($this->any())
            ->method('getTransformer')
            ->with(BookTransformer::class)
            ->willReturn(new BookTransformer());

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
    public function renderTest()
    {
        $book = new Book(1, 'Hogfather', '1998');
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
}
