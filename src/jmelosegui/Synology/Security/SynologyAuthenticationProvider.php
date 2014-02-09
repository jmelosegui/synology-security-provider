<?php

namespace jmelosegui\Synology\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Authentication provider handling Synology Authentication responses.
 *
 * @author Juan Manuel Elosegui <elosegui@gmail.com>
 */
class SynologyAuthenticationProvider implements AuthenticationProviderInterface{

    private $providerKey;

    public function __construct($providerKey)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->providerKey = $providerKey;
    }

    public function authenticate(TokenInterface $token)
    {
        if($this->ValidateToken($token) === true){
            $authToken = new UsernamePasswordToken($token->getUsername(), $token->getCredentials(), 'custom', array(AuthenticatedVoter::IS_AUTHENTICATED_FULLY));
            return $authToken;
        }

        throw new BadCredentialsException('Wrong userName or password');
    }

    public function supports(TokenInterface $token)
    {
        return ($token instanceof UsernamePasswordToken) && $this->providerKey === $token->getProviderKey();
    }

    protected function ValidateToken(TokenInterface $token)
    {
        //this do the trick. Use localhost since this code run inside the Synology Server.
        //If you want to debug in your dev pc change localhost to your synology dns or ip.

        //TODO: get the following variables somewhere in the configuration system;
        $server = '192.168.1.2';
        $port = '5000';

        $url = 'http://' . $server . ':' . $port . '/webman/login.cgi?username='.$token->getUsername().'&passwd='.$token->getCredentials();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        if(preg_match('#\"success\" \: true#',$data)){
            return true;
        }
        else{
            return false;
        }
    }
}