<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerficationTest extends TestCase
{  use RefreshDatabase;
    /**
     *
     *  resend email verification
     *
     */
    public function test_user_can_verify_his_email()
    {
       

        $data = [
            "email" => "foulen@example.com",
            "name" => "foulen",
            "password" => "password",
            "password_confirmation" => "password"
        ];

        $response = $this->postJson(route("user.register"), $data);

        $response->assertOk();

        $this->assertModelExists($user = User::where("email", $data["email"])->first());

        $this->assertFalse($user->hasVerifiedEmail());

        $url = null;

        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($url);

        $response->assertUnauthorized();

        $this->actingAs($user);

        $response = $this->getJson($url);

        $response->assertOk();


    }
}
