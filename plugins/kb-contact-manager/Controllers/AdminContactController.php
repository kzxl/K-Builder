<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactManager\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class AdminContactController
{
    /**
     * Helper response JSON
     */
    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /**
     * GET /api/admin/contacts
     * Lấy danh sách liên hệ, hỗ trợ lọc, tìm kiếm, phân trang
     */
    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        
        $page = (int) ($queryParams['page'] ?? 1);
        $limit = (int) ($queryParams['limit'] ?? 15);
        $search = trim($queryParams['search'] ?? '');
        $status = trim($queryParams['status'] ?? '');
        $priority = trim($queryParams['priority'] ?? '');
        
        $query = DB::table('kb_form_submissions');
        
        // Tìm kiếm đa trường
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        // Lọc theo status
        if (!empty($status)) {
            $query->where('status', $status);
        }
        
        // Lọc theo priority
        if (!empty($priority)) {
            $query->where('priority', $priority);
        }
        
        $total = $query->count();
        $pages = (int) ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        
        $items = $query->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
            
        return $this->json($response, [
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'total' => $total,
                    'pages' => $pages,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]
        ]);
    }

    /**
     * GET /api/admin/contacts/{id}
     * Chi tiết liên hệ, tự động đánh dấu đã đọc nếu là liên hệ mới
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $contact = DB::table('kb_form_submissions')->where('id', $id)->first();
        
        if (!$contact) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Không tìm thấy thông tin liên hệ.'
            ], 404);
        }
        
        // Nếu liên hệ đang ở trạng thái 'new', tự động cập nhật thành 'read'
        if ($contact->status === 'new') {
            DB::table('kb_form_submissions')
                ->where('id', $id)
                ->update([
                    'status' => 'read',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            $contact->status = 'read';
        }
        
        return $this->json($response, [
            'success' => true,
            'data' => $contact
        ]);
    }

    /**
     * PUT /api/admin/contacts/{id}
     * Cập nhật trạng thái xử lý, độ ưu tiên, ghi chú
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        if (!$body) {
            $raw = (string) $request->getBody();
            $body = json_decode($raw, true);
        }
        
        $contact = DB::table('kb_form_submissions')->where('id', $id)->first();
        if (!$contact) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Không tìm thấy thông tin liên hệ.'
            ], 404);
        }
        
        $updateData = [];
        
        if (isset($body['status'])) {
            $status = trim($body['status']);
            if (in_array($status, ['new', 'read', 'in_progress', 'resolved', 'ignored'], true)) {
                $updateData['status'] = $status;
            }
        }
        
        if (isset($body['priority'])) {
            $priority = trim($body['priority']);
            if (in_array($priority, ['low', 'medium', 'high'], true)) {
                $updateData['priority'] = $priority;
            }
        }
        
        if (isset($body['notes'])) {
            $updateData['notes'] = trim($body['notes']);
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            DB::table('kb_form_submissions')->where('id', $id)->update($updateData);
        }
        
        return $this->json($response, [
            'success' => true,
            'message' => 'Cập nhật thông tin thành công.'
        ]);
    }

    /**
     * DELETE /api/admin/contacts/{id}
     * Xóa thông tin liên hệ
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $exists = DB::table('kb_form_submissions')->where('id', $id)->exists();
        
        if (!$exists) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Không tìm thấy thông tin liên hệ.'
            ], 404);
        }
        
        DB::table('kb_form_submissions')->where('id', $id)->delete();
        
        return $this->json($response, [
            'success' => true,
            'message' => 'Xóa liên hệ thành công.'
        ]);
    }

    /**
     * GET /api/admin/contacts/stats/summary
     * Lấy thống kê tổng hợp cho CRM
     */
    public function stats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // 1. Đếm theo trạng thái
        $statusCounts = DB::table('kb_form_submissions')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
        $stats = [
            'total' => DB::table('kb_form_submissions')->count(),
            'new' => $statusCounts['new'] ?? 0,
            'read' => $statusCounts['read'] ?? 0,
            'in_progress' => $statusCounts['in_progress'] ?? 0,
            'resolved' => $statusCounts['resolved'] ?? 0,
            'ignored' => $statusCounts['ignored'] ?? 0,
        ];
        
        // 2. Đếm theo mức độ ưu tiên
        $priorityCounts = DB::table('kb_form_submissions')
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();
            
        $stats['priority'] = [
            'low' => $priorityCounts['low'] ?? 0,
            'medium' => $priorityCounts['medium'] ?? 0,
            'high' => $priorityCounts['high'] ?? 0,
        ];
        
        // 3. Lịch sử biểu đồ 7 ngày gần nhất
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = DB::table('kb_form_submissions')
                ->whereDate('created_at', $date)
                ->count();
                
            $chartData[] = [
                'date' => date('d/m', strtotime($date)),
                'count' => $count
            ];
        }
        $stats['chart_7_days'] = $chartData;
        
        return $this->json($response, [
            'success' => true,
            'data' => $stats
        ]);
    }
}
