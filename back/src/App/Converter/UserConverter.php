<?php

namespace App\Converter;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\UserRepository;

class UserConverter
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function convert($id)
    {
        $user = $this->userRepository->find($id);

        if (null === $user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }
}
