<?php

namespace Portrino\Typo3FractalView\Mvc\View;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use InvalidArgumentException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class FractalView
 * @package Portrino\Typo3FractalView\Mvc\View
 */
class FractalView extends JsonView
{
    /**
     * @var \League\Fractal\Manager
     * @inject
     */
    protected $fractalManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * The rendering configuration for this Fractal view which
     * to determine which Transformer should render each variable / object
     *
     * The configuration array must have the following structure:
     *
     * Example 1:
     *
     * array(
     *      'book' => \Acme\Bar\Fractal\Transformer\BookTransformer::class
     *      'author' => '\Acme\Bar\Fractal\Transformer\AuthorTransformer'
     * )
     */
    protected $configuration = [];

    /**
     * @param Manager $fractalManager
     */
    public function injectFractalManager($fractalManager)
    {
        $this->fractalManager = $fractalManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Loads the configuration and transforms the value to a serializable
     * array via fractal
     *
     * @return array An array containing the values, ready to be JSON encoded
     * @api
     */
    protected function renderArray()
    {
        $result = [
            'data' => []
        ];

        sort($this->variablesToRender);

        if (count($this->variablesToRender) === 1) {
            $variableName = current($this->variablesToRender);
            $valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
            $configuration = isset($this->configuration[$variableName]) ? $this->configuration[$variableName] : '';
            $result = $this->transformObject($valueToRender, [0 => $configuration]);
        } else {
            foreach ($this->variablesToRender as $variableName) {
                $valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
                $configuration = isset($this->configuration[$variableName]) ? $this->configuration[$variableName] : '';
                $transformedObject = $this->transformObject($valueToRender, [0 => $configuration]);
                $result['data'][$variableName] = isset($transformedObject['data']) ? $transformedObject['data'] : '';
            }
        }
        // prevent data array key in result
        return $result['data'];
    }

    /**
     * Traverses the given object structure in order to transform it into an
     * array structure.
     *
     * @param object $object Object to traverse
     * @param array $configuration Configuration for transforming the given object or NULL
     * @return array Object structure as an array
     * @throws InvalidArgumentException
     */
    protected function transformObject($object, array $configuration)
    {
        $transformer = $this->getTransformer($configuration[0]);
        $resource = new Item($object, $transformer);
        return $this->fractalManager->createData($resource)->toArray();
    }

    /**
     * @param string $transformerClassName
     * @return TransformerAbstract
     * @throws InvalidArgumentException
     */
    protected function getTransformer($transformerClassName)
    {
        /** @var TransformerAbstract $result */
        $result = $this->objectManager->get($transformerClassName);

        if ($result instanceof TransformerAbstract === false) {
            throw new InvalidArgumentException(
                'Argument $transformerClassName should extend League\Fractal\TransformerAbstractq'
            );
        }

        return $result;
    }
}
