<?php

class HttpError
{
    private int $code;
    private string $message;
    private ?string $details;

    public function __construct(int $code, string $message, string $details = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->details = $details;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }
}