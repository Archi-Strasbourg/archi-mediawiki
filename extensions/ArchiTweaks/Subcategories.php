<?php

namespace ArchiTweaks;

use Category;
use Parser;
use Title;

class Subcategories
{

    /**
     * @param Parser $parser
     * @param string $parent
     * @return string
     * @noinspection PhpUnusedParameterInspection
     */
    public static function render(Parser $parser, string $parent): string
    {
        $result = [];
        $category = Category::newFromName($parent);

        /** @var Title $member */
        foreach ($category->getMembers() as $member) {
            $result[] = $member->getText();
        }

        return implode(',', $result);
    }

}
