import { useState, useEffect } from 'react';
import { useAuthStore } from '../store/authStore';
import { FileText, Image as ImageIcon, Box, Activity, ArrowUpRight, Edit, Clock } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import api from '../lib/api';

interface DashboardStats {
  counts: {
    pages_total: number;
    pages_published: number;
    pages_draft: number;
    media_total: number;
    plugins_active: number;
  };
  recent_pages: {
    id: number;
    title: string;
    slug: string;
    updated_at: string;
    status: string;
  }[];
}

export default function Dashboard() {
  const user = useAuthStore(state => state.user);
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const res = await api.get('/dashboard/stats');
        if (res.data.success) {
          setStats(res.data.data);
        }
      } catch (err) {
        console.error('Failed to fetch dashboard stats', err);
      } finally {
        setLoading(false);
      }
    };
    fetchStats();
  }, []);

  const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
  };

  if (loading) {
    return <div style={{ padding: '3rem', textAlign: 'center' }}>Đang tải dữ liệu...</div>;
  }

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: '2.5rem' }}>
        <h1 style={{ fontSize: '2rem', marginBottom: '0.5rem', background: 'linear-gradient(135deg, hsl(var(--color-primary)), hsl(var(--color-secondary)))', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>
          Welcome back, {user?.name}! 👋
        </h1>
        <p className="text-muted" style={{ fontSize: '1.05rem' }}>
          Here is what's happening with your projects today.
        </p>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1.5rem', marginBottom: '2.5rem' }}>
        <div className="kb-card">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1.5rem' }}>
            <div style={{ background: 'hsla(var(--color-primary)/0.1)', padding: '0.75rem', borderRadius: 'var(--radius-md)', color: 'hsl(var(--color-primary))' }}>
              <FileText size={24} />
            </div>
            <span className="kb-badge kb-badge--success">{stats?.counts.pages_published} Published</span>
          </div>
          <h3 style={{ fontSize: '2rem', marginBottom: '0.25rem' }}>{stats?.counts.pages_total || 0}</h3>
          <p className="text-muted font-semibold">Total Pages ({stats?.counts.pages_draft} Drafts)</p>
        </div>
        
        <div className="kb-card">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1.5rem' }}>
            <div style={{ background: 'hsla(var(--color-secondary)/0.1)', padding: '0.75rem', borderRadius: 'var(--radius-md)', color: 'hsl(var(--color-secondary))' }}>
              <ImageIcon size={24} />
            </div>
            <span className="kb-badge kb-badge--warning">Assets</span>
          </div>
          <h3 style={{ fontSize: '2rem', marginBottom: '0.25rem' }}>{stats?.counts.media_total || 0}</h3>
          <p className="text-muted font-semibold">Media Library</p>
        </div>

        <div className="kb-card">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '1.5rem' }}>
            <div style={{ background: 'hsla(var(--color-success)/0.1)', padding: '0.75rem', borderRadius: 'var(--radius-md)', color: 'hsl(var(--color-success))' }}>
              <Box size={24} />
            </div>
            <span className="kb-badge kb-badge--neutral">Active</span>
          </div>
          <h3 style={{ fontSize: '2rem', marginBottom: '0.25rem' }}>{stats?.counts.plugins_active || 0}</h3>
          <p className="text-muted font-semibold">Active Plugins</p>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '1.5rem' }}>
        <div className="kb-card" style={{ minHeight: '300px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
            <h3 style={{ fontSize: '1.25rem' }}>Hoạt động gần đây (Recent Activity)</h3>
            <button className="kb-btn kb-btn--sm kb-btn--outline" onClick={() => navigate('/pages')}>View All <ArrowUpRight size={14} style={{marginLeft: '0.25rem'}}/></button>
          </div>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            {stats?.recent_pages && stats.recent_pages.length > 0 ? (
              stats.recent_pages.map((page, i) => (
                <div key={page.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingBottom: '1rem', borderBottom: i !== stats.recent_pages.length - 1 ? '1px solid hsl(var(--color-border))' : 'none' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                    <div style={{ width: '40px', height: '40px', borderRadius: 'var(--radius-md)', background: 'hsla(var(--color-primary)/0.1)', color: 'hsl(var(--color-primary))', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <FileText size={20} />
                    </div>
                    <div>
                      <p style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                        {page.title}
                        <span className={`kb-badge kb-badge--sm ${page.status === 'published' ? 'kb-badge--success' : 'kb-badge--neutral'}`}>{page.status}</span>
                      </p>
                      <p style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))', display: 'flex', alignItems: 'center', gap: '0.25rem', marginTop: '0.25rem' }}>
                        <Clock size={12} /> {formatDate(page.updated_at)}
                      </p>
                    </div>
                  </div>
                  <button onClick={() => navigate(`/builder/${page.id}`)} className="kb-btn kb-btn--outline kb-btn--sm" title="Edit">
                    <Edit size={14} />
                  </button>
                </div>
              ))
            ) : (
              <div style={{ padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
                Chưa có trang nào được cập nhật gần đây.
              </div>
            )}
          </div>
        </div>

        <div className="kb-card">
          <h3 style={{ fontSize: '1.25rem', marginBottom: '1.5rem' }}>Hệ thống</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <div style={{ padding: '1rem', background: 'hsla(var(--color-surface-hover)/0.5)', borderRadius: 'var(--radius-md)', display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <Activity size={24} style={{ color: 'hsl(var(--color-success))' }} />
              <div>
                <p style={{ fontWeight: 500 }}>System Status</p>
                <p style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>All services running</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
