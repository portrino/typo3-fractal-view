# TYPO3 Fractal View

[![Build Status](https://travis-ci.org/portrino/typo3-fractal-view.svg?branch=master)](https://travis-ci.org/portrino/typo3-fractal-view)
[![Code Climate](https://codeclimate.com/github/portrino/typo3-fractal-view/badges/gpa.svg)](https://codeclimate.com/github/portrino/typo3-fractal-view)
[![Test Coverage](https://codeclimate.com/github/portrino/typo3-fractal-view/badges/coverage.svg)](https://codeclimate.com/github/portrino/typo3-fractal-view/coverage)
[![Issue Count](https://codeclimate.com/github/portrino/typo3-fractal-view/badges/issue_count.svg)](https://codeclimate.com/github/portrino/typo3-fractal-view)
[![Latest Stable Version](https://poser.pugx.org/portrino/typo3-fractal-view/version)](https://packagist.org/packages/portrino/typo3-fractal-view)
[![Total Downloads](https://poser.pugx.org/portrino/typo3-fractal-view/downloads)](https://packagist.org/packages/portrino/typo3-fractal-view)

Integrates the fractal package (https://fractal.thephpleague.com/) into TYPO3 Extbase

## Installation

You need to add the repository into your composer.json file

```bash
    composer require portrino/typo3-fractal-view
```

### Extbase view

You can prepend `?tx_par_pi1[format]=json` to your action controller request and extbase 
renders the corresponding view for you. By putting the FractalView class into the `$viewFormatToObjectNameMap` or 
`$defaultViewObjectName` extbase is able to get the correct view class for your request. 

```php
use Portrino\Typo3FractalView\Mvc\View\FractalView;
use Foo\Bar\Transformer\BookingTransformer;

class BookingController
{
    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'json' => FractalView::class
    ];
    
    /**
     * @var string
     */
    protected $defaultViewObjectName = FractalView::class;
    
    /**
     * Action Show
     *
     * @param \Foo\Bar\Domain\Model\Booking $booking
     *
     * @return void
     */
    public function showAction($booking)
    {
        $this->view->assign('booking', $booking);
        $view->setConfiguration([
            'booking' => BookingTransformer::class
        ]);
        $this->view->setVariablesToRender(['booking']);
    }
    
}

```

The only thing you have to do is implement the `BookingTransformer` class by your own like described here: https://fractal.thephpleague.com/transformers/

Example:

```php
namespace Foo\Bar\Transformer;

use Foo\Bar\Domain\Model\Booking;

class BookingTransformer
{
   /**
     * @param Booking $booking
     * @return array
     */
    public function transform(Booking $booking)
    {
        return [
            'uid' => $booking->getUid(),
            'start' => $booking->getStart()->format(DateTime::ISO8601),
            'end' => $booking->getEnd()->format(DateTime::ISO8601)
        ];
    }
}

```

## Authors

![](https://avatars0.githubusercontent.com/u/726519?s=40&v=4)
![](https://avatars2.githubusercontent.com/u/328502?s=40&v=4)

* **André Wuttig** - *Initial work, Unit Tests* - [aWuttig](https://github.com/aWuttig)
* **Christian Deussen** - *Bugfixes* - [nullsub](https://github.com/nullsub)

See also the list of [contributors](https://github.com/portrino/typo3-fractal-view/graphs/contributors) who participated in this project.
