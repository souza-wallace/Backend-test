<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

use App\Models\Redirect;
use App\Models\RedirectLog;
use App\Http\Controllers\RedirectController;

use Tests\TestCase;

use DatabaseMigrations;



class RedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_redirects_from_same_ip_count_as_one_unique_access()
    {
        $redirect = Redirect::factory()->create();

        $ip = '127.0.0.1';
        $logId = RedirectLog::factory()->create([
            'redirect_id' => $redirect->id,
            'ip_address' => $ip,
        ])->id;

        $this->get('/api/redirects/' . $redirect->id . '/stats', ['REMOTE_ADDR' => $ip]);

        $this->assertDatabaseHas('redirect_logs', ['id' => $logId, 'ip_address' => $ip]);
    }

    public function test_referer_headers_are_counted_correctly()
    {
        $redirect = Redirect::factory()->create();

        $referer1 = 'https://www.example.com/page1';
        $referer2 = 'https://www.example.com/page2';

        RedirectLog::factory()->create(['redirect_id' => $redirect->id, 'referer' => $referer1]);
        RedirectLog::factory()->create(['redirect_id' => $redirect->id, 'referer' => $referer1]);
        RedirectLog::factory()->create(['redirect_id' => $redirect->id, 'referer' => $referer2]);

        $response = $this->get('/api/redirects/' . $redirect->id . '/stats');

        $response->assertJson([
            'totalAccesses' => 3,
            'topReferrers' => [$referer1, $referer2],
        ]);
    }

    public function test_generate_redirect_url()
    {
        $urlDestiny = 'https://example.com/redirect?utm_campaign=ads';
        $requestQueryParams = ['utm_source' => 'facebook'];

        $resultUrl = RedirectController::generateRedirectUrl($requestQueryParams, $urlDestiny);

        $expectedUrl = 'https://example.com/redirect?utm_source=facebook&utm_campaign=ads';
        $this->assertEquals($expectedUrl, $resultUrl);
    }

    public function test_generate_redirectUrl_with_empty_keys()
    {
        $urlDestiny = 'https://example.com/redirect?utm_source=facebook';
        $requestQueryParams = ['utm_source' => '', 'utm_campaign' => 'test'];

        $resultUrl = RedirectController::generateRedirectUrl($requestQueryParams, $urlDestiny);

        $expectedUrl = 'https://example.com/redirect?utm_source=facebook&utm_campaign=test';
        $this->assertEquals($expectedUrl, $resultUrl);
    }

    public function test_generate_redirect_url_with_priority()
    {
        $urlDestiny = 'https://example.com/redirect?utm_source=facebook&utm_campaign=ads';
        $requestQueryParams = ['utm_source' => 'instagram'];

        $resultUrl = RedirectController::generateRedirectUrl($requestQueryParams, $urlDestiny);

        $expectedUrl = 'https://example.com/redirect?utm_source=instagram&utm_campaign=ads';
        $this->assertEquals($expectedUrl, $resultUrl);
    }
}
