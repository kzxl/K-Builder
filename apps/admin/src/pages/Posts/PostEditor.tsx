import { useState, useEffect } from 'react';
import { Save, ArrowLeft, Image as ImageIcon } from 'lucide-react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../lib/api';

export default function PostEditor() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isNew = !id || id === 'create';

  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);
  const [taxonomies, setTaxonomies] = useState<any[]>([]);

  const [post, setPost] = useState<any>({
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    status: 'draft',
    taxonomies: []
  });

  useEffect(() => {
    fetchTaxonomies();
    if (!isNew) {
      fetchPost();
    }
  }, [id]);

  const fetchTaxonomies = async () => {
    try {
      const res = await api.get('/taxonomies');
      setTaxonomies(res.data.data);
    } catch (e) {
      console.error(e);
    }
  };

  const fetchPost = async () => {
    try {
      const res = await api.get(`/posts/${id}`);
      setPost(res.data.data);
    } catch (e) {
      alert('Không tìm thấy nội dung');
      navigate('/posts');
    } finally {
      setLoading(false);
    }
  };

  const generateSlug = (text: string) => {
    return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
  };

  const handleTitleChange = (e: any) => {
    const title = e.target.value;
    if (isNew || !post.slug) {
      setPost({ ...post, title, slug: generateSlug(title) });
    } else {
      setPost({ ...post, title });
    }
  };

  const toggleTaxonomy = (taxId: number) => {
    const current = post.taxonomies || [];
    if (current.includes(taxId)) {
      setPost({ ...post, taxonomies: current.filter((t: number) => t !== taxId) });
    } else {
      setPost({ ...post, taxonomies: [...current, taxId] });
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      if (isNew) {
        const res = await api.post('/posts', post);
        navigate(`/posts/${res.data.id}`);
      } else {
        await api.put(`/posts/${id}`, post);
        alert('Lưu thành công');
      }
    } catch (e: any) {
      alert('Lỗi khi lưu: ' + (e.response?.data?.error || e.message));
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="kb-page-container">
      <div className="kb-page-header">
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <button className="kb-btn" style={{ background: 'white' }} onClick={() => navigate('/posts')}>
            <ArrowLeft size={16} />
          </button>
          <div>
            <h1 className="kb-page-title">{isNew ? 'Thêm Nội dung mới' : 'Sửa Nội dung'}</h1>
          </div>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={handleSave} disabled={saving}>
          <Save size={16} /> {saving ? 'Đang lưu...' : 'Lưu nội dung'}
        </button>
      </div>

      <div style={{ display: 'flex', gap: '2rem', alignItems: 'flex-start' }}>
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <div className="kb-card">
            <div style={{ marginBottom: '1rem' }}>
              <label className="kb-label">Tiêu đề (Tên sản phẩm, bài viết...)</label>
              <input type="text" className="kb-input" value={post.title} onChange={handleTitleChange} placeholder="Nhập tiêu đề..." style={{ fontSize: '1.25rem', padding: '0.75rem 1rem' }} />
            </div>
            <div>
              <label className="kb-label">Đường dẫn (Slug)</label>
              <input type="text" className="kb-input" value={post.slug} onChange={e => setPost({ ...post, slug: e.target.value })} />
            </div>
          </div>

          <div className="kb-card">
            <label className="kb-label">Nội dung (Content)</label>
            <textarea 
              className="kb-input" 
              rows={15} 
              value={post.content || ''} 
              onChange={e => setPost({ ...post, content: e.target.value })} 
              placeholder="Nhập nội dung chi tiết (Hỗ trợ HTML/Markdown)..."
              style={{ fontFamily: 'monospace' }}
            />
          </div>

          <div className="kb-card">
            <label className="kb-label">Trích dẫn ngắn (Excerpt)</label>
            <textarea 
              className="kb-input" 
              rows={3} 
              value={post.excerpt || ''} 
              onChange={e => setPost({ ...post, excerpt: e.target.value })} 
              placeholder="Tóm tắt nội dung..."
            />
          </div>
        </div>

        <div style={{ width: '300px', display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Trạng thái</h3>
            <select className="kb-input" value={post.status} onChange={e => setPost({ ...post, status: e.target.value })}>
              <option value="draft">Bản nháp</option>
              <option value="published">Xuất bản</option>
            </select>
          </div>

          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Danh mục</h3>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', maxHeight: '200px', overflowY: 'auto' }}>
              {taxonomies.map(tax => (
                <label key={tax.id} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', cursor: 'pointer' }}>
                  <input 
                    type="checkbox" 
                    checked={(post.taxonomies || []).includes(tax.id)} 
                    onChange={() => toggleTaxonomy(tax.id)}
                  />
                  <span>{tax.name}</span>
                </label>
              ))}
              {taxonomies.length === 0 && <div style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>Chưa có danh mục nào.</div>}
            </div>
          </div>

          <div className="kb-card">
            <h3 style={{ fontSize: '1rem', marginBottom: '1rem', fontWeight: 600 }}>Ảnh đại diện</h3>
            <div style={{ border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)', padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))', cursor: 'pointer' }}>
              <ImageIcon size={32} style={{ margin: '0 auto 0.5rem', opacity: 0.5 }} />
              <div style={{ fontSize: '0.85rem' }}>Click để chọn ảnh (TBD)</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
