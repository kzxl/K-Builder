import { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Save, Play, Monitor, Smartphone, Tablet, Undo2, Redo2, History, X } from 'lucide-react';
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

  // Undo/Redo history
  const [past, setPast] = useState<any[][]>([]);
  const [future, setFuture] = useState<any[][]>([]);

  // Revisions panel
  const [showRevisions, setShowRevisions] = useState(false);
  const [revisions, setRevisions] = useState<any[]>([]);
  const [revLoading, setRevLoading] = useState(false);

  // Responsive preview
  const [device, setDevice] = useState<'desktop' | 'tablet' | 'mobile'>('desktop');
  const deviceWidths: Record<string, string> = { desktop: '1200px', tablet: '768px', mobile: '390px' };

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

  // Ghi layout mới vào history (dùng cho mọi thay đổi có thể undo)
  const commitLayout = useCallback((next: any[]) => {
    setPast((p) => [...p.slice(-49), layout]); // giữ tối đa 50 bước
    setFuture([]);
    setLayout(next);
  }, [layout]);

  const undo = useCallback(() => {
    setPast((p) => {
      if (p.length === 0) return p;
      const previous = p[p.length - 1];
      setFuture((f) => [layout, ...f]);
      setLayout(previous);
      return p.slice(0, -1);
    });
  }, [layout]);

  const redo = useCallback(() => {
    setFuture((f) => {
      if (f.length === 0) return f;
      const next = f[0];
      setPast((p) => [...p, layout]);
      setLayout(next);
      return f.slice(1);
    });
  }, [layout]);

  // Phím tắt Ctrl+Z / Ctrl+Shift+Z / Ctrl+Y
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      const mod = e.ctrlKey || e.metaKey;
      if (!mod) return;
      const key = e.key.toLowerCase();
      if (key === 'z' && !e.shiftKey) {
        e.preventDefault();
        undo();
      } else if ((key === 'z' && e.shiftKey) || key === 'y') {
        e.preventDefault();
        redo();
      }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [undo, redo]);

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;

    if (over && active.id !== over.id) {
      const oldIndex = layout.findIndex((item) => item.id === active.id);
      const newIndex = layout.findIndex((item) => item.id === over.id);
      commitLayout(arrayMove(layout, oldIndex, newIndex));
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

    commitLayout([...layout, newSection]);
    setSelectedSectionId(newSection.id);
  };

  const addPattern = (pattern: any) => {
    const newSections = pattern.blocks.map((block: any, index: number) => ({
      id: `section_${Date.now()}_${index}_${Math.random().toString(36).substr(2, 5)}`,
      type: block.type,
      props: block.props
    }));

    commitLayout([...layout, ...newSections]);
    // Chọn section cuối cùng của pattern
    setSelectedSectionId(newSections[newSections.length - 1].id);
  };

  const removeSection = (sectionId: string) => {
    commitLayout(layout.filter(s => s.id !== sectionId));
    if (selectedSectionId === sectionId) {
      setSelectedSectionId(null);
    }
  };

  const updateSectionProps = (sectionId: string, newProps: any) => {
    commitLayout(layout.map(s => s.id === sectionId ? { ...s, props: newProps } : s));
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

  const openRevisions = async () => {
    setShowRevisions(true);
    setRevLoading(true);
    try {
      const res = await api.get(`/pages/${id}/revisions`);
      setRevisions(res.data.data || []);
    } catch (e) {
      setRevisions([]);
    } finally {
      setRevLoading(false);
    }
  };

  const restoreRevision = async (revId: number) => {
    if (!confirm('Phục hồi phiên bản này? Trạng thái hiện tại sẽ được lưu lại thành một bản nháp để có thể hoàn tác.')) return;
    try {
      const res = await api.post(`/pages/${id}/revisions/${revId}/restore`);
      const restored = res.data.layout || [];
      commitLayout(restored);
      setShowRevisions(false);
      setSelectedSectionId(null);
    } catch (e) {
      alert('Phục hồi thất bại');
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

          {/* Undo / Redo */}
          <div style={{ display: 'flex', gap: '0.25rem', marginLeft: '0.5rem' }}>
            <button onClick={undo} disabled={past.length === 0} title="Hoàn tác (Ctrl+Z)" className="kb-btn" style={{ padding: '0.4rem', background: 'hsl(var(--color-surface-hover))', opacity: past.length === 0 ? 0.4 : 1 }}>
              <Undo2 size={16} />
            </button>
            <button onClick={redo} disabled={future.length === 0} title="Làm lại (Ctrl+Shift+Z)" className="kb-btn" style={{ padding: '0.4rem', background: 'hsl(var(--color-surface-hover))', opacity: future.length === 0 ? 0.4 : 1 }}>
              <Redo2 size={16} />
            </button>
            <button onClick={openRevisions} title="Lịch sử phiên bản" className="kb-btn" style={{ padding: '0.4rem', background: 'hsl(var(--color-surface-hover))' }}>
              <History size={16} />
            </button>
          </div>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          {/* Viewport toggles (Desktop/Tablet/Mobile) */}
          <div style={{ display: 'flex', background: 'hsl(var(--color-surface-hover))', borderRadius: 'var(--radius-md)', padding: '0.25rem' }}>
             <button onClick={() => setDevice('desktop')} title="Desktop" className="kb-btn" style={{ padding: '0.4rem', background: device === 'desktop' ? 'white' : 'transparent', boxShadow: device === 'desktop' ? 'var(--shadow-sm)' : 'none', color: device === 'desktop' ? 'inherit' : 'hsl(var(--color-text-muted))' }}><Monitor size={16} /></button>
             <button onClick={() => setDevice('tablet')} title="Tablet" className="kb-btn" style={{ padding: '0.4rem', background: device === 'tablet' ? 'white' : 'transparent', boxShadow: device === 'tablet' ? 'var(--shadow-sm)' : 'none', color: device === 'tablet' ? 'inherit' : 'hsl(var(--color-text-muted))' }}><Tablet size={16} /></button>
             <button onClick={() => setDevice('mobile')} title="Mobile" className="kb-btn" style={{ padding: '0.4rem', background: device === 'mobile' ? 'white' : 'transparent', boxShadow: device === 'mobile' ? 'var(--shadow-sm)' : 'none', color: device === 'mobile' ? 'inherit' : 'hsl(var(--color-text-muted))' }}><Smartphone size={16} /></button>
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
          <div style={{ width: '100%', maxWidth: deviceWidths[device], background: 'white', minHeight: '100%', boxShadow: 'var(--shadow-md)', borderRadius: 'var(--radius-lg)', padding: '2rem', transition: 'max-width 0.3s ease' }}>
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

      {/* Revisions Panel */}
      {showRevisions && (
        <div
          onClick={() => setShowRevisions(false)}
          style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.4)', zIndex: 2000, display: 'flex', justifyContent: 'flex-end' }}
        >
          <div
            onClick={(e) => e.stopPropagation()}
            style={{ width: '380px', height: '100%', background: 'white', boxShadow: 'var(--shadow-lg)', display: 'flex', flexDirection: 'column' }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '1rem 1.25rem', borderBottom: '1px solid hsl(var(--color-border))' }}>
              <div style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <History size={18} /> Lịch sử phiên bản
              </div>
              <button onClick={() => setShowRevisions(false)} className="kb-btn" style={{ padding: '0.3rem' }}>
                <X size={16} />
              </button>
            </div>

            <div style={{ flex: 1, overflowY: 'auto', padding: '0.75rem' }}>
              {revLoading ? (
                <div style={{ padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>Đang tải...</div>
              ) : revisions.length === 0 ? (
                <div style={{ padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
                  Chưa có phiên bản nào được lưu.
                </div>
              ) : (
                revisions.map((rev) => (
                  <div
                    key={rev.id}
                    style={{ padding: '0.75rem', border: '1px solid hsl(var(--color-border))', borderRadius: 'var(--radius-md)', marginBottom: '0.5rem' }}
                  >
                    <div style={{ fontSize: '0.85rem', fontWeight: 500 }}>{rev.note || 'Phiên bản'}</div>
                    <div style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))', margin: '0.25rem 0 0.5rem' }}>
                      {rev.created_at}
                    </div>
                    <button
                      onClick={() => restoreRevision(rev.id)}
                      className="kb-btn"
                      style={{ padding: '0.3rem 0.75rem', fontSize: '0.8rem', background: 'hsl(var(--color-surface-hover))' }}
                    >
                      Phục hồi
                    </button>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
