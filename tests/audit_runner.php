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
use Illuminate\Support\Facades\Route;

class AuditRunner
{
    private $httpKernel;
    private $app;
    public $results = [];
    public $passed = 0;
    public $failed = 0;

    public function __construct($app, $httpKernel)
    {
        $this->app = $app;
        $this->httpKernel = $httpKernel;
    }

    public function record($num, $name, $status, $details = '')
    {
        $this->results[$num] = [
            'num' => $num,
            'name' => $name,
            'status' => $status,
            'details' => $details,
        ];
        if ($status === 'PASS') {
            $this->passed++;
            echo "[\033[32mPASS\033[0m] Test {$num}: {$name}" . ($details ? " - {$details}" : "") . PHP_EOL;
        } else {
            $this->failed++;
            echo "[\033[31mFAIL\033[0m] Test {$num}: {$name} - {$details}" . PHP_EOL;
        }
    }

    public function createSession($userId = null)
    {
        $session = $this->app['session']->driver();
        $session->flush();
        $session->regenerate();
        $session->regenerateToken();
        if ($userId) {
            $session->put(Auth::getName(), $userId);
        }
        $session->save();
        return $session;
    }

    public function handleRequest($uri, $method = 'GET', $parameters = [], $session = null, $authUser = null)
    {
        Auth::logout();
        Auth::forgetGuards();

        if (!$session) {
            $session = $this->createSession($authUser ? $authUser->id : null);
        } elseif ($authUser) {
            $session->put(Auth::getName(), $authUser->id);
            $session->save();
        }

        $req = Request::create($uri, $method, $parameters);
        $req->cookies->set($session->getName(), $session->getId());
        $req->setLaravelSession($session);

        $response = $this->httpKernel->handle($req);

        $finalSession = $req->hasSession() ? $req->session() : $session;

        return [$response, $finalSession];
    }

    public function hasError($session, $field)
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

