<?php

namespace Tests\Unit;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    public function test_share_includes_profile_photo_fields_for_authenticated_user(): void
    {
        $user = new User();
        $user->forceFill([
            'nombre' => 'Ana',
            'email' => 'ana@example.com',
            'profile_photo_path' => 'profile-photos/test.jpg',
        ]);

        $request = Request::create('/mi-perfil', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new HandleInertiaRequests();
        $sharedProps = $middleware->share($request);

        $this->assertSame('profile-photos/test.jpg', $sharedProps['auth']['user']['profile_photo_path']);
        $this->assertStringContainsString('storage/profile-photos/test.jpg', $sharedProps['auth']['user']['profile_photo_url']);
    }
}
