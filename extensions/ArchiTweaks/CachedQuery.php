<?php

namespace ArchiTweaks;

use OutputPage;

class CachedQuery
{

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $html;

    /**
     * @var string[]
     */
    private array $head;

    /**
     * @var array
     */
    private array $jsconfig;

    /**
     * @var string[]
     */
    private array $modules;

    /**
     * @param string $title
     * @param string $html
     * @param string[] $head
     * @param array $jsconfig
     * @param array $modules
     */
    public function __construct(string $title, string $html, array $head, array $jsconfig, array $modules)
    {
        $this->title = $title;
        $this->html = $html;
        $this->head = $head;
        $this->jsconfig = $jsconfig;
        $this->modules = $modules;
    }

    /**
     * @param OutputPage $output
     * @return void
     */
    public function populateOutput(OutputPage $output)
    {
        $output->addHTML($this->html);
        $output->setPageTitle($this->title);
        $output->addHeadItems($this->head);
        $output->addHeadItems($this->head);
        $output->addJsConfigVars($this->jsconfig);
        $output->addModules($this->modules);
    }

}
