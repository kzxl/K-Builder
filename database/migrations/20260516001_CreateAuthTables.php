<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration 001 — Auth tables
 *
 * IMPORTANT: Phinx 0.14 dùng INT(11) UNSIGNED cho auto-increment PK.
 * FK columns phải match: dùng 'integer' + signed=false.
 */
final class CreateAuthTables extends AbstractMigration
{
    public function up(): void
    {
        $this->table('kb_users')
            ->addColumn('uuid', 'char', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('email', 'string', ['limit' => 200])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('avatar', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive', 'banned'], 'default' => 'active'])
            ->addColumn('email_verified_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('last_login_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('uuid', ['unique' => true])
            ->addIndex('email', ['unique' => true])
            ->addIndex(['status', 'deleted_at'], ['name' => 'idx_status_active'])
            ->create();

        $this->table('kb_roles')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('is_system', 'boolean', ['default' => false])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('slug', ['unique' => true])
            ->create();

        $this->table('kb_permissions')
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('slug', 'string', ['limit' => 150])
            ->addColumn('resource', 'string', ['limit' => 100])
            ->addColumn('action', 'string', ['limit' => 50])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('is_system', 'boolean', ['default' => false])
            ->addColumn('created_at', 'datetime')
            ->addIndex('slug', ['unique' => true])
            ->addIndex(['resource', 'action'], ['name' => 'idx_resource_action'])
            ->create();

        // Pivot tables — id=false, PK composite
        $this->table('kb_role_permissions', ['id' => false, 'primary_key' => ['role_id', 'permission_id']])
            ->addColumn('role_id', 'integer', ['signed' => false])       // match INT UNSIGNED
            ->addColumn('permission_id', 'integer', ['signed' => false])
            ->addColumn('created_at', 'datetime')
            ->create();

        $this->table('kb_user_roles', ['id' => false, 'primary_key' => ['user_id', 'role_id']])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addColumn('created_at', 'datetime')
            ->create();

        $this->table('kb_refresh_tokens')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('token_hash', 'char', ['limit' => 64])
            ->addColumn('expires_at', 'datetime')
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('user_agent', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('revoked_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex('token_hash', ['unique' => true])
            ->addIndex(['user_id', 'revoked_at'], ['name' => 'idx_user_active_token'])
            ->create();

        // FK via raw SQL — sau khi tất cả tables đã tạo
        $this->execute('ALTER TABLE kb_role_permissions ADD CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES kb_roles(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_role_permissions ADD CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES kb_permissions(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_user_roles ADD CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES kb_users(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_user_roles ADD CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES kb_roles(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_refresh_tokens ADD CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES kb_users(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        foreach (['kb_refresh_tokens', 'kb_user_roles', 'kb_role_permissions', 'kb_permissions', 'kb_roles', 'kb_users'] as $t) {
            $this->table($t)->drop()->save();
        }
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
