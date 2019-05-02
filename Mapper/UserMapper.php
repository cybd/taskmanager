<?php declare(strict_types=1);

require_once 'Model/User.php';

class UserMapper
{
    /**
     * @param array $map
     * @return User
     */
    public function fromArray(array $map): User
    {
        return new User(
            (int) $map['id'],
            $map['email'],
            $map['password']
        );
    }

    /**
     * @param User $user
     * @return array
     */
    public function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'token' => $user->getPassword(),
        ];
    }
}