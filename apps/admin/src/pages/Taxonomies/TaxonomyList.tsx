import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Tags, Edit2, Trash2 } from 'lucide-react';
import api from '../../lib/api';

export default function TaxonomyList() {
  const { type } = useParams<{ type: string }>();
  const navigate = useNavigate();
  const [taxonomies, setTaxonomies] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [taxonomyType, setTaxonomyType] = useState<any>(null);

  useEffect(() => {
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
        <button className="kb-btn kb-btn--primary" onClick={() => navigate(`/taxonomies/${type || 'category'}/new`)}>
          <Tags size={16} /> Thêm mới
        </button>
      </div>

      <div className="kb-table-container">
        <table className="kb-table">
            <thead>
              <tr>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
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
                    <span className={`kb-badge kb-badge--${tax.status === 'published' ? 'success' : 'warning'}`}>
                      {tax.status === 'published' ? 'Đã xuất bản' : 'Bản nháp'}
                    </span>
                  </td>
                  <td>
                    <span style={{ fontWeight: 600 }}>{tax.post_count || 0}</span>
                  </td>
                  <td>
                    <div style={{ display: 'flex', gap: '0.5rem' }}>
                      <button className="kb-icon-btn" onClick={() => navigate(`/taxonomies/${type || 'category'}/${tax.id}`)} title="Sửa">
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
  );
}
