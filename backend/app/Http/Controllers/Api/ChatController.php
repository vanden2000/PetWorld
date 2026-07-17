<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChatController extends Controller
{
    private const HISTORY_LIMIT = 10;

    public function __construct(private readonly ChatbotProductService $products)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['nullable', 'uuid'],
            'visitor_id' => ['nullable', 'uuid'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user('sanctum');

        if (! $user && empty($data['visitor_id'])) {
            throw ValidationException::withMessages([
                'visitor_id' => ['Khách chưa đăng nhập cần có mã phiên chat hợp lệ.'],
            ]);
        }

        $conversation = $this->resolveConversation($data, $user?->id);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => trim($data['message']),
        ]);

        $openai = config('services.chatbot');

        if (empty($openai['api_key']) || empty($openai['model'])) {
            return response()->json(['message' => 'Chatbot chưa được cấu hình hoàn chỉnh.'], 503);
        }

        $messages = $this->messages($conversation);

        try {
            $response = $this->openAiRequest($openai, $messages);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'PetWorld chưa thể kết nối chatbot. Vui lòng thử lại sau.'], 502);
        }

        if (! $response->successful()) {
            Log::warning('Chatbot provider request failed.', [
                'provider' => $openai['provider'],
                'status' => $response->status(),
            ]);

            if ($response->status() === 429) {
                return response()->json([
                    'message' => 'Chatbot tạm thời bị giới hạn quota. Vui lòng thử lại sau.',
                ], 503);
            }

            return response()->json(['message' => 'PetWorld chưa thể phản hồi ngay lúc này. Vui lòng thử lại sau.'], 502);
        }

        $assistantMessage = $response->json('choices.0.message', []);

        if (! is_array($assistantMessage)) {
            return response()->json(['message' => 'PetWorld chưa nhận được phản hồi phù hợp. Vui lòng thử lại sau.'], 502);
        }

        $toolCalls = is_array($assistantMessage['tool_calls'] ?? null)
            ? $assistantMessage['tool_calls']
            : [];
        $suggestions = [];
        $reply = null;
        $responseMode = $openai['provider'];

        if ($toolCalls !== []) {
            [$toolMessages, $suggestions] = $this->executeToolCalls($toolCalls);

            try {
                $response = $this->openAiRequest($openai, array_merge($messages, [$assistantMessage], $toolMessages));
            } catch (Throwable $exception) {
                report($exception);
                $reply = $this->catalogFallback($suggestions);
                $responseMode = 'catalog_fallback';
            }

            if ($reply === null && ! $response->successful()) {
                Log::warning('Chatbot provider tool-result request failed.', [
                    'provider' => $openai['provider'],
                    'status' => $response->status(),
                ]);
                $reply = $this->catalogFallback($suggestions);
                $responseMode = 'catalog_fallback';
            }
        }

        $reply ??= $this->textFrom($response->json('choices.0.message.content'));

        if ($reply === null && $suggestions !== []) {
            $reply = $this->catalogFallback($suggestions);
            $responseMode = 'catalog_fallback';
        }

        if ($reply === null) {
            return response()->json(['message' => 'PetWorld chưa nhận được phản hồi phù hợp. Vui lòng thử lại sau.'], 502);
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
            'metadata' => [
                'provider' => $openai['provider'],
                'model' => $openai['model'],
                'response_mode' => $responseMode,
                'suggestions' => $suggestions,
            ],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json(['data' => [
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'suggestions' => $suggestions,
        ]]);
    }

    private function resolveConversation(array $data, ?int $userId): ChatConversation
    {
        if (empty($data['conversation_id'])) {
            return ChatConversation::create([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'session_id' => $userId ? null : $data['visitor_id'],
            ]);
        }

        $query = ChatConversation::query()->whereKey($data['conversation_id']);
        $userId ? $query->where('user_id', $userId) : $query->whereNull('user_id')->where('session_id', $data['visitor_id']);
        $conversation = $query->first();

        if (! $conversation) {
            throw (new ModelNotFoundException())->setModel(ChatConversation::class);
        }

        return $conversation;
    }

    private function messages(ChatConversation $conversation): array
    {
        $history = $conversation->messages()->latest('id')->limit(self::HISTORY_LIMIT)->get()->reverse();

        return array_merge([[
            'role' => 'developer',
            'content' => 'Bạn là PetWorld Assistant, trả lời tiếng Việt thân thiện và ngắn gọn. Dùng tool search_products trước khi nêu sản phẩm, giá hoặc tồn kho. Không bịa voucher, chính sách hay đơn hàng. Không chẩn đoán bệnh hoặc kê thuốc cho thú cưng.',
        ]], $history->map(fn (ChatMessage $message) => [
            'role' => $message->role === 'assistant' ? 'assistant' : 'user',
            'content' => $message->content,
        ])->all());
    }

    private function openAiRequest(array $openai, array $messages)
    {
        $payload = [
            'model' => $openai['model'],
            'messages' => $messages,
            'tools' => $this->tools(),
            'tool_choice' => 'auto',
        ];

        if ($openai['provider'] !== 'gemini') {
            $payload['store'] = false;
        }

        return Http::acceptJson()
            ->withToken($openai['api_key'])
            ->timeout((int) $openai['timeout'])
            ->post($openai['base_url'] . '/chat/completions', $payload);
    }

    private function tools(): array
    {
        return [[
            'type' => 'function',
            'function' => [
                'name' => 'search_products',
                'description' => 'Tìm sản phẩm PetWorld đang bán theo từ khóa, khoảng giá và tồn kho.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string'],
                        'min_price' => ['type' => 'number'],
                        'max_price' => ['type' => 'number'],
                        'in_stock' => ['type' => 'boolean'],
                        'limit' => ['type' => 'integer'],
                    ],
                    'required' => ['query'],
                ],
            ],
        ]];
    }

    private function executeToolCalls(array $calls): array
    {
        $toolMessages = [];
        $suggestions = [];

        foreach ($calls as $call) {
            $callId = data_get($call, 'id');
            $name = data_get($call, 'function.name');
            $decodedArguments = json_decode((string) data_get($call, 'function.arguments', '{}'), true);
            $arguments = is_array($decodedArguments) ? $decodedArguments : [];
            $query = trim((string) ($arguments['query'] ?? ''));

            if (! is_string($callId) || $callId === '') {
                continue;
            }

            $products = $name === 'search_products' && $query !== ''
                ? $this->products->search($arguments)
                : [];
            $suggestions = array_merge($suggestions, $products);
            $toolMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $callId,
                'content' => json_encode(['products' => $products], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        return [$toolMessages, collect($suggestions)->unique('id')->values()->all()];
    }

    private function textFrom(mixed $content): ?string
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        return trim($content);
    }

    private function catalogFallback(array $suggestions): string
    {
        if ($suggestions === []) {
            return 'PetWorld chưa tìm thấy sản phẩm phù hợp. Bạn có thể thử đổi từ khóa hoặc nới rộng khoảng giá.';
        }

        return 'PetWorld đã tìm thấy ' . count($suggestions) . ' sản phẩm phù hợp. Bạn có thể xem các gợi ý bên dưới.';
    }
}
