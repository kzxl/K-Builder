import { useState, useEffect } from 'react';
import { BarChart3, Users, Clock, Globe } from 'lucide-react';

export default function Analytics() {
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Giả lập fetch data
    setTimeout(() => setLoading(false), 800);
  }, []);

  if (loading) return <div style={{ padding: '2rem' }}>Đang tải dữ liệu thống kê...</div>;

  return (
    <div className="animate-fade-in">
      <div className="kb-page-header">
        <h1 className="kb-page-title">Thống kê Truy cập</h1>
        <p className="kb-page-subtitle">Dữ liệu phân tích lưu lượng truy cập (Bản Demo)</p>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '1.5rem', marginBottom: '2rem' }}>
        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-primary)/0.1)', color: 'hsl(var(--color-primary))', borderRadius: 'var(--radius-lg)' }}>
            <Users size={32} />
          </div>
          <div>
            <div style={{ fontSize: '2rem', fontWeight: 700, lineHeight: 1 }}>1,248</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginTop: '0.25rem' }}>Người truy cập (30 ngày)</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-success)/0.1)', color: 'hsl(var(--color-success))', borderRadius: 'var(--radius-lg)' }}>
            <BarChart3 size={32} />
          </div>
          <div>
            <div style={{ fontSize: '2rem', fontWeight: 700, lineHeight: 1 }}>5,432</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginTop: '0.25rem' }}>Lượt xem trang</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-warning)/0.1)', color: 'hsl(var(--color-warning))', borderRadius: 'var(--radius-lg)' }}>
            <Clock size={32} />
          </div>
          <div>
            <div style={{ fontSize: '2rem', fontWeight: 700, lineHeight: 1 }}>02:15</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginTop: '0.25rem' }}>Thời gian trung bình</div>
          </div>
        </div>

        <div className="kb-card" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
          <div style={{ padding: '1rem', background: 'hsla(var(--color-secondary)/0.1)', color: 'hsl(var(--color-secondary))', borderRadius: 'var(--radius-lg)' }}>
            <Globe size={32} />
          </div>
          <div>
            <div style={{ fontSize: '2rem', fontWeight: 700, lineHeight: 1 }}>24%</div>
            <div style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.9rem', marginTop: '0.25rem' }}>Tỷ lệ thoát</div>
          </div>
        </div>
      </div>

      <div className="kb-card" style={{ minHeight: '400px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <div style={{ textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
          <BarChart3 size={48} style={{ opacity: 0.2, margin: '0 auto 1rem' }} />
          <p>Biểu đồ truy cập theo ngày sẽ được hiển thị tại đây.</p>
          <p style={{ fontSize: '0.85rem', marginTop: '0.5rem' }}>Tính năng đang được phát triển bởi plugin kb-analytics.</p>
        </div>
      </div>
    </div>
  );
}
