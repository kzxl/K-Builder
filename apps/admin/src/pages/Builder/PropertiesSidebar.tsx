import { useState, useEffect } from 'react';
import { ArrowLeft, Settings2, Trash2, Plus } from 'lucide-react';

interface PropertiesSidebarProps {
  section: any | null;
  schema: any | null;
  components: any[];
  onChange: (newProps: any) => void;
}

// Helper immutability
const setNestedValue = (obj: any, path: string[], value: any): any => {
  if (path.length === 0) return value;
  const key = path[0];
  const rest = path.slice(1);
  
  if (Array.isArray(obj)) {
    const newArr = [...obj];
    newArr[key as any] = setNestedValue(obj[key as any] || {}, rest, value);
    return newArr;
  }
  
  return {
    ...obj,
    [key]: setNestedValue(obj ? obj[key] : {}, rest, value)
  };
};

const getNestedValue = (obj: any, path: string[]) => {
  return path.reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : undefined), obj);
};

export default function PropertiesSidebar({ section, schema, components, onChange }: PropertiesSidebarProps) {
  const [localProps, setLocalProps] = useState<any>({});
  
  // Drill-down stack
  // Each level stores: path (relative to root props), schema, title
  const [stack, setStack] = useState<{ path: string[], schema: any, title: string }[]>([]);

  useEffect(() => {
    if (section) {
      setLocalProps(section.props || {});
      setStack([]);
    }
  }, [section?.id]);

  useEffect(() => {
    if (!section) return;
    const timeout = setTimeout(() => onChange(localProps), 500);
    return () => clearTimeout(timeout);
  }, [localProps]);

  if (!section) {
    return (
      <div style={{ width: '320px', background: 'hsla(var(--color-surface) / 0.85)', backdropFilter: 'blur(16px)', borderLeft: '1px solid hsl(var(--color-border))', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'hsl(var(--color-text-muted))' }}>
        <div style={{ textAlign: 'center', padding: '2rem' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '50%', background: 'hsl(var(--color-surface-hover))', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 1rem' }}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ opacity: 0.5 }}>
              <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
          </div>
          <p>Chọn một khối bên trái<br/>để cấu hình thuộc tính</p>
        </div>
      </div>
    );
  }

  // Determine current context (root or drilled-down)
  const isRoot = stack.length === 0;
  const currentContext = isRoot ? { path: [], schema: schema, title: schema?.title || 'Khối nội dung' } : stack[stack.length - 1];
  const currentProps = isRoot ? localProps : getNestedValue(localProps, currentContext.path) || {};
  const currentSchema = currentContext.schema;

  if (!currentSchema || !currentSchema.properties) {
    return (
      <div style={{ width: '320px', background: 'white', borderLeft: '1px solid hsl(var(--color-border))', padding: '2rem', textAlign: 'center', color: 'hsl(var(--color-text-muted))' }}>
        Khối này không có cấu hình.
      </div>
    );
  }

  const handleLocalChange = (key: string, value: any) => {
    if (isRoot) {
      setLocalProps((prev: any) => ({ ...prev, [key]: value }));
    } else {
      setLocalProps((prev: any) => setNestedValue(prev, [...currentContext.path, key], value));
    }
  };

  const handleDrillDown = (listKey: string, itemIndex: number, componentType: string) => {
    const compDef = components.find(c => c.type === componentType);
    if (!compDef) return;
    setStack([
      ...stack,
      {
        path: [...currentContext.path, listKey, itemIndex.toString(), 'props'],
        schema: compDef.schema,
        title: compDef.name || componentType
      }
    ]);
  };

  const popStack = () => {
    setStack(stack.slice(0, -1));
  };

  const renderField = (key: string, propDef: any, value: any) => {
    // 1. Array of primitives / enums
    if (propDef.type === 'string' && propDef.enum) {
      return (
        <div style={{ position: 'relative' }}>
          <select className="kb-input" value={value} onChange={(e) => handleLocalChange(key, e.target.value)} style={{ appearance: 'none', background: 'hsl(var(--color-surface-hover))', fontWeight: 500 }}>
            {propDef.enum.map((opt: string) => <option key={opt} value={opt}>{opt}</option>)}
          </select>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ position: 'absolute', right: '12px', top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none', color: 'hsl(var(--color-text-muted))' }}><path d="M6 9l6 6 6-6"/></svg>
        </div>
      );
    }
    // 2. HTML Textarea
    if (propDef.type === 'string' && propDef.format === 'html') {
      return (
        <div style={{ position: 'relative' }}>
          <textarea className="kb-input" value={value} onChange={(e) => handleLocalChange(key, e.target.value)} rows={6} style={{ resize: 'vertical', fontFamily: 'monospace', fontSize: '0.85rem', lineHeight: 1.5, background: 'hsl(220 30% 98%)' }} placeholder="Nhập mã HTML..." />
          <div style={{ position: 'absolute', top: '-1.5rem', right: '0', fontSize: '0.7rem', color: 'hsl(var(--color-primary))', fontWeight: 600, background: 'hsla(var(--color-primary)/0.1)', padding: '2px 6px', borderRadius: '4px' }}>HTML</div>
        </div>
      );
    }
    // 3. Boolean
    if (propDef.type === 'boolean') {
      return (
        <label style={{ display: 'inline-flex', alignItems: 'center', gap: '0.75rem', cursor: 'pointer', background: 'hsl(var(--color-surface-hover))', padding: '0.5rem 1rem', borderRadius: 'var(--radius-md)', border: '1px solid hsl(var(--color-border))' }}>
          <input type="checkbox" checked={value === true || value === 'true'} onChange={(e) => handleLocalChange(key, e.target.checked)} style={{ width: '18px', height: '18px', accentColor: 'hsl(var(--color-primary))' }} />
          <span style={{ fontSize: '0.9rem', fontWeight: 500 }}>Kích hoạt</span>
        </label>
      );
    }
    // 4. Component List (Nested Layouts)
    if (propDef.type === 'component_list') {
      const items = Array.isArray(value) ? value : [];
      return (
        <div style={{ background: 'hsla(var(--color-surface-hover)/0.5)', padding: '0.75rem', borderRadius: 'var(--radius-md)', border: '1px dashed hsl(var(--color-border))' }}>
          {items.map((item: any, index: number) => {
            const compDef = components.find(c => c.type === item.type);
            return (
              <div key={index} style={{ marginBottom: '0.5rem', padding: '0.5rem', background: 'white', border: '1px solid hsl(var(--color-border))', borderRadius: '4px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: '0.8rem', fontWeight: 500, display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                  <Settings2 size={14} style={{ color: 'hsl(var(--color-primary))' }} /> {compDef?.name || item.type}
                </span>
                <div style={{ display: 'flex', gap: '0.25rem' }}>
                  <button className="kb-btn kb-btn--sm kb-btn--outline" style={{ padding: '0.25rem 0.5rem' }} onClick={() => handleDrillDown(key, index, item.type)}>Sửa</button>
                  <button className="kb-btn" style={{ padding: '0.25rem', color: 'hsl(var(--color-danger))' }} onClick={() => {
                    const newArr = [...items];
                    newArr.splice(index, 1);
                    handleLocalChange(key, newArr);
                  }}><Trash2 size={14} /></button>
                </div>
              </div>
            );
          })}
          <div style={{ marginTop: '0.5rem', display: 'flex', gap: '0.5rem' }}>
            <select className="kb-input" style={{ flex: 1, fontSize: '0.8rem', padding: '0.4rem' }} id={`select_${key}`}>
              {components.filter(c => c.group !== 'Layout').map(c => (
                <option key={c.type} value={c.type}>{c.name}</option>
              ))}
            </select>
            <button className="kb-btn kb-btn--primary" style={{ padding: '0.4rem 0.5rem' }} onClick={() => {
              const sel = document.getElementById(`select_${key}`) as HTMLSelectElement;
              if (sel && sel.value) {
                const newArr = [...items];
                const defProps: any = {};
                const schema = components.find(c => c.type === sel.value)?.schema;
                if (schema?.properties) {
                  Object.keys(schema.properties).forEach(k => defProps[k] = schema.properties[k].default || '');
                }
                newArr.push({ id: Math.random().toString(), type: sel.value, props: defProps });
                handleLocalChange(key, newArr);
              }
            }}><Plus size={14} /> Thêm</button>
          </div>
        </div>
      );
    }
    // 5. Default String/Number Input
    return (
      <input type="text" className="kb-input" value={value || ''} onChange={(e) => handleLocalChange(key, e.target.value)} placeholder={`Nhập ${propDef.title || key}...`} />
    );
  };

  return (
    <div style={{ width: '320px', background: 'hsla(var(--color-surface)/0.95)', backdropFilter: 'blur(20px)', borderLeft: '1px solid hsl(var(--color-border))', display: 'flex', flexDirection: 'column', boxShadow: '-10px 0 30px rgba(0,0,0,0.03)', zIndex: 20 }}>
      {/* Header */}
      <div style={{ padding: '1rem 1.5rem', borderBottom: '1px solid hsl(var(--color-border)/0.5)', display: 'flex', alignItems: 'center', gap: '0.75rem', background: 'hsl(var(--color-surface))' }}>
        {!isRoot && (
          <button className="kb-btn" style={{ padding: '0.25rem', color: 'hsl(var(--color-primary))' }} onClick={popStack}>
            <ArrowLeft size={18} />
          </button>
        )}
        <div style={{ fontWeight: 700, fontSize: '1rem', flex: 1, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
          {currentContext.title}
        </div>
      </div>
      
      <div style={{ flex: 1, overflowY: 'auto', padding: '1.5rem' }}>
        {/* Dynamic Source Binding (Only for components that support it) */}
        {currentSchema.supports_dynamic_data && (
          <div style={{ marginBottom: '2rem', padding: '1rem', background: 'hsl(var(--color-surface-hover))', borderRadius: 'var(--radius-md)', border: '1px solid hsl(var(--color-primary)/0.2)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
              <span style={{ fontWeight: 600, fontSize: '0.85rem' }}>Nguồn dữ liệu</span>
              <label style={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}>
                <input type="checkbox" checked={!!currentProps.data_source} onChange={(e) => {
                  if (e.target.checked) handleLocalChange('data_source', { type: 'posts', limit: 6 });
                  else {
                    const newProps = { ...currentProps };
                    delete newProps.data_source;
                    if (isRoot) setLocalProps(newProps);
                    else setLocalProps((prev: any) => setNestedValue(prev, currentContext.path, newProps));
                  }
                }} style={{ marginRight: '0.5rem' }}/>
                <span style={{ fontSize: '0.8rem' }}>Dùng dữ liệu động</span>
              </label>
            </div>

            {currentProps.data_source && (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                <div>
                  <label style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))', display: 'block', marginBottom: '0.25rem' }}>Loại nội dung</label>
                  <select className="kb-input" value={currentProps.data_source.type || 'posts'} onChange={e => handleLocalChange('data_source', { ...currentProps.data_source, type: e.target.value })}>
                    <option value="posts">Bài viết mới nhất</option>
                    <option value="products">Sản phẩm nổi bật (TBD)</option>
                  </select>
                </div>
                <div>
                  <label style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))', display: 'block', marginBottom: '0.25rem' }}>Số lượng hiển thị</label>
                  <input type="number" className="kb-input" value={currentProps.data_source.limit || 6} onChange={e => handleLocalChange('data_source', { ...currentProps.data_source, limit: parseInt(e.target.value) || 6 })} />
                </div>
              </div>
            )}
          </div>
        )}

        {Object.entries(currentSchema.properties).map(([key, propDef]: [string, any]) => {
          const value = currentProps[key] !== undefined ? currentProps[key] : (propDef.default || '');
          return (
            <div key={key} style={{ marginBottom: '1.5rem' }}>
              <label style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', fontWeight: 600, marginBottom: '0.5rem', color: 'hsl(var(--color-text-main))' }}>
                <span>{propDef.title || key} {currentSchema.required?.includes(key) && <span style={{ color: 'hsl(var(--color-danger))' }}>*</span>}</span>
              </label>
              {renderField(key, propDef, value)}
            </div>
          );
        })}
      </div>
    </div>
  );
}
