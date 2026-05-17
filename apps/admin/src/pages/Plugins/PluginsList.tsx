import { useState, useEffect, Fragment } from 'react';
import { Settings, Check, X, ShieldAlert, BookOpen } from 'lucide-react';
import { Link } from 'react-router-dom';
import api from '../../lib/api';

interface Plugin {
  id: string;
  name: string;
  version: string;
  description: string;
  type: string;
  is_active: boolean;
  is_system: boolean;
}

interface PaginationData {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
}

export default function PluginsList() {
  const [plugins, setPlugins] = useState<Plugin[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedPlugin, setSelectedPlugin] = useState<Plugin | null>(null);
  const [pagination, setPagination] = useState<PaginationData>({ page: 1, limit: 10, total: 0, totalPages: 1 });

  const fetchPlugins = async (page: number = 1, query: string = searchQuery) => {
    setLoading(true);
    try {
      const res = await api.get(`/plugins?page=${page}&limit=10&q=${encodeURIComponent(query)}`);
      if (res.data.success) {
        setPlugins(res.data.data);
        if (res.data.pagination) setPagination(res.data.pagination);
      }
    } catch (e) {
      console.error('Lỗi khi tải plugins', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchPlugins(1, searchQuery);
    }, 500); // Debounce search
    return () => clearTimeout(timer);
  }, [searchQuery]);

  const togglePlugin = async (id: string, isSystem: boolean) => {
    if (isSystem) {
      alert('Không thể tắt Plugin hệ thống!');
      return;
    }
    
    try {
      setPlugins(plugins.map(p => p.id === id ? { ...p, is_active: !p.is_active } : p));
      await api.post(`/plugins/${id}/toggle`);
    } catch (e) {
      alert('Lỗi cập nhật trạng thái plugin');
      fetchPlugins(pagination.page); // Revert on failure
    }
  };

  const deletePlugin = async (id: string) => {
    if (confirm('Bạn có chắc chắn muốn gỡ bỏ hoàn toàn plugin này không? Dữ liệu của plugin (nếu có) có thể sẽ bị xóa bỏ!')) {
      try {
        await api.delete(`/plugins/${id}`);
        fetchPlugins(pagination.page);
        if (selectedPlugin?.id === id) setSelectedPlugin(null);
      } catch (e) {
        alert('Lỗi khi xóa plugin!');
      }
    }
  };

  return (
    <div className="animate-fade-in">
      <div className="kb-page-header" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h1 className="kb-page-title">Quản lý Plugins</h1>
          <p className="kb-page-subtitle">Quản lý và kích hoạt các tiện ích mở rộng của KBuilder</p>
        </div>
        <div style={{ display: 'flex', gap: '1rem' }}>
          <input 
            type="text" 
            placeholder="Tìm kiếm plugin..." 
            className="kb-input" 
            style={{ minWidth: '250px' }}
            value={searchQuery}
            onChange={e => setSearchQuery(e.target.value)}
          />
          <Link to="/plugins/docs" className="kb-btn kb-btn--outline" style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <BookOpen size={18} />
            Hướng dẫn viết Plugin
          </Link>
        </div>
      </div>

      <div className="kb-table-container">
        <table className="kb-table">
          <thead>
            <tr>
              <th style={{ width: '250px' }}>Tên Plugin</th>
              <th>Định danh (Slug)</th>
              <th style={{ width: '120px', textAlign: 'center' }}>Loại</th>
              <th style={{ width: '120px', textAlign: 'center' }}>Phiên bản</th>
              <th style={{ width: '120px', textAlign: 'center' }}>Trạng thái</th>
              <th style={{ width: '120px', textAlign: 'right' }}>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5} style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Đang tải dữ liệu...</td></tr>
            ) : plugins.length === 0 ? (
              <tr><td colSpan={5} style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Chưa có plugin nào.</td></tr>
            ) : (
              plugins.map(plugin => (
                <Fragment key={plugin.id}>
                  <tr 
                    style={{ opacity: plugin.is_active ? 1 : 0.6, cursor: 'pointer' }}
                    onClick={() => setSelectedPlugin(plugin)}
                    className="kb-table-row-hover"
                  >
                    <td style={{ fontWeight: 600 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                        <Settings size={18} style={{ color: 'hsl(var(--color-primary))' }} />
                        {plugin.name}
                        {plugin.is_system && (
                          <span style={{ display: 'inline-flex', alignItems: 'center', background: 'hsla(var(--color-warning)/0.1)', color: 'hsl(var(--color-warning))', padding: '0.1rem 0.4rem', borderRadius: '4px', fontSize: '0.75rem', fontWeight: 600 }}>
                            <ShieldAlert size={12} style={{ marginRight: '0.2rem' }} /> Hệ thống
                          </span>
                        )}
                      </div>
                    </td>
                    <td style={{ color: 'hsl(var(--color-text-muted))', fontFamily: 'monospace', fontSize: '0.9rem' }}>{plugin.id}</td>
                    <td style={{ textAlign: 'center' }}>
                      <span style={{ display: 'inline-flex', alignItems: 'center', background: 'hsla(var(--color-primary)/0.1)', color: 'hsl(var(--color-primary))', padding: '0.25rem 0.6rem', borderRadius: '4px', fontSize: '0.8rem', fontWeight: 600, textTransform: 'capitalize' }}>
                        {plugin.type}
                      </span>
                    </td>
                    <td style={{ textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>v{plugin.version}</td>
                    <td style={{ textAlign: 'center' }}>
                      <span className={`kb-badge ${plugin.is_active ? 'kb-badge--success' : 'kb-badge--neutral'}`}>
                        {plugin.is_active ? 'Đang bật' : 'Đã tắt'}
                      </span>
                    </td>
                    <td style={{ textAlign: 'right' }} onClick={e => e.stopPropagation()}>
                      {!plugin.is_system && (
                        <button 
                          className={`kb-btn kb-btn--sm ${plugin.is_active ? 'kb-btn--outline' : 'kb-btn--primary'}`} 
                          onClick={() => togglePlugin(plugin.id, plugin.is_system)}
                        >
                          {plugin.is_active ? <><X size={14} style={{ marginRight: '0.25rem' }}/> Tắt</> : <><Check size={14} style={{ marginRight: '0.25rem' }}/> Bật</>}
                        </button>
                      )}
                    </td>
                  </tr>
                </Fragment>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination UI */}
      {pagination.totalPages > 1 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '1.5rem', padding: '1rem', background: 'var(--kb-bg)', borderRadius: 'var(--kb-radius-lg)', border: '1px solid rgba(100, 116, 139, 0.1)' }}>
          <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.875rem' }}>
            Hiển thị {(pagination.page - 1) * pagination.limit + 1} - {Math.min(pagination.page * pagination.limit, pagination.total)} trong tổng số {pagination.total} plugin
          </div>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <button 
              className="kb-btn kb-btn--outline kb-btn--sm" 
              disabled={pagination.page <= 1}
              onClick={() => fetchPlugins(pagination.page - 1)}
            >
              Trước
            </button>
            <span style={{ padding: '0.5rem 1rem', background: 'var(--kb-bg-alt)', borderRadius: 'var(--kb-radius-sm)', fontWeight: 600 }}>
              Trang {pagination.page} / {pagination.totalPages}
            </span>
            <button 
              className="kb-btn kb-btn--outline kb-btn--sm" 
              disabled={pagination.page >= pagination.totalPages}
              onClick={() => fetchPlugins(pagination.page + 1)}
            >
              Sau
            </button>
          </div>
        </div>
      )}

      {/* Plugin Detail Modal */}
      {selectedPlugin && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(15, 23, 42, 0.6)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 100, backdropFilter: 'blur(4px)' }} onClick={() => setSelectedPlugin(null)}>
          <div style={{ background: 'hsl(var(--color-surface-glass))', width: '500px', borderRadius: 'var(--radius-lg)', padding: '2rem', boxShadow: 'var(--shadow-float)' }} onClick={e => e.stopPropagation()}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1.5rem' }}>
              <div>
                <h2 style={{ fontSize: '1.5rem', marginBottom: '0.25rem', color: 'hsl(var(--color-text-main))' }}>{selectedPlugin.name}</h2>
                <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'center' }}>
                  <span style={{ color: 'hsl(var(--color-text-muted))', fontFamily: 'monospace', fontSize: '0.85rem' }}>{selectedPlugin.id}</span>
                  <span style={{ fontSize: '0.85rem', color: 'hsl(var(--color-primary))', fontWeight: 600 }}>v{selectedPlugin.version}</span>
                </div>
              </div>
              <button className="kb-btn kb-btn--sm kb-btn--outline" onClick={() => setSelectedPlugin(null)} style={{ border: 'none' }}>
                <X size={20} />
              </button>
            </div>
            
            <div style={{ marginBottom: '2rem' }}>
              <h4 style={{ fontSize: '0.9rem', color: 'hsl(var(--color-text-muted))', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '0.5rem' }}>Mô tả chi tiết</h4>
              <p style={{ lineHeight: '1.6', color: 'hsl(var(--color-text-main))' }}>{selectedPlugin.description || 'Không có thông tin mô tả cho plugin này.'}</p>
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid rgba(100, 116, 139, 0.1)', paddingTop: '1.5rem' }}>
              <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'center' }}>
                <span className={`kb-badge ${selectedPlugin.is_active ? 'kb-badge--success' : 'kb-badge--neutral'}`}>
                  {selectedPlugin.is_active ? 'Đang hoạt động' : 'Đã tắt'}
                </span>
                <span style={{ display: 'inline-flex', alignItems: 'center', background: 'hsla(var(--color-primary)/0.1)', color: 'hsl(var(--color-primary))', padding: '0.25rem 0.6rem', borderRadius: '9999px', fontSize: '0.75rem', fontWeight: 600, textTransform: 'uppercase' }}>
                  {selectedPlugin.type}
                </span>
              </div>
              
              {!selectedPlugin.is_system && (
                <div style={{ display: 'flex', gap: '0.5rem' }}>
                  <button 
                    className={`kb-btn kb-btn--sm ${selectedPlugin.is_active ? 'kb-btn--outline' : 'kb-btn--primary'}`}
                    onClick={() => togglePlugin(selectedPlugin.id, selectedPlugin.is_system)}
                  >
                    {selectedPlugin.is_active ? 'Tắt Plugin' : 'Bật Plugin'}
                  </button>
                  <button 
                    className="kb-btn kb-btn--sm"
                    style={{ background: 'hsla(var(--color-danger)/0.1)', color: 'hsl(var(--color-danger))', border: '1px solid hsla(var(--color-danger)/0.2)' }}
                    onClick={() => deletePlugin(selectedPlugin.id)}
                  >
                    Gỡ bỏ
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
