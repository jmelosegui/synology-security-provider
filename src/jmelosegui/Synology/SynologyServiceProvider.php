<?php

namespace jmelosegui\Synology;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\ServiceProviderInterface;

use jmelosegui\Synology\Security\SynologyAuthenticationProvider;
use jmelosegui\Synology\Security\SynologyAuthenticationListener;

/**
 * Synology client secutiry library.
 *
 * @author Juan Manuel Elosegui <elosegui@gmail.com>
 */
class SynologyServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        $app['security.authentication_listener.factory.synology'] = $app->protect(function ($name, $options) use ($app) {

            $app['security.authentication_provider.'.$name.'.synology'] = $app->share(function ($options) use ($app) {
                return new SynologyAuthenticationProvider('synology', $options);
            });

            if (!isset($app['security.authentication_listener.'.$name.'.synology'])) {
                $app['security.authentication_listener.'.$name.'.synology'] = $app['security.authentication_listener.synology._proto']($name, $options);
            }

            return array(
                'security.authentication_provider.'.$name.'.synology',
                'security.authentication_listener.'.$name.'.synology',
                null, // the entry point id
                'pre_auth' // the position of the listener in the stack
            );
        });

        $app['security.authentication_listener.synology._proto'] = $app->protect(function($name, $options) use ($app) {
            return $app->share(function () use ($name, $options, $app) {

                if (!isset($app['security.authentication.success_handler.'.$name.'.synology'])) {
                    $app['security.authentication.success_handler.'.$name.'.synology'] = $app['security.authentication.success_handler._proto']($name, $options);
                }

                if (!isset($app['security.authentication.failure_handler.'.$name.'.synology'])) {
                    $app['security.authentication.failure_handler.'.$name.'.synology'] = $app['security.authentication.failure_handler._proto']($name, $options);
                }

                return new SynologyAuthenticationListener(
                    $app['security'],
                    $app['security.authentication_manager'],
                    $app['security.session_strategy'],
                    $app['security.http_utils'],
                    'synology',
                    $app['security.authentication.success_handler.'.$name.'.synology'],
                    $app['security.authentication.failure_handler.'.$name.'.synology'],
                    $options,
                    $app['logger'],
                    $app['dispatcher'],
                    isset($options['with_csrf']) && $options['with_csrf'] && isset($app['form.csrf_provider']) ? $app['form.csrf_provider'] : null);
            });
        });
    }

    public function boot(Application $app) {
        if (!isset($app['security'])) {
            throw new \LogicException('You must register the SecurityServiceProvider to use the SynologyServiceProvider');
        }
    }
}