<?php

namespace App\Components\Feed;

use App\Model\WordpressPostReader;

class FeedFactory
{
    /**
     * @var WordpressPostReader
     */
    private $postReader;


    /**
     * FeedFactory constructor.
     * @param WordpressPostReader $postReader
     */
    public function __construct(WordpressPostReader $postReader)
    {
        $this->postReader = $postReader;
    }


    /**
     * @return FeedControl
     */
    public function create(): FeedControl
    {
        return new FeedControl($this->postReader);
    }
}
