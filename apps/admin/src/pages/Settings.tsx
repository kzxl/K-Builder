import { useState, useEffect } from 'react';
import { Save, Globe, Lock, Bell } from 'lucide-react';
import api from '../lib/api';

export default function Settings() {
  const [activeTab, setActiveTab] = useState('general');
  const [saving, setSaving] = useState(false);
  const [themeVars, setThemeVars] = useState<Record<string, string>>({
    primary: '#2563EB',
    border_radius: '12px'
  });

  useEffect(() => {
    // Load theme settings
    api.get('/settings/theme').then(res => {
      if (res.data.success && res.data.data) {
        setThemeVars(prev => ({ ...prev, ...res.data.data }));
      }
    });
  }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    
    if (activeTab === 'appearance') {
      await api.put('/settings/theme', themeVars);
    }
    
    setTimeout(() => {
      setSaving(false);
      alert('Đã lưu cấu hình thành công!');
    }, 500);
  };

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: '2.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
        <div>
          <h1 style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>
            Cài đặt Hệ thống
          </h1>
          <p className="text-muted" style={{ fontSize: '1.05rem' }}>
            Cấu hình các thông số cơ bản cho Website của bạn.
          </p>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={handleSave} disabled={saving}>
          <Save size={18} /> {saving ? 'Đang lưu...' : 'Lưu thay đổi'}
        </button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '250px 1fr', gap: '2rem' }}>
        {/* Settings Navigation */}
        <div className="kb-card" style={{ padding: '1rem', height: 'fit-content' }}>
          <nav style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
            <button 
              className={`kb-btn ${activeTab === 'general' ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
              style={{ justifyContent: 'flex-start', border: activeTab === 'general' ? '' : 'none', color: activeTab === 'general' ? '' : 'hsl(var(--color-text-muted))' }}
              onClick={() => setActiveTab('general')}
            >
              <Globe size={18} /> Cài đặt chung
            </button>

            <button 
              className={`kb-btn ${activeTab === 'appearance' ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
              style={{ justifyContent: 'flex-start', border: activeTab === 'appearance' ? '' : 'none', color: activeTab === 'appearance' ? '' : 'hsl(var(--color-text-muted))' }}
              onClick={() => setActiveTab('appearance')}
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Giao diện (Theme)
            </button>

            <button 
              className={`kb-btn ${activeTab === 'security' ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
              style={{ justifyContent: 'flex-start', border: activeTab === 'security' ? '' : 'none', color: activeTab === 'security' ? '' : 'hsl(var(--color-text-muted))' }}
              onClick={() => setActiveTab('security')}
            >
              <Lock size={18} /> Bảo mật
            </button>
            <button 
              className={`kb-btn ${activeTab === 'notifications' ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
              style={{ justifyContent: 'flex-start', border: activeTab === 'notifications' ? '' : 'none', color: activeTab === 'notifications' ? '' : 'hsl(var(--color-text-muted))' }}
              onClick={() => setActiveTab('notifications')}
            >
              <Bell size={18} /> Thông báo
            </button>
            <button 
              className={`kb-btn ${activeTab === 'tools' ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
              style={{ justifyContent: 'flex-start', border: activeTab === 'tools' ? '' : 'none', color: activeTab === 'tools' ? '' : 'hsl(var(--color-text-muted))' }}
              onClick={() => setActiveTab('tools')}
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg> Công cụ hệ thống
            </button>
          </nav>
        </div>

        {/* Settings Form */}
        <div className="kb-card">
          <form onSubmit={handleSave}>
            {activeTab === 'general' && (
              <div className="animate-fade-in" style={{ animationDuration: '0.2s' }}>
                <h3 style={{ fontSize: '1.25rem', marginBottom: '1.5rem', paddingBottom: '1rem', borderBottom: '1px solid hsl(var(--color-border))' }}>Cài đặt chung</h3>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
                  <div className="kb-form-group">
                    <label className="kb-label">Tên Website (Site Title)</label>
                    <input type="text" className="kb-input" defaultValue="My Awesome KBuilder Site" />
                    <p className="text-muted" style={{ fontSize: '0.8rem', marginTop: '0.5rem' }}>Tên này sẽ hiển thị trên thẻ tiêu đề của trình duyệt.</p>
                  </div>

                  <div className="kb-form-group">
                    <label className="kb-label">Mô tả (Tagline)</label>
                    <input type="text" className="kb-input" defaultValue="Just another KBuilder site" />
                  </div>

                  <div className="kb-form-group">
                    <label className="kb-label">Ngôn ngữ mặc định</label>
                    <select className="kb-input">
                      <option value="vi">Tiếng Việt</option>
                      <option value="en">English</option>
                    </select>
                  </div>
                  
                  <div className="kb-form-group">
                    <label className="kb-label">Định dạng ngày</label>
                    <select className="kb-input">
                      <option value="d/m/Y">17/05/2026 (d/m/Y)</option>
                      <option value="Y-m-d">2026-05-17 (Y-m-d)</option>
                    </select>
                  </div>
                </div>
              </div>
            )}

            {activeTab === 'appearance' && (
              <div className="animate-fade-in" style={{ animationDuration: '0.2s' }}>
                <h3 style={{ fontSize: '1.25rem', marginBottom: '1.5rem', paddingBottom: '1rem', borderBottom: '1px solid hsl(var(--color-border))' }}>Cấu hình Giao diện (Theme)</h3>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
                  <div className="kb-form-group">
                    <label className="kb-label">Màu chủ đạo (Primary Color)</label>
                    <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                      <input 
                        type="color" 
                        value={themeVars.primary || '#2563EB'} 
                        onChange={(e) => setThemeVars({...themeVars, primary: e.target.value})}
                        style={{ width: '40px', height: '40px', padding: '0', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                      />
                      <input 
                        type="text" 
                        className="kb-input" 
                        value={themeVars.primary || '#2563EB'} 
                        onChange={(e) => setThemeVars({...themeVars, primary: e.target.value})}
                        style={{ width: '120px' }}
                      />
                    </div>
                  </div>

                  <div className="kb-form-group">
                    <label className="kb-label">Độ bo góc mặc định (Border Radius)</label>
                    <select 
                      className="kb-input" 
                      value={themeVars.border_radius || '8px'}
                      onChange={(e) => setThemeVars({...themeVars, border_radius: e.target.value})}
                    >
                      <option value="0px">Vuông vức (0px)</option>
                      <option value="4px">Bo nhẹ (4px)</option>
                      <option value="8px">Bo vừa (8px)</option>
                      <option value="12px">Bo tròn (12px)</option>
                      <option value="20px">Tròn góc lớn (20px)</option>
                    </select>
                  </div>
                  
                  <div className="kb-form-group">
                    <label className="kb-label">Custom CSS</label>
                    <textarea 
                      className="kb-input" 
                      rows={6}
                      value={(themeVars as any).custom_css || ''}
                      onChange={(e) => setThemeVars({...themeVars, custom_css: e.target.value})}
                      placeholder="/* Nhập CSS tùy chỉnh ở đây... */"
                      style={{ fontFamily: 'monospace', fontSize: '0.85rem' }}
                    />
                  </div>
                </div>
              </div>
            )}



            {activeTab === 'tools' && (
              <div className="animate-fade-in" style={{ animationDuration: '0.2s' }}>
                <h3 style={{ fontSize: '1.25rem', marginBottom: '1.5rem', paddingBottom: '1rem', borderBottom: '1px solid hsl(var(--color-border))' }}>Công cụ Hệ thống</h3>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
                  
                  {/* Khởi tạo Web mẫu */}
                  <div style={{ padding: '1.5rem', background: 'hsla(var(--color-primary)/0.05)', borderRadius: 'var(--radius-md)', border: '1px solid hsl(var(--color-primary)/0.2)' }}>
                    <h4 style={{ fontSize: '1.1rem', fontWeight: 600, marginBottom: '0.5rem', color: 'hsl(var(--color-primary))' }}>Khởi tạo Web mẫu (Demo)</h4>
                    <p style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginBottom: '1rem', lineHeight: 1.5 }}>
                      Tự động khởi tạo dữ liệu mẫu bao gồm Trang chủ, Tin tức, Danh mục, Menu, và các Layout phức tạp. <strong style={{color: 'hsl(var(--color-danger))'}}>CẢNH BÁO: Hành động này sẽ xoá toàn bộ dữ liệu hiện tại!</strong>
                    </p>
                    <button 
                      type="button"
                      className="kb-btn kb-btn--primary" 
                      onClick={async () => {
                        if (confirm('BẠN CÓ CHẮC CHẮN? Toàn bộ dữ liệu hiện tại (Pages, Posts, Categories...) sẽ bị XOÁ SẠCH để chèn web mẫu!')) {
                          try {
                            setSaving(true);
                            await api.post('/settings/tools/demo');
                            alert('Đã khởi tạo Web mẫu thành công!');
                            window.location.reload();
                          } catch (e: any) {
                            alert('Lỗi: ' + (e.response?.data?.error || e.message));
                          } finally {
                            setSaving(false);
                          }
                        }
                      }}
                    >
                      Bắt đầu khởi tạo Demo
                    </button>
                  </div>

                  {/* Export & Import */}
                  <div style={{ padding: '1.5rem', background: 'hsl(var(--color-surface-hover))', borderRadius: 'var(--radius-md)', border: '1px solid hsl(var(--color-border))' }}>
                    <h4 style={{ fontSize: '1.1rem', fontWeight: 600, marginBottom: '0.5rem' }}>Import / Export Dữ liệu</h4>
                    <p style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginBottom: '1.5rem', lineHeight: 1.5 }}>
                      Di chuyển toàn bộ nội dung website giữa các máy chủ dễ dàng bằng tệp JSON.
                    </p>
                    
                    <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
                      <button 
                        type="button"
                        className="kb-btn kb-btn--outline" 
                        onClick={() => {
                          window.open(`${api.defaults.baseURL}/settings/tools/export`, '_blank');
                        }}
                      >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{marginRight: '0.5rem'}}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Xuất dữ liệu (Export JSON)
                      </button>

                      <span style={{ color: 'hsl(var(--color-text-muted))' }}>|</span>

                      <div style={{ position: 'relative' }}>
                        <input 
                          type="file" 
                          accept=".json"
                          id="importFile"
                          style={{ display: 'none' }}
                          onChange={async (e) => {
                            const file = e.target.files?.[0];
                            if (!file) return;
                            
                            if (confirm('BẠN CÓ CHẮC CHẮN? Import sẽ ghi đè và xoá toàn bộ dữ liệu hiện tại!')) {
                              try {
                                setSaving(true);
                                const reader = new FileReader();
                                reader.onload = async (event) => {
                                  try {
                                    const json = JSON.parse(event.target?.result as string);
                                    await api.post('/settings/tools/import', json);
                                    alert('Phục hồi dữ liệu thành công!');
                                    window.location.reload();
                                  } catch (err) {
                                    alert('File JSON không hợp lệ hoặc lỗi import');
                                  }
                                };
                                reader.readAsText(file);
                              } catch (err: any) {
                                alert('Lỗi import: ' + err.message);
                              } finally {
                                setSaving(false);
                                e.target.value = ''; // reset
                              }
                            }
                          }}
                        />
                        <button 
                          type="button"
                          className="kb-btn kb-btn--outline" 
                          onClick={() => document.getElementById('importFile')?.click()}
                        >
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{marginRight: '0.5rem'}}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                          Nhập dữ liệu (Import JSON)
                        </button>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            )}

            {activeTab !== 'general' && activeTab !== 'appearance' && activeTab !== 'tools' && (
              <div className="animate-fade-in" style={{ animationDuration: '0.2s', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', height: '300px' }}>
                <Lock size={48} style={{ color: 'hsl(var(--color-text-muted))', opacity: 0.3, marginBottom: '1rem' }} />
                <h3 style={{ color: 'hsl(var(--color-text-muted))' }}>Chức năng đang được phát triển</h3>
              </div>
            )}
          </form>
        </div>
      </div>
    </div>
  );
}
