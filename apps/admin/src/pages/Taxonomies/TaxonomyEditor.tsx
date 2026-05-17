import { useState, useEffect } from 'react';
import { Save, ArrowLeft, Image as ImageIcon } from 'lucide-react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../lib/api';
import MediaPickerModal from '../../components/Media/MediaPickerModal';

export default function TaxonomyEditor() {
  const { type, id } = useParams<{ type: string; id: string }>();
  const navigate = useNavigate();
  const isNew = !id || id === 'new';

  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);
  const [taxonomyType, setTaxonomyType] = useState<any>(null);
  const [mediaPickerOpen, setMediaPickerOpen] = useState(false);

  const [form, setForm] = useState({
    name: '',
    slug: '',
    description: '',
    image_id: null as number | null,
    status: 'published',
    type: type || 'category'
  });

  useEffect(() => {
    fetchMetadata();
    if (!isNew) {
      fetchTaxonomy();
    }
  }, [id, type]);

  const fetchMetadata = async () => {
    try {
      const typeRes = await api.get('/content-types');
      if (typeRes.data?.success) {
        setTaxonomyType(typeRes.data.data.taxonomies[type || 'category'] || { label: 'Danh mục' });
      }
    } catch (e) {
      console.error(e);
    }
  };

  const fetchTaxonomy = async () => {
    try {
      const res = await api.get(`/taxonomies/${id}`);
      const tax = res.data.data;
      setForm({
        name: tax.name,
        slug: tax.slug,
        description: tax.description || '',
        image_id: tax.image_id || null,
        status: tax.status || 'published',
        type: tax.type || 'category'
      });
    } catch (e) {
      alert('Không tìm thấy danh mục');
      navigate(`/taxonomies/${type || 'category'}`);
    } finally {
      setLoading(false);
    }
  };

  const generateSlug = (text: string) => {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
  };

  const handleNameChange = (e: any) => {
    const name = e.target.value;
    if (isNew || !form.slug) {
      setForm({ ...form, name, slug: generateSlug(name) });
    } else {
      setForm({ ...form, name });
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      if (isNew) {
        const res = await api.post('/taxonomies', form);
        navigate(`/taxonomies/${type || 'category'}/${res.data.id}`);
      } else {
        await api.put(`/taxonomies/${id}`, form);
        alert('Lưu thành công');
      }
    } catch (e: any) {
      alert('Lỗi khi lưu: ' + (e.response?.data?.error || e.message));
    } finally {
      setSaving(false);
    }
  };

  const handleMediaSelect = (media: any) => {
    setForm({ ...form, image_id: media.id });
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="kb-page-container">
      <div className="kb-page-header">
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <button className="kb-btn" style={{ background: 'white' }} onClick={() => navigate(`/taxonomies/${type || 'category'}`)}>
            <ArrowLeft size={16} />
          </button>
          <div>
            <h1 className="kb-page-title">{isNew ? `Thêm ${taxonomyType?.label || 'mới'}` : `Sửa ${taxonomyType?.label || 'danh mục'}`}</h1>
          </div>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={handleSave} disabled={saving}>
          <Save size={16} /> {saving ? 'Đang lưu...' : 'Lưu danh mục'}
        </button>
      </div>

      <div style={{ display: 'flex', gap: '2rem', alignItems: 'flex-start' }}>
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <div className="kb-card">
            <div style={{ marginBottom: '1rem' }}>
              <label className="kb-label">Tên danh mục</label>
              <input type="text" className="kb-input" value={form.name} onChange={handleNameChange} placeholder="Nhập tên..." style={{ fontSize: '1.25rem', padding: '0.75rem 1rem' }} />
            </div>
            <div>
              <label className="kb-label">Đường dẫn (Slug)</label>
              <input type="text" className="kb-input" value={form.slug} onChange={e => setForm({ ...form, slug: e.target.value })} />
            </div>
          </div>

          <div className="kb-card">
            <label className="kb-label">Mô tả chi tiết</label>
            <textarea 
              className="kb-input" 
              rows={5} 
              value={form.description || ''} 
              onChange={e => setForm({ ...form, description: e.target.value })} 
              placeholder="Mô tả cho danh mục này..."
            />
          </div>
        </div>

        <div style={{ width: '300px', display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Trạng thái</h3>
            <select className="kb-input" value={form.status} onChange={e => setForm({ ...form, status: e.target.value })}>
              <option value="draft">Bản nháp</option>
              <option value="published">Đã xuất bản</option>
            </select>
          </div>

          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Loại</h3>
            <input type="text" className="kb-input" value={form.type} disabled style={{ background: 'hsl(var(--color-surface-hover))', cursor: 'not-allowed' }} />
          </div>

          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Ảnh đại diện</h3>
            <div 
              style={{ border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)', padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))', cursor: 'pointer' }}
              onClick={() => setMediaPickerOpen(true)}
            >
              {form.image_id ? (
                <>
                  <ImageIcon size={32} style={{ margin: '0 auto 0.5rem', color: 'hsl(var(--color-primary))' }} />
                  <div style={{ fontSize: '0.85rem', color: 'hsl(var(--color-primary))', fontWeight: 500 }}>Ảnh #{form.image_id} (Click đổi)</div>
                </>
              ) : (
                <>
                  <ImageIcon size={32} style={{ margin: '0 auto 0.5rem', opacity: 0.5 }} />
                  <div style={{ fontSize: '0.85rem' }}>Click để chọn ảnh</div>
                </>
              )}
            </div>
            {form.image_id && (
              <button 
                className="kb-btn" 
                style={{ width: '100%', marginTop: '0.5rem', color: 'hsl(var(--color-danger))' }}
                onClick={() => setForm({ ...form, image_id: null })}
              >
                Gỡ ảnh
              </button>
            )}
          </div>
        </div>
      </div>

      <MediaPickerModal 
        isOpen={mediaPickerOpen} 
        onClose={() => setMediaPickerOpen(false)} 
        onSelect={handleMediaSelect} 
        allowedTypes={['image']} 
      />
    </div>
  );
}
