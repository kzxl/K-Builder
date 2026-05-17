<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSiteTables extends AbstractMigration
{
    public function up(): void
    {
        $this->table('kb_themes')
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('version', 'string', ['limit' => 20, 'default' => '1.0.0'])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('preview_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('is_system', 'boolean', ['default' => false])
            ->addColumn('config', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('slug', ['unique' => true])
            ->create();

        $this->table('kb_sites')
            ->addColumn('uuid', 'char', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('domain', 'string', ['limit' => 253, 'null' => true, 'default' => null])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactive', 'suspended'], 'default' => 'active'])
            ->addColumn('plan', 'string', ['limit' => 50, 'default' => 'free'])
            ->addColumn('theme_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('created_by', 'integer', ['signed' => false])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('uuid', ['unique' => true])
            ->addIndex('slug', ['unique' => true])
            ->addIndex(['status', 'deleted_at'], ['name' => 'idx_site_status'])
            ->create();

        $this->table('kb_site_users', ['id' => false, 'primary_key' => ['site_id', 'user_id']])
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addColumn('invited_by', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex('user_id', ['name' => 'idx_su_user'])
            ->create();

        $this->table('kb_site_settings')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('group', 'string', ['limit' => 50])
            ->addColumn('key', 'string', ['limit' => 100])
            ->addColumn('value', 'text', ['null' => true, 'default' => null])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'string'])
            ->addColumn('is_autoload', 'boolean', ['default' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'group', 'key'], ['unique' => true, 'name' => 'idx_settings_key'])
            ->addIndex(['site_id', 'is_autoload'], ['name' => 'idx_settings_autoload'])
            ->create();

        $this->table('kb_theme_settings')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('theme_id', 'integer', ['signed' => false])
            ->addColumn('overrides', 'json', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'theme_id'], ['unique' => true, 'name' => 'idx_site_theme'])
            ->create();

        // FK via raw SQL
        $this->execute('ALTER TABLE kb_sites ADD CONSTRAINT fk_site_theme FOREIGN KEY (theme_id) REFERENCES kb_themes(id) ON DELETE SET NULL');
        $this->execute('ALTER TABLE kb_sites ADD CONSTRAINT fk_site_creator FOREIGN KEY (created_by) REFERENCES kb_users(id) ON DELETE RESTRICT');
        $this->execute('ALTER TABLE kb_site_users ADD CONSTRAINT fk_su_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_site_users ADD CONSTRAINT fk_su_user FOREIGN KEY (user_id) REFERENCES kb_users(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_site_users ADD CONSTRAINT fk_su_role FOREIGN KEY (role_id) REFERENCES kb_roles(id) ON DELETE RESTRICT');
        $this->execute('ALTER TABLE kb_site_settings ADD CONSTRAINT fk_ss_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_theme_settings ADD CONSTRAINT fk_ts_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_theme_settings ADD CONSTRAINT fk_ts_theme FOREIGN KEY (theme_id) REFERENCES kb_themes(id) ON DELETE RESTRICT');
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        foreach (['kb_theme_settings', 'kb_site_settings', 'kb_site_users', 'kb_sites', 'kb_themes'] as $t) {
            $this->table($t)->drop()->save();
        }
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
