<?php

namespace BeberAgua\API\Infrastructure\Security;

use Firebase\JWT\JWT;

class Authorization
{
    private $key;
    private $user_id;
    private $issuer_claim;
    private $audience_claim;
    private $issuedat_claim;
    private $notbefore_claim;
    private $expire_claim;

    public function __construct(int $secondsToExpire)
    {
        $this->key = "&vfDr9hje6Xa8euk^Ben";
        $this->issuer_claim = "http://example.org";
        $this->audience_claim = "http://example.com";
        $this->issuedat_claim = time();
        $this->notbefore_claim = $this->issuedat_claim + 10;
        $this->expire_claim = $this->issuedat_claim + $secondsToExpire;
    }

    public function setUserId(int $id)
    {
        $this->user_id = $id;
    }

    private function payload(): array
    {
        return array(
            "iss" => $this->issuer_claim,
            "aud" => $this->audience_claim,
            "iat" => $this->issuedat_claim,
            "nbf" => $this->notbefore_claim,
            "exp" => $this->expire_claim,
            "user_id" => $this->user_id,
        );
    }

    public function token()
    {
        return JWT::encode($this->payload(), $this->key);
    }

    public function decode($token)
    {
        try {
            return JWT::decode($token, $this->key, array('HS256'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function tokenFromHeaders()
    {
        $authHeader = apache_request_headers()["Authorization"];
        $token = explode(" ", $authHeader);
        return $token[1];
    }

}
