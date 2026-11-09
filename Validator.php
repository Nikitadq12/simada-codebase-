<?php
/**
 * Class Validator
 * Menampung aturan validasi input form dan pesan error per kolom.
 */
class Validator
{
    private array $errors = [];

    public function required(string $field, $value, string $label): self
    {
        if (trim((string)$value) === '') {
            $this->errors[$field] = "$label wajib diisi.";
        }
        return $this;
    }

    public function email(string $field, $value): self
    {
        if (!isset($this->errors[$field]) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Format email tidak valid.';
        }
        return $this;
    }

    public function minLength(string $field, $value, int $min, string $label): self
    {
        if (!isset($this->errors[$field]) && strlen((string)$value) < $min) {
            $this->errors[$field] = "$label minimal $min karakter.";
        }
        return $this;
    }

    public function date(string $field, $value, string $label): self
    {
        if (!isset($this->errors[$field])) {
            $d = DateTime::createFromFormat('Y-m-d', (string)$value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $this->errors[$field] = "$label tidak valid.";
            }
        }
        return $this;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function fails(): bool  { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}
