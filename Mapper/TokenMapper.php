<?php declare(strict_types=1);

require_once 'Model/Token.php';

class TokenMapper
{
    /**
     * @param array $map
     * @return Token
     */
    public function fromArray(array $map): Token
    {
        return new Token(
            (int) $map['id'],
            (int) $map['userId'],
            $map['token'],
            (int) $map['expireAt']
        );
    }

    /**
     * @param Token $token
     * @return array
     */
    public function toArray(Token $token): array
    {
        return [
            'id' => $token->getId(),
            'userId' => $token->getUserId(),
            'token' => $token->getToken(),
            'expireAt' => $token->getExpireAt(),
        ];
    }
}
