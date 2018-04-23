<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

/**
 * Matches exception attributes against exception instances.
 */
final class ExceptionDescriptor
{
    private $type;

    private $message;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function matches(\Exception $exception): bool
    {
        if (!is_a($exception, $this->type)) {
            return false;
        }

        if ($this->message !== null && $exception->getMessage() !== $this->message) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
