<?php

declare(strict_types=1);

namespace Integrations\Session;

use Hibla\QueryBuilder\Interfaces\DatabaseConnectionInterface;
use SessionHandlerInterface;

use function Hibla\await;
use function Rcalicdan\ConfigLoader\config;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private readonly DatabaseConnectionInterface $db)
    {
    }

    /**
     * @inheritDoc
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): string|false
    {
        $table = (string) config('session.table', 'sessions');

        $session = await($this->db->table($table)->where('id', $id)->first());

        if (! $session) {
            return '';
        }

        $lifetime = (int) config('session.lifetime', 7200);

        if ($session->last_activity < (time() - $lifetime)) {
            return '';
        }

        return base64_decode((string) $session->payload);
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, string $data): bool
    {
        $table = (string) config('session.table', 'sessions');

        await(
            $this->db->table($table)->upsert([
                'id' => $id,
                'payload' => base64_encode($data),
                'last_activity' => time(),
            ], 'id')
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        $table = (string) config('session.table', 'sessions');

        await($this->db->table($table)->where('id', $id)->delete());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc(int $max_lifetime): int|false
    {
        $table = (string) config('session.table', 'sessions');
        $expired = time() - $max_lifetime;

        return await($this->db->table($table)->where('last_activity', '<', $expired)->delete());
    }
}
