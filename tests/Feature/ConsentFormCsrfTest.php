<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use ReflectionProperty;
use Tests\TestCase;

class ConsentFormCsrfTest extends TestCase
{
    public function test_public_consent_upload_is_not_tied_to_the_browser_session(): void
    {
        $except = new ReflectionProperty(VerifyCsrfToken::class, 'except');
        $except->setAccessible(true);

        $this->assertContains('upload', $except->getValue(new VerifyCsrfToken($this->app)));
    }
}