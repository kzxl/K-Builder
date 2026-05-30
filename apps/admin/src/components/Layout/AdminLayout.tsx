import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { LayoutDashboard, FileText, Image, Settings, LogOut, ExternalLink, Menu as MenuIcon, Tags, Layout, BarChart2, Briefcase, Code, Database, File, Globe, Mail, MessageSquare, Shield, ShoppingBag, Star, Users, Zap, Package } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import { useEffect, useState } from 'react';
import api from '../../lib/api';

// Map icon name from backend to Lucide component
const IconMap: Record<string, React.ElementType> = {
  LayoutDashboard, FileText, Image, Settings, LogOut, ExternalLink, MenuIcon, Tags, Layout,
  BarChart2, Briefcase, Code, Database, File, Globe, Mail, MessageSquare, Shield, ShoppingBag, Star, Users, Zap, Package
};

interface AdminMenu {
  id: string;
  label: string;
  icon: string;
  route: string;
  pluginId: string;
}

export default function AdminLayout() {
  const { user, logout, refreshToken } = useAuthStore();
  const navigate = useNavigate();
  const [pluginMenus, setPluginMenus] = useState<AdminMenu[]>([]);
  const [contentTypes, setContentTypes] = useState<Record<string, any>>({});
  const [taxonomies, setTaxonomies] = useState<Record<string, any>>({});

  const handleLogout = async () => {
    try {
      await api.post('/auth/logout', { refresh_token: refreshToken });
    } catch (e) {
      console.error('Logout API failed', e);
    } finally {
      logout();
      navigate('/login');
    }
  };

  useEffect(() => {
    // Load plugin menus
    api.get('/admin/menus').then(res => {
      if (res.data?.success) {
        setPluginMenus(res.data.data);
      }
    }).catch(err => console.error('Failed to load plugin menus', err));

    // Load content types and taxonomies
    api.get('/content-types').then(res => {
      if (res.data?.success) {
        setContentTypes(res.data.data.post_types || {});
        setTaxonomies(res.data.data.taxonomies || {});
      }
    }).catch(err => console.error('Failed to load content types', err));
  }, []);

  return (
    <div className="kb-layout">
      {/* Sidebar */}
      <aside className="kb-sidebar">
        <div className="kb-sidebar-header">
          KBuilder
        </div>
        <nav className="kb-nav">
          <div className="kb-nav-label">Chính</div>
          <NavLink to="/dashboard" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
            <LayoutDashboard size={20} />
            <span>Dashboard</span>
          </NavLink>
          <NavLink to="/pages" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
            <Layout size={20} />
            <span>Trang (Pages)</span>
          </NavLink>
          
          <div className="kb-nav-label" style={{ marginTop: '1.5rem' }}>Nội dung</div>
          {Object.entries(contentTypes).map(([type, config]) => {
            const IconComponent = IconMap[config.icon] || FileText;
            return (
              <NavLink key={type} to={`/content/${type}`} className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
                <IconComponent size={20} />
                <span>{config.label}</span>
              </NavLink>
            );
          })}

          {Object.entries(taxonomies).map(([type, config]) => (
            <NavLink key={type} to={`/taxonomies/${type}`} className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
              <Tags size={20} />
              <span>{config.label}</span>
            </NavLink>
          ))}

          <div className="kb-nav-label" style={{ marginTop: '1.5rem' }}>Tài nguyên</div>
          <NavLink to="/media" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
            <Image size={20} />
            <span>Thư viện (Media)</span>
          </NavLink>
          <NavLink to="/menus" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
            <MenuIcon size={20} />
            <span>Điều hướng (Menus)</span>
          </NavLink>
          
          <div className="kb-nav-label" style={{ marginTop: '1.5rem' }}>Hệ thống</div>
          <NavLink to="/settings" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
            <Settings size={20} />
            <span>Cài đặt Site</span>
          </NavLink>

          {pluginMenus.length > 0 && (
            <>
              <div className="kb-nav-label" style={{ marginTop: '1.5rem' }}>Mở rộng</div>
              {pluginMenus.map(menu => {
                const IconComponent = IconMap[menu.icon] || Package;
                return (
                  <NavLink key={menu.id} to={menu.route} className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
                    <IconComponent size={20} />
                    <span>{menu.label}</span>
                  </NavLink>
                );
              })}
            </>
          )}
          
          {user?.roles?.includes('super_admin') && (
            <>
              <div className="kb-nav-label" style={{ marginTop: '1.5rem' }}>Quản trị hệ thống</div>
              <NavLink to="/users" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
                <Users size={20} />
                <span>Người dùng</span>
              </NavLink>
              <NavLink to="/plugins" className={({isActive}) => `kb-nav-item ${isActive ? 'active' : ''}`}>
                <Settings size={20} />
                <span>Quản lý Plugins</span>
              </NavLink>
            </>
          )}
        </nav>
      </aside>

      {/* Main Container */}
      <div className="kb-main">
        {/* Header */}
        <header className="kb-header">
          <div className="kb-header-title" style={{ display: 'flex', alignItems: 'center' }}>
            Hệ thống Quản trị KBuilder
            <a 
              href={window.location.pathname.startsWith('/kbuilder/public/admin') ? '/kbuilder/public' : (window.location.pathname.startsWith('/kbuilder/admin') ? '/kbuilder' : '/')} 
              target="_blank" 
              rel="noopener noreferrer" 
              className="kb-btn kb-btn--sm kb-btn--outline" 
              style={{ marginLeft: '1rem', textDecoration: 'none' }}
            >
              <ExternalLink size={14} style={{ marginRight: '0.25rem' }} /> Xem trang
            </a>
          </div>
          <div className="kb-header-user">
            <div className="kb-user-info">
              <span className="kb-user-name">{user?.name}</span>
              <span className="kb-user-role">{user?.roles?.[0]?.replace('_', ' ')}</span>
            </div>
            <div className="kb-avatar">{user?.name?.charAt(0).toUpperCase()}</div>
            <button onClick={handleLogout} className="kb-btn" style={{ padding: '0.5rem', color: 'hsl(var(--color-danger))', marginLeft: '0.5rem' }}>
              <LogOut size={18} />
            </button>
          </div>
        </header>

        {/* Content Area */}
        <main className="kb-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
