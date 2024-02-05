<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;

use App\Models\Redirect;
use App\Http\Controllers\RedirectController;


class RedirectTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_if_the_URL_is_being_created_valid()
    {
        $this->assertEmpty(RedirectController::validateUrl('https://google.com'));
    }

    public function test_creates_redirect_com_url_invalida_por_dns_invalido()
    {
        $this->assertNotEmpty(RedirectController::validateUrl('https://www.google.com'));
    }

    public function test_if_url_is_valid()
    {
        $this->assertEmpty(RedirectController::validateUrl('https://google.com'));
    }

    public function test_if_the_url_applies_to_the_application_itself()
    {
        $this->assertEmpty(RedirectController::validateUrl('https://google.com'));
    }

    public function test_if_is_url_without_https()
    {
        $this->assertNotEmpty(RedirectController::validateUrl('http://google.com'));
    }

    // public function test_if_return_different_of_status_200_or_201()
    // {
    //     $this->assertEmpty(RedirectController::validateUrl('https://creators.llc/dashboard/admin/logs'));
    // }

    public function test_invalid_URL_as_it_has_query_params_with_empty_key()
    {
        $this->assertNotEmpty(RedirectController::validateUrl('https://google.com?teste='));
    }

    
}
