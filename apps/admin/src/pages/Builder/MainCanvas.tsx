import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Trash2 } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';
import api from '../../lib/api';

function PreviewIFrame({ type, props }: { type: string; props: any }) {
  const [html, setHtml] = useState<string>('');
  const [loading, setLoading] = useState<boolean>(true);
  const [themeVars, setThemeVars] = useState<any>({});
  const iframeRef = useRef<HTMLIFrameElement>(null);

  // Lấy cấu hình Theme
  useEffect(() => {
    api.get('/settings/theme').then(res => {
      if (res.data.success && res.data.data) {
        setThemeVars(res.data.data);
      }
    });
  }, []);

  // Gọi API lấy HTML từ Twig Backend (có debounce)
  useEffect(() => {
    setLoading(true);
    const timeout = setTimeout(async () => {
      try {
        const res = await api.post('/components/preview', { type, props });
        if (res.data.success) {
          const content = `
            <!DOCTYPE html>
            <html>
            <head>
              <style>
                :root {
                  --kb-primary: ${themeVars.primary || '#2563EB'};
                  --kb-radius-md: ${themeVars.border_radius || '12px'};
                  --kb-radius-lg: ${themeVars.border_radius === '0px' ? '0px' : (parseInt(themeVars.border_radius) * 1.5 + 'px')};
                }
                body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: transparent; }
                * { box-sizing: border-box; }
                /* Custom scrollbar for iframe */
                ::-webkit-scrollbar { width: 6px; }
                ::-webkit-scrollbar-track { background: transparent; }
                ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }
              </style>
              <link rel="stylesheet" href="/kbuilder/public/assets/css/kbuilder.css">
            </head>
            <body>
              ${res.data.html}
              <script>
                // Gửi chiều cao thực tế ra cho Parent
                const resizeObserver = new ResizeObserver(() => {
                  window.parent.postMessage({ type: 'resize', height: document.body.scrollHeight, id: '${type}' }, '*');
                });
                resizeObserver.observe(document.body);
              </script>
            </body>
            </html>
          `;
          setHtml(content);
        }
      } catch (e) {
        setHtml('<div style="padding: 1rem; color: #e11d48; text-align: center; font-weight: 500;">Lỗi render khối nội dung</div>');
      } finally {
        setLoading(false);
      }
    }, 400); // 400ms debounce

    return () => clearTimeout(timeout);
  }, [type, props]);

  // Nghe event resize từ Iframe để tự động dãn chiều cao
  useEffect(() => {
    const handleMessage = (e: MessageEvent) => {
      if (e.data?.type === 'resize' && e.data?.id === type && iframeRef.current) {
        iframeRef.current.style.height = `${Math.max(60, e.data.height)}px`;
      }
    };
    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [type]);

  return (
    <div style={{ position: 'relative', width: '100%', minHeight: '80px', background: 'white', borderRadius: '0 0 var(--radius-md) var(--radius-md)' }}>
      {loading && (
        <div style={{ position: 'absolute', inset: 0, background: 'rgba(255,255,255,0.8)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 10, backdropFilter: 'blur(2px)' }}>
          <div style={{ width: '24px', height: '24px', border: '3px solid hsl(var(--color-primary))', borderTopColor: 'transparent', borderRadius: '50%', animation: 'spin 1s linear infinite' }} />
        </div>
      )}
      <iframe 
        ref={iframeRef}
        srcDoc={html} 
        style={{ width: '100%', border: 'none', pointerEvents: 'none', display: 'block', transition: 'height 0.2s ease' }} 
        sandbox="allow-same-origin allow-scripts"
      />
    </div>
  );
}

interface SortableSectionProps {
  section: any;
  isSelected: boolean;
  onSelect: () => void;
  onRemove: () => void;
  componentDef: any;
}

function SortableSection({ section, isSelected, onSelect, onRemove, componentDef }: SortableSectionProps) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: section.id });

  const [isHovered, setIsHovered] = useState(false);

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    border: isSelected ? '2px solid hsl(var(--color-primary))' : (isHovered ? '2px solid hsla(var(--color-primary)/0.3)' : '2px solid transparent'),
    background: 'transparent',
    position: 'relative' as const,
    cursor: 'pointer',
    zIndex: isDragging ? 50 : (isSelected ? 10 : (isHovered ? 5 : 1)),
    scale: isDragging ? '0.98' : '1',
  };

  return (
    <div 
      ref={setNodeRef} 
      style={style} 
      onClick={onSelect}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Floating Action Bar (Only visible when selected or hovered) */}
      {(isSelected || isHovered) && (
        <div style={{ 
          position: 'absolute', 
          top: '0', 
          left: '50%',
          transform: 'translateX(-50%)',
          display: 'flex', 
          alignItems: 'center', 
          gap: '0.5rem', 
          padding: '0.25rem 0.5rem', 
          background: 'hsl(var(--color-primary))',
          color: 'white',
          borderRadius: '0 0 var(--radius-md) var(--radius-md)',
          boxShadow: 'var(--shadow-md)',
          zIndex: 20
        }}>
          <div {...attributes} {...listeners} style={{ cursor: 'grab', display: 'flex', alignItems: 'center', padding: '0.25rem', borderRadius: '4px' }} className="drag-handle">
            <GripVertical size={16} />
          </div>
          <span style={{ fontWeight: 600, fontSize: '0.8rem' }}>
            {componentDef?.name || section.type}
          </span>
          {isSelected && (
            <button 
              onClick={(e) => { e.stopPropagation(); onRemove(); }}
              style={{ color: 'white', background: 'hsla(0,0%,0%,0.2)', border: 'none', cursor: 'pointer', padding: '0.25rem', borderRadius: '4px', display: 'flex', alignItems: 'center', transition: 'all 0.2s', marginLeft: '0.5rem' }}
              title="Xóa khối này"
            >
              <Trash2 size={14} />
            </button>
          )}
        </div>
      )}

      {/* Block Preview via IFrame */}
      <PreviewIFrame type={section.type} props={section.props} />
    </div>
  );
}

export default function MainCanvas({ layout, selectedId, onSelect, onRemove, components }: any) {
  return (
    <div>
      {layout.map((section: any) => (
        <SortableSection 
          key={section.id}
          section={section}
          isSelected={selectedId === section.id}
          onSelect={() => onSelect(section.id)}
          onRemove={() => onRemove(section.id)}
          componentDef={components.find((c: any) => c.type === section.type)}
        />
      ))}
    </div>
  );
}
