import { useState, useEffect } from 'react';
import { Users as UsersIcon, Edit2, Trash2, UserPlus, X } from 'lucide-react';
import api from '../../lib/api';

interface Role {
  id: number;
  name: string;
  slug: string;
  description?: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  status: string;
  roles: string[];
  last_login_at?: string | null;
}

const STATUS_LABEL: Record<string, string> = {
  active: 'Hoạt động',
  inactive: 'Tạm khóa',
  banned: 'Bị cấm',
};

export default function UserList() {
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editing, setEditing] = useState<User | null>(null);
  const [form, setForm] = useState<any>({ name: '', email: '', password: '', status: 'active', roles: [] });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchAll();
  }, []);

  const fetchAll = async () => {
    try {
      setLoading(true);
      const [uRes, rRes] = await Promise.all([api.get('/users'), api.get('/roles')]);
      setUsers(uRes.data.data || []);
      setRoles(rRes.data.data || []);
    } catch (e) {
      alert('Không tải được danh sách người dùng');
    } finally {
      setLoading(false);
    }
  };

  const openCreate = () => {
    setEditing(null);
    setForm({ name: '', email: '', password: '', status: 'active', roles: [] });
    setErrors({});
    setShowModal(true);
  };

  const openEdit = (u: User) => {
    setEditing(u);
    setForm({ name: u.name, email: u.email, password: '', status: u.status, roles: u.roles || [] });
    setErrors({});
    setShowModal(true);
  };

  const toggleRole = (slug: string) => {
    setForm((f: any) => ({
      ...f,
      roles: f.roles.includes(slug) ? f.roles.filter((r: string) => r !== slug) : [...f.roles, slug],
    }));
  };

  const save = async () => {
    setSaving(true);
    setErrors({});
    try {
      if (editing) {
        const payload: any = { name: form.name, email: form.email, status: form.status, roles: form.roles };
        if (form.password) payload.password = form.password;
        await api.put(`/users/${editing.id}`, payload);
      } else {
        await api.post('/users', form);
      }
      setShowModal(false);
      fetchAll();
    } catch (e: any) {
      const res = e?.response?.data;
      if (res?.errors) {
        setErrors(res.errors);
      } else {
        alert(res?.error || 'Lưu thất bại');
      }
    } finally {
      setSaving(false);
    }
  };

  const remove = async (u: User) => {
    if (!confirm(`Xóa người dùng "${u.name}"?`)) return;
    try {
      await api.delete(`/users/${u.id}`);
      fetchAll();
    } catch (e: any) {
      alert(e?.response?.data?.error || 'Lỗi khi xóa');
    }
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="kb-page-container">
      <div className="kb-page-header">
        <div>
          <h1 className="kb-page-title">Quản lý người dùng</h1>
          <p className="kb-page-subtitle">Tạo, phân quyền và quản lý tài khoản truy cập hệ thống</p>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={openCreate}>
          <UserPlus size={16} /> Thêm người dùng
        </button>
      </div>

      <div className="kb-table-container">
        <table className="kb-table">
          <thead>
            <tr>
              <th>Tên / Email</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Đăng nhập gần nhất</th>
              <th style={{ width: '100px' }}>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id}>
                <td>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <UsersIcon size={16} style={{ color: 'hsl(var(--color-text-muted))' }} />
                    <div>
                      <div style={{ fontWeight: 500 }}>{u.name}</div>
                      <div style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))' }}>{u.email}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style={{ display: 'flex', gap: '0.25rem', flexWrap: 'wrap' }}>
                    {(u.roles || []).map((r) => (
                      <span key={r} className="kb-badge kb-badge--neutral">{r}</span>
                    ))}
                    {(!u.roles || u.roles.length === 0) && <span style={{ color: 'hsl(var(--color-text-muted))' }}>-</span>}
                  </div>
                </td>
                <td>
                  <span className={`kb-badge kb-badge--${u.status === 'active' ? 'success' : 'warning'}`}>
                    {STATUS_LABEL[u.status] || u.status}
                  </span>
                </td>
                <td style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>{u.last_login_at || 'Chưa từng'}</td>
                <td>
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button className="kb-icon-btn" onClick={() => openEdit(u)} title="Sửa"><Edit2 size={16} /></button>
                    <button className="kb-icon-btn" style={{ color: 'hsl(var(--color-danger))' }} onClick={() => remove(u)} title="Xóa"><Trash2 size={16} /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {users.length === 0 && (
          <div style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
            <UsersIcon size={48} style={{ opacity: 0.2, margin: '0 auto 1rem' }} />
            <p>Chưa có người dùng nào.</p>
          </div>
        )}
      </div>

      {showModal && (
        <div
          onClick={() => setShowModal(false)}
          style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.4)', zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center' }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{ width: '480px', maxWidth: '90vw', background: 'white', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-lg)', maxHeight: '90vh', overflowY: 'auto' }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '1rem 1.25rem', borderBottom: '1px solid hsl(var(--color-border))' }}>
              <h2 style={{ fontWeight: 600, fontSize: '1.1rem' }}>{editing ? 'Sửa người dùng' : 'Thêm người dùng'}</h2>
              <button className="kb-icon-btn" onClick={() => setShowModal(false)}><X size={18} /></button>
            </div>

            <div style={{ padding: '1.25rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div>
                <label className="kb-label">Họ tên</label>
                <input className="kb-input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                {errors.name && <div style={{ color: 'hsl(var(--color-danger))', fontSize: '0.8rem', marginTop: '0.25rem' }}>{errors.name}</div>}
              </div>

              <div>
                <label className="kb-label">Email</label>
                <input className="kb-input" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                {errors.email && <div style={{ color: 'hsl(var(--color-danger))', fontSize: '0.8rem', marginTop: '0.25rem' }}>{errors.email}</div>}
              </div>

              <div>
                <label className="kb-label">Mật khẩu {editing && <span style={{ fontWeight: 400, color: 'hsl(var(--color-text-muted))' }}>(để trống nếu không đổi)</span>}</label>
                <input className="kb-input" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                {errors.password && <div style={{ color: 'hsl(var(--color-danger))', fontSize: '0.8rem', marginTop: '0.25rem' }}>{errors.password}</div>}
              </div>

              <div>
                <label className="kb-label">Trạng thái</label>
                <select className="kb-input" value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>
                  <option value="active">Hoạt động</option>
                  <option value="inactive">Tạm khóa</option>
                  <option value="banned">Bị cấm</option>
                </select>
              </div>

              <div>
                <label className="kb-label">Vai trò</label>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', marginTop: '0.25rem' }}>
                  {roles.map((r) => (
                    <label key={r.id} style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', fontSize: '0.9rem', cursor: 'pointer' }}>
                      <input type="checkbox" checked={form.roles.includes(r.slug)} onChange={() => toggleRole(r.slug)} />
                      <span style={{ fontWeight: 500 }}>{r.name}</span>
                      {r.description && <span style={{ color: 'hsl(var(--color-text-muted))', fontSize: '0.8rem' }}>— {r.description}</span>}
                    </label>
                  ))}
                </div>
              </div>
            </div>

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.5rem', padding: '1rem 1.25rem', borderTop: '1px solid hsl(var(--color-border))' }}>
              <button className="kb-btn" onClick={() => setShowModal(false)}>Hủy</button>
              <button className="kb-btn kb-btn--primary" onClick={save} disabled={saving}>{saving ? 'Đang lưu...' : 'Lưu'}</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
