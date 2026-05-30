<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePluginAndMiscTables extends AbstractMigration
{
    public function up(): void
    {
        $this->table('kb_plugins')
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('version', 'string', ['limit' => 20])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('is_system', 'boolean', ['default' => false])
            ->addColumn('config', 'json', ['null' => true, 'default' => null])
            ->addColumn('installed_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('slug', ['unique' => true])
            ->addIndex('is_active', ['name' => 'idx_plugin_active'])
            ->create();

        $this->table('kb_component_schema_versions')
            ->addColumn('component_type', 'string', ['limit' => 100])
            ->addColumn('version', 'string', ['limit' => 20])
            ->addColumn('schema', 'json')
            ->addColumn('migrator_class', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['component_type', 'version'], ['unique' => true, 'name' => 'idx_schema_version'])
            ->create();

        $this->table('kb_redirects')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('from_url', 'string', ['limit' => 500])
            ->addColumn('to_url', 'string', ['limit' => 500])
            ->addColumn('code', 'integer', ['default' => 301])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('hit_count', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'from_url'], ['name' => 'idx_redirect_from'])
            ->create();

        $this->table('kb_analytics_events')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('event_type', 'string', ['limit' => 50])
            ->addColumn('target_url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('target_id', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->addColumn('session_id', 'char', ['limit' => 36, 'null' => true, 'default' => null])
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('referrer', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('user_agent', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['site_id', 'event_type', 'created_at'], ['name' => 'idx_event_type_date'])
            ->addIndex(['site_id', 'target_url', 'created_at'], ['name' => 'idx_event_url_date'])
            ->create();

        $this->table('kb_activity_logs')
            ->addColumn('site_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('action', 'string', ['limit' => 100])
            ->addColumn('resource_type', 'string', ['limit' => 50, 'null' => true, 'default' => null])
            ->addColumn('resource_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('detail', 'json', ['null' => true, 'default' => null])
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('user_agent', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['site_id', 'action', 'created_at'], ['name' => 'idx_log_action'])
            ->addIndex(['user_id', 'created_at'], ['name' => 'idx_log_user'])
            ->create();

        // FK via raw SQL
        $this->execute('ALTER TABLE kb_redirects ADD CONSTRAINT fk_redirect_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_analytics_events ADD CONSTRAINT fk_ae_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        foreach (['kb_activity_logs', 'kb_analytics_events', 'kb_redirects', 'kb_component_schema_versions', 'kb_plugins'] as $t) {
            $this->table($t)->drop()->save();
        }
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
