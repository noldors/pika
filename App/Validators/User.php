<?php

declare(strict_types=1);

namespace App\Validators;

use App\Repositories\User as UserRepository;
use DateTime;
use Support\Exceptions\ValidationException;
use Support\Request\RequestInterface;

class User
{
    private const AVAILABLE_GENDERS = [0, 1, 2];

    private const USER_INPUT_REQUIRED_FIELDS = ['email', 'name', 'password', 'dob', 'gender'];

    /**
     * @var \App\Repositories\User
     */
    private $userRepository;

    public function __construct(UserRepository $repository)
    {
        $this->userRepository = $repository;
    }

    public function validateAuthRequest(RequestInterface $request): void
    {
        if (! $request->has('email')) {
            throw new ValidationException('Missing email!');
        }

        if (! $request->has('password')) {
            throw new ValidationException('Missing password!');
        }
    }

    public function validateUserInput(RequestInterface $request): void
    {
        $this->validateUserInputContainsRequiredFields($request);

        $this->isValidEmail($request->get('email'));
        $this->isValidName($request->get('name'));
        $this->isValidPassword($request->get('password'));
        $this->isValidGender((int) $request->get('gender'));
        $this->isValidDateOfBirth($request->get('dob'));

        if ($request->has('phone')) {
            $this->isValidPhone($request->get('phone'));
        }
    }

    public function validateOptionalUserInput(RequestInterface $request, int $currentUserId = 0): void
    {
        if ($request->has('email')) {
            $this->isValidEmail($request->get('email'), $currentUserId);
        }

        if ($request->has('name')) {
            $this->isValidName($request->get('name'), $currentUserId);
        }

        if ($request->has('password')) {
            $this->isValidPassword($request->get('password'));
        }

        if ($request->has('gender')) {
            $this->isValidGender((int) $request->get('gender'));
        }

        if ($request->has('dob')) {
            $this->isValidDateOfBirth($request->get('dob'));
        }

        if ($request->has('phone')) {
            $this->isValidPhone($request->get('phone'));
        }
    }

    private function validateUserInputContainsRequiredFields(RequestInterface $request): void
    {
        $diff = \array_diff(static::USER_INPUT_REQUIRED_FIELDS, $request->keys());

        if (\count($diff) > 0) {
            throw new ValidationException(\sprintf('Missing fields: [%s]', \implode(', ', $diff)));
        }
    }

    public function isValidEmail(string $email, int $currentUserId = 0): bool
    {
        if (\filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException('Seem`s user email is not an email!');
        }

        if ($this->userRepository->hasEmail($email, $currentUserId)) {
            throw new ValidationException('User with this email already exists!');
        }

        return true;
    }

    public function isValidName(string $name, int $currentUserId = 0): bool
    {
        if (\preg_match('/^[-.a-zа-яё0-9]+$/', $name) !== 1) {
            throw new ValidationException(
                'User name must contain only latin or russian characters, digits and . and -'
            );
        }

        if ($this->userRepository->hasName($name, $currentUserId)) {
            throw new ValidationException('User with this name already exists!');
        }

        return true;
    }

    public function isValidPassword(string $password): bool
    {
        $length = \mb_strlen($password);

        if ($length < 6 || $length > 255) {
            throw new ValidationException(
                'User password must have length greater or equal to 6 chars and less or equal to 255 chars!'
            );
        }

        return true;
    }

    public function isValidPhone(string $phone): bool
    {
        if (\preg_match('/^\+[0-9]{7,16}$/', $phone) !== 1) {
            throw new ValidationException('Seem`s that phone not in international phone number format!');
        }

        return true;
    }

    public function isValidGender(int $gender): bool
    {
        if (! \in_array($gender, static::AVAILABLE_GENDERS, true)) {
            throw new ValidationException(
                \sprintf('User gender must be one of [%s]', \implode(', ', static::AVAILABLE_GENDERS))
            );
        }

        return true;
    }

    public function isValidDateOfBirth(string $dateOfBirth): bool
    {
        if (! $this->isValidDate($dateOfBirth)) {
            throw new ValidationException('Seem`s that date of birth has wrong format!');
        }

        return true;
    }

    public function isValidDate(string $dateString, string $format = DateTime::ATOM): bool
    {
        $date = DateTime::createFromFormat($format, $dateString);

        return $date !== false && $date->format($format) === $dateString;
    }
}
