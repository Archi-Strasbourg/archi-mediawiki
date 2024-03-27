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
            if ($member->getNamespace() == NS_CATEGORY) {
                $result[] = $member->getText();
            }
        }

        return implode(',', $result);
    }

}
