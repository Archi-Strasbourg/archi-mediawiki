<?php
namespace ArchiTweaks;

use ApiBase;
use User;

/**
 * Class ArchiCategoryTree
 * @package ArchiTweaks
 */
class RemoveAlerte extends ApiBase
{
    
    public function __construct($main, $action)
    {
        parent::__construct($main, $action);
    }
    
    
    public function execute()
    {
        $result = $this->getResult();

        $params = $this->extractRequestParams();
        $user =  User::newFromName($params['user']);
        if ($params['cancel']) {
            $user->removeGroup("noAlerteMail");
            $result->addValue(null, $this->getModuleName(), ['status' => 'ok', 'user' => $params['user'], 'action' => 'remove']);
        } else {
            $user->addGroup("noAlerteMail");
            $result->addValue(null, $this->getModuleName(), ['status' => 'ok', 'user' => $params['user'], 'action' => 'add']);
        }


    }

    public function getAllowedParams()
    {
        return [
            'user' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ],
            'cancel' => [
                ApiBase::PARAM_TYPE => 'boolean',
                ApiBase::PARAM_REQUIRED => false,
            ],
        ];
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
        return "permet d'exclure quelqu'un de l'alerte mail";
    }

    /**
     * @return array
     */
    protected function getExamples()
    {
        return [
            'action=RemoveAlerte&user=Bobby',
        ];
    }
}