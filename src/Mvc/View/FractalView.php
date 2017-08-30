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
use DateTime;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;

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
    protected $fractal;

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
     * Loads the configuration and transforms the value to a serializable
     * array via fractal
     *
     * @return array An array containing the values, ready to be JSON encoded
     * @api
     */
    protected function renderArray()
    {
        if (count($this->variablesToRender) === 1) {
            $variableName = current($this->variablesToRender);
            $valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
            $configuration = isset($this->configuration[$variableName]) ? $this->configuration[$variableName] : [];
        } else {
            $valueToRender = [];
            foreach ($this->variablesToRender as $variableName) {
                $valueToRender[$variableName] = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;


            }
        }

        return $this->transformValue($valueToRender, $configuration);
    }

    /**
     * Transforms a value depending on type recursively using the
     * supplied configuration.
     *
     * @param mixed $value The value to transform
     * @param array $configuration Configuration for transforming the value
     * @return array The transformed value
     */
    protected function transformValue($value, array $configuration)
    {
        if (is_array($value) || $value instanceof ArrayAccess) {
            $array = [];
            foreach ($value as $key => $element) {
                $array[$key] = $this->transformObject($value, isset($configuration[$key]) ? $configuration[$key] : []);
            }
            return $array;
        } elseif (is_object($value)) {
            return $this->transformObject($value, $configuration);
        } else {
            return $value;
        }
    }

    /**
     * Traverses the given object structure in order to transform it into an
     * array structure.
     *
     * @param object $object Object to traverse
     * @param array $configuration Configuration for transforming the given object or NULL
     * @return array Object structure as an array
     */
    protected function transformObject($object, array $configuration)
    {
        if ($object instanceof DateTime) {
            return $object->format(DateTime::ISO8601);
        } else {

            $resource = new Fractal\Resource\Item($book, new BookTransformer);


            return $propertiesToRender;
        }
    }
}
