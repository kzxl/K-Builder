<?php

declare(strict_types=1);

namespace KBuilder\Domain\User;

use Illuminate\Database\Capsule\Manager as DB;

class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $row = DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();
        return $row ? (array) $row : null;
    }

    public function findById(int $id): ?array
    {
        $row = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        return $row ? (array) $row : null;
    }

    public function updateLastLogin(int $id): void
    {
        DB::table('users')
            ->where('id', $id)
            ->update(['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public function saveRefreshToken(int $userId, string $tokenHash, \DateTimeImmutable $expiresAt, string $ip = null): void
    {
        DB::table('refresh_tokens')->insert([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'ip'         => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findRefreshToken(string $tokenHash): ?array
    {
        $row = DB::table('refresh_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();
        return $row ? (array) $row : null;
    }

    public function revokeRefreshToken(string $tokenHash): void
    {
        DB::table('refresh_tokens')
            ->where('token_hash', $tokenHash)
            ->update(['revoked_at' => date('Y-m-d H:i:s')]);
    }

    public function getRoles(int $userId): array
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->pluck('r.slug')
            ->toArray();
    }

    /**
     * Lấy site_id mặc định của user (site đầu tiên user thuộc về).
     * Trả về null nếu user chưa được gán site nào.
     */
    public function getDefaultSiteId(int $userId): ?int
    {
        $row = DB::table('site_users')
            ->where('user_id', $userId)
            ->orderBy('site_id', 'asc')
            ->first();

        return $row ? (int) $row->site_id : null;
    }

    /**
     * Danh sách site mà user có quyền truy cập (kèm role tại từng site).
     *
     * @return array<int, array{id:int,name:string,slug:string,role_id:int}>
     */
    public function getAccessibleSites(int $userId): array
    {
        return DB::table('site_users as su')
            ->join('sites as s', 's.id', '=', 'su.site_id')
            ->where('su.user_id', $userId)
            ->whereNull('s.deleted_at')
            ->get(['s.id', 's.name', 's.slug', 'su.role_id'])
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }
}
