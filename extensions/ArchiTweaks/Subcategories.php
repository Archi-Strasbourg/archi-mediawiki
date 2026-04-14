<?php

namespace ArchiTweaks;

use MediaWiki\Category\Category;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;

class Subcategories
{

    /**
     * @param Parser $parser
     * @param string $parent
     * @return string
     * @noinspection PhpUnusedParameterInspection
     */
    public static function render(Parser $parser, string $parent): string {
        $result = [];
        $category = Category::newFromName($parent);
        $pageProps = MediaWikiServices::getInstance()->getPageProps();

        foreach ($category->getMembers() as $member) {
            if ($member->getNamespace() != NS_CATEGORY) {
                continue;
            }
            $properties = $pageProps->getProperties($member, 'hiddencat');
            if (!empty($properties)) {
                continue;
            }

            $result[] = $member->getText();
        }

        return implode(',', $result);
    }

}
