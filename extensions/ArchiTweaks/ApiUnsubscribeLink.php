<?php

namespace ArchiTweaks;

use ApiQueryBase;
use ApiUsageException;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

class ApiUnsubscribeLink extends ApiQueryBase
{

    /**
     * {@inheritdoc}
     * @throws ApiUsageException
     */
    public function execute(): void
    {
        $services = MediaWikiServices::getInstance();
        $urlUtils = $services->getUrlUtils();
        $result = $this->getResult();
        $params = $this->extractRequestParams();

        if (!$services->getPermissionManager()->userHasRight($this->getUser(), 'unsubscribe-link')) {
            $this->dieWithError('permissiondenied', 'permissiondenied');
        }

        $user = $services->getUserFactory()->newFromName($params['user']);
        if ($user->isAnon()) {
            $this->dieWithError('permissiondenied', 'permissiondenied');
        }

        $page = $services->getSpecialPageFactory()->getPage('Unsubscribe');
        $urlParts = $urlUtils->parse($page->getPageTitle()->getFullURL());
        $query = wfCgiToArray($urlParts['query']);
        $query['hash'] = SpecialUnsubscribe::getHash($user);
        $query['user'] = $user->getName();
        $urlParts['query'] = wfArrayToCgi($query);

        $result->addValue([], 'url', $urlUtils->assemble($urlParts));
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllowedParams(): array
    {
        return array_merge(
            parent::getAllowedParams(),
            ['user' => [
                ParamValidator::PARAM_TYPE => 'string',
                ParamValidator::PARAM_REQUIRED => TRUE
            ]]
        );
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return TRUE;
    }

}
