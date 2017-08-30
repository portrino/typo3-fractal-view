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

use ArrayAccess;
use InvalidArgumentException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
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
     * @var string
     */
    protected $includes = '';

    /**
     * @var string
     */
    protected $excludes = '';

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
    public function injectFractalManager(Manager $fractalManager)
    {
        $this->fractalManager = $fractalManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Transforms the value view variable to a serializable
     * array representation using a YAML view configuration and JSON encodes
     * the result.
     *
     * @return string The JSON encoded variables
     * @api
     */
    public function render()
    {
        $this->setIncludesFromRequest();
        $this->setExcludesFromRequest();

        return parent::render();
    }

    /**
     * Sets the includes from magic GET param "_includes"
     *
     * @return void
     */
    protected function setIncludesFromRequest()
    {
        if ($this->controllerContext->getRequest()->hasArgument('_includes')) {
            $this->setIncludes($this->controllerContext->getRequest()->getArgument('_includes'));
        }
    }

    /**
     * Sets the excludes from magic GET param "_excludes"
     *
     * @return void
     */
    protected function setExcludesFromRequest()
    {
        if ($this->controllerContext->getRequest()->hasArgument('_excludes')) {
            $this->setIncludes($this->controllerContext->getRequest()->getArgument('_excludes'));
        }
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
        $result = [];

        $this->fractalManager->setSerializer(new ArraySerializer());
        $this->fractalManager->parseIncludes($this->includes);

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
                $result[$variableName] = isset($transformedObject) ? $transformedObject : '';
            }
        }
        // prevent data array key in result
        return $result;
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
        if (is_array($object) || $object instanceof ArrayAccess) {
            $transformer = $this->getTransformer($configuration[0]);
            $resource = new Collection($object, $transformer);
            $result = $this->fractalManager->createData($resource)->toArray();
        } elseif (is_object($object)) {
            $transformer = $this->getTransformer($configuration[0]);
            $resource = new Item($object, $transformer);
            $result = $this->fractalManager->createData($resource)->toArray();
        } else {
            $result = [$object];
        }
        return $result;
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

    /**
     * @param string $includes
     */
    public function setIncludes($includes)
    {
        $this->includes = $includes;
    }

    /**
     * @param string $excludes
     */
    public function setExcludes($excludes)
    {
        $this->excludes = $excludes;
    }
}
