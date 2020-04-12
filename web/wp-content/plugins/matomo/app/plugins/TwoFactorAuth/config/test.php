<?php

return array(
    'Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretRandomGenerator' => DI\object('Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretStaticGenerator'),
    'Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeRandomGenerator' => DI\object('Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeStaticGenerator'),
    'Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication $previous */

        if (!\Piwik\SettingsPiwik::isPiwikInstalled()) {
            return $previous;
        }

        $fakeCorrectAuthCode = \Piwik\Container\StaticContainer::get('test.vars.fakeCorrectAuthCode');
        if (!empty($fakeCorrectAuthCode) && !\Piwik\Common::isPhpCliMode()) {
            $staticSecret = new \Piwik\Plugins\TwoFactorAuth\Dao\TwoFaSecretStaticGenerator();
            $secret = $staticSecret->generateSecret();

            require_once PIWIK_DOCUMENT_ROOT . '/libs/Authenticator/TwoFactorAuthenticator.php';
            $authenticator = new \TwoFactorAuthenticator();
            $_GET['authcode'] = $authenticator->getCode($secret);
            $_GET['authCode'] = $_GET['authcode'];
            $_POST['authCode'] = $_GET['authcode'];
            $_POST['authcode'] = $_GET['authcode'];
            $_REQUEST['authcode'] = $_GET['authcode'];
            $_REQUEST['authCode'] = $_GET['authcode'];
        }

        return $previous;
    }),
    'Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeDao $previous */

        if (!\Piwik\SettingsPiwik::isPiwikInstalled()) {
            return $previous;
        }

        $restoreCodes = \Piwik\Container\StaticContainer::get('test.vars.restoreRecoveryCodes');
        if (!empty($restoreCodes)) {
            // we ensure this recovery code always works for those users
            foreach (array('with2FA', 'with2FADisable') as $user) {
                $previous->useRecoveryCode($user, '123456'); // we are using it first to make sure there is no duplicate
                $previous->insertRecoveryCode($user, '123456');
            }
        }

        return $previous;
    }),
    'Piwik\Plugins\TwoFactorAuth\SystemSettings' => DI\decorate(function ($previous) {
        /** @var Piwik\Plugins\TwoFactorAuth\SystemSettings $previous */
        if (!\Piwik\SettingsPiwik::isPiwikInstalled()) {
            return $previous;
        }

        Piwik\Access::doAsSuperUser(function () use ($previous) {
            $requireTwoFa = \Piwik\Container\StaticContainer::get('test.vars.requireTwoFa');
            if (!empty($requireTwoFa)) {
                $previous->twoFactorAuthRequired->setValue(1);
            } else {
                try {
                    $previous->twoFactorAuthRequired->setValue(0);
                } catch (Exception $e) {
                    // may fail when matomo is trying to update or so
                }
            }
        });

        return $previous;
    })
);
