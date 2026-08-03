<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $customer): Order
    {
        return $customer->orders()->create([
            'status' => 'pending',
            'total' => 99.98,
            'subtotal' => 99.98,
            'tax_amount' => 0,
            'shipping_amount' => 0,
        ]);
    }

    // ------------------------------------------------------------------ //
    // Order placed → admin in-app notifications
    // ------------------------------------------------------------------ //

    public function test_order_placed_creates_in_app_notification_for_each_admin(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $superAdmin = User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);
        $regularUser = User::factory()->create(['is_admin' => false, 'role' => 'user']); // must NOT be notified
        $customer = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $order = $this->makeOrder($customer);

        event(new OrderPlaced($order));

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'title' => 'New order placed',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $superAdmin->id,
            'title' => 'New order placed',
        ]);

        // Regular users (customers) must not receive the admin alert.
        $this->assertDatabaseMissing('user_notifications', [
            'user_id' => $regularUser->id,
            'title' => 'New order placed',
        ]);

        // The customer still gets the standard confirmation notification.
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $customer->id,
            'title' => 'Order placed',
        ]);
    }

    public function test_order_placed_notification_includes_order_link(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $customer = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $order = $this->makeOrder($customer);

        event(new OrderPlaced($order));

        $notification = $admin->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString("order #{$order->id}", $notification->message);
        $this->assertStringContainsString(
            route('admin.orders.show', $order->id),
            (string) $notification->action_url
        );
    }

    public function test_order_placed_without_customer_still_notifies_admins(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $customer = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $order = $this->makeOrder($customer);

        // Detach the loaded customer relation in-memory to simulate the
        // customer account being gone by the time the listener runs — the
        // listener must fall back to 'Guest' instead of crashing.
        $order->setRelation('user', null);

        event(new OrderPlaced($order));

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $admin->id,
            'title' => 'New order placed',
            'message' => 'New order #' . $order->id . ' from Guest — $' . number_format($order->total, 2) . '.',
        ]);
    }

    // ------------------------------------------------------------------ //
    // Order placed → Telegram alert to admin chat
    // ------------------------------------------------------------------ //

    public function test_order_placed_sends_telegram_alert_to_admin_chat(): void
    {
        Queue::fake();

        config(['services.telegram.bot_token' => 'test-bot-token']);
        config(['services.telegram.chat_id' => '123456789']);

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $customer = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $order = $this->makeOrder($customer);

        event(new OrderPlaced($order));

        Http::assertSent(function ($request) use ($order) {
            return str_contains($request->url(), 'api.telegram.org/bottest-bot-token/sendMessage')
                && $request['chat_id'] === '123456789'
                && str_contains($request['text'], "New order #{$order->id}");
        });
    }

    public function test_order_placed_skips_telegram_when_not_configured(): void
    {
        Queue::fake();

        config(['services.telegram.bot_token' => null]);
        config(['services.telegram.chat_id' => null]);

        Http::fake();

        $customer = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $order = $this->makeOrder($customer);

        event(new OrderPlaced($order));

        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ //
    // Admin notification endpoints (web)
    // ------------------------------------------------------------------ //

    public function test_admin_can_list_notifications(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $admin->notifications()->create([
            'title' => 'New order placed',
            'message' => 'New order #5 from John — $10.00.',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.index'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'New order placed');
    }

    public function test_admin_sees_unread_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $admin->notifications()->create(['title' => 'Unread one']);
        $admin->notifications()->create(['title' => 'Unread two']);
        $admin->notifications()->create(['title' => 'Read one', 'read_at' => now()]);

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 2]);
    }

    public function test_admin_can_mark_single_notification_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $notification = $admin->notifications()->create(['title' => 'New order placed']);

        $this->actingAs($admin)
            ->putJson(route('admin.notifications.read', $notification))
            ->assertOk()
            ->assertJsonPath('read_at', fn ($value) => $value !== null);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_cannot_mark_another_users_notification_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $other = User::factory()->create(['is_admin' => false, 'role' => 'user']);
        $notification = $other->notifications()->create(['title' => 'Someone else']);

        $this->actingAs($admin)
            ->putJson(route('admin.notifications.read', $notification))
            ->assertForbidden();
    }

    public function test_admin_can_mark_all_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $admin->notifications()->create(['title' => 'One']);
        $admin->notifications()->create(['title' => 'Two']);

        $this->actingAs($admin)
            ->putJson(route('admin.notifications.read-all'))
            ->assertOk()
            ->assertJson(['updated' => 2]);

        $this->assertSame(0, $admin->notifications()->whereNull('read_at')->count());
    }

    public function test_notification_endpoints_require_auth(): void
    {
        $this->getJson(route('admin.notifications.index'))->assertUnauthorized();
        $this->getJson(route('admin.notifications.unread-count'))->assertUnauthorized();
        $this->putJson(route('admin.notifications.read-all'))->assertUnauthorized();
    }
}
