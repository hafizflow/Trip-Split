<?php

namespace App\Traits;

trait GeneratesCode
{
    /**
     * Generate a unique code for the model
     *
     * @param int $length
     * @return string
     */
    public function generateUniqueCode($length = 6)
    {
        do {
            $code = $this->generateRandomCode($length);
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * Generate a random alphanumeric code
     *
     * @param int $length
     * @return string
     */
    protected function generateRandomCode($length = 6)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Check if code already exists
     *
     * @param string $code
     * @return bool
     */
    protected function codeExists($code)
    {
        return static::where('code', $code)->exists();
    }
}
