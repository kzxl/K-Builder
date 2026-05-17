<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactForm\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class ContactController
{
    public function submit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (!$body) {
            $raw = (string) $request->getBody();
            $body = json_decode($raw, true);
        }

        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $phone = trim($body['phone'] ?? '');
        $message = trim($body['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Vui lòng điền đầy đủ Họ Tên, Email và Nội dung lời nhắn.'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Email không hợp lệ.'
            ], 400);
        }

        try {
            DB::table('kb_form_submissions')->insert([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->json($response, [
                'success' => true,
                'message' => 'Cảm ơn bạn! Lời nhắn đã được gửi thành công.'
            ]);
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'error' => 'Đã có lỗi hệ thống xảy ra khi gửi lời nhắn.'
            ], 500);
        }
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
