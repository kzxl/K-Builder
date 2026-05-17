import { useParams } from 'react-router-dom';
import { Settings } from 'lucide-react';

export default function PluginPlaceholder() {
  const { slug } = useParams();

  return (
    <div className="animate-fade-in" style={{ padding: '2rem', textAlign: 'center', minHeight: '60vh', display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center' }}>
      <Settings size={64} style={{ color: 'hsla(var(--color-primary)/0.2)', marginBottom: '1.5rem' }} />
      <h1 className="kb-page-title" style={{ marginBottom: '0.5rem' }}>Plugin Đang Phát Triển</h1>
      <p className="kb-page-subtitle" style={{ maxWidth: '500px', margin: '0 auto' }}>
        Màn hình giao diện cho plugin <strong>{slug}</strong> hiện tại chưa được triển khai hoàn chỉnh hoặc đang trong quá trình phát triển.
      </p>
      <p style={{ marginTop: '2rem', fontSize: '0.9rem', color: 'hsl(var(--color-text-muted))' }}>
        Tính năng này sẽ sớm ra mắt trong các bản cập nhật tiếp theo.
      </p>
    </div>
  );
}
