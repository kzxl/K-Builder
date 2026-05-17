import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, FileText } from 'lucide-react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../lib/api';

export default function PostList() {
  const { type } = useParams<{ type: string }>();
  const [posts, setPosts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [contentType, setContentType] = useState<any>(null);
  const navigate = useNavigate();

  useEffect(() => {
    fetchPosts();
  }, [type]);

  const fetchPosts = async () => {
    try {
      setLoading(true);
      // Fetch content type info first (could be cached in a real app)
      const typeRes = await api.get('/content-types');
      if (typeRes.data?.success) {
        setContentType(typeRes.data.data.post_types[type || 'post'] || { label: 'Nội dung' });
      }

      const res = await api.get(`/posts?type=${type || 'post'}`);
      setPosts(res.data.data);
    } catch (e) {
      alert('Lỗi khi tải danh sách nội dung');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa nội dung này?')) return;
    try {
      await api.delete(`/posts/${id}`);
      setPosts(posts.filter(p => p.id !== id));
    } catch (e) {
      alert('Lỗi khi xóa');
    }
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="kb-page-container">
      <div className="kb-page-header">
        <div>
          <h1 className="kb-page-title">Quản lý {contentType?.label || 'Nội dung'}</h1>
          <p className="kb-page-subtitle">Quản lý danh sách {contentType?.label || 'nội dung'}</p>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={() => navigate(`/content/${type || 'post'}/new`)}>
          <Plus size={16} /> Thêm mới
        </button>
      </div>

      <div className="kb-table-container">
        <table className="kb-table">
          <thead>
            <tr>
              <th>Tiêu đề</th>
              <th>Danh mục</th>
              <th>Trạng thái</th>
              <th>Ngày đăng</th>
              <th style={{ width: '120px' }}>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            {posts.map(post => (
              <tr key={post.id}>
                <td>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                    <div style={{ width: '32px', height: '32px', background: 'hsl(var(--color-surface-hover))', borderRadius: '4px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'hsl(var(--color-primary))' }}>
                      <FileText size={16} />
                    </div>
                    <div>
                      <div style={{ fontWeight: 500 }}>{post.title}</div>
                      <div style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))' }}>{post.slug}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
                    {post.taxonomies?.map((tax: string, i: number) => (
                      <span key={i} style={{ fontSize: '0.75rem', background: 'hsl(var(--color-surface-hover))', padding: '2px 6px', borderRadius: '4px' }}>
                        {tax}
                      </span>
                    ))}
                  </div>
                </td>
                <td>
                  <span className={`kb-badge kb-badge--${post.status === 'published' ? 'success' : 'warning'}`}>
                    {post.status === 'published' ? 'Đã xuất bản' : 'Bản nháp'}
                  </span>
                </td>
                <td>{post.created_at}</td>
                <td>
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button className="kb-icon-btn" onClick={() => navigate(`/content/${type || 'post'}/${post.id}`)} title="Sửa">
                      <Edit2 size={16} />
                    </button>
                    <button className="kb-icon-btn" style={{ color: 'hsl(var(--color-danger))' }} onClick={() => handleDelete(post.id)} title="Xóa">
                      <Trash2 size={16} />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {posts.length === 0 && (
          <div style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
            <FileText size={48} style={{ opacity: 0.2, margin: '0 auto 1rem' }} />
            <p>Chưa có nội dung nào.</p>
          </div>
        )}
      </div>
    </div>
  );
}
