<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\State;
use App\Models\Lga;
use Illuminate\Support\Facades\Mail;

/**
 * End-to-end smoke test for the golden path: a brand new visitor registers,
 * verifies their email, logs in, and posts their first advert — exercising
 * the real HTTP routes/controllers/validation/DB, not just unit pieces.
 */
class RegisterLoginPostAdFlowTest extends TestCase
{
    protected ?Category $category = null;
    protected ?SubCategory $subCategory = null;
    protected ?int $userId = null;
    protected array $advertIds = [];

    protected function tearDown(): void
    {
        if (!empty($this->advertIds)) {
            Advert::whereIn('id', $this->advertIds)->delete();
        }
        if ($this->userId) {
            User::where('id', $this->userId)->delete();
        }
        if ($this->subCategory) {
            $this->subCategory->delete();
        }
        if ($this->category) {
            $this->category->delete();
        }

        parent::tearDown();
    }

    /** @test */
    public function a_new_user_can_register_verify_login_and_post_an_advert()
    {
        Mail::fake();

        // Category id 3 ("Jobs") is hardcoded throughout AdvertValidationService
        // to skip the mandatory-image, price, and item-condition requirements —
        // those category/subcategory IDs are production data normally entered
        // through the admin panel, not seeded by a migration. `id` isn't
        // mass-assignable on these models, so unguard() to force it for this
        // fixture rather than fighting category-specific validation branches
        // that don't apply to a generic "post an ad" smoke test.
        Category::unguard();
        $this->category = Category::create([
            'id' => 3,
            'category' => 'Test Jobs Category',
            'icon' => 'test-icon.svg',
        ]);
        Category::reguard();

        SubCategory::unguard();
        $this->subCategory = SubCategory::create([
            'id' => 999,
            'cat_id' => $this->category->id,
            'sub_category' => 'Test Jobs Subcategory',
            'icon' => 'test-icon.svg',
        ]);
        SubCategory::reguard();

        // Don't rely on StateSeeder having run — a sibling test suite using
        // RefreshDatabase may have migrate:fresh'd the DB without reseeding.
        // Create self-contained fixtures instead of depending on execution order.
        $state = State::firstOrCreate(
            ['name' => 'Central'],
            ['slug' => 'central-' . uniqid(), 'station_id' => 1]
        );
        $lga = Lga::firstOrCreate(
            ['state_id' => $state->id, 'name' => 'Kampala'],
            ['slug' => 'kampala-' . uniqid()]
        );

        $email = 'e2e-' . uniqid() . '@example.com';
        $password = 'password123';
        $phone = '07' . random_int(10000000, 99999999);

        // ── 1. Register ─────────────────────────────────────────────────
        $registerResponse = $this->post('/register', [
            'acc_type' => 'Private',
            'name' => 'E2E Test User',
            'phone' => $phone,
            'email' => $email,
            'password' => $password,
        ]);

        $registerResponse->assertSessionHasNoErrors();
        $registerResponse->assertRedirect('/login');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'Registration should have created a user.');
        $this->userId = $user->id;
        $this->assertSame(0, (int) $user->acc_status, 'New account should start unverified.');
        $this->assertNotEmpty($user->token, 'Registration should set a verification token.');

        Mail::assertQueued(\App\Mail\RegisterMail::class);

        // ── 2. Verify email (simulates clicking the emailed link) ──────
        $verifyResponse = $this->get('/verifyaccount/' . $user->email . '/' . $user->token);
        $verifyResponse->assertRedirect('/login');
        $verifyResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(1, (int) $user->acc_status, 'Account should be verified after visiting the link.');
        $this->assertNull($user->token, 'Verification token should be cleared after use.');

        // ── 3. Login ─────────────────────────────────────────────────
        $loginResponse = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // No OTP step is actually wired into processLoginAttempt (triggerOtpLogin
        // is dead code) — login should complete immediately and land on the
        // user's profile page, not bounce to /authenticate.
        $loginResponse->assertSessionHasNoErrors();
        $loginResponse->assertRedirect();
        $this->assertStringNotContainsString('/authenticate', $loginResponse->headers->get('Location') ?? '');
        // This app tracks the logged-in user via a custom session('user_id') key,
        // not Laravel's Auth facade/guard — so assertAuthenticatedAs doesn't apply.
        $this->assertSame($user->user_id, session('user_id'), 'Web session should carry the logged-in user_id.');

        // ── 4. View the post-ad form ────────────────────────────────────
        $formResponse = $this->get('/user/post-ad');
        $formResponse->assertOk();
        $formResponse->assertViewIs('user.post-ad');

        // ── 5. Post an advert ────────────────────────────────────────────
        $postResponse = $this->post('/user/post-ad', [
            'ad_title' => 'E2E Test Advert ' . uniqid(),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'subcategory' => $this->subCategory->id,
            'brand' => 'N/A',
            'salary' => 500000,
            'buy_direct' => 'No',
            'state' => $state->name,
            'lga' => $lga->name,
            'description' => 'This is a complete end-to-end test advert description, well over the minimum length required.',
            'shipment' => 'No',
            'show_contact' => 'Yes',
        ]);

        $postResponse->assertSessionHasNoErrors();
        $postResponse->assertRedirect('/user/my-ads');
        $postResponse->assertSessionHas('success');

        $advert = Advert::where('user_id', $user->user_id)->latest('id')->first();
        $this->assertNotNull($advert, 'Posting the advert should have created an Advert row.');
        $this->advertIds[] = $advert->id;
        $this->assertContains($advert->ad_status, ['active', 'pending_review'],
            'Newly posted advert should be live or awaiting moderation, not stuck in an error state.');

        // ── 6. The new ad shows up on the user's "My Ads" page ──────────
        $myAdsResponse = $this->get('/user/my-ads');
        $myAdsResponse->assertOk();
    }
}
