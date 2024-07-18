<?php
namespace ArchiTweaks;

use ApiQueryBase;
use CategoryBreadcrumb\CategoryBreadcrumb;
use Title;

/**
 * Class ArchiCategoryTree
 * @package ArchiTweaks
 */
class ArchiCategoryTree extends ApiQueryBase
{
    private static function getCategoryTree(Title $title)
    {
        if ($title->getNamespace() == NS_ADDRESS_NEWS) {
            $title = Title::newFromText($title->getText(), NS_ADDRESS);
        }
        $parenttree = $title->getParentCategoryTree();
        CategoryBreadcrumb::checkParentCategory($parenttree);
        CategoryBreadcrumb::checkTree($parenttree);
        $flatTree = CategoryBreadcrumb::getFlatTree($parenttree);
        return $flatTree;
    }

    /* private function apiRequest($options)
    {
        $params = new DerivativeRequest(
            $this->getRequest(),
            $options
        );
        $api = new \ApiMain($params);

        $mobileContext = MediaWikiServices::getInstance()->getService('MobileFrontend.Context');

       
        $context = new DerivativeContext($mobileContext->getContext());
        $context->setRequest(new DerivativeRequest($context->getRequest(), $options));
        $mobileContext->setContext($context);

        $api->execute();

        return $api->getResult()->getResultData();
    } */

    
    public function execute()
    {
        $result = $this->getResult();

        foreach ($this->getPageSet()->getGoodTitles() as $id => $title) {

            $result->addValue(
                [
                    'query',
                    'pages',
                    $id,
                ],
                'test',
                self::getCategoryTree($title)
            );
        }

    }

    

    /**
     * @return bool
     */
    public function isInternal()
    {
        return TRUE;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return "permet de récupérer l'arborescence de catégories d'une page";
    }

    /**
     * @return array
     */
    protected function getExamples()
    {
        return [
            'action=query&prop=archiCategoryTree&titles=Adresse:14 Avenue de la Marseillaise (Strasbourg)',
        ];
    }
}