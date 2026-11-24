<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_login_screen_shows_google_sso_when_configured(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('auth.google'), false)
            ->assertSee('Masuk dengan Google');
    }

    public function test_google_sso_reports_missing_configuration(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('auth.google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');
    }

    public function test_google_sso_returns_to_login_when_oauth_state_is_invalid(): void
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'google' => 'Sesi login Google sudah kedaluwarsa atau tidak cocok. Silakan mulai lagi dari halaman login.',
            ]);
    }

    public function test_google_sso_creates_a_verified_guest_account(): void
    {
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-user-id',
            'name' => 'Google Guest',
            'email' => 'guest@example.com',
            'email_verified' => true,
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Google Guest',
            'email' => 'guest@example.com',
            'role' => 'guest',
        ]);
        $this->assertNotNull(User::query()->where('email', 'guest@example.com')->firstOrFail()->email_verified_at);
    }

    public function test_google_sso_preserves_the_role_of_an_existing_user(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-user-id',
            'email' => $user->email,
            'email_verified' => true,
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('admin', $user->fresh()->role->value);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_sso_rejects_unverified_emails(): void
    {
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-user-id',
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_sso_requires_two_factor_challenge_for_protected_users(): void
    {
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
        $user = User::factory()->withTwoFactor()->create();
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-user-id',
            'email' => $user->email,
            'email_verified' => true,
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHas('login.id', $user->id);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrorsIn('email');

        $this->assertGuest();
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge(): void
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
