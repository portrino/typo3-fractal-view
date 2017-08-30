<?php
namespace Portrino\Typo3FractalView\Tests\Model;

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
 * Class BookWithAuthorRelation
 * @package Portrino\Typo3FractalView\Tests\Model
 */
class BookWithAuthorRelation
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $yr;

    /**
     * @var Author
     */
    public $author;

    /**
     * Book constructor.
     * @param int $id
     * @param string $title
     * @param int $yr
     */
    public function __construct($id, $title, $yr)
    {
        $this->id = $id;
        $this->title = $title;
        $this->yr = $yr;
    }
}
