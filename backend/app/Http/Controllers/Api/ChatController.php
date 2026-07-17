<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotProductService;
use App\Services\ChatbotKnowledgeService;
use App\Services\ChatbotOrderService;
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

    public function __construct(
        private readonly ChatbotProductService $products,
        private readonly ChatbotKnowledgeService $knowledge,
        private readonly ChatbotOrderService $orders,
    )
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
        $this->updateConversationContext($conversation, trim($data['message']));

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
        $sources = [];
        $orderSummaries = [];
        $reply = null;
        $responseMode = $openai['provider'];

        if ($toolCalls !== []) {
            [$toolMessages, $suggestions, $sources, $orderSummaries] = $this->executeToolCalls($conversation, $user?->id, $toolCalls);

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
                'sources' => $sources,
                'orders' => $orderSummaries,
            ],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json(['data' => [
            'conversation_id' => $conversation->id,
            'message' => $reply,
            'suggestions' => $suggestions,
            'sources' => $sources,
            'orders' => $orderSummaries,
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
            'content' => <<<'PROMPT'
Bạn là PetWorld Assistant. Trả lời bằng tiếng Việt tự nhiên, thân thiện và ngắn gọn.

QUY TRÌNH TƯ VẤN SẢN PHẨM
- Khi khách hỏi, nhắc đến, hoặc cần tư vấn sản phẩm, thức ăn, phụ kiện, đồ chơi, giá hay tồn kho: bắt buộc gọi search_products trước khi trả lời.
- Chuyển yêu cầu sang từ khóa ngắn, dễ tìm trong catalog; giữ lại ngân sách trong min_price/max_price và đặt in_stock=true nếu khách không yêu cầu hàng hết.
- Nếu thiếu thông tin quan trọng (loài thú cưng, độ tuổi/kích cỡ, ngân sách), chỉ hỏi một câu làm rõ. Không hỏi lại thông tin khách đã nêu.
- Khi đã có kết quả tool, chỉ gợi ý tối đa 3 sản phẩm trong phần chữ. Nêu ngắn gọn vì sao phù hợp; không lặp lại chi tiết giá hoặc tồn kho vì giao diện hiển thị thẻ sản phẩm.
- Khi khách yêu cầu so sánh sản phẩm, bắt buộc gọi compare_products; chỉ so sánh dữ liệu tool trả về và nêu điểm khác biệt thực tế.
- Nếu tool không có kết quả, nói rõ PetWorld chưa tìm được sản phẩm phù hợp và đề nghị khách đổi từ khóa hoặc nới ngân sách. Không tự bịa sản phẩm, giá, tồn kho hoặc khuyến mãi.

GIỚI HẠN
- Với đơn hàng, gọi get_my_orders trước khi trả lời. Nếu tool nói cần đăng nhập, hướng dẫn khách đăng nhập; không suy đoán trạng thái đơn.
- Với giao hàng, thanh toán, đổi trả, voucher hoặc liên hệ: gọi search_knowledge_articles trước khi trả lời. Chỉ dùng nội dung tool trả về; nếu không có bài phù hợp thì nói chưa có thông tin đã xác nhận.
- Không chẩn đoán bệnh, kê thuốc hoặc chỉ định liều dùng. Với dấu hiệu sức khỏe nghiêm trọng, khuyên khách đưa thú cưng đến bác sĩ thú y.
PROMPT,
        ], [
            'role' => 'developer',
            'content' => 'Ngữ cảnh tư vấn đã biết của cuộc trò chuyện (chỉ dùng nếu có giá trị): '
                . json_encode($conversation->context ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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

        // A follow-up already includes a real tool result. Forcing a tool a
        // second time would create an endless tool-call loop instead of a reply.
        $hasToolResult = collect($messages)->contains(fn (array $message) => ($message['role'] ?? null) === 'tool');

        if (! $hasToolResult && $this->shouldForceCatalog($messages)) {
            $payload['tool_choice'] = [
                'type' => 'function',
                'function' => ['name' => 'search_products'],
            ];
        }
        if (! $hasToolResult && $this->isKnowledgeQuestion($messages)) {
            $payload['tool_choice'] = ['type' => 'function', 'function' => ['name' => 'search_knowledge_articles']];
        }
        if (! $hasToolResult && $this->isComparisonQuestion($messages)) {
            $payload['tool_choice'] = ['type' => 'function', 'function' => ['name' => 'compare_products']];
        }
        if (! $hasToolResult && $this->isOrderQuestion($messages)) {
            $payload['tool_choice'] = ['type' => 'function', 'function' => ['name' => 'get_my_orders']];
        }

        if ($openai['provider'] !== 'gemini') {
            $payload['store'] = false;
        }

        return Http::acceptJson()
            ->withToken($openai['api_key'])
            ->timeout((int) $openai['timeout'])
            ->post($openai['base_url'] . '/chat/completions', $payload);
    }

    /** Route obvious catalog questions so the model cannot skip real data. */
    private function shouldForceCatalog(array $messages): bool
    {
        $latestUserMessage = collect($messages)
            ->reverse()
            ->first(fn (array $message) => ($message['role'] ?? null) === 'user');
        $content = mb_strtolower((string) ($latestUserMessage['content'] ?? ''));

        // Broad questions such as “tư vấn thức ăn” should be allowed to ask
        // one clarifying question. Once pet type, price, or stock is known,
        // catalog lookup becomes mandatory.
        return preg_match('/\b(mèo|chó|cún|cat|dog|\d+\s*(tháng|tuổi|kg)|dưới\s*\d+|trên\s*\d+|còn hàng|tồn kho)\b/u', $content) === 1;
    }

    private function isKnowledgeQuestion(array $messages): bool
    {
        $latestUserMessage = collect($messages)
            ->reverse()
            ->first(fn (array $message) => ($message['role'] ?? null) === 'user');
        $content = mb_strtolower((string) ($latestUserMessage['content'] ?? ''));
        return preg_match('/\b(giao hàng|vận chuyển|thanh toán|đổi trả|hoàn trả|voucher|mã giảm giá|liên hệ)\b/u', $content) === 1;
    }

    private function isComparisonQuestion(array $messages): bool
    {
        $latestUserMessage = collect($messages)->reverse()->first(fn (array $message) => ($message['role'] ?? null) === 'user');
        return preg_match('/\b(so sánh|hay hơn|khác nhau|hay là|versus|vs\.? )\b/u', mb_strtolower((string) ($latestUserMessage['content'] ?? ''))) === 1;
    }

    private function isOrderQuestion(array $messages): bool
    {
        $latestUserMessage = collect($messages)->reverse()->first(fn (array $message) => ($message['role'] ?? null) === 'user');
        return preg_match('/\b(đơn hàng|đơn của tôi|mã đơn|theo dõi đơn|trạng thái đơn)\b/u', mb_strtolower((string) ($latestUserMessage['content'] ?? ''))) === 1;
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
                        'query' => ['type' => 'string', 'description' => 'Từ khóa ngắn để fallback; có thể để trống nếu filters đã đủ.'],
                        'pet_type' => ['type' => 'string', 'enum' => ['cat', 'dog']],
                        'life_stage' => ['type' => 'string', 'enum' => ['kitten', 'puppy', 'adult', 'senior', 'all_life_stages']],
                        'product_type' => ['type' => 'string', 'enum' => ['dry_food', 'wet_food', 'treat', 'toy', 'litter', 'accessory']],
                        'needs' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['daily_nutrition', 'picky_eater', 'skin_coat', 'weight_control', 'dental', 'indoor']]],
                        'min_price' => ['type' => 'number'],
                        'max_price' => ['type' => 'number'],
                        'in_stock' => ['type' => 'boolean'],
                        'limit' => ['type' => 'integer'],
                    ],
                ],
            ],
        ], [
            'type' => 'function',
            'function' => [
                'name' => 'search_knowledge_articles',
                'description' => 'Tìm FAQ hoặc chính sách PetWorld đã được xuất bản và kiểm duyệt.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => ['type' => 'string'],
                        'category' => ['type' => 'string', 'enum' => ['shipping', 'payment', 'returns', 'voucher', 'contact']],
                    ],
                ],
            ],
        ], [
            'type' => 'function',
            'function' => [
                'name' => 'compare_products',
                'description' => 'So sánh 2 hoặc 3 sản phẩm PetWorld bằng dữ liệu catalog thật.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => ['product_names' => ['type' => 'array', 'items' => ['type' => 'string'], 'minItems' => 2, 'maxItems' => 3]],
                    'required' => ['product_names'],
                ],
            ],
        ], [
            'type' => 'function',
            'function' => [
                'name' => 'get_my_orders',
                'description' => 'Tra cứu các đơn hàng thuộc chính tài khoản hiện tại. Không nhận user_id.',
                'parameters' => ['type' => 'object', 'properties' => ['order_code' => ['type' => 'string']]],
            ],
        ]];
    }

    private function executeToolCalls(ChatConversation $conversation, ?int $userId, array $calls): array
    {
        $toolMessages = [];
        $suggestions = [];
        $sources = [];
        $orderSummaries = [];

        foreach ($calls as $call) {
            $callId = data_get($call, 'id');
            $name = data_get($call, 'function.name');
            $decodedArguments = json_decode((string) data_get($call, 'function.arguments', '{}'), true);
            $arguments = is_array($decodedArguments) ? $decodedArguments : [];

            if (! is_string($callId) || $callId === '') {
                continue;
            }

            $products = [];
            if ($name === 'search_products' && $this->hasSearchCriteria($arguments)) {
                $this->mergeAdviceContext($conversation, $arguments);
                $products = $this->products->search($arguments);
            }
            $articles = $name === 'search_knowledge_articles'
                ? $this->knowledge->search($arguments)
                : [];
            $comparison = $name === 'compare_products'
                ? $this->products->compare((array) ($arguments['product_names'] ?? []))
                : [];
            $orderResult = $name === 'get_my_orders'
                ? $this->orders->search($userId, $arguments['order_code'] ?? null)
                : null;
            if ($orderResult !== null) $orderSummaries = array_merge($orderSummaries, $orderResult['orders']);
            $sources = array_merge($sources, $articles);
            $suggestions = array_merge($suggestions, $products);
            $toolMessages[] = [
                'role' => 'tool',
                'tool_call_id' => $callId,
                'content' => json_encode(['products' => $products, 'articles' => $articles, 'comparison' => $comparison, 'orders' => $orderResult], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        return [
            $toolMessages,
            collect($suggestions)->unique('id')->values()->all(),
            collect($sources)->unique('id')->values()->all(),
            collect($orderSummaries)->unique('id')->values()->all(),
        ];
    }

    private function hasSearchCriteria(array $arguments): bool
    {
        return trim((string) ($arguments['query'] ?? '')) !== ''
            || ! empty($arguments['pet_type'])
            || ! empty($arguments['life_stage'])
            || ! empty($arguments['product_type'])
            || ! empty($arguments['needs']);
    }

    private function mergeAdviceContext(ChatConversation $conversation, array $arguments): void
    {
        $allowed = [
            'pet_type' => ['cat', 'dog'],
            'life_stage' => ['kitten', 'puppy', 'adult', 'senior', 'all_life_stages'],
            'product_type' => ['dry_food', 'wet_food', 'treat', 'toy', 'litter', 'accessory'],
        ];
        $context = $conversation->context ?? [];

        foreach ($allowed as $key => $values) {
            if (in_array($arguments[$key] ?? null, $values, true)) {
                $context[$key] = $arguments[$key];
            }
        }
        if (is_array($arguments['needs'] ?? null)) {
            $context['needs'] = array_values(array_unique(array_filter($arguments['needs'], 'is_string')));
        }
        foreach (['min_price', 'max_price'] as $key) {
            if (isset($arguments[$key]) && is_numeric($arguments[$key])) {
                $context[$key] = (float) $arguments[$key];
            }
        }

        $conversation->update(['context' => $context]);
    }

    private function updateConversationContext(ChatConversation $conversation, string $message): void
    {
        $content = mb_strtolower($message);
        $context = $conversation->context ?? [];

        if (preg_match('/\b(mèo|cat)\b/u', $content)) $context['pet_type'] = 'cat';
        if (preg_match('/\b(chó|cún|dog)\b/u', $content)) $context['pet_type'] = 'dog';
        if (preg_match('/\b(mèo con|kitten)\b/u', $content)) $context['life_stage'] = 'kitten';
        if (preg_match('/\b(chó con|puppy)\b/u', $content)) $context['life_stage'] = 'puppy';
        if (preg_match('/\b(kén ăn)\b/u', $content)) $context['needs'] = array_values(array_unique([...(array) ($context['needs'] ?? []), 'picky_eater']));
        if (preg_match('/dưới\s*(\d+(?:[.,]\d+)?)\s*(k|nghìn|triệu)?/u', $content, $match)) {
            $amount = (float) str_replace(',', '.', $match[1]);
            $context['max_price'] = str_contains($match[0], 'triệu') ? $amount * 1000000 : $amount * 1000;
        }

        if ($context !== ($conversation->context ?? [])) {
            $conversation->update(['context' => $context]);
        }
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
