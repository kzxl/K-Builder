import { useState, useEffect } from 'react';
import { Save, Plus, Trash2, GripVertical, Navigation } from 'lucide-react';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors, DragEndEvent } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import api from '../../lib/api';

interface MenuItem {
  id?: number | string;
  label: string;
  url: string;
  target: string;
}

// Sub-component for Draggable Item
function SortableMenuItem({ item, onUpdate, onRemove }: { item: MenuItem, onUpdate: (item: MenuItem) => void, onRemove: () => void }) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: item.id as string });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    zIndex: isDragging ? 10 : 1,
    position: 'relative' as const,
  };

  return (
    <div ref={setNodeRef} className="kb-card" style={{ ...style, padding: '1rem', marginBottom: '0.75rem', display: 'flex', gap: '1rem', alignItems: 'center', background: 'white', border: '1px solid hsl(var(--color-border))' }}>
      <div {...attributes} {...listeners} style={{ cursor: 'grab', color: 'hsl(var(--color-text-muted))', padding: '0.5rem' }}>
        <GripVertical size={18} />
      </div>
      
      <div style={{ flex: 1, display: 'grid', gridTemplateColumns: '1fr 1fr 100px', gap: '1rem' }}>
        <div>
          <label className="kb-label" style={{ fontSize: '0.75rem' }}>Tên hiển thị</label>
          <input type="text" className="kb-input" value={item.label} onChange={e => onUpdate({ ...item, label: e.target.value })} />
        </div>
        <div>
          <label className="kb-label" style={{ fontSize: '0.75rem' }}>Đường dẫn (URL)</label>
          <input type="text" className="kb-input" value={item.url} onChange={e => onUpdate({ ...item, url: e.target.value })} />
        </div>
        <div>
          <label className="kb-label" style={{ fontSize: '0.75rem' }}>Mở trang</label>
          <select className="kb-input" value={item.target} onChange={e => onUpdate({ ...item, target: e.target.value })}>
            <option value="_self">Hiện tại</option>
            <option value="_blank">Tab mới</option>
          </select>
        </div>
      </div>
      
      <button onClick={onRemove} className="kb-btn" style={{ color: 'hsl(var(--color-danger))', padding: '0.5rem' }}>
        <Trash2 size={18} />
      </button>
    </div>
  );
}

export default function MenuList() {
  const [menus, setMenus] = useState<any[]>([]);
  const [activeMenu, setActiveMenu] = useState<any>(null);
  const [items, setItems] = useState<MenuItem[]>([]);
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
        setMenus(res.data.data);
        if (res.data.data.length > 0) {
          loadMenu(res.data.data[0]);
        }
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const loadMenu = async (menu: any) => {
    setActiveMenu(menu);
    try {
      const res = await api.get(`/menus/${menu.id}`);
      if (res.data.success) {
        // Assign temporary string IDs for DnD if not present
        const loadedItems = (res.data.data.items || []).map((it: any) => ({
          ...it,
          id: it.id ? it.id.toString() : Math.random().toString()
        }));
        setItems(loadedItems);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (over && active.id !== over.id) {
      setItems((items) => {
        const oldIndex = items.findIndex((item) => item.id === active.id);
        const newIndex = items.findIndex((item) => item.id === over.id);
        return arrayMove(items, oldIndex, newIndex);
      });
    }
  };

  const handleAddItem = () => {
    setItems([...items, { id: Math.random().toString(), label: 'Menu mới', url: '#', target: '_self' }]);
  };

  const handleUpdateItem = (index: number, updated: MenuItem) => {
    const newItems = [...items];
    newItems[index] = updated;
    setItems(newItems);
  };

  const handleRemoveItem = (index: number) => {
    const newItems = [...items];
    newItems.splice(index, 1);
    setItems(newItems);
  };

  const handleSave = async () => {
    if (!activeMenu) return;
    setSaving(true);
    try {
      await api.put(`/menus/${activeMenu.id}`, {
        name: activeMenu.name,
        location: activeMenu.location,
        items: items
      });
      alert('Đã lưu Menu thành công!');
    } catch (e) {
      alert('Lỗi lưu Menu');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div>Đang tải...</div>;

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: '2.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end' }}>
        <div>
          <h1 style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>
            Quản lý Menu
          </h1>
          <p className="text-muted" style={{ fontSize: '1.05rem' }}>
            Thiết lập thanh điều hướng cho người dùng.
          </p>
        </div>
        <button className="kb-btn kb-btn--primary" onClick={handleSave} disabled={saving || !activeMenu}>
          <Save size={18} /> {saving ? 'Đang lưu...' : 'Lưu Menu'}
        </button>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '250px 1fr', gap: '2rem' }}>
        {/* Sidebar: Menu List */}
        <div className="kb-card" style={{ padding: '1rem', height: 'fit-content' }}>
          <h3 style={{ fontSize: '1rem', marginBottom: '1rem', paddingBottom: '0.5rem', borderBottom: '1px solid hsl(var(--color-border))' }}>Danh sách Menu</h3>
          <nav style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
            {menus.map(menu => (
              <button 
                key={menu.id}
                className={`kb-btn ${activeMenu?.id === menu.id ? 'kb-btn--primary' : 'kb-btn--outline'}`} 
                style={{ justifyContent: 'flex-start', border: activeMenu?.id === menu.id ? '' : 'none', color: activeMenu?.id === menu.id ? '' : 'hsl(var(--color-text-muted))' }}
                onClick={() => loadMenu(menu)}
              >
                <Navigation size={18} /> {menu.name} ({menu.location})
              </button>
            ))}
          </nav>
        </div>

        {/* Main: Menu Items Builder */}
        <div className="kb-card" style={{ background: 'hsl(var(--color-surface-hover))', border: '1px solid hsl(var(--color-border))' }}>
          {activeMenu ? (
            <div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem', paddingBottom: '1rem', borderBottom: '1px solid hsl(var(--color-border))' }}>
                <h3 style={{ fontSize: '1.25rem' }}>Cấu trúc: {activeMenu.name}</h3>
                <button className="kb-btn kb-btn--outline" onClick={handleAddItem} style={{ background: 'white' }}>
                  <Plus size={16} /> Thêm liên kết
                </button>
              </div>

              {items.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '3rem', color: 'hsl(var(--color-text-muted))', border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)' }}>
                  Chưa có liên kết nào. Hãy thêm liên kết mới.
                </div>
              ) : (
                <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                  <SortableContext items={items.map(i => i.id as string)} strategy={verticalListSortingStrategy}>
                    {items.map((item, index) => (
                      <SortableMenuItem 
                        key={item.id} 
                        item={item} 
                        onUpdate={(updated) => handleUpdateItem(index, updated)}
                        onRemove={() => handleRemoveItem(index)}
                      />
                    ))}
                  </SortableContext>
                </DndContext>
              )}
            </div>
          ) : (
            <div style={{ textAlign: 'center', padding: '4rem', color: 'hsl(var(--color-text-muted))' }}>
              Chọn một Menu bên trái để chỉnh sửa
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
