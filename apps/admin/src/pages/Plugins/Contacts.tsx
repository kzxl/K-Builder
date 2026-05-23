import { useState, useEffect } from 'react';
import { 
  Mail, Zap, Clock, CheckCircle, Search, Filter, Trash2, Eye, 
  RefreshCw, FileSpreadsheet, User, Phone, MessageSquare, 
  Bookmark, ChevronLeft, ChevronRight, X, Copy, Check
} from 'lucide-react';
import api from '../../lib/api';

interface Contact {
  id: number;
  name: string;
  email: string;
  phone: string;
  message: string;
  ip_address: string;
  status: 'new' | 'read' | 'in_progress' | 'resolved' | 'ignored';
  priority: 'low' | 'medium' | 'high';
  notes: string | null;
  created_at: string;
  updated_at: string;
}

interface Stats {
  total: number;
  new: number;
  read: number;
  in_progress: number;
  resolved: number;
  ignored: number;
  priority: {
    low: number;
    medium: number;
    high: number;
  };
  chart_7_days: Array<{ date: string; count: number }>;
}

export default function Contacts() {
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);
  const [loading, setLoading] = useState(true);
  const [statsLoading, setStatsLoading] = useState(true);
  const [selectedContact, setSelectedContact] = useState<Contact | null>(null);
  
  // Filters & Search
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [priority, setPriority] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [limit] = useState(10);
  
  // Modal Edit State
  const [notes, setNotes] = useState('');
  const [detailStatus, setDetailStatus] = useState<string>('new');
  const [detailPriority, setDetailPriority] = useState<string>('medium');
  const [savingDetail, setSavingDetail] = useState(false);
  const [copiedField, setCopiedField] = useState<string | null>(null);

  const fetchContacts = async () => {
    setLoading(true);
    try {
      const res = await api.get('/admin/contacts', {
        params: { page, limit, search, status, priority }
      });
      if (res.data.success) {
        setContacts(res.data.data.items);
        setTotalPages(res.data.data.pagination.pages);
      }
    } catch (e) {
      console.error('Failed to fetch contacts', e);
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    setStatsLoading(true);
    try {
      const res = await api.get('/admin/contacts/stats/summary');
      if (res.data.success) {
        setStats(res.data.data);
      }
    } catch (e) {
      console.error('Failed to fetch stats', e);
    } finally {
      setStatsLoading(false);
    }
  };

  useEffect(() => {
    fetchContacts();
  }, [page, status, priority]);

  useEffect(() => {
    fetchStats();
  }, []);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    fetchContacts();
  };

  const handleResetFilters = () => {
    setSearch('');
    setStatus('');
    setPriority('');
    setPage(1);
    // Cần gọi lại vì search state không được bind tự động kích hoạt useEffect
    api.get('/admin/contacts', {
      params: { page: 1, limit, search: '', status: '', priority: '' }
    }).then(res => {
      if (res.data.success) {
        setContacts(res.data.data.items);
        setTotalPages(res.data.data.pagination.pages);
      }
    });
  };

  const deleteContact = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa lượt liên hệ này? Thao tác này không thể khôi phục.')) return;
    try {
      await api.delete(`/admin/contacts/${id}`);
      fetchContacts();
      fetchStats();
      if (selectedContact?.id === id) {
        setSelectedContact(null);
      }
    } catch (e) {
      alert('Xóa liên hệ thất bại.');
    }
  };

  const openDetail = async (contact: Contact) => {
    setSelectedContact(contact);
    setNotes(contact.notes || '');
    setDetailStatus(contact.status);
    setDetailPriority(contact.priority);
    
    // Nếu status là new, backend tự động chuyển thành read. Cập nhật UI list và stats.
    if (contact.status === 'new') {
      try {
        const res = await api.get(`/admin/contacts/${contact.id}`);
        if (res.data.success) {
          // Refresh list và stats để phản ánh trạng thái "Đã đọc"
          fetchContacts();
          fetchStats();
        }
      } catch (e) {
        console.error('Auto mark as read failed', e);
      }
    }
  };

  const saveDetailChanges = async () => {
    if (!selectedContact) return;
    setSavingDetail(true);
    try {
      const res = await api.put(`/admin/contacts/${selectedContact.id}`, {
        status: detailStatus,
        priority: detailPriority,
        notes: notes
      });
      if (res.data.success) {
        setSelectedContact({
          ...selectedContact,
          status: detailStatus as any,
          priority: detailPriority as any,
          notes: notes
        });
        fetchContacts();
        fetchStats();
        alert('Đã cập nhật thông tin CRM thành công!');
      }
    } catch (e) {
      alert('Cập nhật thất bại.');
    } finally {
      setSavingDetail(false);
    }
  };

  const copyToClipboard = (text: string, field: string) => {
    navigator.clipboard.writeText(text);
    setCopiedField(field);
    setTimeout(() => setCopiedField(null), 2000);
  };

  // Trực quan hóa dữ liệu biểu đồ bằng CSS Divs
  const getMaxChartCount = () => {
    if (!stats || !stats.chart_7_days.length) return 1;
    const counts = stats.chart_7_days.map(d => d.count);
    const max = Math.max(...counts);
    return max === 0 ? 1 : max;
  };

  // Tiện ích xuất CSV
  const handleExportCSV = () => {
    if (contacts.length === 0) {
      alert('Không có dữ liệu để xuất.');
      return;
    }
    
    // Tạo headers
    const headers = ['ID', 'Họ Tên', 'Email', 'Số Điện Thoại', 'Nội Dung Lời Nhắn', 'Ghi Chú', 'Trạng Thái', 'Độ Ưu Tiên', 'IP Address', 'Ngày Gửi'];
    
    // Bản dịch trạng thái
    const statusMap: Record<string, string> = {
      new: 'Mới',
      read: 'Đã đọc',
      in_progress: 'Đang xử lý',
      resolved: 'Đã giải quyết',
      ignored: 'Bỏ qua'
    };
    const priorityMap: Record<string, string> = {
      low: 'Thấp',
      medium: 'Trung bình',
      high: 'Cao'
    };

    // Tạo rows
    const rows = contacts.map(c => [
      c.id,
      `"${c.name.replace(/"/g, '""')}"`,
      c.email,
      c.phone || '',
      `"${c.message.replace(/"/g, '""')}"`,
      `"${(c.notes || '').replace(/"/g, '""')}"`,
      statusMap[c.status] || c.status,
      priorityMap[c.priority] || c.priority,
      c.ip_address || '',
      new Date(c.created_at).toLocaleString('vi-VN')
    ]);

    const csvContent = "\uFEFF" + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `contacts_export_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'new': return 'kb-badge--primary';
      case 'read': return 'kb-badge--neutral';
      case 'in_progress': return 'kb-badge--warning';
      case 'resolved': return 'kb-badge--success';
      case 'ignored': return 'kb-badge--danger';
      default: return 'kb-badge--neutral';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'new': return 'Mới';
      case 'read': return 'Đã xem';
      case 'in_progress': return 'Đang xử lý';
      case 'resolved': return 'Đã xong';
      case 'ignored': return 'Bỏ qua';
      default: return status;
    }
  };

  const getPriorityBadgeClass = (priority: string) => {
    switch (priority) {
      case 'high': return 'kb-badge--danger';
      case 'medium': return 'kb-badge--warning';
      case 'low': return 'kb-badge--success';
      default: return 'kb-badge--neutral';
    }
  };

  const getPriorityLabel = (priority: string) => {
    switch (priority) {
      case 'high': return 'Cao';
      case 'medium': return 'T.Bình';
      case 'low': return 'Thấp';
      default: return priority;
    }
  };

  // Mẫu email phản hồi CRM chuyên nghiệp
  const getMailtoLink = (templateType: 'ack' | 'quote' | 'meeting') => {
    if (!selectedContact) return '';
    let subject = '';
    let body = '';
    
    switch (templateType) {
      case 'ack':
        subject = `[KBuilder] Xác nhận đã nhận thông tin liên hệ của ${selectedContact.name}`;
        body = `Xin chào ${selectedContact.name},\n\nChúng tôi đã nhận được yêu cầu liên hệ từ bạn thông qua website.\nNội dung bạn gửi: "${selectedContact.message}"\n\nChúng tôi đang xử lý thông tin này và sẽ liên hệ lại với bạn trong vòng 24 giờ tới.\n\nTrân trọng,\nĐội ngũ KBuilder.`;
        break;
      case 'quote':
        subject = `[KBuilder] Gửi thông tin báo giá & tư vấn dịch vụ cho ${selectedContact.name}`;
        body = `Xin chào ${selectedContact.name},\n\nCảm ơn bạn đã quan tâm đến giải pháp của chúng tôi.\nDựa trên yêu cầu liên hệ của bạn, chúng tôi gửi kèm tài liệu giới thiệu và bảng báo giá chi tiết sản phẩm.\n\n[Đính kèm file báo giá tại đây]\n\nHãy phản hồi lại email này nếu bạn cần bất kỳ sự hỗ trợ nào.\n\nTrân trọng,\nĐội ngũ KBuilder.`;
        break;
      case 'meeting':
        subject = `[KBuilder] Lịch hẹn trao đổi chi tiết về yêu cầu hỗ trợ của ${selectedContact.name}`;
        body = `Xin chào ${selectedContact.name},\n\nCảm ơn bạn đã để lại lời nhắn.\nĐể trao đổi rõ hơn về nhu cầu của bạn, chúng tôi kính mời bạn tham gia một buổi meeting trực tuyến khoảng 15 phút.\n\nVui lòng lựa chọn khung giờ phù hợp với bạn:\n1. 9:00 AM - Thứ Hai\n2. 2:00 PM - Thứ Ba\n3. Khung giờ khác (vui lòng đề xuất).\n\nRất mong nhận được phản hồi từ bạn.\n\nTrân trọng,\nĐội ngũ KBuilder.`;
        break;
    }
    
    return `mailto:${selectedContact.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  };

  return (
    <div className="animate-fade-in" style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Page Header */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '1rem' }}>
        <div>
          <h2 style={{ fontSize: '1.75rem', fontWeight: 700 }}>Quản trị Khách hàng (CRM)</h2>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginTop: '0.25rem' }}>
            Theo dõi, ghi chú và xử lý các phản hồi của khách hàng từ Biểu mẫu liên hệ.
          </p>
        </div>
        <div style={{ display: 'flex', gap: '0.75rem' }}>
          <button onClick={handleExportCSV} className="kb-btn kb-btn--outline">
            <FileSpreadsheet size={16} /> Xuất Excel (CSV)
          </button>
          <button 
            onClick={() => { fetchContacts(); fetchStats(); }} 
            className="kb-btn kb-btn--primary"
            style={{ minWidth: '130px' }}
          >
            <RefreshCw size={16} /> Làm mới
          </button>
        </div>
      </div>

      {/* Metrics Bar */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '1.5rem' }}>
        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-primary)/0.1)', color: 'hsl(var(--color-primary))', borderRadius: 'var(--radius-md)' }}>
            <Mail size={24} />
          </div>
          <div>
            <div style={{ fontSize: '1.75rem', fontWeight: 700, lineHeight: 1 }}>{statsLoading ? '...' : stats?.total}</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.85rem', marginTop: '0.25rem', fontWeight: 500 }}>Tổng lượt liên hệ</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-primary)/0.15)', color: 'hsl(var(--color-primary))', borderRadius: 'var(--radius-md)' }}>
            <Zap size={24} />
          </div>
          <div>
            <div style={{ fontSize: '1.75rem', fontWeight: 700, lineHeight: 1, color: 'hsl(var(--color-primary))' }}>{statsLoading ? '...' : stats?.new}</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.85rem', marginTop: '0.25rem', fontWeight: 500 }}>Khách mới cần đọc</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-warning)/0.1)', color: 'hsl(var(--color-warning))', borderRadius: 'var(--radius-md)' }}>
            <Clock size={24} />
          </div>
          <div>
            <div style={{ fontSize: '1.75rem', fontWeight: 700, lineHeight: 1, color: 'hsl(var(--color-warning))' }}>{statsLoading ? '...' : stats?.in_progress}</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.85rem', marginTop: '0.25rem', fontWeight: 500 }}>Đang xử lý/Liên hệ</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-success)/0.1)', color: 'hsl(var(--color-success))', borderRadius: 'var(--radius-md)' }}>
            <CheckCircle size={24} />
          </div>
          <div>
            <div style={{ fontSize: '1.75rem', fontWeight: 700, lineHeight: 1, color: 'hsl(var(--color-success))' }}>{statsLoading ? '...' : stats?.resolved}</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.85rem', marginTop: '0.25rem', fontWeight: 500 }}>Đã hoàn tất chăm sóc</div>
          </div>
        </div>
      </div>

      {/* Analytics & 7 Day Chart */}
      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '1.5rem', flexWrap: 'wrap' }}>
        
        {/* CSS Chart 7 Days */}
        <div className="kb-card" style={{ padding: '1.5rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <h3 style={{ fontSize: '1.1rem', fontWeight: 600 }}>Tần suất nhận liên hệ (7 ngày gần nhất)</h3>
          
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', height: '180px', padding: '1rem 0', borderBottom: '1px solid hsl(var(--color-border))' }}>
            {statsLoading ? (
              <div style={{ width: '100%', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Đang nạp dữ liệu thống kê...</div>
            ) : stats?.chart_7_days.map((item, index) => {
              const heightPercent = (item.count / getMaxChartCount()) * 100;
              return (
                <div key={index} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', flex: 1, gap: '0.5rem' }}>
                  <div style={{ color: 'hsl(var(--color-primary))', fontSize: '0.8rem', fontWeight: 600 }}>{item.count}</div>
                  <div 
                    style={{ 
                      width: '60%', 
                      maxWidth: '30px', 
                      height: `${Math.max(8, heightPercent * 1.2)}px`, // tối thiểu 8px để vẫn nhìn thấy cột 0
                      background: item.count > 0 
                        ? 'linear-gradient(to top, hsl(var(--color-primary)), hsl(var(--color-secondary)))' 
                        : 'hsla(var(--color-text-muted)/0.1)', 
                      borderRadius: 'var(--radius-sm) var(--radius-sm) 0 0',
                      transition: 'height 0.5s ease-out'
                    }}
                  />
                  <div style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))' }}>{item.date}</div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Priority distribution card */}
        <div className="kb-card" style={{ padding: '1.5rem', display: 'flex', flexDirection: 'column', gap: '1.25rem', justifyContent: 'space-between' }}>
          <h3 style={{ fontSize: '1.1rem', fontWeight: 600 }}>Phân phối độ ưu tiên</h3>
          
          {statsLoading ? (
            <div style={{ textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>...</div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', marginBottom: '0.25rem' }}>
                  <span style={{ fontWeight: 600, color: 'hsl(var(--color-danger))' }}>Ưu tiên Cao (High)</span>
                  <span>{stats?.priority.high} lượt</span>
                </div>
                <div style={{ height: '8px', background: 'hsla(var(--color-danger)/0.1)', borderRadius: '9999px', overflow: 'hidden' }}>
                  <div style={{ width: `${(stats?.priority.high || 0) / (stats?.total || 1) * 100}%`, height: '100%', background: 'hsl(var(--color-danger))' }} />
                </div>
              </div>

              <div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', marginBottom: '0.25rem' }}>
                  <span style={{ fontWeight: 600, color: 'hsl(var(--color-warning))' }}>Ưu tiên Trung bình (Medium)</span>
                  <span>{stats?.priority.medium} lượt</span>
                </div>
                <div style={{ height: '8px', background: 'hsla(var(--color-warning)/0.1)', borderRadius: '9999px', overflow: 'hidden' }}>
                  <div style={{ width: `${(stats?.priority.medium || 0) / (stats?.total || 1) * 100}%`, height: '100%', background: 'hsl(var(--color-warning))' }} />
                </div>
              </div>

              <div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', marginBottom: '0.25rem' }}>
                  <span style={{ fontWeight: 600, color: 'hsl(var(--color-success))' }}>Ưu tiên Thấp (Low)</span>
                  <span>{stats?.priority.low} lượt</span>
                </div>
                <div style={{ height: '8px', background: 'hsla(var(--color-success)/0.1)', borderRadius: '9999px', overflow: 'hidden' }}>
                  <div style={{ width: `${(stats?.priority.low || 0) / (stats?.total || 1) * 100}%`, height: '100%', background: 'hsl(var(--color-success))' }} />
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Main Content Area */}
      <div className="kb-card" style={{ padding: '2rem' }}>
        
        {/* Filter Section */}
        <form onSubmit={handleSearchSubmit} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
          
          <div style={{ display: 'flex', gap: '1rem', flex: 1, minWidth: '300px' }}>
            <div style={{ position: 'relative', flex: 1 }}>
              <Search size={16} style={{ position: 'absolute', left: '1rem', top: '50%', transform: 'translateY(-50%)', color: 'hsl(var(--color-text-muted))' }} />
              <input 
                type="text" 
                className="kb-input" 
                placeholder="Tìm tên, email, sđt, tin nhắn..." 
                value={search}
                onChange={e => setSearch(e.target.value)}
                style={{ paddingLeft: '2.5rem' }}
              />
            </div>
            <button type="submit" className="kb-btn kb-btn--outline" style={{ minWidth: '90px' }}>
              Tìm
            </button>
          </div>

          <div style={{ display: 'flex', gap: '1rem', flexWrap: 'wrap' }}>
            
            {/* Status Filter */}
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
              <Filter size={14} style={{ color: 'hsl(var(--color-text-muted))' }} />
              <select 
                className="kb-input" 
                value={status} 
                onChange={e => { setStatus(e.target.value); setPage(1); }}
                style={{ padding: '0.5rem 1rem', width: '150px' }}
              >
                <option value="">-- Trạng thái --</option>
                <option value="new">Chưa đọc (Mới)</option>
                <option value="read">Đã đọc (Xem)</option>
                <option value="in_progress">Đang xử lý</option>
                <option value="resolved">Hoàn thành</option>
                <option value="ignored">Bỏ qua</option>
              </select>
            </div>

            {/* Priority Filter */}
            <select 
              className="kb-input" 
              value={priority} 
              onChange={e => { setPriority(e.target.value); setPage(1); }}
              style={{ padding: '0.5rem 1rem', width: '150px' }}
            >
              <option value="">-- Mức ưu tiên --</option>
              <option value="low">Thấp</option>
              <option value="medium">Trung bình</option>
              <option value="high">Cao</option>
            </select>

            <button type="button" onClick={handleResetFilters} className="kb-btn kb-btn--outline" style={{ padding: '0.5rem 1rem' }}>
              Đặt lại lọc
            </button>

          </div>
        </form>

        {/* Data Table */}
        <div className="kb-table-container">
          <table className="kb-table">
            <thead>
              <tr>
                <th style={{ width: '180px' }}>Họ Tên</th>
                <th style={{ width: '220px' }}>Thông Tin Liên Hệ</th>
                <th>Lời Nhắn / Yêu Cầu</th>
                <th style={{ width: '140px' }}>Độ Ưu Tiên</th>
                <th style={{ width: '130px' }}>Trạng Thái</th>
                <th style={{ width: '110px' }}>Ngày Gửi</th>
                <th style={{ width: '100px', textAlign: 'right' }}>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={7} style={{ textAlign: 'center', padding: '3rem', color: 'hsl(var(--color-text-muted))' }}>
                    Đang nạp danh sách khách hàng liên hệ...
                  </td>
                </tr>
              ) : contacts.length === 0 ? (
                <tr>
                  <td colSpan={7} style={{ textAlign: 'center', padding: '3rem', color: 'hsl(var(--color-text-muted))' }}>
                    Không tìm thấy lượt liên hệ nào phù hợp bộ lọc.
                  </td>
                </tr>
              ) : (
                contacts.map(c => (
                  <tr key={c.id}>
                    <td style={{ fontWeight: 600 }}>{c.name}</td>
                    <td>
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.2rem' }}>
                        <a href={`mailto:${c.email}`} className="text-primary" style={{ fontSize: '0.9rem', display: 'inline-flex', alignItems: 'center', gap: '0.25rem' }}>
                          <Mail size={12} /> {c.email}
                        </a>
                        {c.phone && (
                          <a href={`tel:${c.phone}`} style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))', display: 'inline-flex', alignItems: 'center', gap: '0.25rem' }}>
                            <Phone size={12} /> {c.phone}
                          </a>
                        )}
                      </div>
                    </td>
                    <td>
                      <div style={{ 
                        maxWidth: '300px', 
                        whiteSpace: 'nowrap', 
                        overflow: 'hidden', 
                        textOverflow: 'ellipsis', 
                        fontSize: '0.9rem',
                        color: 'hsl(var(--color-text-muted))' 
                      }}>
                        {c.message}
                      </div>
                    </td>
                    <td>
                      <span className={`kb-badge ${getPriorityBadgeClass(c.priority)}`}>
                        {getPriorityLabel(c.priority)}
                      </span>
                    </td>
                    <td>
                      <span className={`kb-badge ${getStatusBadgeClass(c.status)}`}>
                        {getStatusLabel(c.status)}
                      </span>
                    </td>
                    <td style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>
                      {new Date(c.created_at).toLocaleDateString('vi-VN')}
                    </td>
                    <td style={{ textAlign: 'right' }}>
                      <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
                        <button 
                          onClick={() => openDetail(c)} 
                          className="kb-btn kb-btn--outline kb-btn--sm" 
                          title="Chi tiết & Chăm sóc"
                          style={{ padding: '0.35rem' }}
                        >
                          <Eye size={14} />
                        </button>
                        <button 
                          onClick={() => deleteContact(c.id)} 
                          className="kb-btn kb-btn--sm" 
                          title="Xóa liên hệ"
                          style={{ color: 'hsl(var(--color-danger))', background: 'hsla(var(--color-danger)/0.1)', padding: '0.35rem' }}
                        >
                          <Trash2 size={14} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination Section */}
        {totalPages > 1 && (
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '1.5rem' }}>
            <span style={{ fontSize: '0.9rem', color: 'hsl(var(--color-text-muted))' }}>
              Trang {page} / {totalPages}
            </span>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              <button 
                onClick={() => setPage(p => Math.max(1, p - 1))} 
                disabled={page === 1}
                className="kb-btn kb-btn--outline kb-btn--sm"
              >
                <ChevronLeft size={16} /> Trước
              </button>
              <button 
                onClick={() => setPage(p => Math.min(totalPages, p + 1))} 
                disabled={page === totalPages}
                className="kb-btn kb-btn--outline kb-btn--sm"
              >
                Sau <ChevronRight size={16} />
              </button>
            </div>
          </div>
        )}
      </div>

      {/* CRM Customer Profile / Detail Modal */}
      {selectedContact && (
        <div style={{ 
          position: 'fixed', 
          inset: 0, 
          background: 'hsla(var(--color-sidebar)/0.4)', 
          backdropFilter: 'blur(8px)', 
          WebkitBackdropFilter: 'blur(8px)',
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'center', 
          zIndex: 100 
        }}>
          
          <div className="kb-card animate-fade-in" style={{ width: '90%', maxWidth: '850px', maxHeight: '90vh', overflowY: 'auto', padding: '2.5rem', display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
            
            {/* Modal Header */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid hsl(var(--color-border))', paddingBottom: '1rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                <Bookmark size={24} style={{ color: 'hsl(var(--color-primary))' }} />
                <div>
                  <h3 style={{ fontSize: '1.5rem', fontWeight: 700 }}>Thông Tin Liên Hệ #{selectedContact.id}</h3>
                  <p style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>
                    Khởi tạo từ IP: {selectedContact.ip_address} | Gửi lúc: {new Date(selectedContact.created_at).toLocaleString('vi-VN')}
                  </p>
                </div>
              </div>
              <button 
                onClick={() => setSelectedContact(null)} 
                style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'hsl(var(--color-text-muted))' }}
              >
                <X size={24} />
              </button>
            </div>

            {/* Modal Body: 2 columns */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.2fr', gap: '2rem', flexWrap: 'wrap' }}>
              
              {/* Column 1: Client Card */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
                
                <div style={{ background: 'hsla(var(--color-surface-hover)/0.5)', padding: '1.25rem', borderRadius: 'var(--radius-md)', border: '1px solid hsl(var(--color-border))' }}>
                  <h4 style={{ fontSize: '0.95rem', fontWeight: 600, marginBottom: '1rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <User size={16} /> Hồ sơ khách hàng
                  </h4>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem', fontSize: '0.9rem' }}>
                    
                    <div>
                      <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.8rem' }}>Họ Tên khách hàng</div>
                      <div style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.5rem', marginTop: '0.15rem' }}>
                        {selectedContact.name}
                        <button onClick={() => copyToClipboard(selectedContact.name, 'name')} style={{ color: 'hsl(var(--color-text-muted))' }} title="Sao chép">
                          {copiedField === 'name' ? <Check size={12} style={{color:'hsl(var(--color-success))'}} /> : <Copy size={12} />}
                        </button>
                      </div>
                    </div>

                    <div>
                      <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.8rem' }}>Địa chỉ Email</div>
                      <div style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.5rem', marginTop: '0.15rem' }}>
                        <a href={`mailto:${selectedContact.email}`} className="text-primary">{selectedContact.email}</a>
                        <button onClick={() => copyToClipboard(selectedContact.email, 'email')} style={{ color: 'hsl(var(--color-text-muted))' }} title="Sao chép">
                          {copiedField === 'email' ? <Check size={12} style={{color:'hsl(var(--color-success))'}} /> : <Copy size={12} />}
                        </button>
                      </div>
                    </div>

                    {selectedContact.phone && (
                      <div>
                        <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.8rem' }}>Số điện thoại</div>
                        <div style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.5rem', marginTop: '0.15rem' }}>
                          <a href={`tel:${selectedContact.phone}`}>{selectedContact.phone}</a>
                          <button onClick={() => copyToClipboard(selectedContact.phone, 'phone')} style={{ color: 'hsl(var(--color-text-muted))' }} title="Sao chép">
                            {copiedField === 'phone' ? <Check size={12} style={{color:'hsl(var(--color-success))'}} /> : <Copy size={12} />}
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>

                {/* Email Templates section */}
                <div style={{ border: '1px solid hsl(var(--color-border))', borderRadius: 'var(--radius-md)', padding: '1.25rem' }}>
                  <h4 style={{ fontSize: '0.95rem', fontWeight: 600, marginBottom: '0.75rem', display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <MessageSquare size={16} /> Phản hồi khách nhanh
                  </h4>
                  <p style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))', marginBottom: '1rem' }}>
                    Click vào mẫu bên dưới để tạo thư phản hồi tự động trong trình quản lý Mail của bạn:
                  </p>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                    <a href={getMailtoLink('ack')} className="kb-btn kb-btn--outline kb-btn--sm" style={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.85rem' }}>
                      📨 Mẫu 1: Xác nhận đã nhận tin
                    </a>
                    <a href={getMailtoLink('quote')} className="kb-btn kb-btn--outline kb-btn--sm" style={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.85rem' }}>
                      📄 Mẫu 2: Báo giá & Tư vấn
                    </a>
                    <a href={getMailtoLink('meeting')} className="kb-btn kb-btn--outline kb-btn--sm" style={{ width: '100%', justifyContent: 'flex-start', fontSize: '0.85rem' }}>
                      📆 Mẫu 3: Mời họp trao đổi (15m)
                    </a>
                  </div>
                </div>

              </div>

              {/* Column 2: Message Content, Status edit, Note Edit */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
                
                {/* Message bubble */}
                <div>
                  <label className="kb-label" style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                    <MessageSquare size={14} /> Nội dung lời nhắn khách hàng gửi
                  </label>
                  <div style={{ 
                    background: 'hsla(var(--color-primary)/0.03)', 
                    border: '1px solid hsla(var(--color-primary)/0.1)', 
                    padding: '1.25rem', 
                    borderRadius: 'var(--radius-md)', 
                    fontSize: '0.95rem',
                    lineHeight: 1.5,
                    maxHeight: '150px',
                    overflowY: 'auto'
                  }}>
                    {selectedContact.message}
                  </div>
                </div>

                {/* Edit Controls */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                  <div>
                    <label className="kb-label">Trạng thái chăm sóc</label>
                    <select 
                      className="kb-input" 
                      value={detailStatus} 
                      onChange={e => setDetailStatus(e.target.value)}
                      style={{ padding: '0.6rem 1rem' }}
                    >
                      <option value="new">Chưa đọc</option>
                      <option value="read">Đã đọc</option>
                      <option value="in_progress">Đang xử lý</option>
                      <option value="resolved">Hoàn thành</option>
                      <option value="ignored">Bỏ qua</option>
                    </select>
                  </div>
                  <div>
                    <label className="kb-label">Mức độ ưu tiên CRM</label>
                    <select 
                      className="kb-input" 
                      value={detailPriority} 
                      onChange={e => setDetailPriority(e.target.value)}
                      style={{ padding: '0.6rem 1rem' }}
                    >
                      <option value="low">Thấp</option>
                      <option value="medium">Trung bình</option>
                      <option value="high">Cao</option>
                    </select>
                  </div>
                </div>

                {/* Internal Notes */}
                <div>
                  <label className="kb-label" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <span>✍️ Nhật ký chăm sóc (Internal Notes)</span>
                    <span style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))', fontWeight: 'normal' }}>Ghi chú nội bộ dành cho nhân viên sales</span>
                  </label>
                  <textarea 
                    className="kb-input" 
                    rows={4}
                    value={notes}
                    onChange={e => setNotes(e.target.value)}
                    placeholder="Ghi chú kết quả gọi điện, nhu cầu khách, báo giá đã gửi..."
                    style={{ fontSize: '0.9rem', lineHeight: 1.4 }}
                  />
                </div>

                {/* Save button inside modal */}
                <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end', marginTop: '1rem' }}>
                  <button type="button" className="kb-btn kb-btn--outline" onClick={() => setSelectedContact(null)}>
                    Đóng
                  </button>
                  <button 
                    type="button" 
                    className="kb-btn kb-btn--primary" 
                    onClick={saveDetailChanges}
                    disabled={savingDetail}
                  >
                    {savingDetail ? 'Đang lưu...' : 'Lưu cập nhật CRM'}
                  </button>
                </div>

              </div>

            </div>

          </div>
        </div>
      )}

    </div>
  );
}
