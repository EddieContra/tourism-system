<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    // --- Output escaping -----------------------------------------------------

    public function testEscapesHtmlAndQuotes(): void
    {
        $this->assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt; &amp; &#039;y&#039;', e('<script>alert("x")</script> & \'y\''));
    }

    public function testEscapeHandlesNullAndNumbers(): void
    {
        $this->assertSame('', e(null));
        $this->assertSame('42', e(42));
    }

    // --- Passwords -----------------------------------------------------------

    public function testVerifiesBcryptPasswords(): void
    {
        $hash = password_hash('Secret123', PASSWORD_DEFAULT);

        $this->assertTrue(verify_password('Secret123', $hash, $rehash));
        $this->assertNull($rehash, 'A current bcrypt hash needs no upgrade');
        $this->assertFalse(verify_password('wrong', $hash));
    }

    public function testUpgradesLegacyMd5Hashes(): void
    {
        $legacy = md5('1234');

        $this->assertTrue(verify_password('1234', $legacy, $rehash));
        $this->assertNotNull($rehash);
        $this->assertTrue(password_verify('1234', $rehash), 'Upgraded hash must verify the same password');
        $this->assertFalse(verify_password('12345', $legacy));
    }

    public function testRejectsEmptyStoredHash(): void
    {
        $this->assertFalse(verify_password('anything', null));
        $this->assertFalse(verify_password('anything', ''));
    }

    #[DataProvider('passwordRules')]
    public function testPasswordStrengthRules(string $password, bool $acceptable): void
    {
        $this->assertSame($acceptable, password_problem($password) === null);
    }

    public static function passwordRules(): array
    {
        return [
            'too short'      => ['Ab1', false],
            'letters only'   => ['abcdefgh', false],
            'digits only'    => ['12345678', false],
            'letters+digits' => ['safari2026', true],
            'demo password'  => ['Demo@1234', true],
        ];
    }

    // --- Redirect safety -----------------------------------------------------

    #[DataProvider('redirectTargets')]
    public function testSafeNextOnlyAllowsLocalPaths(?string $next, string $expected): void
    {
        $this->assertSame($expected, safe_next($next));
    }

    public static function redirectTargets(): array
    {
        return [
            'relative page'        => ['tour_history.php', 'tour_history.php'],
            'path with query'      => ['/tms/package_details.php?pkgid=1#book', '/tms/package_details.php?pkgid=1#book'],
            'empty'                => ['', 'index.php'],
            'null'                 => [null, 'index.php'],
            'absolute URL'         => ['https://evil.example', 'index.php'],
            'protocol-relative'    => ['//evil.example', 'index.php'],
            'backslash trick'      => ['/\\evil.example', 'index.php'],
            'javascript scheme'    => ['javascript:alert(1)', 'index.php'],
            'leading whitespace'   => [' //evil.example', 'index.php'],
        ];
    }

    // --- CSRF ----------------------------------------------------------------

    public function testCsrfTokenIsStableAndValidated(): void
    {
        $token = csrf_token();

        $this->assertSame(64, strlen($token));
        $this->assertSame($token, csrf_token(), 'Token is reused within a session');
        $this->assertTrue(csrf_valid($token));
        $this->assertFalse(csrf_valid('forged'));
        $this->assertFalse(csrf_valid(null));
        $this->assertStringContainsString('name="_token"', csrf_field());
    }

    // --- Flash messages ------------------------------------------------------

    public function testFlashMessagesAreReadOnce(): void
    {
        flash('success', 'Saved', ['page.php', 'View']);

        $this->assertSame([['type' => 'success', 'message' => 'Saved', 'link' => ['page.php', 'View']]], take_flashes());
        $this->assertSame([], take_flashes());
    }

    // --- Brute-force protection keys ----------------------------------------

    public function testThrottleKeysAreNormalisedAndBounded(): void
    {
        $this->assertSame('login|demo@example.com', throttle_key('login', '  Demo@Example.COM '));
        $this->assertSame(190, strlen(throttle_key('login', str_repeat('a', 500))));
    }

    // --- Formatting ----------------------------------------------------------

    public function testFormatsPricesAndDates(): void
    {
        $this->assertSame('USD 5,400', format_price(5400));
        $this->assertSame('Dec 8, 2026', format_date('2026-12-08'));
        $this->assertSame('—', format_date(null));
    }

    // --- .env loading --------------------------------------------------------

    public function testEnvLoaderHandlesQuotesAndComments(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, implode("\n", [
            '# full-line comment',
            'FST_TEST_PLAIN=local            # inline comment',
            'FST_TEST_EMPTY=                 # nothing set',
            'FST_TEST_QUOTED="414 Meru Street, Arusha # not a comment"',
            "FST_TEST_SINGLE='single quoted'",
        ]));

        load_env($file);
        unlink($file);

        $this->assertSame('local', env('FST_TEST_PLAIN'));
        $this->assertSame('', env('FST_TEST_EMPTY'));
        $this->assertSame('414 Meru Street, Arusha # not a comment', env('FST_TEST_QUOTED'));
        $this->assertSame('single quoted', env('FST_TEST_SINGLE'));
        $this->assertSame('fallback', env('FST_TEST_MISSING', 'fallback'));
    }

    public function testRealEnvironmentVariablesWinOverDotEnv(): void
    {
        putenv('FST_TEST_OVERRIDE=from-environment');
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "FST_TEST_OVERRIDE=from-file\n");

        load_env($file);
        unlink($file);

        $this->assertSame('from-environment', env('FST_TEST_OVERRIDE'));
    }
}
