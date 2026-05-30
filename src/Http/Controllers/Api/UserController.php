<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KBuilder\Http\Validation\Validator;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;

/**
 * Quản lý người dùng + gán vai trò (RBAC).
 */
class UserController
{
    private const STATUSES = ['active', 'inactive', 'banned'];

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /** Gắn danh sách role slug vào mỗi user. */
    private function attachRoles(int $userId): array
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->pluck('r.slug')
            ->toArray();
    }

    /** GET /api/users */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $users = DB::table('users')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'uuid', 'name', 'email', 'avatar', 'status', 'last_login_at', 'created_at']);

        $users = $users->map(function ($u) {
            $u->roles = $this->attachRoles((int) $u->id);
            return $u;
        });

        return $this->json($response, ['success' => true, 'data' => $users]);
    }

    /** GET /api/users/{id} */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first(['id', 'uuid', 'name', 'email', 'avatar', 'status', 'last_login_at', 'created_at']);

        if (!$user) {
            return $this->json($response, ['success' => false, 'error' => 'User not found'], 404);
        }

        $user->roles = $this->attachRoles($id);
        return $this->json($response, ['success' => true, 'data' => $user]);
    }

    /** POST /api/users */
    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        Validator::validate($body, [
            'name'     => v::notEmpty()->stringType()->length(1, 150),
            'email'    => v::notEmpty()->email(),
            'password' => v::notEmpty()->stringType()->length(6, null),
        ]);

        $email = strtolower(trim($body['email']));

        $exists = DB::table('users')->where('email', $email)->whereNull('deleted_at')->exists();
        if ($exists) {
            return $this->json($response, ['success' => false, 'error' => 'Email đã tồn tại'], 409);
        }

        $status = in_array($body['status'] ?? 'active', self::STATUSES, true) ? $body['status'] : 'active';
        $now = date('Y-m-d H:i:s');

        $id = DB::table('users')->insertGetId([
            'uuid'       => Uuid::uuid4()->toString(),
            'name'       => trim($body['name']),
            'email'      => $email,
            'password'   => password_hash($body['password'], PASSWORD_BCRYPT),
            'status'     => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->syncRoles($id, $body['roles'] ?? []);

        return $this->json($response, ['success' => true, 'id' => $id], 201);
    }

    /** PUT /api/users/{id} */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody() ?? [];

        $user = DB::table('users')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$user) {
            return $this->json($response, ['success' => false, 'error' => 'User not found'], 404);
        }

        $data = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['name'])) $data['name'] = trim($body['name']);

        if (isset($body['email'])) {
            $email = strtolower(trim($body['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json($response, ['success' => false, 'error' => 'Email không hợp lệ', 'errors' => ['email' => 'Email không hợp lệ']], 422);
            }
            $dup = DB::table('users')->where('email', $email)->where('id', '!=', $id)->whereNull('deleted_at')->exists();
            if ($dup) {
                return $this->json($response, ['success' => false, 'error' => 'Email đã tồn tại'], 409);
            }
            $data['email'] = $email;
        }

        if (!empty($body['password'])) {
            if (strlen($body['password']) < 6) {
                return $this->json($response, ['success' => false, 'error' => 'Mật khẩu tối thiểu 6 ký tự', 'errors' => ['password' => 'Tối thiểu 6 ký tự']], 422);
            }
            $data['password'] = password_hash($body['password'], PASSWORD_BCRYPT);
        }

        if (isset($body['status']) && in_array($body['status'], self::STATUSES, true)) {
            $data['status'] = $body['status'];
        }

        DB::table('users')->where('id', $id)->update($data);

        if (isset($body['roles']) && is_array($body['roles'])) {
            $this->syncRoles($id, $body['roles']);
        }

        return $this->json($response, ['success' => true]);
    }

    /** DELETE /api/users/{id} */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $currentUserId = (int) $request->getAttribute('auth_user_id');

        if ($id === $currentUserId) {
            return $this->json($response, ['success' => false, 'error' => 'Không thể xóa chính tài khoản đang đăng nhập'], 409);
        }

        // Không cho xóa super admin cuối cùng
        $isSuperAdmin = DB::table('user_roles as ur')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $id)
            ->where('r.slug', 'super_admin')
            ->exists();

        if ($isSuperAdmin) {
            $superAdminCount = DB::table('user_roles as ur')
                ->join('roles as r', 'r.id', '=', 'ur.role_id')
                ->join('users as u', 'u.id', '=', 'ur.user_id')
                ->where('r.slug', 'super_admin')
                ->whereNull('u.deleted_at')
                ->count();
            if ($superAdminCount <= 1) {
                return $this->json($response, ['success' => false, 'error' => 'Không thể xóa Super Admin cuối cùng'], 409);
            }
        }

        DB::table('users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->json($response, ['success' => true]);
    }

    /** GET /api/roles */
    public function roles(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $roles = DB::table('roles')->orderBy('id', 'asc')->get(['id', 'name', 'slug', 'description', 'is_system']);
        return $this->json($response, ['success' => true, 'data' => $roles]);
    }

    /**
     * Đồng bộ vai trò cho user: nhận mảng role slug, ghi lại pivot user_roles.
     *
     * @param array<int,string> $roleSlugs
     */
    private function syncRoles(int $userId, array $roleSlugs): void
    {
        if (!is_array($roleSlugs)) {
            return;
        }

        DB::table('user_roles')->where('user_id', $userId)->delete();

        if (empty($roleSlugs)) {
            return;
        }

        $roleIds = DB::table('roles')->whereIn('slug', $roleSlugs)->pluck('id')->toArray();
        $now = date('Y-m-d H:i:s');
        $rows = array_map(fn ($rid) => [
            'user_id'    => $userId,
            'role_id'    => (int) $rid,
            'created_at' => $now,
        ], $roleIds);

        if (!empty($rows)) {
            DB::table('user_roles')->insert($rows);
        }
    }
}
