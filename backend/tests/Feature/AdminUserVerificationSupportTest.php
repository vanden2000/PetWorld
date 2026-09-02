<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSupportLog;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserVerificationSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_resend_verification_to_an_unverified_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->unverified()->create(['role' => 'user', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.users.verification.resend', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        Notification::assertSentTo($customer, VerifyEmailNotification::class);
        $this->assertDatabaseHas('user_support_logs', [
            'user_id' => $customer->id,
            'admin_id' => $admin->id,
            'action' => UserSupportLog::VERIFICATION_RESENT,
        ]);
    }

    public function test_admin_cannot_resend_verification_to_a_verified_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.users.verification.resend', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        Notification::assertNothingSent();
        $this->assertDatabaseCount('user_support_logs', 0);
    }

    public function test_admin_can_send_password_reset_otp_to_a_verified_customer(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.users.password-reset-otp.send', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $customer->email]);
        $this->assertDatabaseHas('user_support_logs', [
            'user_id' => $customer->id,
            'admin_id' => $admin->id,
            'action' => UserSupportLog::PASSWORD_RESET_SENT,
        ]);
    }
    public function test_admin_can_unblock_a_customer_with_a_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'blocked']);

        $response = $this->actingAs($admin)->patch(route('admin.users.unblock', $customer), [
            'reason' => 'Đã xác minh lại thông tin khách hàng.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'status' => 'active']);
        $this->assertDatabaseHas('user_support_logs', [
            'user_id' => $customer->id,
            'admin_id' => $admin->id,
            'action' => UserSupportLog::ACCOUNT_UNBLOCKED,
            'reason' => 'Đã xác minh lại thông tin khách hàng.',
        ]);
    }
}
