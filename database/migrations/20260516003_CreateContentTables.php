<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateContentTables extends AbstractMigration
{
    public function up(): void
    {
        $this->table('kb_pages')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('status', 'enum', ['values' => ['draft', 'published', 'archived'], 'default' => 'draft'])
            ->addColumn('layout', 'json', ['null' => true, 'default' => null])
            ->addColumn('seo', 'json', ['null' => true, 'default' => null])
            ->addColumn('settings', 'json', ['null' => true, 'default' => null])
            ->addColumn('author_id', 'integer', ['signed' => false])
            ->addColumn('published_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'slug'], ['unique' => true, 'name' => 'idx_page_slug'])
            ->addIndex(['site_id', 'status', 'deleted_at'], ['name' => 'idx_page_status'])
            ->create();

        $this->table('kb_page_revisions')
            ->addColumn('page_id', 'integer', ['signed' => false])
            ->addColumn('layout', 'json', ['null' => true, 'default' => null])
            ->addColumn('seo', 'json', ['null' => true, 'default' => null])
            ->addColumn('author_id', 'integer', ['signed' => false])
            ->addColumn('note', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['page_id', 'created_at'], ['name' => 'idx_revision_page_date'])
            ->create();

        $this->table('kb_media')
            ->addColumn('uuid', 'char', ['limit' => 36])
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('disk', 'string', ['limit' => 30, 'default' => 'local'])
            ->addColumn('path', 'string', ['limit' => 500])
            ->addColumn('url', 'string', ['limit' => 500])
            ->addColumn('original_name', 'string', ['limit' => 255])
            ->addColumn('filename', 'string', ['limit' => 255])
            ->addColumn('mime_type', 'string', ['limit' => 100])
            ->addColumn('size', 'integer', ['signed' => false])
            ->addColumn('width', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('height', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('alt', 'string', ['limit' => 300, 'null' => true, 'default' => null])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('uploaded_by', 'integer', ['signed' => false])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex('uuid', ['unique' => true])
            ->addIndex(['site_id', 'mime_type'], ['name' => 'idx_media_type'])
            ->create();

        $this->table('kb_posts')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('type', 'string', ['limit' => 50, 'default' => 'post'])
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('content', 'text', ['null' => true, 'default' => null,
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('excerpt', 'text', ['null' => true, 'default' => null])
            ->addColumn('status', 'enum', ['values' => ['draft', 'published', 'scheduled', 'archived'], 'default' => 'draft'])
            ->addColumn('featured_image_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('author_id', 'integer', ['signed' => false])
            ->addColumn('published_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('seo', 'json', ['null' => true, 'default' => null])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'type', 'slug'], ['unique' => true, 'name' => 'idx_post_type_slug'])
            ->addIndex(['site_id', 'type', 'status', 'published_at'], ['name' => 'idx_post_list'])
            ->create();

        $this->table('kb_taxonomies')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('type', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('slug', 'string', ['limit' => 150])
            ->addColumn('description', 'text', ['null' => true, 'default' => null])
            ->addColumn('parent_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('image_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('meta', 'json', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'type', 'slug'], ['unique' => true, 'name' => 'idx_tax_slug'])
            ->addIndex(['site_id', 'type', 'parent_id'], ['name' => 'idx_tax_tree'])
            ->create();

        $this->table('kb_post_taxonomies', ['id' => false, 'primary_key' => ['post_id', 'taxonomy_id']])
            ->addColumn('post_id', 'integer', ['signed' => false])
            ->addColumn('taxonomy_id', 'integer', ['signed' => false])
            ->addIndex('taxonomy_id', ['name' => 'idx_pt_taxonomy'])
            ->create();

        $this->table('kb_menus')
            ->addColumn('site_id', 'integer', ['signed' => false])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('location', 'string', ['limit' => 50, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['site_id', 'location'], ['name' => 'idx_menu_location'])
            ->create();

        $this->table('kb_menu_items')
            ->addColumn('menu_id', 'integer', ['signed' => false])
            ->addColumn('parent_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('label', 'string', ['limit' => 150])
            ->addColumn('type', 'string', ['limit' => 30, 'default' => 'url'])
            ->addColumn('target_id', 'integer', ['signed' => false, 'null' => true, 'default' => null])
            ->addColumn('url', 'string', ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('target', 'enum', ['values' => ['_self', '_blank'], 'default' => '_self'])
            ->addColumn('icon', 'string', ['limit' => 50, 'null' => true, 'default' => null])
            ->addColumn('sort_order', 'integer', ['default' => 0])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['menu_id', 'parent_id', 'sort_order'], ['name' => 'idx_menu_tree'])
            ->create();

        // FK via raw SQL
        $this->execute('ALTER TABLE kb_pages ADD CONSTRAINT fk_page_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_pages ADD CONSTRAINT fk_page_author FOREIGN KEY (author_id) REFERENCES kb_users(id) ON DELETE RESTRICT');
        $this->execute('ALTER TABLE kb_page_revisions ADD CONSTRAINT fk_rev_page FOREIGN KEY (page_id) REFERENCES kb_pages(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_media ADD CONSTRAINT fk_media_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_posts ADD CONSTRAINT fk_post_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_posts ADD CONSTRAINT fk_post_author FOREIGN KEY (author_id) REFERENCES kb_users(id) ON DELETE RESTRICT');
        $this->execute('ALTER TABLE kb_posts ADD CONSTRAINT fk_post_image FOREIGN KEY (featured_image_id) REFERENCES kb_media(id) ON DELETE SET NULL');
        $this->execute('ALTER TABLE kb_taxonomies ADD CONSTRAINT fk_tax_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_post_taxonomies ADD CONSTRAINT fk_pt_post FOREIGN KEY (post_id) REFERENCES kb_posts(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_post_taxonomies ADD CONSTRAINT fk_pt_tax FOREIGN KEY (taxonomy_id) REFERENCES kb_taxonomies(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_menus ADD CONSTRAINT fk_menu_site FOREIGN KEY (site_id) REFERENCES kb_sites(id) ON DELETE CASCADE');
        $this->execute('ALTER TABLE kb_menu_items ADD CONSTRAINT fk_mi_menu FOREIGN KEY (menu_id) REFERENCES kb_menus(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        foreach (['kb_menu_items', 'kb_menus', 'kb_post_taxonomies', 'kb_taxonomies', 'kb_posts', 'kb_media', 'kb_page_revisions', 'kb_pages'] as $t) {
            $this->table($t)->drop()->save();
        }
        $this->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
