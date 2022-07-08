<?php

namespace ArchiTweaks;

class CachedQuery
{

    /**
     * @var string
     */
    public string $title;

    /**
     * @var string
     */
    public string $html;

    /**
     * @var string[]
     */
    public array $head;

    /**
     * @param string $title
     * @param string $html
     * @param string[] $head
     */
    public function __construct(string $title, string $html, array $head)
    {
        $this->title = $title;
        $this->html = $html;
        $this->head = $head;
    }

}
