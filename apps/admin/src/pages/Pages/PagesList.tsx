import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit, Trash2, ExternalLink, Copy, Check, X, Edit3 } from 'lucide-react';
import api from '../../lib/api';

interface Page {
  id: number;
  title: string;
  slug: string;
  status: string;
  updated_at: string;
}

export default function PagesList() {
  const getBaseUrl = () => {
    if (window.location.pathname.startsWith('/kbuilder/public/admin')) return '/kbuilder/public';
    if (window.location.pathname.startsWith('/kbuilder/admin')) return '/kbuilder';
    return '';
  };

  const [pages, setPages] = useState<Page[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const navigate = useNavigate();

  // Create Form State
  const [newTitle, setNewTitle] = useState('');
  const [newSlug, setNewSlug] = useState('');
  const [newStatus, setNewStatus] = useState('draft');
  const [createLoading, setCreateLoading] = useState(false);

  // Quick Edit State
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editTitle, setEditTitle] = useState('');
  const [editSlug, setEditSlug] = useState('');
  const [editStatus, setEditStatus] = useState('');

  const fetchPages = async () => {
    try {
      const res = await api.get('/pages');
      setPages(res.data.data);
    } catch (e) {
      console.error('Failed to fetch pages', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPages();
  }, []);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setCreateLoading(true);
    try {
      const res = await api.post('/pages', { title: newTitle, slug: newSlug, status: newStatus });
      setShowCreateModal(false);
      setNewTitle('');
      setNewSlug('');
      setNewStatus('draft');
      navigate(`/builder/${res.data.id}`);
    } catch (e) {
      alert('Tạo trang thất bại. Có thể slug đã tồn tại.');
    } finally {
      setCreateLoading(false);
    }
  };

  const autoGenerateSlug = (title: string, setter: (val: string) => void) => {
    const slug = title.toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[đĐ]/g, 'd')
      .replace(/([^0-9a-z-\s])/g, '')
      .replace(/(\s+)/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '');
    setter(slug);
  };

  const deletePage = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa trang này? Thao tác này không thể hoàn tác.')) return;
    try {
      await api.delete(`/pages/${id}`);
      fetchPages();
    } catch (e) {
      alert('Xóa thất bại');
    }
  };

  const duplicatePage = async (id: number) => {
    try {
      await api.post(`/pages/${id}/duplicate`);
      fetchPages();
    } catch (e) {
      alert('Nhân bản thất bại');
    }
  };

  const startQuickEdit = (page: Page) => {
    setEditingId(page.id);
    setEditTitle(page.title);
    setEditSlug(page.slug);
    setEditStatus(page.status);
  };

  const cancelQuickEdit = () => {
    setEditingId(null);
  };

  const saveQuickEdit = async (id: number) => {
    try {
      await api.put(`/pages/${id}`, { title: editTitle, slug: editSlug, status: editStatus });
      setEditingId(null);
      fetchPages();
    } catch (e) {
      alert('Cập nhật thất bại. Có thể slug đã tồn tại.');
    }
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <h2>Quản lý Trang (Pages)</h2>
        <button className="kb-btn kb-btn--primary" onClick={() => setShowCreateModal(true)}>
          <Plus size={18} /> Tạo trang mới
        </button>
      </div>

      <div className="kb-table-container animate-fade-in" style={{ animationDelay: '0.1s' }}>
        <table className="kb-table">
          <thead>
            <tr>
              <th>Tiêu đề</th>
              <th>URL (Slug)</th>
              <th>Trạng thái</th>
              <th>Ngày cập nhật</th>
              <th style={{ textAlign: 'right' }}>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={5} style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Đang tải dữ liệu...</td></tr>
            ) : pages.length === 0 ? (
              <tr><td colSpan={5} style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Chưa có trang nào. Khởi tạo trang đầu tiên của bạn!</td></tr>
            ) : (
              pages.map(page => (
                editingId === page.id ? (
                  <tr key={`edit-${page.id}`} style={{ background: 'hsla(var(--color-primary)/0.05)' }}>
                    <td>
                      <input type="text" className="kb-input" value={editTitle} onChange={e => { setEditTitle(e.target.value); autoGenerateSlug(e.target.value, setEditSlug); }} style={{ width: '100%', padding: '0.4rem', fontSize: '0.9rem' }} />
                    </td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
                        <span style={{ color: 'hsl(var(--color-text-muted))' }}>/</span>
                        <input type="text" className="kb-input" value={editSlug} onChange={e => setEditSlug(e.target.value)} style={{ width: '100%', padding: '0.4rem', fontSize: '0.9rem', fontFamily: 'monospace' }} />
                      </div>
                    </td>
                    <td>
                      <select className="kb-input" value={editStatus} onChange={e => setEditStatus(e.target.value)} style={{ padding: '0.4rem', fontSize: '0.9rem' }}>
                        <option value="published">Đã xuất bản</option>
                        <option value="draft">Bản nháp</option>
                      </select>
                    </td>
                    <td style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem' }}>
                      {new Date(page.updated_at).toLocaleDateString('vi-VN')}
                    </td>
                    <td style={{ textAlign: 'right' }}>
                      <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
                        <button onClick={() => saveQuickEdit(page.id)} className="kb-btn kb-btn--primary kb-btn--sm" title="Lưu">
                          <Check size={14} /> Lưu
                        </button>
                        <button onClick={cancelQuickEdit} className="kb-btn kb-btn--outline kb-btn--sm" title="Hủy">
                          <X size={14} /> Hủy
                        </button>
                      </div>
                    </td>
                  </tr>
                ) : (
                  <tr key={page.id}>
                    <td style={{ fontWeight: 600 }}>
                      {page.title}
                      {page.status === 'draft' && <span style={{ marginLeft: '0.5rem', fontWeight: 'normal', color: 'hsl(var(--color-text-muted))', fontSize: '0.85rem' }}>— Bản nháp</span>}
                    </td>
                    <td style={{ color: 'hsl(var(--color-text-muted))', fontFamily: 'monospace', fontSize: '0.9rem' }}>/{page.slug}</td>
                    <td>
                      <span className={`kb-badge ${page.status === 'published' ? 'kb-badge--success' : 'kb-badge--neutral'}`}>
                        {page.status === 'published' ? 'Đã xuất bản' : 'Bản nháp'}
                      </span>
                    </td>
                    <td style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem' }}>
                      {new Date(page.updated_at).toLocaleDateString('vi-VN')}
                    </td>
                    <td style={{ textAlign: 'right' }}>
                      <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
                        {page.status === 'published' && (
                          <a href={`${getBaseUrl()}/${page.slug}`} target="_blank" className="kb-btn kb-btn--outline kb-btn--sm" title="Xem trực tiếp">
                            <ExternalLink size={14} />
                          </a>
                        )}
                        <button onClick={() => startQuickEdit(page)} className="kb-btn kb-btn--outline kb-btn--sm" title="Sửa nhanh">
                          <Edit3 size={14} />
                        </button>
                        <button onClick={() => duplicatePage(page.id)} className="kb-btn kb-btn--outline kb-btn--sm" title="Nhân bản">
                          <Copy size={14} />
                        </button>
                        <button onClick={() => navigate(`/builder/${page.id}`)} className="kb-btn kb-btn--primary kb-btn--sm" title="Thiết kế">
                          <Edit size={14} style={{marginRight: '0.25rem'}}/> Thiết kế
                        </button>
                        <button onClick={() => deletePage(page.id)} className="kb-btn kb-btn--sm" style={{ color: 'hsl(var(--color-danger))', background: 'hsla(var(--color-danger)/0.1)' }} title="Xóa">
                          <Trash2 size={14} />
                        </button>
                      </div>
                    </td>
                  </tr>
                )
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Create Modal */}
      {showCreateModal && (
        <div style={{ position: 'fixed', inset: 0, background: 'hsla(var(--color-sidebar)/0.4)', backdropFilter: 'blur(8px)', WebkitBackdropFilter: 'blur(8px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 100 }}>
          <div className="kb-card animate-fade-in" style={{ width: '100%', maxWidth: '420px', padding: '2.5rem' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
              <h3 style={{ fontSize: '1.5rem' }}>Tạo trang mới</h3>
              <button onClick={() => setShowCreateModal(false)} style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'hsl(var(--color-text-muted))' }}>
                <X size={20} />
              </button>
            </div>
            
            <form onSubmit={handleCreate}>
              <div className="kb-form-group" style={{ marginBottom: '1.25rem' }}>
                <label className="kb-label">Tiêu đề trang</label>
                <input 
                  type="text" 
                  className="kb-input" 
                  value={newTitle} 
                  onChange={e => {
                    setNewTitle(e.target.value);
                    autoGenerateSlug(e.target.value, setNewSlug);
                  }} 
                  required 
                  placeholder="VD: Trang chủ"
                />
              </div>
              <div className="kb-form-group" style={{ marginBottom: '1.25rem' }}>
                <label className="kb-label">URL (Slug)</label>
                <input 
                  type="text" 
                  className="kb-input" 
                  value={newSlug} 
                  onChange={e => setNewSlug(e.target.value)} 
                  required 
                  placeholder="VD: trang-chu"
                  style={{ fontFamily: 'monospace' }}
                />
              </div>
              <div className="kb-form-group">
                <label className="kb-label">Trạng thái ban đầu</label>
                <select className="kb-input" value={newStatus} onChange={e => setNewStatus(e.target.value)}>
                  <option value="draft">Bản nháp</option>
                  <option value="published">Đã xuất bản</option>
                </select>
              </div>
              
              <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end', marginTop: '2.5rem' }}>
                <button type="button" className="kb-btn kb-btn--outline" onClick={() => setShowCreateModal(false)}>Hủy</button>
                <button type="submit" className="kb-btn kb-btn--primary" disabled={createLoading}>
                  {createLoading ? 'Đang khởi tạo...' : 'Tạo & Thiết kế'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
