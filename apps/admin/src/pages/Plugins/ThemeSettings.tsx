import { useState, useEffect } from 'react';
import { Save, RefreshCw } from 'lucide-react';
import api from '../../lib/api';

interface ThemeConfig {
  primary: string;
  secondary: string;
  accent: string;
  background: string;
  text: string;
  border_radius: string;
  container_width: string;
  custom_css: string;
}

const defaultTheme: ThemeConfig = {
  primary: '#2563EB',
  secondary: '#64748B',
  accent: '#F59E0B',
  background: '#FFFFFF',
  text: '#1E293B',
  border_radius: '8px',
  container_width: '1200px',
  custom_css: ''
};

export default function ThemeSettings() {
  const [config, setConfig] = useState<ThemeConfig>(defaultTheme);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchTheme();
  }, []);

  const fetchTheme = async () => {
    try {
      const res = await api.get('/settings/theme');
      if (res.data.success && Object.keys(res.data.data).length > 0) {
        setConfig({ ...defaultTheme, ...res.data.data });
      }
    } catch (error) {
      console.error('Failed to fetch theme settings', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await api.put('/settings/theme', config);
      if (res.data.success) {
        alert('Đã lưu cấu hình giao diện thành công!');
      }
    } catch (error) {
      alert('Lỗi khi lưu cấu hình.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div style={{ padding: '2rem' }}>Đang tải cấu hình...</div>;

  return (
    <div className="animate-fade-in" style={{ maxWidth: '900px' }}>
      <div className="kb-page-header">
        <h1 className="kb-page-title">Giao diện & CSS</h1>
        <p className="kb-page-subtitle">Tùy chỉnh màu sắc chủ đạo và viết mã CSS thủ công cho Frontend.</p>
      </div>

      <div className="kb-card" style={{ marginBottom: '2rem' }}>
        <h3 style={{ marginBottom: '1.5rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          Màu sắc chủ đạo
        </h3>
        
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem' }}>
          <div>
            <label className="kb-label">Primary Color (Màu chính)</label>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <input type="color" value={config.primary} onChange={e => setConfig({...config, primary: e.target.value})} style={{ height: '42px', width: '60px', padding: '2px' }} />
              <input type="text" className="kb-input" value={config.primary} onChange={e => setConfig({...config, primary: e.target.value})} />
            </div>
          </div>
          <div>
            <label className="kb-label">Secondary Color (Màu phụ)</label>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <input type="color" value={config.secondary} onChange={e => setConfig({...config, secondary: e.target.value})} style={{ height: '42px', width: '60px', padding: '2px' }} />
              <input type="text" className="kb-input" value={config.secondary} onChange={e => setConfig({...config, secondary: e.target.value})} />
            </div>
          </div>
          <div>
            <label className="kb-label">Accent Color (Màu nhấn)</label>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <input type="color" value={config.accent} onChange={e => setConfig({...config, accent: e.target.value})} style={{ height: '42px', width: '60px', padding: '2px' }} />
              <input type="text" className="kb-input" value={config.accent} onChange={e => setConfig({...config, accent: e.target.value})} />
            </div>
          </div>
          <div>
            <label className="kb-label">Background Color (Màu nền)</label>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <input type="color" value={config.background} onChange={e => setConfig({...config, background: e.target.value})} style={{ height: '42px', width: '60px', padding: '2px' }} />
              <input type="text" className="kb-input" value={config.background} onChange={e => setConfig({...config, background: e.target.value})} />
            </div>
          </div>
          <div>
            <label className="kb-label">Text Color (Màu chữ)</label>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <input type="color" value={config.text} onChange={e => setConfig({...config, text: e.target.value})} style={{ height: '42px', width: '60px', padding: '2px' }} />
              <input type="text" className="kb-input" value={config.text} onChange={e => setConfig({...config, text: e.target.value})} />
            </div>
          </div>
        </div>
      </div>

      <div className="kb-card" style={{ marginBottom: '2rem' }}>
        <h3 style={{ marginBottom: '1.5rem' }}>Kích thước Layout</h3>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem' }}>
          <div>
            <label className="kb-label">Border Radius (Bo góc)</label>
            <input 
              type="text" 
              className="kb-input" 
              value={config.border_radius} 
              onChange={e => setConfig({...config, border_radius: e.target.value})} 
              placeholder="Ví dụ: 8px, 12px, 20px"
            />
          </div>
          <div>
            <label className="kb-label">Container Width (Khung nội dung)</label>
            <input 
              type="text" 
              className="kb-input" 
              value={config.container_width} 
              onChange={e => setConfig({...config, container_width: e.target.value})} 
              placeholder="Ví dụ: 1200px, 1400px, 100%"
            />
          </div>
        </div>
      </div>

      <div className="kb-card" style={{ marginBottom: '2rem' }}>
        <h3 style={{ marginBottom: '1.5rem' }}>Custom CSS</h3>
        <p style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginBottom: '1rem' }}>
          Đoạn mã CSS này sẽ được chèn thẳng vào thẻ &lt;style&gt; của trang web Public. Hãy cẩn thận vì nó có thể làm vỡ giao diện.
        </p>
        <textarea 
          className="kb-input" 
          style={{ height: '300px', fontFamily: 'monospace', lineHeight: '1.5', resize: 'vertical' }}
          value={config.custom_css}
          onChange={e => setConfig({...config, custom_css: e.target.value})}
          placeholder="/* Viết mã CSS tùy chỉnh của bạn tại đây */&#10;body {&#10;  /* font-family: 'Roboto', sans-serif; */&#10;}"
        />
      </div>

      <div style={{ display: 'flex', gap: '1rem' }}>
        <button className="kb-btn kb-btn--primary kb-btn--lg" onClick={handleSave} disabled={saving}>
          {saving ? <RefreshCw size={20} className="spin" /> : <Save size={20} />}
          Lưu cấu hình giao diện
        </button>
      </div>
    </div>
  );
}
