<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for this database test.');
        }

        parent::setUp();

        config()->set('services.chatbot', [
            'provider' => 'gemini',
            'api_key' => 'test-chatbot-key',
            'model' => 'gemini-test-model',
            'base_url' => 'https://chatbot.example.test/v1',
            'timeout' => 30,
        ]);
    }

    public function test_guest_can_start_a_chat_and_the_messages_are_saved(): void
    {
        Http::fake([
            'chatbot.example.test/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello! I can help.',
                        'tool_calls' => null,
                    ],
                ]],
            ]),
        ]);

        $response = $this->postJson('/api/chat', [
            'visitor_id' => 'd9553d05-bd29-4f6f-9dcc-bb1fd7fb9307',
            'message' => 'Hello PetWorld',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.message', 'Hello! I can help.')
            ->assertJsonStructure(['data' => ['conversation_id', 'message']]);

        $conversationId = $response->json('data.conversation_id');

        $this->assertDatabaseHas('chat_conversations', [
            'id' => $conversationId,
            'session_id' => 'd9553d05-bd29-4f6f-9dcc-bb1fd7fb9307',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => 'Hello PetWorld',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => 'Hello! I can help.',
        ]);

        Http::assertSent(function (HttpRequest $request): bool {
            return $request->url() === 'https://chatbot.example.test/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer test-chatbot-key')
                && $request['model'] === 'gemini-test-model'
                && ! array_key_exists('store', $request->data());
        });
    }

    public function test_guest_requires_a_visitor_id(): void
    {
        $response = $this->postJson('/api/chat', [
            'message' => 'Hello PetWorld',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('visitor_id');

        $this->assertSame(0, ChatConversation::query()->count());
        $this->assertSame(0, ChatMessage::query()->count());
    }

    public function test_product_search_uses_catalog_fallback_when_second_provider_request_fails(): void
    {
        Http::fake([
            'chatbot.example.test/*' => Http::sequence()
                ->push([
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [[
                                'id' => 'call_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'search_products',
                                    'arguments' => '{"query":"cat food"}',
                                ],
                            ]],
                        ],
                    ]],
                ])
                ->pushStatus(504),
        ]);

        $response = $this->postJson('/api/chat', [
            'visitor_id' => 'd9553d05-bd29-4f6f-9dcc-bb1fd7fb9307',
            'message' => 'Find cat food',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.suggestions', [])
            ->assertJsonStructure(['data' => ['conversation_id', 'message', 'suggestions']]);

        Http::assertSentCount(2);
        Http::assertSent(function (HttpRequest $request): bool {
            $messages = $request->data()['messages'] ?? [];

            return $request->url() === 'https://chatbot.example.test/v1/chat/completions'
                && data_get($messages, '2.role') === 'assistant'
                && data_get($messages, '3.role') === 'tool'
                && data_get($messages, '3.tool_call_id') === 'call_123';
        });
    }
}
