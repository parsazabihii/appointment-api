<?php

namespace Tests\Feature;

use Tests\TestCase;

class DemoPageTest extends TestCase
{
    public function test_interactive_booking_demo_is_available(): void
    {
        $this->app['config']->set('session.driver', 'array');

        $this->get('/demo')
            ->assertOk()
            ->assertSee('Appointment Booking Demo')
            ->assertSee('Find available times')
            ->assertSee('/api', false);
    }
}