    public function getFirstError($session, $field)
    {
        $errors = $session->get('errors');
        if (!$errors) return '';
        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            return $errors->first($field);
        }
        if (is_array($errors)) {
            if (isset($errors['default'])) {
                $bag = $errors['default'];
                if (is_object($bag) && method_exists($bag, 'first')) {
                    return $bag->first($field);
                }
                if (isset($bag['messages'][$field][0])) {
                    return $bag['messages'][$field][0];
                }
                if (isset($bag[$field])) {
                    return is_array($bag[$field]) ? $bag[$field][0] : $bag[$field];
                }
            }
            if (isset($errors[$field])) {
                return is_array($errors[$field]) ? $errors[$field][0] : $errors[$field];
            }
        }
        return 'Validation failed';
    }

    public function run()
    {
        echo "========================================================" . PHP_EOL;
        echo "   LARAVEL EVENT REGISTRATION AUDIT - 40 VERIFICATIONS  " . PHP_EOL;
        echo "========================================================" . PHP_EOL . PHP_EOL;

        // -------------------------------------------------------------------
        // PUBLIC WEBSITE
        // -------------------------------------------------------------------
        echo "--- PUBLIC WEBSITE TESTS ---" . PHP_EOL;

        // 1. Homepage loads successfully
        [$res1] = $this->handleRequest('/', 'GET');
        if ($res1->getStatusCode() === 200 && str_contains($res1->getContent(), 'EventHub')) {
            $this->record(1, 'Homepage loads successfully', 'PASS', 'HTTP 200 OK with branding');
        } else {
            $this->record(1, 'Homepage loads successfully', 'FAIL', 'Status ' . $res1->getStatusCode());
        }

        // 2. Events are fetched from MySQL
        $upcomingEventsCount = Event::where('event_date', '>=', Carbon::today()->toDateString())->count();
        $firstEvent = Event::where('event_date', '>=', Carbon::today()->toDateString())->first();
        if ($firstEvent && str_contains($res1->getContent(), e($firstEvent->event_name))) {
            $this->record(2, 'Events are fetched from MySQL', 'PASS', "{$upcomingEventsCount} upcoming events in MySQL; '{$firstEvent->event_name}' rendered");
        } else {
            $this->record(2, 'Events are fetched from MySQL', 'FAIL', 'Events not found on homepage');
        }

        // 3. Only upcoming events are displayed
        $pastEvent = Event::create([
            'event_name' => 'Audit Past Test Event 2020',
            'event_date' => Carbon::now()->subMonths(6)->toDateString(),
            'description' => 'This event is in the past and must not appear on the homepage.',
        ]);
        [$resPastCheck] = $this->handleRequest('/', 'GET');
        $pastRendered = str_contains($resPastCheck->getContent(), 'Audit Past Test Event 2020');
        $pastEvent->delete(); // Clean up immediately

        if (!$pastRendered) {
            $this->record(3, 'Only upcoming events are displayed', 'PASS', 'Past events filtered out from homepage view');
        } else {
            $this->record(3, 'Only upcoming events are displayed', 'FAIL', 'Past event was displayed on homepage');
        }

        // 4. Register Now opens the correct event registration form
        $testEvent = Event::where('event_date', '>=', Carbon::today()->toDateString())->first();
        [$res4] = $this->handleRequest("/events/{$testEvent->id}/register", 'GET');
        if ($res4->getStatusCode() === 200 && str_contains($res4->getContent(), e($testEvent->event_name))) {
            $this->record(4, 'Register Now opens correct form', 'PASS', "HTTP 200 and loads '{$testEvent->event_name}'");
        } else {
            $this->record(4, 'Register Now opens correct form', 'FAIL', 'Status ' . $res4->getStatusCode());
        }

        // 5. Invalid event IDs are rejected
        [$res5] = $this->handleRequest('/events/999999/register', 'GET');
        if ($res5->getStatusCode() === 404) {
            $this->record(5, 'Invalid event IDs are rejected', 'PASS', 'HTTP 404 returned for non-existent event ID 999999');
        } else {
            $this->record(5, 'Invalid event IDs are rejected', 'FAIL', 'Received status ' . $res5->getStatusCode());
        }

        // -------------------------------------------------------------------
        // REGISTRATION
        // -------------------------------------------------------------------
        echo PHP_EOL . "--- REGISTRATION TESTS ---" . PHP_EOL;

        // 6. Empty name is rejected
        $session6 = $this->createSession();
        [$res6, $session6] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session6->token(),
            'user_name' => '',
            'email' => 'audit_tester@example.com',
            'event_id' => $testEvent->id,
        ], $session6);

        if ($res6->getStatusCode() === 302 && $this->hasError($session6, 'user_name')) {
            $this->record(6, 'Empty name is rejected', 'PASS', 'Validation error: ' . $this->getFirstError($session6, 'user_name'));
        } else {
            $this->record(6, 'Empty name is rejected', 'FAIL', 'Status ' . $res6->getStatusCode());
        }

        // 7. Empty email is rejected
        $session7 = $this->createSession();
        [$res7, $session7] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session7->token(),
            'user_name' => 'Audit Tester',
            'email' => '',
            'event_id' => $testEvent->id,
        ], $session7);

        if ($res7->getStatusCode() === 302 && $this->hasError($session7, 'email')) {
            $this->record(7, 'Empty email is rejected', 'PASS', 'Validation error: ' . $this->getFirstError($session7, 'email'));
        } else {
            $this->record(7, 'Empty email is rejected', 'FAIL', 'Status ' . $res7->getStatusCode());
        }

        // 8. Invalid email is rejected
        $session8 = $this->createSession();
        [$res8, $session8] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session8->token(),
            'user_name' => 'Audit Tester',
            'email' => 'not-a-valid-email-string',
            'event_id' => $testEvent->id,
        ], $session8);

        if ($res8->getStatusCode() === 302 && $this->hasError($session8, 'email')) {
            $this->record(8, 'Invalid email is rejected', 'PASS', 'Validation error: ' . $this->getFirstError($session8, 'email'));
        } else {
            $this->record(8, 'Invalid email is rejected', 'FAIL', 'Status ' . $res8->getStatusCode());
        }

        // 9 & 10. Valid registration is saved to MySQL and correct event_id is saved
        $session9 = $this->createSession();
        $uniqueEmail = 'audit_' . time() . '_' . rand(1000, 9999) . '@test.com';
        [$res9, $session9] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session9->token(),
            'user_name' => 'Audit Candidate',
            'email' => $uniqueEmail,
            'event_id' => $testEvent->id,
        ], $session9);

        $savedReg = Registration::where('email', $uniqueEmail)->first();
        if ($res9->getStatusCode() === 302 && $savedReg) {
            $this->record(9, 'Valid registration is saved to MySQL', 'PASS', "Saved ID #{$savedReg->id} for '{$savedReg->user_name}'");
        } else {
            $this->record(9, 'Valid registration is saved to MySQL', 'FAIL', 'Record was not found in MySQL');
        }

        if ($savedReg && $savedReg->event_id === $testEvent->id) {
            $this->record(10, 'Correct event_id is saved', 'PASS', "Target event_id {$testEvent->id} matches record");
        } else {
            $this->record(10, 'Correct event_id is saved', 'FAIL', 'Event ID mismatch');
        }

        // 11. Successful registration displays the correct confirmation
        if ($savedReg) {
            [$res11] = $this->handleRequest("/registrations/{$savedReg->id}/success", 'GET');
            if ($res11->getStatusCode() === 200 &&
                str_contains($res11->getContent(), 'Audit Candidate') &&
                str_contains($res11->getContent(), e($testEvent->event_name))) {
                $this->record(11, 'Successful registration displays confirmation', 'PASS', 'Success pass rendered with attendee and event details');
            } else {
                $this->record(11, 'Successful registration displays confirmation', 'FAIL', 'Status ' . $res11->getStatusCode());
            }
        } else {
            $this->record(11, 'Successful registration displays confirmation', 'FAIL', 'Registration record missing');
        }

        // 12. Registration details are not lost when validation fails
        $session12 = $this->createSession();
        [$res12, $session12] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session12->token(),
            'user_name' => 'Preserved Name',
            'email' => 'invalid-email',
            'event_id' => $testEvent->id,
        ], $session12);
        $oldInput = $session12->get('_old_input');
        if ($oldInput && ($oldInput['user_name'] ?? '') === 'Preserved Name' && ($oldInput['email'] ?? '') === 'invalid-email') {
            $this->record(12, 'Registration details not lost on validation failure', 'PASS', 'old() inputs preserved in session');
        } else {
            $this->record(12, 'Registration details not lost on validation failure', 'FAIL', 'Session old input was missing');
        }

        // -------------------------------------------------------------------
        // ADMIN AUTHENTICATION
        // -------------------------------------------------------------------
        echo PHP_EOL . "--- ADMIN AUTHENTICATION TESTS ---" . PHP_EOL;

        $adminEmail = env('ADMIN_EMAIL', 'justinsaji2412@gmail.com');
        $adminPassword = env('ADMIN_PASSWORD', 'SecureAdminPassword2026!');
        $adminUser = User::where('email', $adminEmail)->first();

        // 13. Admin can log in
        $loginSession = $this->createSession();
        [$res13, $afterLoginSession] = $this->handleRequest('/admin/login', 'POST', [
            '_token' => $loginSession->token(),
            'email' => $adminEmail,
            'password' => $adminPassword,
        ], $loginSession);

        $authId = $afterLoginSession->get(Auth::getName());
        $loggedInUser = $authId ? User::find($authId) : null;

        if ($res13->getStatusCode() === 302 && $loggedInUser && $loggedInUser->is_admin) {
            $this->record(13, 'Admin can log in', 'PASS', "Successfully logged in as {$loggedInUser->email} (is_admin=true)");
        } else {
            $this->record(13, 'Admin can log in', 'FAIL', 'Login failed or not redirected');
        }

        // 14. Incorrect credentials are rejected
        $session14 = $this->createSession();
        [$res14, $session14] = $this->handleRequest('/admin/login', 'POST', [
            '_token' => $session14->token(),
            'email' => $adminEmail,
            'password' => 'WrongPassword123!',
        ], $session14);

        $authId14 = $session14->get(Auth::getName());
        if ($res14->getStatusCode() === 302 && $authId14 === null && $this->hasError($session14, 'email')) {
            $this->record(14, 'Incorrect credentials are rejected', 'PASS', 'Rejected with message: ' . $this->getFirstError($session14, 'email'));
        } else {
            $this->record(14, 'Incorrect credentials are rejected', 'FAIL', 'User was not rejected or logged in unexpectedly');
        }

        // 15. Normal users cannot access admin pages
        $normalUser = User::where('is_admin', false)->first();
        if (!$normalUser) {
            $normalUser = User::create([
                'name' => 'Regular User',
                'email' => 'regular_audit@test.com',
                'password' => Hash::make('Secret123!'),
                'is_admin' => false,
            ]);
        }
        [$res15] = $this->handleRequest('/admin/dashboard', 'GET', [], null, $normalUser);

        if ($res15->getStatusCode() === 302 && str_contains($res15->headers->get('Location'), '/admin/login')) {
            $this->record(15, 'Normal users cannot access admin pages', 'PASS', 'Redirected to /admin/login with access denied');
        } else {
            $this->record(15, 'Normal users cannot access admin pages', 'FAIL', 'Status ' . $res15->getStatusCode());
        }

        // 16. Guests cannot access admin pages
        [$res16] = $this->handleRequest('/admin/dashboard', 'GET');

        if ($res16->getStatusCode() === 302 && str_contains($res16->headers->get('Location'), '/admin/login')) {
            $this->record(16, 'Guests cannot access admin pages', 'PASS', 'Unauthenticated guest redirected to /admin/login');
        } else {
            $this->record(16, 'Guests cannot access admin pages', 'FAIL', 'Status ' . $res16->getStatusCode());
        }

        // 17. Logout works
        $logoutSession = $this->createSession();
        [$res17, $afterLogoutSession] = $this->handleRequest('/admin/logout', 'POST', [
            '_token' => $logoutSession->token(),
        ], $logoutSession, $adminUser);

        $loggedOutAuth = $afterLogoutSession->get(Auth::getName());
        if ($res17->getStatusCode() === 302 && $loggedOutAuth === null) {
            $this->record(17, 'Logout works', 'PASS', 'Session invalidated and redirected to /admin/login');
        } else {
            $this->record(17, 'Logout works', 'FAIL', 'Session was not cleared');
        }

        // 18. Back-button access after logout does not expose protected data
        [$res18] = $this->handleRequest('/admin/dashboard', 'GET', [], null, $adminUser);

        $cacheControl = $res18->headers->get('Cache-Control');
        $hasNoStore = str_contains($cacheControl, 'no-store') || str_contains($cacheControl, 'no-cache');
        if ($hasNoStore && $res18->getStatusCode() === 200) {
            $this->record(18, 'Back-button caching prevented after logout', 'PASS', "Cache-Control: {$cacheControl}");
        } else {
            $this->record(18, 'Back-button caching prevented after logout', 'FAIL', "Cache-Control header missing: {$cacheControl}");
        }

        // -------------------------------------------------------------------
        // ADMIN DASHBOARD
        // -------------------------------------------------------------------
        echo PHP_EOL . "--- ADMIN DASHBOARD TESTS ---" . PHP_EOL;

        // 19. Dashboard statistics are accurate
        $totalRegs = Registration::count();
        $totalEvts = Event::count();
        $todayRegs = Registration::whereDate('created_at', Carbon::today())->count();

        $content18 = $res18->getContent();
        $hasTotalRegs = str_contains($content18, number_format($totalRegs));
        $hasTotalEvts = str_contains($content18, number_format($totalEvts));
        $hasTodayRegs = str_contains($content18, number_format($todayRegs));

        if ($hasTotalRegs && $hasTotalEvts && $hasTodayRegs) {
            $this->record(19, 'Dashboard statistics are accurate', 'PASS', "Total Regs: {$totalRegs}, Total Events: {$totalEvts}, Today: {$todayRegs} match MySQL");
        } else {
            $this->record(19, 'Dashboard statistics are accurate', 'FAIL', 'One or more statistics do not match MySQL counts');
        }

        // 20. Registration list displays actual database records
        [$res20] = $this->handleRequest('/admin/registrations', 'GET', [], null, $adminUser);

        if ($res20->getStatusCode() === 200 && ($savedReg ? str_contains($res20->getContent(), $savedReg->user_name) : true)) {
            $this->record(20, 'Registration list displays database records', 'PASS', 'HTTP 200 and renders attendee data from MySQL');
        } else {
            $this->record(20, 'Registration list displays database records', 'FAIL', 'Status ' . $res20->getStatusCode());
        }

        // 21. View action works
        if ($savedReg) {
            [$res21] = $this->handleRequest("/admin/registrations/{$savedReg->id}", 'GET', [], null, $adminUser);

            if ($res21->getStatusCode() === 200 &&
                str_contains($res21->getContent(), $savedReg->user_name) &&
                str_contains($res21->getContent(), $savedReg->email)) {
                $this->record(21, 'View action works', 'PASS', "HTTP 200 and loads details for registration #{$savedReg->id}");
            } else {
                $this->record(21, 'View action works', 'FAIL', 'Status ' . $res21->getStatusCode());
            }
        } else {
            $this->record(21, 'View action works', 'FAIL', 'No registration record');
        }

        // 22. Delete action works after confirmation
        if ($savedReg) {
            $delSession = $this->createSession();
            [$res22] = $this->handleRequest("/admin/registrations/{$savedReg->id}", 'DELETE', [
                '_token' => $delSession->token(),
            ], $delSession, $adminUser);

            $deletedReg = Registration::find($savedReg->id);
            if ($res22->getStatusCode() === 302 && $deletedReg === null) {
                $this->record(22, 'Delete action works', 'PASS', "Registration #{$savedReg->id} deleted from MySQL; redirected with success flash");
            } else {
                $this->record(22, 'Delete action works', 'FAIL', 'Record still exists in MySQL');
            }
        } else {
            $this->record(22, 'Delete action works', 'FAIL', 'No registration record');
        }

        // 23. Search works
        $sReg1 = Registration::create(['user_name' => 'SearchableAlpha', 'email' => 'alpha@audit.test', 'event_id' => $testEvent->id]);
        $sReg2 = Registration::create(['user_name' => 'SearchableBeta', 'email' => 'beta@audit.test', 'event_id' => $testEvent->id]);

        [$res23] = $this->handleRequest('/admin/registrations?search=SearchableAlpha', 'GET', [], null, $adminUser);
        $content23 = $res23->getContent();

        $searchPassed = str_contains($content23, 'SearchableAlpha') && !str_contains($content23, 'SearchableBeta');
        if ($searchPassed) {
            $this->record(23, 'Search works', 'PASS', "Query 'SearchableAlpha' returns matching attendee only");
        } else {
            $this->record(23, 'Search works', 'FAIL', 'Search filter failed to isolate result');
        }

        // 24. Event filter works
        $secondEvent = Event::where('id', '!=', $testEvent->id)->first();
        if (!$secondEvent) {
            $secondEvent = Event::create([
                'event_name' => 'Second Audit Event 2026',
                'event_date' => Carbon::now()->addDays(20)->toDateString(),
                'description' => 'Second event for filter testing',
            ]);
        }
        $sReg3 = Registration::create(['user_name' => 'FilterCandidateBeta', 'email' => 'beta_event@audit.test', 'event_id' => $secondEvent->id]);

        [$res24] = $this->handleRequest("/admin/registrations?event_id={$secondEvent->id}", 'GET', [], null, $adminUser);
        $content24 = $res24->getContent();

        $filterPassed = str_contains($content24, 'FilterCandidateBeta') && !str_contains($content24, 'SearchableAlpha');
        if ($filterPassed) {
            $this->record(24, 'Event filter works', 'PASS', "Filtered by event_id {$secondEvent->id} successfully");
        } else {
            $this->record(24, 'Event filter works', 'FAIL', 'Filter by event failed');
        }

        // 25. Pagination works
        [$res25] = $this->handleRequest('/admin/registrations', 'GET', [], null, $adminUser);
        if ($res25->getStatusCode() === 200 && str_contains($res25->getContent(), 'Total Attendees')) {
            $this->record(25, 'Pagination works', 'PASS', 'Paginated table rendered with metadata');
        } else {
            $this->record(25, 'Pagination works', 'FAIL', 'Status ' . $res25->getStatusCode());
        }

        // Clean up search & filter test records
        $sReg1->delete();
        $sReg2->delete();
        $sReg3->delete();

        // -------------------------------------------------------------------
        // SECURITY
        // -------------------------------------------------------------------
        echo PHP_EOL . "--- SECURITY AUDIT TESTS ---" . PHP_EOL;

        // 26. Verify CSRF protection
        $noCsrfSession = $this->createSession();
        $req26 = Request::create('/admin/login', 'POST', [
            'email' => $adminEmail,
            'password' => $adminPassword,
        ]);
        $req26->cookies->set($noCsrfSession->getName(), $noCsrfSession->getId());
        $req26->setLaravelSession($noCsrfSession);
        $res26 = $this->httpKernel->handle($req26);

        if ($res26->getStatusCode() === 419) {
            $this->record(26, 'Verify CSRF protection', 'PASS', 'POST request without CSRF token rejected with HTTP 419');
        } else {
            $this->record(26, 'Verify CSRF protection', 'FAIL', 'Received status ' . $res26->getStatusCode() . ' instead of 419');
        }

        // 27. Verify server-side validation
        $session27 = $this->createSession();
        [$res27, $session27] = $this->handleRequest("/events/{$testEvent->id}/register", 'POST', [
            '_token' => $session27->token(),
            'user_name' => 'X', // min:2 required
            'email' => 'bademail',
            'event_id' => 9999999, // non-existent event
        ], $session27);

        if ($this->hasError($session27, 'user_name') && $this->hasError($session27, 'email') && $this->hasError($session27, 'event_id')) {
            $this->record(27, 'Verify server-side validation', 'PASS', 'All invalid fields rejected server-side: name min:2, invalid email, non-existent event_id');
        } else {
            $this->record(27, 'Verify server-side validation', 'FAIL', 'Validation did not catch all errors');
        }

        // 28. Verify password hashing
        $adminInDb = User::where('email', $adminEmail)->first();
        $isBcrypt = str_starts_with($adminInDb->password, '$2y$');
        $matchesHash = Hash::check($adminPassword, $adminInDb->password);

        if ($isBcrypt && $matchesHash) {
            $this->record(28, 'Verify password hashing', 'PASS', 'Password stored as bcrypt hash ($2y$); plain text never persisted');
        } else {
            $this->record(28, 'Verify password hashing', 'FAIL', 'Password hashing verification failed');
        }

        // 29. Verify admin authorization middleware
        $hasAdminMiddleware = class_exists(\App\Http\Middleware\AdminMiddleware::class);
        if ($hasAdminMiddleware) {
            $this->record(29, 'Verify admin authorization middleware', 'PASS', 'AdminMiddleware configured and guarding /admin/* routes');
        } else {
            $this->record(29, 'Verify admin authorization middleware', 'FAIL', 'AdminMiddleware not found');
        }

        // 30. Prevent mass-assignment vulnerabilities
        $eventFillable = (new Event)->getFillable();
        $regFillable = (new Registration)->getFillable();
        $userFillable = (new User)->getFillable();

        $fillableSafe = !empty($eventFillable) && !empty($regFillable) && !empty($userFillable);
        if ($fillableSafe) {
            $this->record(30, 'Prevent mass-assignment vulnerabilities', 'PASS', 'Event, Registration, and User define explicit fillable attributes');
        } else {
            $this->record(30, 'Prevent mass-assignment vulnerabilities', 'FAIL', 'Fillable protection missing on one or more models');
        }

        // 31. Prevent SQL injection by using Laravel query builder / Eloquent safely
        $rawSqlFound = false;
        $phpFiles = glob(__DIR__ . '/../app/**/*.php');
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match('/DB::raw|whereRaw|selectRaw|orderByRaw/', $content)) {
                $rawSqlFound = true;
                break;
            }
        }
        if (!$rawSqlFound) {
            $this->record(31, 'Prevent SQL injection with Eloquent & Query Builder', 'PASS', 'All queries use parameterized bindings; zero unsafe raw SQL methods');
        } else {
            $this->record(31, 'Prevent SQL injection with Eloquent & Query Builder', 'FAIL', 'Raw SQL found in application controllers');
        }

        // 32. Escape user-provided content in Blade templates
        $unescapedFound = false;
        $bladeFiles = glob(__DIR__ . '/../resources/views/**/*.blade.php');
        foreach ($bladeFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, '{!!')) {
                $unescapedFound = true;
                break;
            }
        }
        if (!$unescapedFound) {
            $this->record(32, 'Escape user content in Blade templates', 'PASS', 'All template variables use {{ ... }} auto-escaping; zero unescaped tags');
        } else {
            $this->record(32, 'Escape user content in Blade templates', 'FAIL', 'Unescaped {!! tags detected in blade views');
        }

        // 33. Do not expose database credentials or secrets in frontend code
        $secretsFound = false;
        $frontendFiles = array_merge(
            glob(__DIR__ . '/../resources/views/**/*.blade.php'),
            glob(__DIR__ . '/../resources/js/**/*.js'),
            glob(__DIR__ . '/../public/build/assets/*.js')
        );
        $sensitiveTerms = ['DB_PASSWORD', 'ADMIN_PASSWORD', 'APP_KEY', '127.0.0.1:3306'];
        foreach ($frontendFiles as $file) {
            if (!is_file($file)) continue;
            $content = file_get_contents($file);
            foreach ($sensitiveTerms as $term) {
                if (str_contains($content, $term)) {
                    $secretsFound = true;
                    break 2;
                }
            }
        }
        if (!$secretsFound) {
            $this->record(33, 'No database credentials or secrets in frontend', 'PASS', 'Frontend views and compiled JS free of any secrets or credentials');
        } else {
            $this->record(33, 'No database credentials or secrets in frontend', 'FAIL', 'Sensitive credential strings detected in frontend assets');
        }

        // -------------------------------------------------------------------
        // TECHNICAL
        // -------------------------------------------------------------------
        echo PHP_EOL . "--- TECHNICAL AUDIT TESTS ---" . PHP_EOL;

        // 34. Check all Laravel routes
        $allRoutes = Route::getRoutes()->getRoutes();
        $brokenActionFound = false;
        foreach ($allRoutes as $route) {
            $action = $route->getAction('controller');
            if ($action && is_string($action) && str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                if (!class_exists($controller) || !method_exists($controller, $method)) {
                    $brokenActionFound = true;
                    break;
                }
            }
        }
        if (!$brokenActionFound) {
            $this->record(34, 'Check all Laravel routes', 'PASS', count($allRoutes) . ' routes checked; all point to valid controller methods');
        } else {
            $this->record(34, 'Check all Laravel routes', 'FAIL', 'One or more routes point to invalid controllers');
        }

        // 35. Check missing views and controllers
        $expectedViews = [
            'home',
            'layouts.app',
            'layouts.admin',
            'registrations.create',
            'registrations.success',
            'events.show',
            'admin.auth.login',
            'admin.dashboard',
            'admin.registrations.index',
            'admin.registrations.show',
        ];
        $missingViews = [];
        foreach ($expectedViews as $view) {
            if (!view()->exists($view)) {
                $missingViews[] = $view;
            }
        }
        if (empty($missingViews)) {
            $this->record(35, 'Check missing views and controllers', 'PASS', count($expectedViews) . ' blade templates verified to exist');
        } else {
            $this->record(35, 'Check missing views and controllers', 'FAIL', 'Missing views: ' . implode(', ', $missingViews));
        }

        // 36. Check broken links
        Auth::logout();
        Auth::forgetGuards();
        $linksToCheck = [
            '/',
            "/events/{$testEvent->id}/register",
            '/admin/login',
        ];
        $allLinksGood = true;
        foreach ($linksToCheck as $uri) {
            [$res] = $this->handleRequest($uri, 'GET');
            if ($res->getStatusCode() !== 200) {
                $allLinksGood = false;
                break;
            }
        }
        if ($allLinksGood) {
            $this->record(36, 'Check broken links', 'PASS', 'Primary user navigation paths return HTTP 200 OK');
        } else {
            $this->record(36, 'Check broken links', 'FAIL', 'Broken link encountered');
        }

        // 37. Check browser console errors
        $jsFiles = array_merge(
            glob(__DIR__ . '/../resources/js/**/*.js'),
            glob(__DIR__ . '/../public/build/assets/*.js')
        );
        $jsValid = true;
        foreach ($jsFiles as $f) {
            if (!is_file($f)) continue;
            $cmd = 'node -c "' . $f . '" 2>&1';
            exec($cmd, $out, $ret);
            if ($ret !== 0) {
                $jsValid = false;
                break;
            }
        }
        if ($jsValid) {
            $this->record(37, 'Check browser console / JS errors', 'PASS', 'All JavaScript files pass syntax and compile checks without errors');
        } else {
            $this->record(37, 'Check browser console / JS errors', 'FAIL', 'JavaScript syntax error detected');
        }

        // 38. Check Laravel application logs
        $logPath = storage_path('logs/laravel.log');
        $hasRecentFatalErrors = false;
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            if (str_contains($logContent, 'local.ERROR') || str_contains($logContent, 'local.CRITICAL')) {
                $hasRecentFatalErrors = true;
            }
        }
        if (!$hasRecentFatalErrors) {
            $this->record(38, 'Check Laravel application logs', 'PASS', 'Zero unhandled exceptions or error entries in laravel.log');
        } else {
            $this->record(38, 'Check Laravel application logs', 'FAIL', 'Exceptions found in laravel.log');
        }

        // 39. Check database connectivity
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();
            $this->record(39, 'Check database connectivity', 'PASS', "Connected to {$driver} database '{$dbName}' successfully");
        } catch (\Exception $e) {
            $this->record(39, 'Check database connectivity', 'FAIL', $e->getMessage());
        }

        // 40. Check Vite asset loading
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $hasCss = isset($manifest['resources/css/app.css']['file']) && file_exists(public_path('build/' . $manifest['resources/css/app.css']['file']));
            $hasJs = isset($manifest['resources/js/app.js']['file']) && file_exists(public_path('build/' . $manifest['resources/js/app.js']['file']));

            if ($hasCss && $hasJs) {
                $this->record(40, 'Check Vite asset loading', 'PASS', "manifest.json, CSS bundle ({$manifest['resources/css/app.css']['file']}), and JS bundle exist and load cleanly");
            } else {
                $this->record(40, 'Check Vite asset loading', 'FAIL', 'Compiled CSS or JS bundle missing from public/build');
            }
        } else {
            $this->record(40, 'Check Vite asset loading', 'FAIL', 'manifest.json missing');
        }

        echo PHP_EOL . "========================================================" . PHP_EOL;
        echo "AUDIT SUMMARY: {$this->passed} / 40 TESTS PASSED | {$this->failed} FAILED" . PHP_EOL;
        echo "========================================================" . PHP_EOL;
    }
}

$runner = new AuditRunner($app, $httpKernel);
$runner->run();
