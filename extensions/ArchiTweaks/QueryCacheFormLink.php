<?php

namespace ArchiTweaks;

use Html;
use Parser;
use PFQueryFormLink;
use PFUtils;

class QueryCacheFormLink extends PFQueryFormLink
{

    /**
     * @param Parser $parser
     * @return array
     */
    public static function run(Parser $parser): array
    {
        /*
         * Obligé de dupliquer une partie du code
         * parce qu'on ne peut pas juste passer le nom
         * de la page spéciale en paramètre.
         */
        $params = func_get_args();
        array_shift($params);
        $str = self::createFormLink($parser, $params);
        return [$str, 'noparse' => true, 'isHTML' => true];
    }

    /**
     * @param Parser $parser
     * @param $params
     * @return string|null
     */
    protected static function createFormLink(Parser $parser, $params): ?string
    {
        $inFormName = '';
        $inLinkStr = wfMessage('runquery')->parse();
        $classStr = '';
        $inQueryArr = [];
        $targetWindow = '_self';

        foreach ($params as $param) {
            $elements = explode('=', $param, 2);

            if (count($elements) > 1) {
                $param_name = trim($elements[0]);
                $value = trim($parser->recursiveTagParse($elements[1]));
            } else {
                $param_name = null;
                $value = trim($parser->recursiveTagParse($param));
            }

            if ($param_name == 'form') {
                $inFormName = $value;
            } elseif ($param_name == 'link text') {
                $inLinkStr = $value;
            } elseif ($param_name !== null) {
                $value = urlencode($value);
                parse_str("$param_name=$value", $arr);
                $inQueryArr = PFUtils::arrayMergeRecursiveDistinct($inQueryArr, $arr);
            }
        }

        $formSpecialPage = PFUtils::getSpecialPage('RunQueryCache');
        $formSpecialPageTitle = $formSpecialPage->getPageTitle();

        $link_url = $formSpecialPageTitle->getLocalURL() . "/$inFormName";
        $link_url = str_replace(' ', '_', $link_url);

        if (!empty($inQueryArr)) {
            $link_url .= (strstr($link_url, '?')) ? '&' : '?';
            $link_url .= str_replace('+', '%20', http_build_query($inQueryArr, '', '&'));
        }

        if (!empty($inTargetName)) {
            if ($inLinkStr == '') {
                $inLinkStr = $inTargetName;
            }
        }
        return Html::rawElement('a', ['href' => $link_url, 'class' => $classStr, 'target' => $targetWindow], $inLinkStr);
    }
}
