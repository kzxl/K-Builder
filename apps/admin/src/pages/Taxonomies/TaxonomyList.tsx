import { useState, useEffect } from 'react';
import { Edit2, Trash2, Tags } from 'lucide-react';
import { useParams } from 'react-router-dom';
import api from '../../lib/api';

export default function TaxonomyList() {
  const { type } = useParams<{ type: string }>();
  const [taxonomies, setTaxonomies] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [taxonomyType, setTaxonomyType] = useState<any>(null);

  const [form, setForm] = useState({
    name: '',
    slug: '',
    description: '',
    image_id: null as number | null,
    type: type || 'category'
  });

  useEffect(() => {
    setForm(prev => ({ ...prev, type: type || 'category' }));
    fetchTax();
  }, [type]);

  const fetchTax = async () => {
    try {
      setLoading(true);
      const typeRes = await api.get('/content-types');
      if (typeRes.data?.success) {
        setTaxonomyType(typeRes.data.data.taxonomies[type || 'category'] || { label: 'Danh mục' });
      }

      const res = await api.get(`/taxonomies?type=${type || 'category'}`);
      setTaxonomies(res.data.data);
    } catch (e) {
      alert('Lỗi khi tải danh mục');
    } finally {
      setLoading(false);
    }
  };

  const generateSlug = (text: string) => {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
  };

  const handleNameChange = (e: any) => {
    const name = e.target.value;
    if (!editingId && !form.slug) {
      setForm({ ...form, name, slug: generateSlug(name) });
    } else {
      setForm({ ...form, name });
    }
  };

  const handleSubmit = async (e: any) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (editingId) {
        await api.put(`/taxonomies/${editingId}`, form);
        setEditingId(null);
      } else {
        await api.post('/taxonomies', form);
      }
      setForm({ name: '', slug: '', description: '', image_id: null, type: type || 'category' });
      fetchTax();
    } catch (err: any) {
      alert('Lỗi: ' + (err.response?.data?.error || err.message));
    } finally {
      setSaving(false);
    }
  };

  const handleEdit = (tax: any) => {
    setEditingId(tax.id);
    setForm({
      name: tax.name,
      slug: tax.slug,
      description: tax.description || '',
      image_id: tax.image_id || null,
      type: tax.type || 'category'
    });
  };

  const handleCancelEdit = () => {
    setEditingId(null);
    setForm({ name: '', slug: '', description: '', image_id: null, type: type || 'category' });
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa danh mục này? Các bài viết sẽ bị gỡ khỏi danh mục.')) return;
    try {
      await api.delete(`/taxonomies/${id}`);
      fetchTax();
    } catch (e) {
      alert('Lỗi khi xóa');
    }
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="kb-page-container">
      <div className="kb-page-header">
        <div>
          <h1 className="kb-page-title">Quản lý {taxonomyType?.label || 'Danh mục'}</h1>
          <p className="kb-page-subtitle">Quản lý phân loại nội dung cho {taxonomyType?.label || 'danh mục'}</p>
        </div>
      </div>

      <div style={{ display: 'flex', gap: '2rem', alignItems: 'flex-start' }}>
        {/* Form bên trái */}
        <div className="kb-card" style={{ width: '350px', position: 'sticky', top: '2rem' }}>
          <h3 style={{ fontSize: '1.1rem', fontWeight: 600, marginBottom: '1.5rem' }}>
            {editingId ? 'Sửa Danh mục' : 'Thêm Danh mục mới'}
          </h3>
          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <div>
              <label className="kb-label">Tên danh mục</label>
              <input type="text" className="kb-input" value={form.name} onChange={handleNameChange} required />
            </div>
            <div>
              <label className="kb-label">Đường dẫn (Slug)</label>
              <input type="text" className="kb-input" value={form.slug} onChange={e => setForm({ ...form, slug: e.target.value })} required />
            </div>
            <div>
              <label className="kb-label">Loại</label>
              <input type="text" className="kb-input" value={form.type} disabled style={{ background: 'hsl(var(--color-surface-hover))', cursor: 'not-allowed' }} />
            </div>
            <div>
              <label className="kb-label">Mô tả</label>
              <textarea className="kb-input" rows={3} value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} />
            </div>
            <div>
              <label className="kb-label">Ảnh đại diện (Tùy chọn)</label>
              <div 
                style={{ border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)', padding: '1rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))', cursor: 'pointer' }}
                onClick={() => alert('Media Library đang phát triển')}
              >
                {form.image_id ? (
                  <div style={{ color: 'hsl(var(--color-primary))', fontWeight: 500 }}>Ảnh #{form.image_id} (Click để đổi)</div>
                ) : (
                  <div>Click để chọn ảnh</div>
                )}
              </div>
            </div>
            <div style={{ display: 'flex', gap: '0.5rem', marginTop: '0.5rem' }}>
              <button type="submit" className="kb-btn kb-btn--primary" style={{ flex: 1 }} disabled={saving}>
                {saving ? 'Đang lưu...' : (editingId ? 'Cập nhật' : 'Thêm mới')}
              </button>
              {editingId && (
                <button type="button" className="kb-btn" onClick={handleCancelEdit}>Hủy</button>
              )}
            </div>
          </form>
        </div>

        {/* Bảng bên phải */}
        <div className="kb-table-container" style={{ flex: 1 }}>
          <table className="kb-table">
            <thead>
              <tr>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Loại</th>
                <th>Bài viết</th>
                <th style={{ width: '100px' }}>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              {taxonomies.map(tax => (
                <tr key={tax.id}>
                  <td>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                      <Tags size={16} style={{ color: 'hsl(var(--color-text-muted))' }} />
                      <div>
                        <div style={{ fontWeight: 500 }}>{tax.name}</div>
                        <div style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))' }}>{tax.slug}</div>
                      </div>
                    </div>
                  </td>
                  <td style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem' }}>{tax.description || '-'}</td>
                  <td>
                    <span style={{ fontSize: '0.75rem', background: 'hsl(var(--color-surface-hover))', padding: '2px 6px', borderRadius: '4px', textTransform: 'capitalize' }}>
                      {tax.type}
                    </span>
                  </td>
                  <td>
                    <span style={{ fontWeight: 600 }}>{tax.post_count || 0}</span>
                  </td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                      <button className="kb-icon-btn" onClick={() => handleEdit(tax)} title="Sửa">
                        <Edit2 size={16} />
                      </button>
                      <button className="kb-icon-btn" style={{ color: 'hsl(var(--color-danger))' }} onClick={() => handleDelete(tax.id)} title="Xóa">
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {taxonomies.length === 0 && (
            <div style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
              <Tags size={48} style={{ opacity: 0.2, margin: '0 auto 1rem' }} />
              <p>Chưa có danh mục nào.</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
