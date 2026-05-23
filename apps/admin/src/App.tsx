import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from './store/authStore';
import AdminLayout from './components/Layout/AdminLayout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import PagesList from './pages/Pages/PagesList';
import PostList from './pages/Posts/PostList';
import PostEditor from './pages/Posts/PostEditor';
import TaxonomyList from './pages/Taxonomies/TaxonomyList';
import TaxonomyEditor from './pages/Taxonomies/TaxonomyEditor';
import MediaLibrary from './pages/Media/MediaLibrary';
import Builder from './pages/Builder/Builder';
import Settings from './pages/Settings';
import Menus from './pages/Menus';
import PluginsList from './pages/Plugins/PluginsList';
import PluginDocs from './pages/Plugins/PluginDocs';
import ThemeSettings from './pages/Plugins/ThemeSettings';
import Analytics from './pages/Plugins/Analytics';
import PluginPlaceholder from './pages/Plugins/PluginPlaceholder';
import Contacts from './pages/Plugins/Contacts';

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, isInitializing } = useAuthStore();
  
  if (isInitializing) {
    return <div className="kb-loading-screen">Loading...</div>;
  }
  
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  
  return <>{children}</>;
}

const getBasename = () => {
  if (window.location.pathname.startsWith('/kbuilder/public/admin')) {
    return '/kbuilder/public/admin';
  }
  return '/kbuilder/admin';
};

export default function App() {
  const initAuth = useAuthStore(state => state.init);
  
  useEffect(() => {
    initAuth();
  }, [initAuth]);

  return (
    <BrowserRouter basename={getBasename()}>
      <Routes>
        <Route path="/login" element={<Login />} />
        
        <Route path="/builder/:id" element={
          <ProtectedRoute>
            <Builder />
          </ProtectedRoute>
        } />
        
        <Route path="/" element={
          <ProtectedRoute>
            <AdminLayout />
          </ProtectedRoute>
        }>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="pages" element={<PagesList />} />
          <Route path="content/:type" element={<PostList />} />
          <Route path="content/:type/new" element={<PostEditor />} />
          <Route path="content/:type/:id" element={<PostEditor />} />
          <Route path="taxonomies/:type" element={<TaxonomyList />} />
          <Route path="taxonomies/:type/new" element={<TaxonomyEditor />} />
          <Route path="taxonomies/:type/:id" element={<TaxonomyEditor />} />
          <Route path="media" element={<MediaLibrary />} />
          <Route path="menus" element={<Menus />} />
          <Route path="settings" element={<Settings />} />
          <Route path="plugins" element={<PluginsList />} />
          <Route path="plugins/docs" element={<PluginDocs />} />
          <Route path="plugins/kb-theme-manager" element={<ThemeSettings />} />
          <Route path="plugins/kb-analytics" element={<Analytics />} />
          <Route path="contacts" element={<Contacts />} />
          <Route path="plugins/:slug" element={<PluginPlaceholder />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
