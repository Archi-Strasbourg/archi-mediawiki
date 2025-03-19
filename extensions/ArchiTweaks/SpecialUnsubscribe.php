<?php

namespace ArchiTweaks;

use MediaWiki\MediaWikiServices;
use MWException;
use SpecialPage;
use User;

class SpecialUnsubscribe extends SpecialPage
{

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('Unsubscribe');
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    private static function hmacBase64(string $data, string $key): string
    {
        $hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));

        return str_replace(['+', '/', '='], ['-', '_', ''], $hmac);
    }

    /**
     * @param User $user
     * @return string
     */
    public static function getHash(User $user): string
    {
        $services = MediaWikiServices::getInstance();
        $data = $user->getName();
        $data .= ':' . $user->getEmailAuthenticationTimestamp();
        $data .= ':' . $user->getId();
        $data .= ':' . $user->getEmail();

        return self::hmacBase64($data, $services->getMainConfig()->get('HashSalt'));
    }

    /**
     * {@inheritdoc}
     * @throws MWException
     */
    public function execute($subPage): void
    {
        parent::execute($subPage);

        $services = MediaWikiServices::getInstance();
        $output = $this->getOutput();

        $query = $this->getRequest()->getQueryValues();
        $user = $services->getUserFactory()->newFromName($query['user']);

        if ($user->isAnon()) {
            // Action valable que pour les utilisateurs connectés.
            $this->displayRestrictionError();
        }

        if (!hash_equals($this->getHash($user), $query['hash'])) {
            // Le hash doit être valide pour cet utilisateur.
            $this->displayRestrictionError();
        }

        $userOptionsManager = $services->getUserOptionsManager();
        if ($userOptionsManager->getOption($user, 'disablemail')) {
            $output->addWikiTextAsInterface("Vous êtes déjà désinscrit de l'alerte mail.");
        } else {
            $userOptionsManager->setOption($user, 'disablemail', TRUE);
            $user->saveSettings();
            $output->addWikiTextAsInterface("Vous avez été désinscrit de l'alerte mail.");
        }
    }

}
