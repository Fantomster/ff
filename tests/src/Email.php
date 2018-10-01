<?php
declare(strict_types=1);

//namespace app\tests\strict_typesrc;
/**
 * Class Email
 */
final class Email
{
    /**
     * @var string
     */
    private $email;

    /**
     * Email constructor.
     *
     * @param string $email
     * @throws \InvalidArgumentException
     */
    private function __construct(string $email)
    {
        $this->ensureIsValidEmail($email);

        $this->email = $email;
    }

    /**
     * @param string $email
     * @return Email
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @throws \InvalidArgumentException
     */
    private function ensureIsValidEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid email address',
                    $email
                )
            );
        }
    }
}