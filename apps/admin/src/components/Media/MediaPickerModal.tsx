import { useState, useEffect, useRef } from 'react';
import { X, UploadCloud, Check, Search } from 'lucide-react';
import api from '../../lib/api';

interface Media {
  id: number;
  url: string;
  original_name: string;
  mime_type: string;
}

interface MediaPickerModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (media: Media) => void;
  allowedTypes?: string[]; // e.g. ['image']
}

export default function MediaPickerModal({ isOpen, onClose, onSelect, allowedTypes = ['image'] }: MediaPickerModalProps) {
  const [mediaList, setMediaList] = useState<Media[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (isOpen) {
      fetchMedia();
    }
  }, [isOpen]);

  const fetchMedia = async () => {
    try {
      setLoading(true);
      const res = await api.get('/media');
      let data = res.data.data;
      if (allowedTypes && allowedTypes.length > 0) {
        data = data.filter((m: Media) => allowedTypes.some(type => m.mime_type.startsWith(type)));
      }
      setMediaList(data);
    } catch (e) {
      console.error('Failed to fetch media', e);
    } finally {
      setLoading(false);
    }
  };

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    
    setUploading(true);
    const file = e.target.files[0];
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await api.post('/media/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      // Refresh list
      await fetchMedia();
      // Auto select newly uploaded file if id is returned
      if (res.data && res.data.success) {
        // Find it in updated list or fetch again
      }
    } catch (e) {
      alert('Upload thất bại!');
    } finally {
      setUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const handleConfirm = () => {
    const selected = mediaList.find(m => m.id === selectedId);
    if (selected) {
      onSelect(selected);
      onClose();
    }
  };

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
      background: 'rgba(0,0,0,0.5)', backdropFilter: 'blur(4px)',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      zIndex: 9999
    }}>
      <div style={{
        background: 'white', width: '90%', maxWidth: '900px', height: '80vh',
        borderRadius: 'var(--radius-lg)', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)',
        display: 'flex', flexDirection: 'column', overflow: 'hidden'
      }}>
        {/* Header */}
        <div style={{ padding: '1.5rem', borderBottom: '1px solid hsl(var(--color-border))', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <h2 style={{ fontSize: '1.25rem', fontWeight: 600, margin: 0 }}>Chọn Media</h2>
          <button onClick={onClose} className="kb-icon-btn" style={{ background: 'hsl(var(--color-surface-hover))' }}>
            <X size={20} />
          </button>
        </div>

        {/* Toolbar */}
        <div style={{ padding: '1rem 1.5rem', borderBottom: '1px solid hsl(var(--color-border))', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: 'hsl(var(--color-background))' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
            <div style={{ position: 'relative', width: '250px' }}>
              <Search size={16} style={{ position: 'absolute', left: '10px', top: '50%', transform: 'translateY(-50%)', color: 'hsl(var(--color-text-muted))' }} />
              <input type="text" placeholder="Tìm kiếm..." className="kb-input" style={{ paddingLeft: '2.25rem', height: '36px' }} />
            </div>
          </div>
          <div>
            <input type="file" ref={fileInputRef} onChange={handleUpload} style={{ display: 'none' }} accept="image/*" />
            <button className="kb-btn kb-btn--outline" onClick={() => fileInputRef.current?.click()} disabled={uploading} style={{ height: '36px' }}>
              <UploadCloud size={16} style={{ marginRight: '0.5rem' }} /> 
              {uploading ? 'Đang tải lên...' : 'Tải lên từ máy'}
            </button>
          </div>
        </div>

        {/* Content */}
        <div style={{ flex: 1, overflowY: 'auto', padding: '1.5rem', background: 'hsl(var(--color-surface-hover))' }}>
          {loading ? (
            <div style={{ textAlign: 'center', padding: '3rem', color: 'hsl(var(--color-text-muted))' }}>Đang tải...</div>
          ) : mediaList.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '4rem', color: 'hsl(var(--color-text-muted))' }}>
              <UploadCloud size={48} style={{ opacity: 0.2, margin: '0 auto 1rem' }} />
              <p>Chưa có file nào phù hợp.</p>
            </div>
          ) : (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(140px, 1fr))', gap: '1rem' }}>
              {mediaList.map(item => (
                <div 
                  key={item.id} 
                  onClick={() => setSelectedId(item.id)}
                  style={{ 
                    background: 'white', 
                    borderRadius: 'var(--radius-md)', 
                    border: `2px solid ${selectedId === item.id ? 'hsl(var(--color-primary))' : 'transparent'}`,
                    boxShadow: selectedId === item.id ? '0 0 0 2px hsla(var(--color-primary)/0.2)' : '0 1px 3px rgba(0,0,0,0.1)',
                    overflow: 'hidden',
                    cursor: 'pointer',
                    position: 'relative'
                  }}
                >
                  <div style={{ height: '120px', background: 'hsl(var(--color-background))', display: 'flex', alignItems: 'center', justifyContent: 'center', overflow: 'hidden' }}>
                    {item.mime_type.startsWith('image/') ? (
                      <img src={`/kbuilder${item.url}`} alt={item.original_name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                    ) : (
                      <div style={{ fontSize: '1.5rem', color: 'hsl(var(--color-text-muted))', fontWeight: 'bold' }}>FILE</div>
                    )}
                  </div>
                  {selectedId === item.id && (
                    <div style={{ position: 'absolute', top: '0.5rem', right: '0.5rem', background: 'hsl(var(--color-primary))', color: 'white', borderRadius: '50%', width: '20px', height: '20px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <Check size={12} />
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Footer */}
        <div style={{ padding: '1rem 1.5rem', borderTop: '1px solid hsl(var(--color-border))', display: 'flex', justifyContent: 'flex-end', gap: '0.75rem', background: 'white' }}>
          <button className="kb-btn" onClick={onClose} style={{ background: 'hsl(var(--color-surface-hover))' }}>Hủy</button>
          <button className="kb-btn kb-btn--primary" onClick={handleConfirm} disabled={!selectedId}>Chọn</button>
        </div>
      </div>
    </div>
  );
}
