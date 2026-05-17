import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Save, Play, Monitor, Smartphone } from 'lucide-react';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors, DragEndEvent } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import api from '../../lib/api';

// Sub-components
import ComponentSidebar from './ComponentSidebar';
import MainCanvas from './MainCanvas';
import PropertiesSidebar from './PropertiesSidebar';

export default function Builder() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [page, setPage] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  
  const [availableComponents, setAvailableComponents] = useState<any[]>([]);
  
  // Dnd state
  const [layout, setLayout] = useState<any[]>([]);
  const [selectedSectionId, setSelectedSectionId] = useState<string | null>(null);

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [pageRes, compRes] = await Promise.all([
          api.get(`/pages/${id}`),
          api.get('/components')
        ]);
        
        setPage(pageRes.data.data);
        setLayout(pageRes.data.data.layout || []);
        setAvailableComponents(compRes.data.data);
      } catch (e) {
        alert('Không tải được dữ liệu Builder');
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [id]);

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    
    if (over && active.id !== over.id) {
      setLayout((items) => {
        const oldIndex = items.findIndex((item) => item.id === active.id);
        const newIndex = items.findIndex((item) => item.id === over.id);
        return arrayMove(items, oldIndex, newIndex);
      });
    }
  };

  const addComponent = (componentType: string) => {
    const compDef = availableComponents.find(c => c.type === componentType);
    if (!compDef) return;

    // Generate default props from schema
    const defaultProps: any = {};
    if (compDef.schema?.properties) {
      Object.keys(compDef.schema.properties).forEach(key => {
        const prop = compDef.schema.properties[key];
        if (prop.default !== undefined) {
          defaultProps[key] = prop.default;
        }
      });
    }

    const newSection = {
      id: `section_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`,
      type: componentType,
      props: defaultProps
    };

    setLayout([...layout, newSection]);
    setSelectedSectionId(newSection.id);
  };

  const addPattern = (pattern: any) => {
    const newSections = pattern.blocks.map((block: any, index: number) => ({
      id: `section_${Date.now()}_${index}_${Math.random().toString(36).substr(2, 5)}`,
      type: block.type,
      props: block.props
    }));

    setLayout([...layout, ...newSections]);
    // Chọn section cuối cùng của pattern
    setSelectedSectionId(newSections[newSections.length - 1].id);
  };

  const removeSection = (sectionId: string) => {
    setLayout(layout.filter(s => s.id !== sectionId));
    if (selectedSectionId === sectionId) {
      setSelectedSectionId(null);
    }
  };

  const updateSectionProps = (sectionId: string, newProps: any) => {
    setLayout(layout.map(s => s.id === sectionId ? { ...s, props: newProps } : s));
  };

  const saveLayout = async () => {
    setSaving(true);
    try {
      await api.put(`/pages/${id}`, { layout, status: page?.status });
      // Hiển thị toast success (TBD)
    } catch (e) {
      alert('Lưu thất bại');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="kb-loading-screen">Đang tải trình thiết kế...</div>;

  const selectedSection = layout.find(s => s.id === selectedSectionId);
  const selectedComponentDef = selectedSection ? availableComponents.find(c => c.type === selectedSection.type) : null;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100vh', width: '100vw', position: 'fixed', top: 0, left: 0, background: 'hsl(var(--color-background))', zIndex: 1000 }}>
      {/* Builder Topbar */}
      <div style={{ height: '56px', background: 'white', borderBottom: '1px solid hsl(var(--color-border))', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 1.5rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <button onClick={() => navigate('/pages')} className="kb-btn" style={{ padding: '0.5rem', background: 'hsl(var(--color-surface-hover))' }}>
            <ArrowLeft size={16} /> Thoát
          </button>
          <div style={{ fontWeight: 600 }}>{page?.title}</div>
          <div style={{ fontSize: '0.85rem', color: 'hsl(var(--color-text-muted))' }}>/ {page?.slug}</div>
          
          <select 
            value={page?.status || 'draft'} 
            onChange={e => setPage({ ...page, status: e.target.value })}
            style={{ marginLeft: '1rem', padding: '0.25rem 0.5rem', borderRadius: 'var(--radius-sm)', border: '1px solid hsl(var(--color-border))', fontSize: '0.85rem', background: page?.status === 'published' ? 'hsla(var(--color-success)/0.1)' : 'hsla(var(--color-surface-hover))', color: page?.status === 'published' ? 'hsl(var(--color-success))' : 'inherit' }}
          >
            <option value="draft">Bản nháp</option>
            <option value="published">Đã xuất bản</option>
          </select>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          {/* Viewport toggles (Desktop/Mobile) - Demo purpose */}
          <div style={{ display: 'flex', background: 'hsl(var(--color-surface-hover))', borderRadius: 'var(--radius-md)', padding: '0.25rem' }}>
             <button className="kb-btn" style={{ padding: '0.4rem', background: 'white', boxShadow: 'var(--shadow-sm)' }}><Monitor size={16} /></button>
             <button className="kb-btn" style={{ padding: '0.4rem', color: 'hsl(var(--color-text-muted))' }}><Smartphone size={16} /></button>
          </div>

          <a href={`/kbuilder/${page?.slug}?preview=1`} target="_blank" className="kb-btn" style={{ padding: '0.5rem 1rem', background: 'hsl(var(--color-surface-hover))' }}>
            <Play size={16} style={{ marginRight: '0.5rem' }} /> Xem thử
          </a>
          
          <button onClick={saveLayout} disabled={saving} className="kb-btn kb-btn--primary">
            <Save size={16} style={{ marginRight: '0.5rem' }} /> {saving ? 'Đang lưu...' : 'Lưu giao diện'}
          </button>
        </div>
      </div>

      {/* Builder Workspace */}
      <div style={{ display: 'flex', flex: 1, overflow: 'hidden' }}>
        {/* Left Sidebar - Component Library */}
        <ComponentSidebar components={availableComponents} onAdd={addComponent} onAddPattern={addPattern} />

        {/* Center Canvas - DnD Area */}
        <div style={{ flex: 1, overflowY: 'auto', padding: '2rem', display: 'flex', justifyContent: 'center' }}>
          <div style={{ width: '100%', maxWidth: '1200px', background: 'white', minHeight: '100%', boxShadow: 'var(--shadow-md)', borderRadius: 'var(--radius-lg)', padding: '2rem' }}>
            {layout.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '4rem', color: 'hsl(var(--color-text-muted))', border: '2px dashed hsl(var(--color-border))', borderRadius: 'var(--radius-md)' }}>
                Kéo thả component từ cột trái vào đây, hoặc click để thêm.
              </div>
            ) : (
              <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                <SortableContext items={layout.map(i => i.id)} strategy={verticalListSortingStrategy}>
                  <MainCanvas 
                    layout={layout} 
                    selectedId={selectedSectionId}
                    onSelect={setSelectedSectionId}
                    onRemove={removeSection}
                    components={availableComponents}
                  />
                </SortableContext>
              </DndContext>
            )}
          </div>
        </div>

        {/* Right Sidebar - Properties Editor */}
        <PropertiesSidebar 
          section={selectedSection} 
          schema={selectedComponentDef?.schema} 
          components={availableComponents}
          onChange={(newProps) => updateSectionProps(selectedSection!.id, newProps)} 
        />
      </div>
    </div>
  );
}
