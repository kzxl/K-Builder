import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import api from '../lib/api';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const navigate = useNavigate();
  const { setTokens, setUser } = useAuthStore();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    
    try {
      const res = await api.post('/auth/login', { email, password });
      
      const authData = res.data.data;
      setTokens(authData.access_token, authData.refresh_token);
      setUser(authData.user);
      
      navigate('/dashboard', { replace: true });
    } catch (err: any) {
      setError(err.response?.data?.error || 'Đăng nhập thất bại. Vui lòng thử lại.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="kb-login-page">
      <div className="kb-login-card">
        <h1>Đăng nhập</h1>
        <p className="subtitle">Hệ thống quản trị KBuilder</p>
        
        {error && <div className="kb-error">{error}</div>}
        
        <form onSubmit={handleLogin}>
          <div className="kb-form-group">
            <label className="kb-label">Email</label>
            <input 
              type="email" 
              className="kb-input" 
              value={email}
              onChange={e => setEmail(e.target.value)}
              placeholder="admin@example.com"
              required 
            />
          </div>
          
          <div className="kb-form-group">
            <label className="kb-label">Mật khẩu</label>
            <input 
              type="password" 
              className="kb-input" 
              value={password}
              onChange={e => setPassword(e.target.value)}
              placeholder="••••••••"
              required 
            />
          </div>
          
          <button type="submit" className="kb-btn kb-btn--primary" style={{ width: '100%' }} disabled={loading}>
            {loading ? 'Đang xử lý...' : 'Đăng nhập'}
          </button>
        </form>
      </div>
    </div>
  );
}
