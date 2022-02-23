<?php

namespace App\Models\Storage;

use App\Exceptions\CredentialException;

class Credentials extends AccessesDisk
{
    /**
     * Returns the value for a specified credential.
     *
     * @param string $credentialKey
     *
     * @return null|string
     */
    public function get(string $credentialKey): ?string
    {
        return $this->contents->{$credentialKey} ?? null;
    }

    /**
     * Loads saved credentials. If none are stored, an initial set is used.
     */
    public function loadOrCreate(): void
    {
        $fileContents = (array) $this->getFileContents();
        $defaultCredentials = [
            'SPOTIFY_CLIENT_ID' => '',
            'SPOTIFY_CLIENT_SECRET' => '',
        ];

        $this->contents = (object) array_merge(
            $defaultCredentials,
            $fileContents
        );

        $this->confirmOrGet();
    }

    /**
     * Saves a new credential.
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): void
    {
        $this->contents->{$key} = $value;
        $this->save();
    }

    /**
     * Confirms that every credential has a value. If it does not, it will ask the user for input.
     */
    protected function confirmOrGet(): void
    {
        foreach ($this->contents as $credential => $value) {
            if ($value !== '') {
                continue;
            }

            echo "Please enter {$credential}: ";
            $this->contents->{$credential} = readline();
            $this->save();

            if ($this->contents->{$credential} === '') {
                throw new CredentialException($credential);
            }
        }
    }
}
