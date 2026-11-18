<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthSystemTest
{
    private $app;
    private $httpKernel;
    private $passed = 0;
    private $failed = 0;

    public function __construct($app, $httpKernel)
    {
        $this->app = $app;
        $this->httpKernel = $httpKernel;
    }

    private function log($status, $title, $detail = '')
    {
        if ($status === 'PASS') {
            $this->passed++;
            echo "[\033[32mPASS\033[0m] {$title}" . ($detail ? " — {$detail}" : "") . PHP_EOL;
        } else {
            $this->failed++;
            echo "[\033[31mFAIL\033[0m] {$title} — {$detail}" . PHP_EOL;
        }
    }

    private function request($uri, $method = 'GET', $parameters = [], $authUser = null)
    {
        Auth::logout();
        Auth::forgetGuards();

        $session = $this->app['session']->driver();
        $session->flush();
        $session->regenerate();
        $session->regenerateToken();

        if ($authUser) {
            $session->put(Auth::getName(), $authUser->id);
        }

        $session->save();

        if ($method === 'POST' && (!isset($parameters['_token']) || $parameters['_token'] !== 'INVALID_TOKEN')) {
            $parameters['_token'] = $session->token();
        }

        $req = Request::create($uri, $method, $parameters);
        $req->cookies->set($session->getName(), $session->getId());
        $req->setLaravelSession($session);

        $response = $this->httpKernel->handle($req);
        $finalSession = $req->hasSession() ? $req->session() : $session;

        return [$response, $finalSession];
    }

    private function hasError($session, $field)
    {
        $errors = $session->get('errors');
        if (!$errors) return false;
        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            return $errors->has($field);
        }
        if (is_array($errors)) {
            if (isset($errors['default'])) {
                $bag = $errors['default'];
                if (is_object($bag) && method_exists($bag, 'has')) {
                    return $bag->has($field);
                }
                if (isset($bag['messages'][$field])) {
                    return !empty($bag['messages'][$field]);
                }
                if (isset($bag[$field])) {
                    return !empty($bag[$field]);
                }
            }
            return isset($errors[$field]);
        }
        return false;
    }

    private function getError($session, $field)
    {
        $errors = $session->get('errors');
        if (!$errors) return '';
        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            return $errors->first($field);
        }
        if (is_array($errors) && isset($errors['default']['messages'][$field][0])) {
            return $errors['default']['messages'][$field][0];
        }
        return 'Validation error';
    }

    public function run()
    {
        echo "==========================================================" . PHP_EOL;
        echo "   EVENTHUB USER AUTHENTICATION & INTEGRATION VERIFICATION" . PHP_EOL;
        echo "==========================================================" . PHP_EOL . PHP_EOL;

        // 1. Successful Signup
        $testEmail = 'newuser_' . time() . '@test.com';
        $testPassword = 'Password123!';
        
        $s1 = $this->app['session']->driver();
        $s1->flush(); $s1->regenerate(); $s1->regenerateToken();
        [$res1, $after1] = $this->request('/register', 'POST', [
            '_token' => $s1->token(),
            'name' => 'Sophia Taylor',
            'email' => $testEmail,
            'password' => $testPassword,
            'password_confirmation' => $testPassword,
            'is_admin' => 1, // Malicious attempt to self-assign admin!
        ]);

        $createdUser = User::where('email', $testEmail)->first();
        if ($res1->getStatusCode() === 302 && $createdUser) {
            $this->log('PASS', '1. User Signup', "Account created for '{$createdUser->name}' ({$createdUser->email}) in MySQL event_registration.users");
        } else {
            $this->log('FAIL', '1. User Signup', 'User was not created in MySQL');
        }

        // 2. Public signup cannot create an admin
        if ($createdUser && $createdUser->is_admin === false) {
            $this->log('PASS', '2. Admin Privilege Escalation Prevention', 'is_admin is strictly FALSE in MySQL despite is_admin=1 payload');
        } else {
            $this->log('FAIL', '2. Admin Privilege Escalation Prevention', 'is_admin was not false');
        }

        // 3. Password Hashing
        if ($createdUser && str_starts_with($createdUser->password, '$2y$') && Hash::check($testPassword, $createdUser->password)) {
            $this->log('PASS', '3. Password Hashing', 'Bcrypt hash verified ($2y$); plain text never saved');
        } else {
            $this->log('FAIL', '3. Password Hashing', 'Password hashing verification failed');
        }

        // 4. Email verification not falsely set
        if ($createdUser && $createdUser->email_verified_at === null) {
            $this->log('PASS', '4. Email Verification Integrity', 'email_verified_at is null as required');
        } else {
            $this->log('FAIL', '4. Email Verification Integrity', 'email_verified_at was falsely marked verified');
        }

        // 5. Duplicate email rejection
        $s2 = $this->app['session']->driver();
        $s2->flush(); $s2->regenerate(); $s2->regenerateToken();
        [$res2, $after2] = $this->request('/register', 'POST', [
            '_token' => $s2->token(),
            'name' => 'Duplicate Person',
            'email' => $testEmail,
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);

        if ($res2->getStatusCode() === 302 && $this->hasError($after2, 'email')) {
            $this->log('PASS', '5. Duplicate Email Rejection', 'Rejected: ' . $this->getError($after2, 'email'));
        } else {
            $this->log('FAIL', '5. Duplicate Email Rejection', 'Duplicate email was not rejected');
        }

        // 6. Invalid email rejection
        $s3 = $this->app['session']->driver();
        $s3->flush(); $s3->regenerate(); $s3->regenerateToken();
        [$res3, $after3] = $this->request('/register', 'POST', [
            '_token' => $s3->token(),
            'name' => 'Invalid Email Person',
            'email' => 'notanemail',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        if ($res3->getStatusCode() === 302 && $this->hasError($after3, 'email')) {
            $this->log('PASS', '6. Invalid Email Rejection', 'Rejected: ' . $this->getError($after3, 'email'));
        } else {
            $this->log('FAIL', '6. Invalid Email Rejection', 'Invalid email was not rejected');
        }

        // 7. Password confirmation mismatch
        $s4 = $this->app['session']->driver();
        $s4->flush(); $s4->regenerate(); $s4->regenerateToken();
        [$res4, $after4] = $this->request('/register', 'POST', [
            '_token' => $s4->token(),
            'name' => 'Mismatch Person',
            'email' => 'mismatch_' . time() . '@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword456!',
        ]);

        if ($res4->getStatusCode() === 302 && $this->hasError($after4, 'password')) {
            $this->log('PASS', '7. Password Confirmation Validation', 'Rejected: ' . $this->getError($after4, 'password'));
        } else {
            $this->log('FAIL', '7. Password Confirmation Validation', 'Password mismatch was not rejected');
        }

        // 8. Password minimum length validation
        $s5 = $this->app['session']->driver();
        $s5->flush(); $s5->regenerate(); $s5->regenerateToken();
        [$res5, $after5] = $this->request('/register', 'POST', [
            '_token' => $s5->token(),
            'name' => 'Short Pass Person',
            'email' => 'short_' . time() . '@test.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        if ($res5->getStatusCode() === 302 && $this->hasError($after5, 'password')) {
            $this->log('PASS', '8. Password Length Validation', 'Rejected: ' . $this->getError($after5, 'password'));
        } else {
            $this->log('FAIL', '8. Password Length Validation', 'Short password was not rejected');
        }

        // 9. Successful Login
        $s6 = $this->app['session']->driver();
        $s6->flush(); $s6->regenerate(); $s6->regenerateToken();
        [$res6, $after6] = $this->request('/login', 'POST', [
            '_token' => $s6->token(),
            'email' => $testEmail,
            'password' => $testPassword,
        ]);

        $loggedAuthId = $after6->get(Auth::getName());
        if ($res6->getStatusCode() === 302 && $loggedAuthId === $createdUser->id) {
            $this->log('PASS', '9. User Login', "Authenticated successfully as user ID {$loggedAuthId}");
        } else {
            $this->log('FAIL', '9. User Login', 'Login failed');
        }

        // 10. Incorrect password rejection
        $s7 = $this->app['session']->driver();
        $s7->flush(); $s7->regenerate(); $s7->regenerateToken();
        [$res7, $after7] = $this->request('/login', 'POST', [
            '_token' => $s7->token(),
            'email' => $testEmail,
            'password' => 'WrongPassword999!',
        ]);

        $failedAuthId = $after7->get(Auth::getName());
        if ($res7->getStatusCode() === 302 && $failedAuthId === null && $this->hasError($after7, 'email')) {
            $this->log('PASS', '10. Incorrect Password Rejection', 'Rejected: ' . $this->getError($after7, 'email'));
        } else {
            $this->log('FAIL', '10. Incorrect Password Rejection', 'Incorrect password was not rejected');
        }

        // 11. User Logout
        $s8 = $this->app['session']->driver();
        $s8->flush(); $s8->regenerate(); $s8->regenerateToken();
        [$res8, $after8] = $this->request('/logout', 'POST', [
            '_token' => $s8->token(),
        ], $createdUser);

        $logoutAuthId = $after8->get(Auth::getName());
        if ($res8->getStatusCode() === 302 && $logoutAuthId === null) {
            $this->log('PASS', '11. User Logout', 'Session invalidated and redirected to homepage');
        } else {
            $this->log('FAIL', '11. User Logout', 'Logout failed');
        }

        // 12. Normal user cannot access admin dashboard
        [$res12] = $this->request('/admin/dashboard', 'GET', [], $createdUser);
        if ($res12->getStatusCode() === 302 && str_contains($res12->headers->get('Location'), '/admin/login')) {
            $this->log('PASS', '12. Admin Route Protection', 'Normal user blocked from /admin/dashboard, redirected to /admin/login');
        } else {
            $this->log('FAIL', '12. Admin Route Protection', 'Normal user was not blocked (Status ' . $res12->getStatusCode() . ')');
        }

        // 13. Public Navigation Bar States
        // 13a: Guest navigation
        [$resNavGuest] = $this->request('/', 'GET');
        $guestHtml = $resNavGuest->getContent();
        $hasGuestLinks = str_contains($guestHtml, 'Login') && str_contains($guestHtml, 'Sign Up') && !str_contains($guestHtml, 'Admin Dashboard');
        if ($hasGuestLinks) {
            $this->log('PASS', '13a. Guest Navigation', "Renders 'Login' and 'Sign Up'; hides Admin Dashboard");
        } else {
            $this->log('FAIL', '13a. Guest Navigation', 'Navigation failed guest check');
        }

        // 13b: Normal User navigation
        [$resNavUser] = $this->request('/', 'GET', [], $createdUser);
        $userHtml = $resNavUser->getContent();
        $hasUserLinks = str_contains($userHtml, 'Welcome,') && str_contains($userHtml, 'My Account') && str_contains($userHtml, 'Logout') && !str_contains($userHtml, 'Admin Dashboard');
        if ($hasUserLinks) {
            $this->log('PASS', '13b. Normal User Navigation', "Renders 'Welcome, Sophia Taylor', 'My Account', 'Logout'; hides Admin Dashboard");
        } else {
            $this->log('FAIL', '13b. Normal User Navigation', 'Navigation failed user check');
        }

        // 13c: Admin User navigation
        $adminUser = User::where('is_admin', true)->first();
        [$resNavAdmin] = $this->request('/', 'GET', [], $adminUser);
        $adminHtml = $resNavAdmin->getContent();
        $hasAdminLinks = str_contains($adminHtml, 'Admin Dashboard') && str_contains($adminHtml, 'Logout');
        if ($hasAdminLinks) {
            $this->log('PASS', '13c. Administrator Navigation', "Renders 'Admin Dashboard' and 'Logout'");
        } else {
            $this->log('FAIL', '13c. Administrator Navigation', 'Navigation failed admin check');
        }

        // 14. Event Registration Integration (Autofill & user_id binding)
        $event = Event::where('event_date', '>=', Carbon::today()->toDateString())->first();
        [$resRegForm] = $this->request("/events/{$event->id}/register", 'GET', [], $createdUser);
        $formHtml = $resRegForm->getContent();
        $autofilled = str_contains($formHtml, 'value="' . $createdUser->name . '"') && str_contains($formHtml, 'value="' . $createdUser->email . '"');
        if ($autofilled) {
            $this->log('PASS', '14. Form Pre-Population', "Name '{$createdUser->name}' and email '{$createdUser->email}' automatically populated");
        } else {
            $this->log('FAIL', '14. Form Pre-Population', 'Form inputs were not pre-populated');
        }

        // 15. Store registration with user_id associated
        $sReg = $this->app['session']->driver();
        $sReg->flush(); $sReg->regenerate(); $sReg->regenerateToken();
        [$resStoreReg] = $this->request("/events/{$event->id}/register", 'POST', [
            '_token' => $sReg->token(),
            'user_name' => $createdUser->name,
            'email' => $createdUser->email,
            'event_id' => $event->id,
        ], $createdUser);

        $savedReg = Registration::where('email', $createdUser->email)->where('event_id', $event->id)->first();
        if ($savedReg && $savedReg->user_id === $createdUser->id) {
            $this->log('PASS', '15. User Account Event Registration Binding', "Registration #{$savedReg->id} successfully saved with user_id = {$createdUser->id}");
        } else {
            $this->log('FAIL', '15. User Account Event Registration Binding', 'Registration failed or user_id not saved');
        }

        // 16. Guest Registration Preserved (user_id = null)
        $guestEmail = 'guest_' . time() . '@test.com';
        $sGuest = $this->app['session']->driver();
        $sGuest->flush(); $sGuest->regenerate(); $sGuest->regenerateToken();
        [$resGuestStore] = $this->request("/events/{$event->id}/register", 'POST', [
            '_token' => $sGuest->token(),
            'user_name' => 'Guest Attendee',
            'email' => $guestEmail,
            'event_id' => $event->id,
        ]);

        $guestReg = Registration::where('email', $guestEmail)->first();
        if ($guestReg && $guestReg->user_id === null) {
            $this->log('PASS', '16. Guest Registration Preserved', "Guest registration #{$guestReg->id} saved with user_id = NULL without requiring an account");
        } else {
            $this->log('FAIL', '16. Guest Registration Preserved', 'Guest registration failed');
        }

        // 17. My Account / Profile View
        [$resProfile] = $this->request('/profile', 'GET', [], $createdUser);
        $profileHtml = $resProfile->getContent();
        if ($resProfile->getStatusCode() === 200 &&
            str_contains($profileHtml, $createdUser->name) &&
            str_contains($profileHtml, $event->event_name)) {
            $this->log('PASS', '17. Profile & My Account Page', "HTTP 200, displays user details and registered event '{$event->event_name}'");
        } else {
            $this->log('FAIL', '17. Profile & My Account Page', 'Status ' . $resProfile->getStatusCode());
        }

        // Clean up test registrations
        if ($savedReg) $savedReg->delete();
        if ($guestReg) $guestReg->delete();
        if ($createdUser) $createdUser->delete();

        echo PHP_EOL . "==========================================================" . PHP_EOL;
        echo "VERIFICATION SUMMARY: {$this->passed} PASSED | {$this->failed} FAILED" . PHP_EOL;
        echo "==========================================================" . PHP_EOL;
    }
}

$test = new AuthSystemTest($app, $httpKernel);
$test->run();
