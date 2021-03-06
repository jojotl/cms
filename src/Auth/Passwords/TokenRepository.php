<?php

namespace Statamic\Auth\Passwords;

use Statamic\Facades\YAML;
use Illuminate\Support\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class TokenRepository extends DatabaseTokenRepository
{
    protected $files;
    protected $hasher;
    protected $hashKey;
    protected $expires;
    protected $path;

    public function __construct(Filesystem $files, HasherContract $hasher, $hashKey, $expires = 60, $throttle = 60)
    {
        $this->files = $files;
        $this->hasher = $hasher;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->throttle = $throttle;

        $this->path = storage_path('statamic/password_resets.yaml');
    }

    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();

        $token = $this->createNewToken();

        $this->insert($this->getPayload($email, $token));

        return $token;
    }

    protected function insert($payload)
    {
        $resets = $this->getResets();

        $resets[$payload['email']] = [
            'token' => $payload['token'],
            'created_at' => $payload['created_at']->timestamp
        ];

        $this->putResets($resets);
    }

    public function delete(CanResetPasswordContract $user)
    {
        $this->putResets(
            $this->getResets()->forget($user->email())
        );
    }

    public function deleteExpired()
    {
        $this->putResets($this->getResets()->reject(function ($item, $email) {
            return $this->tokenExpired($item['created_at']);
        }));
    }

    public function exists(CanResetPasswordContract $user, $token)
    {
        $record = $this->getResets()->get($user->email());

        return $record &&
            ! $this->tokenExpired(Carbon::createFromTimestamp($record['created_at']))
            && $this->hasher->check($token, $record['token']);
    }

    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = $this->getResets()->get($user->email());

        return $record && parent::tokenRecentlyCreated($record['created_at']);
    }

    protected function getResets()
    {
        if (! $this->files->exists($this->path)) {
            return collect();
        }

        return collect(YAML::parse($this->files->get($this->path)));
    }

    protected function putResets($resets)
    {
        $this->files->put($this->path, YAML::dump($resets->all()));
    }
}
