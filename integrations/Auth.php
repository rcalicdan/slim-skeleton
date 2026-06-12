<?php

declare(strict_types=1);

namespace Integrations;

use Hibla\QueryBuilder\DB;
use Hibla\Promise\Interfaces\PromiseInterface;
use Hibla\Promise\Promise;

use function Rcalicdan\ConfigLoader\config;

class Auth
{
    /**
     * Retrieve the authenticated user's ID from the session, decrypting it safely.
     */
    public static function id(): mixed
    {
        $sessionKey = (string) config('auth.session_key', 'auth_user_id');
        $encryptedId = session($sessionKey);

        if ($encryptedId === null) {
            return null;
        }

        return Crypt::decrypt((string) $encryptedId);
    }

    /**
     * Check if the current user is authenticated.
     */
    public static function check(): bool
    {
        return self::id() !== null;
    }

    /**
     * Check if the current user is a guest.
     */
    public static function guest(): bool
    {
        return ! self::check();
    }

    /**
     * Retrieve a fresh copy of the authenticated user from the database.
     *
     * @return PromiseInterface<object|null>
     */
    public static function user(): PromiseInterface
    {
        $id = self::id();

        if ($id === null) {
            return Promise::resolved(null);
        }

        $table = (string) config('auth.table', 'users');
        $primaryKey = (string) config('auth.primary_key', 'id');

        return DB::table($table)->where($primaryKey, $id)->first();
    }

    /**
     * Log a user into the session by encrypting their primary key.
     */
    public static function login(mixed $userId): void
    {
        $sessionKey = (string) config('auth.session_key', 'auth_user_id');

        $encryptedId = Crypt::encrypt($userId);

        session()->set($sessionKey, $encryptedId);
    }

    /**
     * Log the user out of the session.
     */
    public static function logout(): void
    {
        $sessionKey = (string) config('auth.session_key', 'auth_user_id');
        session()->delete($sessionKey);
    }
}
