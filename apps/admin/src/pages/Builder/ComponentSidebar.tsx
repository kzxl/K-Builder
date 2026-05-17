import { useState } from 'react';
import { Plus, Layout } from 'lucide-react';

interface ComponentSidebarProps {
  components: any[];
  onAdd: (type: string) => void;
  onAddPattern?: (pattern: any) => void;
}

const BLOCK_PATTERNS = [
  {
    id: 'pattern_landing_header',
    name: 'Landing Header (Hero + Features)',
    description: 'Phần đầu trang giới thiệu hoàn hảo',
    blocks: [
      {
        type: 'hero_split',
        props: {
          title: 'Giải pháp chuyển đổi số toàn diện',
          subtitle: 'Giúp doanh nghiệp của bạn bứt phá với công nghệ lõi tiên tiến.',
          cta_text: 'Bắt đầu ngay',
          image_side: 'right'
        }
      },
      {
        type: 'core_features',
        props: {
          title: '',
          subtitle: '',
          columns: '3',
          items: [
            { icon: 'zap', title: 'Siêu tốc', description: 'Tốc độ xử lý cực nhanh' },
            { icon: 'shield', title: 'Bảo mật', description: 'An toàn dữ liệu tuyệt đối' },
            { icon: 'smartphone', title: 'Đa nền tảng', description: 'Hỗ trợ mọi thiết bị' }
          ]
        }
      }
    ]
  },
  {
    id: 'pattern_faq_cta',
    name: 'FAQ & CTA',
    description: 'Giải đáp thắc mắc và Kêu gọi hành động',
    blocks: [
      {
        type: 'core_faq',
        props: {
          title: 'Câu hỏi thường gặp',
          items: [
            { question: 'Chi phí triển khai là bao nhiêu?', answer: 'Tùy thuộc vào quy mô doanh nghiệp.' },
            { question: 'Có hỗ trợ kỹ thuật không?', answer: 'Chúng tôi hỗ trợ 24/7.' }
          ]
        }
      },
      {
        type: 'core_button',
        props: {
          text: 'Đăng ký nhận tư vấn',
          url: '/contact',
          style: 'primary',
          size: 'lg',
          alignment: 'center'
        }
      }
    ]
  }
];

export default function ComponentSidebar({ components, onAdd, onAddPattern }: ComponentSidebarProps) {
  const [activeTab, setActiveTab] = useState<'blocks' | 'patterns'>('blocks');

  // Group components by category
  const grouped = components.reduce((acc, curr) => {
    const cat = curr.category || 'Khác';
    if (!acc[cat]) acc[cat] = [];
    acc[cat].push(curr);
    return acc;
  }, {} as Record<string, any[]>);

  return (
    <div style={{ width: '280px', background: 'white', borderRight: '1px solid hsl(var(--color-border))', display: 'flex', flexDirection: 'column' }}>
      <div style={{ padding: '1rem', borderBottom: '1px solid hsl(var(--color-border))', fontWeight: 600 }}>
        Thư viện Component
      </div>
      
      {/* Tabs */}
      <div style={{ display: 'flex', borderBottom: '1px solid hsl(var(--color-border))' }}>
        <button 
          onClick={() => setActiveTab('blocks')}
          style={{ flex: 1, padding: '0.75rem', background: activeTab === 'blocks' ? 'transparent' : 'hsl(var(--color-surface-hover))', border: 'none', borderBottom: activeTab === 'blocks' ? '2px solid hsl(var(--color-primary))' : '2px solid transparent', fontWeight: activeTab === 'blocks' ? 600 : 400, color: activeTab === 'blocks' ? 'hsl(var(--color-primary))' : 'inherit', cursor: 'pointer' }}
        >
          Khối Đơn
        </button>
        <button 
          onClick={() => setActiveTab('patterns')}
          style={{ flex: 1, padding: '0.75rem', background: activeTab === 'patterns' ? 'transparent' : 'hsl(var(--color-surface-hover))', border: 'none', borderBottom: activeTab === 'patterns' ? '2px solid hsl(var(--color-primary))' : '2px solid transparent', fontWeight: activeTab === 'patterns' ? 600 : 400, color: activeTab === 'patterns' ? 'hsl(var(--color-primary))' : 'inherit', cursor: 'pointer' }}
        >
          Cụm (Patterns)
        </button>
      </div>

      <div style={{ flex: 1, overflowY: 'auto', padding: '1rem' }}>
        {activeTab === 'blocks' && Object.entries(grouped).map(([category, items]) => {
          const compItems = items as any[];
          return (
          <div key={category} style={{ marginBottom: '1.5rem' }}>
            <div style={{ fontSize: '0.8rem', textTransform: 'uppercase', color: 'hsl(var(--color-text-muted))', fontWeight: 600, marginBottom: '0.75rem', letterSpacing: '0.05em' }}>
              {category}
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '0.5rem' }}>
              {compItems.map(comp => (
                <button 
                  key={comp.type}
                  onClick={() => onAdd(comp.type)}
                  style={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'space-between',
                    padding: '0.75rem 1rem', 
                    background: 'hsl(var(--color-surface-hover))', 
                    border: '1px solid hsl(var(--color-border))',
                    borderRadius: 'var(--radius-md)',
                    cursor: 'pointer',
                    textAlign: 'left',
                    transition: 'all 0.2s'
                  }}
                  onMouseOver={e => e.currentTarget.style.borderColor = 'hsl(var(--color-primary))'}
                  onMouseOut={e => e.currentTarget.style.borderColor = 'hsl(var(--color-border))'}
                >
                  <div>
                    <div style={{ fontWeight: 500, fontSize: '0.9rem' }}>{comp.name}</div>
                    <div style={{ fontSize: '0.75rem', color: 'hsl(var(--color-text-muted))' }}>{comp.type}</div>
                  </div>
                  <Plus size={16} style={{ color: 'hsl(var(--color-text-muted))' }} />
                </button>
              ))}
            </div>
          </div>
        )})}

        {activeTab === 'patterns' && (
          <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '1rem' }}>
            {BLOCK_PATTERNS.map(pattern => (
              <div 
                key={pattern.id}
                style={{ 
                  background: 'hsl(var(--color-surface-hover))', 
                  border: '1px solid hsl(var(--color-border))',
                  borderRadius: 'var(--radius-md)',
                  overflow: 'hidden'
                }}
              >
                <div style={{ padding: '2rem 1rem', background: 'hsla(var(--color-primary)/0.05)', display: 'flex', justifyContent: 'center', color: 'hsl(var(--color-primary))' }}>
                  <Layout size={32} />
                </div>
                <div style={{ padding: '1rem' }}>
                  <div style={{ fontWeight: 600, fontSize: '0.95rem', marginBottom: '0.25rem' }}>{pattern.name}</div>
                  <div style={{ fontSize: '0.8rem', color: 'hsl(var(--color-text-muted))', marginBottom: '1rem' }}>{pattern.description}</div>
                  <button 
                    onClick={() => onAddPattern && onAddPattern(pattern)}
                    className="kb-btn kb-btn--outline" 
                    style={{ width: '100%', justifyContent: 'center' }}
                  >
                    <Plus size={14} style={{ marginRight: '0.25rem' }} /> Chèn Cụm
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
