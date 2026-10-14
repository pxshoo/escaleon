<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_assistant_form(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()->assertSee(route('assistant.ask'));
    }

    public function test_guests_cannot_ask(): void
    {
        $this->post(route('assistant.ask'), ['prompt' => 'Hola'])->assertRedirect(route('login'));
    }

    public function test_prompt_is_validated(): void
    {
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => ' '])
            ->assertSessionHasErrors('prompt');
    }

    public function test_missing_key_returns_visible_error_and_preserves_prompt(): void
    {
        config(['services.gemini.api_key' => null]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Planifica mi día'])
            ->assertRedirect(route('dashboard'))->assertSessionHasErrors('assistant')
            ->assertSessionHas('_old_input.prompt', 'Planifica mi día');
        $this->get('/dashboard')->assertOk()->assertSee('Falta GEMINI_API_KEY');
    }

    public function test_assistant_response_is_shown(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Respuesta de prueba']]]]],
        ])]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Hola'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('assistant_reply', 'Respuesta de prueba');
        $this->get('/dashboard')->assertOk()->assertSee('Respuesta de prueba');
        Http::assertSent(fn ($request) => str_contains($request->url(), ':generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['contents'][0]['role'] === 'user'
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Hola')
            && isset($request['systemInstruction']['parts'][0]['text']));
    }

    public function test_provider_failure_returns_error_instead_of_server_error(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::failedConnection()]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Hola'])
            ->assertRedirect(route('dashboard'))->assertSessionHasErrors('assistant');
    }

    public function test_exhausted_quota_shows_error_and_preserves_prompt(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['error' => ['status' => 'RESOURCE_EXHAUSTED']], 429)]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Resume mis tareas'])
            ->assertRedirect(route('dashboard'))->assertSessionHasErrors('assistant')
            ->assertSessionMissing('assistant_reply')->assertSessionHas('_old_input.prompt', 'Resume mis tareas');
        $this->get('/dashboard')->assertOk()->assertSee('Se alcanzó la cuota de Gemini')->assertSee('Resume mis tareas');
    }

    public function test_success_without_text_shows_an_error(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => []])]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Hola'])
            ->assertSessionHasErrors('assistant')->assertSessionMissing('assistant_reply');
    }

    public function test_multiple_text_parts_are_joined_without_thoughts(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [
            ['thought' => true, 'text' => 'Pensamiento interno'],
            ['text' => 'Primera parte. '],
            ['text' => 'Segunda parte.'],
        ]]]]])]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Hola'])
            ->assertSessionHas('assistant_reply', 'Primera parte. Segunda parte.');
    }

    public function test_blocked_prompt_shows_error(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']])]);
        $this->actingAs(User::factory()->create())->post(route('assistant.ask'), ['prompt' => 'Hola'])
            ->assertSessionHasErrors('assistant')->assertSessionMissing('assistant_reply');
    }
}
