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
        if ($query['action'] == 'enable') {
            $userOptionsManager->setOption($user, 'disablemail', FALSE);
            $user->saveSettings();
            $output->addWikiTextAsInterface('Votre mail ' . $user->getEmail() . ' (utilisateur&nbsp;: ' . $user->getName() . ") a été réinscrit à l'alerte mail.");

            $unsubUrl = $services->getUrlUtils()->assemble(['query' => wfArrayToCgi(['hash' => $query['hash'], 'user' => $query['user']])]);
            $output->addHTML('<a class="mw-ui-button" href="' . $unsubUrl . '">Désabonnement</a>');
        } else {
            if ($userOptionsManager->getOption($user, 'disablemail')) {
                $output->addWikiTextAsInterface('Votre mail ' . $user->getEmail() . ' (utilisateur&nbsp;: ' . $user->getName() . ") est déjà désinscrit de l'alerte mail.");
            } else {
                $userOptionsManager->setOption($user, 'disablemail', TRUE);
                $user->saveSettings();
                $output->addWikiTextAsInterface('Votre mail ' . $user->getEmail() . ' (utilisateur&nbsp;: ' . $user->getName() . ") a été désinscrit de l'alerte mail.");
            }

            $resubUrl = $services->getUrlUtils()->assemble(['query' => wfArrayToCgi(['hash' => $query['hash'], 'user' => $query['user'], 'action' => 'enable'])]);
            $output->addHTML('<a class="mw-ui-button" href="' . $resubUrl . '">Se réabonner</a>');
        }
    }

}
