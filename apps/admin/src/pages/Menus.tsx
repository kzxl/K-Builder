import { useState, useEffect } from 'react';
import { Save, Plus, GripVertical, Trash2, Link as LinkIcon, AlertCircle } from 'lucide-react';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors, DragEndEvent } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import api from '../lib/api';

// Sortable Item Component
function SortableMenuItem({ item, onUpdate, onRemove }: any) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.id || item.tempId });
  
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    zIndex: isDragging ? 50 : 1,
  };

  return (
    <div ref={setNodeRef} style={{...style, marginBottom: '0.75rem', padding: '0.75rem 1rem', display: 'flex', gap: '1rem', alignItems: 'center'}} className={`kb-card animate-fade-in ${isDragging ? 'shadow-lg ring-2 ring-primary/20' : ''}`}>
      <div {...attributes} {...listeners} style={{ cursor: 'grab', padding: '0.5rem', color: 'hsl(var(--color-text-muted))' }}>
        <GripVertical size={18} />
      </div>
      
      <div style={{ flex: 1, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
        <div>
          <label style={{ fontSize: '0.75rem', fontWeight: 600, color: 'hsl(var(--color-text-muted))', marginBottom: '0.25rem', display: 'block' }}>Tên hiển thị</label>
          <input 
            type="text" 
            className="kb-input" 
            value={item.label} 
            onChange={e => onUpdate({ ...item, label: e.target.value })} 
            placeholder="Ví dụ: Giới thiệu"
          />
        </div>
        <div>
          <label style={{ fontSize: '0.75rem', fontWeight: 600, color: 'hsl(var(--color-text-muted))', marginBottom: '0.25rem', display: 'block' }}>Đường dẫn (URL)</label>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <input 
              type="text" 
              className="kb-input" 
              style={{ flex: 1 }}
              value={item.url} 
              onChange={e => onUpdate({ ...item, url: e.target.value })} 
              placeholder="/gioi-thieu"
            />
          </div>
        </div>
      </div>
      
      <button 
        onClick={onRemove}
        className="kb-btn kb-btn--outline" 
        style={{ padding: '0.5rem', color: 'hsl(var(--color-error))', borderColor: 'transparent' }}
        title="Xóa link"
      >
        <Trash2 size={18} />
      </button>
    </div>
  );
}

export default function Menus() {
  const [menus, setMenus] = useState<any[]>([]);
  const [activeMenuId, setActiveMenuId] = useState<number | null>(null);
  const [menuItems, setMenuItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  useEffect(() => {
    fetchMenus();
  }, []);

  const fetchMenus = async () => {
    try {
      const res = await api.get('/menus');
      if (res.data.success) {
        const menuList = res.data.data;
        setMenus(menuList);
        if (menuList.length > 0) {
          fetchMenuDetails(menuList[0].id);
        } else {
          setLoading(false);
        }
      }
    } catch (e) {
      console.error(e);
      setLoading(false);
    }
  };

  const fetchMenuDetails = async (id: number) => {
    setLoading(true);
    try {
      const res = await api.get(`/menus/${id}`);
      if (res.data.success) {
        setActiveMenuId(id);
        const items = res.data.data.items || [];
        // Gắn tempId để dnd-kit làm việc ổn định với các item chưa có ID thật
        setMenuItems(items.map((i: any) => ({ ...i, tempId: i.id || Math.random().toString() })));
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const createDefaultMenu = async () => {
    try {
      const res = await api.post('/menus', { name: 'Main Menu', location: 'header' });
      if (res.data.success) {
        fetchMenus();
      }
    } catch (e) {
      alert('Lỗi tạo menu');
    }
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (over && active.id !== over.id) {
      setMenuItems((items) => {
        const oldIndex = items.findIndex((item) => (item.id || item.tempId) === active.id);
        const newIndex = items.findIndex((item) => (item.id || item.tempId) === over.id);
        return arrayMove(items, oldIndex, newIndex);
      });
    }
  };

  const addMenuItem = () => {
    const newItem = {
      tempId: Math.random().toString(),
      label: 'Link mới',
      url: '#',
      type: 'url'
    };
    setMenuItems([...menuItems, newItem]);
  };

  const updateMenuItem = (tempId: string, newData: any) => {
    setMenuItems(menuItems.map(item => (item.id || item.tempId) === tempId ? newData : item));
  };

  const removeMenuItem = (tempId: string) => {
    setMenuItems(menuItems.filter(item => (item.id || item.tempId) !== tempId));
  };

  const saveMenu = async () => {
    if (!activeMenuId) return;
    setSaving(true);
    try {
      await api.put(`/menus/${activeMenuId}`, {
        name: menus.find(m => m.id === activeMenuId)?.name || 'Menu',
        location: 'header',
        items: menuItems
      });
      alert('Lưu menu thành công!');
    } catch (e) {
      alert('Lỗi lưu menu');
    } finally {
      setSaving(false);
    }
  };

  if (loading && menus.length === 0) return <div style={{ padding: '2rem' }}>Đang tải...</div>;

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: '2.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
        <div>
          <h1 style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>Quản lý Menu</h1>
          <p className="text-muted" style={{ fontSize: '1.05rem' }}>
            Thiết lập thanh điều hướng cho toàn bộ website.
          </p>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={saveMenu} disabled={saving || !activeMenuId}>
          <Save size={18} /> {saving ? 'Đang lưu...' : 'Lưu cấu hình'}
        </button>
      </div>

      {menus.length === 0 ? (
        <div className="kb-card" style={{ padding: '4rem', textAlign: 'center', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
          <div style={{ width: '64px', height: '64px', borderRadius: '50%', background: 'hsla(var(--color-primary)/0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'hsl(var(--color-primary))', marginBottom: '1.5rem' }}>
            <LinkIcon size={32} />
          </div>
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1rem' }}>Website chưa có Menu nào</h2>
          <p style={{ color: 'hsl(var(--color-text-muted))', marginBottom: '2rem', maxWidth: '400px' }}>
            Menu giúp người dùng dễ dàng chuyển qua lại giữa các trang. Hãy tạo menu đầu tiên cho website của bạn.
          </p>
          <button className="kb-btn kb-btn--primary kb-btn--lg" onClick={createDefaultMenu}>
            Tạo Header Menu
          </button>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: '250px 1fr', gap: '2rem' }}>
          {/* Menu Selector Sidebar */}
          <div className="kb-card" style={{ padding: '1rem', height: 'fit-content' }}>
            <h3 style={{ fontSize: '0.9rem', textTransform: 'uppercase', color: 'hsl(var(--color-text-muted))', marginBottom: '1rem', fontWeight: 600 }}>Danh sách Menu</h3>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              {menus.map(menu => (
                <button 
                  key={menu.id}
                  className={`kb-btn ${activeMenuId === menu.id ? 'kb-btn--primary' : 'kb-btn--outline'}`}
                  style={{ justifyContent: 'flex-start', border: activeMenuId === menu.id ? '' : 'none' }}
                  onClick={() => fetchMenuDetails(menu.id)}
                >
                  <LinkIcon size={16} style={{ marginRight: '0.5rem' }} /> {menu.name}
                </button>
              ))}
            </div>
            <button className="kb-btn kb-btn--outline" style={{ width: '100%', marginTop: '1rem', borderStyle: 'dashed' }} onClick={createDefaultMenu}>
              <Plus size={16} /> Thêm Menu mới
            </button>
          </div>

          {/* Menu Builder */}
          {activeMenuId && (
            <div className="kb-card" style={{ padding: '1.5rem' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem', paddingBottom: '1rem', borderBottom: '1px solid hsl(var(--color-border))' }}>
                <h3 style={{ fontSize: '1.25rem' }}>Chỉnh sửa Menu Links</h3>
                <button className="kb-btn kb-btn--outline" onClick={addMenuItem}>
                  <Plus size={16} /> Thêm liên kết
                </button>
              </div>

              {menuItems.length === 0 ? (
                <div style={{ padding: '3rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))', border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)' }}>
                  Menu đang trống. Nhấn "Thêm liên kết" để bắt đầu.
                </div>
              ) : (
                <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                  <SortableContext items={menuItems.map(i => i.id || i.tempId)} strategy={verticalListSortingStrategy}>
                    <div>
                      {menuItems.map(item => (
                        <SortableMenuItem 
                          key={item.id || item.tempId} 
                          item={item} 
                          onUpdate={(data: any) => updateMenuItem(item.id || item.tempId, data)}
                          onRemove={() => removeMenuItem(item.id || item.tempId)}
                        />
                      ))}
                    </div>
                  </SortableContext>
                </DndContext>
              )}
              
              <div style={{ marginTop: '2rem', padding: '1rem', background: 'hsla(var(--color-primary)/0.05)', borderRadius: 'var(--radius-md)', display: 'flex', gap: '1rem' }}>
                <AlertCircle size={20} style={{ color: 'hsl(var(--color-primary))', flexShrink: 0 }} />
                <p style={{ fontSize: '0.9rem', color: 'hsl(var(--color-text-muted))', margin: 0 }}>
                  Gợi ý: Cầm vào biểu tượng 6 dấu chấm ở đầu mỗi hàng để kéo thả thay đổi thứ tự. Nhớ bấm <strong>Lưu cấu hình</strong> sau khi sửa xong!
                </p>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
