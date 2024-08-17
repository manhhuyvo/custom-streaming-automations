<?php 

namespace StreamingAutomations\Modules;

use Illuminate\Support\Arr;

class ModuleResponse
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    private string $status;
    private string $message;
    private array $data = [];
    private array $errors = [];

    public function __construct(
        string $status = self::STATUS_SUCCESS,
        string $message = '',
        array $data = [],
        array $errors = []
    )
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data ?? [];
        $this->errors = $errors ?? [];
    }

    public static function success(): self
    {
        return new self(self::STATUS_SUCCESS);
    }

    public static function error(): self
    {
        return new self(self::STATUS_ERROR);
    }

    public static function make(
        string $status = self::STATUS_SUCCESS,
        string $message = '',
        array $data = [],
        array $errors = []
    ): self
    {
        return new self($status, $message, $data, $errors);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(string $key = '')
    {
        if (empty($key)) {
            return $this->data;
        }

        $data = Arr::dot($this->data);

        return $data[$key] ?? '';
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function isSuccesful(): bool
    {
        return $this->getStatus() == self::STATUS_SUCCESS;
    }

    public function message(string $message = ''): self
    {
        $this->message = $message;

        return $this;
    }

    public function status(string $status = self::STATUS_SUCCESS): self
    {
        $this->status = $status;

        return $this;
    }

    public function data(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    public function errors(array $errors = []): self
    {
        $this->errors = $errors;

        return $this;
    }
}