<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSeoManager;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Kiểm tra bảng redirects: nếu path hiện tại khớp from_url đang active thì
 * trả về HTTP redirect (301/302) tới to_url và tăng hit_count.
 *
 * Bỏ qua các path /api/ và /admin để không cản trở backend & SPA.
 */
class RedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (str_contains($path, '/api/') || str_contains($path, '/admin')) {
            return $handler->handle($request);
        }

        // Chuẩn hóa: so khớp theo path (bỏ basePath nếu có thể) — thử cả path đầy đủ và path đuôi
        $candidates = array_unique([$path, '/' . ltrim($path, '/')]);

        try {
            $redirect = DB::table('redirects')
                ->whereIn('from_url', $candidates)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable $e) {
            return $handler->handle($request);
        }

        if ($redirect) {
            DB::table('redirects')->where('id', $redirect->id)->increment('hit_count');

            $code = in_array((int) $redirect->code, [301, 302, 307, 308], true) ? (int) $redirect->code : 301;
            $response = new Response($code);
            return $response->withHeader('Location', $redirect->to_url);
        }

        return $handler->handle($request);
    }
}
