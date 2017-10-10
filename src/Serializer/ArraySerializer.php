<?php
namespace Portrino\Typo3FractalView\Serializer;

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

/**
 * Class ArraySerializer
 * @package Portrino\Typo3FractalView\Serializer
 */
class ArraySerializer extends \League\Fractal\Serializer\ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return ($resourceKey && $resourceKey !== 'data') ? [$resourceKey => $data] : $data;
    }
}
