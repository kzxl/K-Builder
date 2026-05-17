import { useState, useEffect, useRef } from 'react';
import { UploadCloud, Trash2, Copy, Check } from 'lucide-react';
import api from '../../lib/api';

interface Media {
  id: number;
  url: string;
  original_name: string;
  filename: string;
  mime_type: string;
  size: number;
  created_at: string;
}

export default function MediaLibrary() {
  const [mediaList, setMediaList] = useState<Media[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [copiedId, setCopiedId] = useState<number | null>(null);

  const fetchMedia = async () => {
    try {
      const res = await api.get('/media');
      setMediaList(res.data.data);
    } catch (e) {
      console.error('Failed to fetch media', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMedia();
  }, []);

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    
    setUploading(true);
    const file = e.target.files[0];
    const formData = new FormData();
    formData.append('file', file);

    try {
      await api.post('/media/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      fetchMedia();
    } catch (e) {
      alert('Upload thất bại!');
    } finally {
      setUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const deleteMedia = async (id: number) => {
    if (!confirm('Bạn có chắc muốn xóa file này? Mọi Component đang sử dụng URL này sẽ bị lỗi hiển thị.')) return;
    try {
      await api.delete(`/media/${id}`);
      fetchMedia();
    } catch (e) {
      alert('Xóa thất bại');
    }
  };

  const copyUrl = (url: string, id: number) => {
    // Vì đang chạy subfolder trong PHP kbuilder
    const fullUrl = `/kbuilder${url}`;
    navigator.clipboard.writeText(fullUrl);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <h2>Thư viện (Media Library)</h2>
        <div>
          <input 
            type="file" 
            ref={fileInputRef} 
            onChange={handleUpload} 
            style={{ display: 'none' }} 
            accept="image/*,.pdf,.doc,.docx"
          />
          <button 
            className="kb-btn kb-btn--primary" 
            onClick={() => fileInputRef.current?.click()}
            disabled={uploading}
          >
            <UploadCloud size={18} /> {uploading ? 'Đang tải lên...' : 'Tải lên (Upload)'}
          </button>
        </div>
      </div>

      {loading ? (
        <div style={{ textAlign: 'center', padding: '3rem' }}>Đang tải thư viện...</div>
      ) : mediaList.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '4rem', background: 'white', borderRadius: 'var(--radius-lg)', border: '1px dashed hsl(var(--color-border))' }}>
          <UploadCloud size={48} style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '1rem' }} />
          <h3 style={{ marginBottom: '0.5rem' }}>Chưa có file nào</h3>
          <p style={{ color: 'hsl(var(--color-text-muted))' }}>Nhấn nút Tải lên ở góc phải để thêm file mới.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '1.5rem' }}>
          {mediaList.map(item => (
            <div key={item.id} style={{ 
              background: 'white', 
              borderRadius: 'var(--radius-lg)', 
              border: '1px solid hsl(var(--color-border))',
              overflow: 'hidden',
              display: 'flex',
              flexDirection: 'column'
            }}>
              <div style={{ height: '150px', background: 'hsl(var(--color-background))', display: 'flex', alignItems: 'center', justifyContent: 'center', overflow: 'hidden' }}>
                {item.mime_type.startsWith('image/') ? (
                  <img src={`/kbuilder${item.url}`} alt={item.original_name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                ) : (
                  <div style={{ fontSize: '2rem', color: 'hsl(var(--color-text-muted))', fontWeight: 'bold' }}>
                    {item.mime_type.split('/')[1]?.toUpperCase() || 'FILE'}
                  </div>
                )}
              </div>
              <div style={{ padding: '1rem', flex: 1, display: 'flex', flexDirection: 'column' }}>
                <div style={{ fontSize: '0.9rem', fontWeight: 500, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', marginBottom: '0.25rem' }} title={item.original_name}>
                  {item.original_name}
                </div>
                <div style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))', marginBottom: '1rem' }}>
                  {formatSize(item.size)} • {new Date(item.created_at).toLocaleDateString('vi-VN')}
                </div>
                
                <div style={{ display: 'flex', gap: '0.5rem', marginTop: 'auto' }}>
                  <button 
                    onClick={() => copyUrl(item.url, item.id)} 
                    className="kb-btn" 
                    style={{ flex: 1, padding: '0.5rem', fontSize: '0.85rem', background: 'hsl(var(--color-background))' }}
                  >
                    {copiedId === item.id ? <Check size={14} style={{ color: 'hsl(var(--color-success))' }} /> : <Copy size={14} />} 
                    <span style={{ marginLeft: '4px' }}>{copiedId === item.id ? 'Đã copy' : 'Copy URL'}</span>
                  </button>
                  <button 
                    onClick={() => deleteMedia(item.id)} 
                    className="kb-btn" 
                    style={{ padding: '0.5rem', color: 'hsl(var(--color-danger))', background: 'hsla(var(--color-danger)/0.1)' }}
                    title="Xóa"
                  >
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
