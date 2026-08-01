<?php

namespace App\Services;

use App\Support\ProductDescriptionSanitizer;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProductAiContentService
{
    public function __construct(private readonly ProductDescriptionSanitizer $descriptionSanitizer) {}

    public function isConfigured(): bool
    {
        $ai = config('services.chatbot');

        return filled($ai['api_key'] ?? null)
            && filled($ai['model'] ?? null)
            && filled($ai['base_url'] ?? null);
    }

    public function generate(string $action, array $product, array $catalog, array $options): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('AI provider is not configured.');
        }

        $ai = config('services.chatbot');
        $response = Http::acceptJson()
            ->withToken($ai['api_key'])
            ->timeout((int) $ai['timeout'])
            ->post(rtrim((string) $ai['base_url'], '/') . '/chat/completions', [
                'model' => $ai['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($action)],
                    ['role' => 'user', 'content' => json_encode([
                        'action' => $action,
                        'product' => $product,
                        'catalog' => $catalog,
                        'options' => $options,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
                ...(($ai['provider'] ?? null) !== 'gemini' ? ['store' => false] : []),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'AI provider returned HTTP %d: %s',
                $response->status(),
                mb_substr(trim($response->body()), 0, 500),
            ));
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException('AI provider returned an empty response.');
        }

        $decoded = json_decode($this->stripCodeFence($content), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        return $action === 'generate_product_draft'
            ? $this->normalizeProductDraft($decoded, $catalog)
            : $this->normalize($decoded, $catalog);
    }

    public function generateImageAlts(array $product, array $images): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('AI provider is not configured.');
        }

        $ai = config('services.chatbot');
        $content = [[
            'type' => 'text',
            'text' => json_encode([
                'product' => $product,
                'images' => collect($images)->map(fn(array $image): array => ['id' => $image['id']])->all(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]];

        foreach ($images as $image) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $image['url']],
            ];
        }

        $response = Http::acceptJson()
            ->withToken($ai['api_key'])
            ->timeout((int) $ai['timeout'])
            ->post(rtrim((string) $ai['base_url'], '/') . '/chat/completions', [
                'model' => $ai['model'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->imageAltPrompt()],
                    ['role' => 'user', 'content' => $content],
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                ...(($ai['provider'] ?? null) !== 'gemini' ? ['store' => false] : []),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'AI provider returned HTTP %d: %s',
                $response->status(),
                mb_substr(trim($response->body()), 0, 500),
            ));
        }

        $answer = $response->json('choices.0.message.content');
        $decoded = is_string($answer) ? json_decode($this->stripCodeFence($answer), true) : null;

        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        $allowedIds = collect($images)->pluck('id')->map(fn($id) => (string) $id)->all();
        $alts = collect(is_array($decoded['image_alts'] ?? null) ? $decoded['image_alts'] : [])
            ->filter(fn($item): bool => is_array($item) && in_array((string) ($item['id'] ?? ''), $allowedIds, true))
            ->map(fn(array $item): array => [
                'id' => (string) $item['id'],
                'alt_text' => $this->text($item['alt_text'] ?? null, 125),
            ])
            ->filter(fn(array $item): bool => $item['alt_text'] !== '')
            ->unique('id')
            ->values()
            ->all();

        return [
            'suggestions' => ['image_alts' => $alts],
            'audit' => [],
            'warnings' => $this->messages($decoded['warnings'] ?? [], 8),
        ];
    }

    private function imageAltPrompt(): string
    {
        return <<<'PROMPT'
You write Vietnamese image alt text for PetWorld products. Reply only with one valid JSON object: {"image_alts":[{"id":string,"alt_text":string}],"warnings":[string]}. Preserve each image id exactly as supplied.
Use the supplied images as the primary source, and product context only to clarify the item. Write one accurate, natural alt text per image, maximum 125 Vietnamese characters. Describe what is visibly useful for a shopper: product, packaging, visible color, size/variant, or viewing angle. Do not keyword-stuff, repeat the same sentence for every image, mention file names, or invent features that are not visible or provided. If an image cannot be identified confidently, omit it and add a Vietnamese warning.
PROMPT;
    }

    private function systemPrompt(string $action): string
    {
        $basePrompt = <<<'PROMPT'
You are PetWorld's internal product content assistant. Reply only with one valid JSON object.
Use only the provided product information and catalog choices. Never invent ingredients, certifications, origin, price, stock, SKU, medical claims, or product facts. If information is missing, add a warning instead of guessing.
All user-facing text, including content, SEO text, audit, and warnings, must be written in natural Vietnamese.
Do not turn a product name into unsupported claims. In particular, do not infer flavor, size, ingredients, benefits, suitability, or a brand mismatch from the name alone. Never use generic filler such as "hỗ trợ duy trì", "tăng hấp dẫn", or "phù hợp" unless that fact is explicitly present in the supplied product information.
You may suggest only: short_description, description (safe HTML using p,h2,ul,li,strong only), focus_keyword, seo_title, seo_description, category_id, pet_species_ids, advice_life_stages, advice_needs, audit, warnings.
category_id and pet_species_ids must use IDs from the supplied catalog. advice_life_stages and advice_needs must use only supplied codes. Product category is the only product-type classification; do not return a separate product_type field.
Do not keyword-stuff. Keep SEO titles concise and SEO descriptions natural. Return empty strings or empty arrays for suggestions that are unsupported by the supplied facts.
PROMPT;

        $actionPrompt = match ($action) {
            'generate_product_draft' => <<<'PROMPT'
For this action, create a new-product draft from the supplied product name and any current form data. The description must be a detailed Vietnamese SEO draft of about 400-600 words, with an opening paragraph and 3-4 useful h2 sections. Use the product name, category, brand, selected pet profile, variants, and any current description to make it specific; avoid repetitive or empty marketing language. If a product detail is not certain, phrase it as a neutral selection or usage guide rather than a claim about the product. Besides the standard fields, you may return brand_id and variants. Each variant must have only value_ids selected from the supplied variant_types; do not invent a type or value. A variant may also include sku, price, sale_price, quantity, weight_grams, and operation_note. These operational values are suggestions only and must be accompanied by a short Vietnamese operation_note saying what must be checked. Return null for an operational value that cannot be reasonably estimated. sale_price must be lower than price; all inventory and shipping-weight values must be non-negative integers. Return brand_id only from the supplied brands. This is a preview-only draft: never imply that a product was created or saved. Leave uncertain fields empty and explain what the editor needs to verify in Vietnamese warnings.
PROMPT,
            'generate_seo_content' => <<<'PROMPT'
Create a detailed, SEO-optimized product description in English with a target length of approximately 500–700 words. The final output must be a complete and informative article, not a short summary or generic promotional copy.

## 1. Product Information to Use

Prioritize all information provided in the product form, including:

* Product name
* Brand
* Product category
* Suitable pet type, such as dogs, cats, or other pets
* Pet age, size, breed, or special condition
* Weight, volume, dimensions, or package size
* Flavor, ingredients, materials, features, or intended use
* Available product variants
* Country of origin
* Usage instructions
* Storage instructions
* Existing product description
* Other technical information entered in the form

Do not ignore specific details that are already available in the product form.

Do not modify or reinterpret the product name, brand, weight, package size, ingredients, specifications, intended use, or available variants.

## 2. Google Research and Source Verification

When the information provided in the product form is incomplete, search Google for additional information from reliable sources.

Prioritize sources in the following order:

1. The official website of the brand or manufacturer.
2. The official website of an authorized distributor.
3. The brand’s verified or official store on an e-commerce platform.
4. Official product labels, technical documents, brochures, or catalogues.
5. Reputable retail websites that provide consistent product information.

Cross-check information from at least two reliable sources when the content relates to:

* Ingredients
* Nutritional information
* Product benefits
* Intended users
* Country of origin
* Package size
* Product specifications
* Usage instructions
* Feeding recommendations
* Safety information

Make sure the researched information refers to the exact product, brand, product line, package size, flavor, model, or variant shown in the form.

Do not copy information from another product simply because its name, packaging, category, or appearance is similar.

Never invent, assume, or exaggerate any of the following:

* Ingredients or materials
* Protein, fat, moisture, fiber, mineral, or calorie values
* Medical, treatment, or disease-prevention benefits
* Certifications or quality standards
* Country of manufacture
* Suitable pet age, breed, size, or health condition
* Flavor
* Weight, size, or package quantity
* Veterinary recommendations
* Expiration period
* Feeding amount or dosage
* Product guarantees
* Claims such as “100% safe,” “the best,” “completely hypoallergenic,” “clinically proven,” or “guaranteed effective”

When reliable information cannot be found, omit that detail or write it as a neutral buying guide instead of presenting it as a verified product claim.

For example, instead of claiming that a specific size is suitable for a particular pet, advise buyers to compare the product dimensions with their pet’s weight, body measurements, age, or daily needs.

## 3. Required Article Structure

The article must include one opening paragraph and three to four useful H2 sections.

Only use sections that are supported by the available product information.

### Opening Paragraph

Write an opening paragraph of approximately 70–100 words.

Introduce the product directly by explaining:

* What the product is
* Which brand it belongs to
* Which type of pet it is intended for
* Its main package size, specification, or variant
* The practical need it may help address

Avoid generic or repetitive openings such as:

* “Are you looking for the perfect product for your pet?”
* “Pets are wonderful companions in our lives.”
* “Choosing the right product has never been easier.”
* “This is a must-have product for every pet owner.”

The opening should provide useful product information immediately.

### Suggested H2 Sections

Use three to four relevant H2 sections, such as the following.

## Key Product Features

Describe verified and specific product characteristics, such as:

* Ingredients
* Materials
* Design
* Texture
* Flavor
* Package size
* Dimensions
* Intended function
* Construction
* Product variants
* Distinctive features

Every feature must be supported by the product form or a reliable source.

Explain why each feature matters to the buyer instead of merely listing specifications.

## Which Pets Is This Product Suitable For?

Clearly state the suitable pet type, age group, breed size, body size, or usage need only when this information has been verified.

When the manufacturer does not provide a specific suitability statement, use neutral guidance.

Advise buyers to check factors such as:

* Pet age
* Body weight
* Breed size
* Health condition
* Activity level
* Dietary needs
* Product measurements
* Instructions printed on the packaging

Do not claim that the product is suitable for puppies, kittens, senior pets, sensitive pets, or pets with medical conditions unless this is explicitly supported by the product form or an official source.

## How to Choose the Right Size or Variant

Explain the differences between the available:

* Weights
* Package sizes
* Dimensions
* Colors
* Flavors
* Models
* Materials
* Product variants

Provide practical selection guidance based on factors such as:

* Pet size
* Number of pets
* Frequency of use
* Daily consumption
* Storage space
* Intended purpose
* Product measurements
* Buyer preferences

Do not claim that a specific variant is suitable for a certain pet unless the information has been verified.

When the available form does not provide enough information, present this section as a general selection guide rather than a product-specific claim.

## How to Use and Store the Product

Explain how to use the product according to the manufacturer’s instructions, product packaging, or verified official sources.

For pet food or treats, you may advise buyers to adjust the serving amount according to the pet’s:

* Weight
* Age
* Activity level
* Existing diet
* Health condition

However, do not create feeding tables, serving quantities, or dosage recommendations unless they are explicitly provided by the manufacturer.

For pet accessories, explain how buyers should check:

* Product dimensions
* Fit
* Comfort
* Durability
* Fasteners or connection points
* Product condition before use

Include storage instructions only when they are available from the form, label, manufacturer, or another reliable source.

## Important Usage Notes

Provide practical warnings that are appropriate for the product category, such as:

* Read the product label and instructions before use.
* Check the package size, dimensions, ingredients, or materials before purchasing.
* Do not use the product if the packaging is torn, swollen, leaking, damaged, or shows unusual signs.
* Stop using the product if the pet develops an unusual or adverse reaction.
* Consult a veterinarian when the pet has a medical condition, food sensitivity, allergy, or special dietary requirement.
* Do not use the product as a replacement for veterinary diagnosis or treatment.
* Keep the product away from children and pets when unattended, where appropriate.
* Store the product according to the manufacturer’s instructions.
* Regularly inspect accessories for damage, loose parts, sharp edges, or excessive wear.

Only include warnings that are relevant to the actual product category.

## 4. SEO Requirements

Identify one primary keyword based on the exact product name and realistic search intent.

The primary keyword should appear naturally in:

* The SEO title
* The opening paragraph
* At least one H2 heading
* The main article content
* The conclusion

Use related secondary keywords where relevant, such as:

* Brand name
* Product category
* Pet type
* Pet age
* Product function
* Package size
* Product dimensions
* Flavor
* Material
* Intended use

Do not use keyword stuffing.

Do not repeat the full product name unnaturally in every paragraph.

Do not insert keywords into sentences where they reduce readability or make the content sound automated.

Each paragraph should normally contain two to four useful sentences.

Use bullet points only when they improve the presentation of:

* Product specifications
* Ingredients
* Materials
* Variants
* Selection criteria
* Usage instructions
* Safety notes

The final article must be original.

Do not copy entire sentences or paragraphs from another website. Research, compare, verify, and rewrite the information in natural language.

## 5. Writing Style

Use a professional, clear, natural, and trustworthy writing style.

The article should help customers understand the product and make an informed purchasing decision.

Focus on practical information instead of aggressive sales language.

Avoid:

* Unsupported promotional claims
* Absolute guarantees
* Medical treatment claims
* Unverified comparisons
* Fake customer reviews
* Unverified expert or veterinarian recommendations
* Unsupported technical statements
* Repetitive introductions
* Empty marketing filler
* Excessive use of adjectives

Do not use phrases such as:

* “The number one product on the market”
* “The best product available today”
* “Guaranteed results”
* “100% harmless”
* “Completely safe for every pet”
* “Recommended by all veterinarians”
* “Scientifically proven”

These claims may only be used when they are explicitly supported by a credible and verifiable official source.

## 6. Required Output Format

Return the result in the following order:

1. **SEO Title**
   Write a clear title containing the primary keyword.

2. **Meta Description**
   Write a natural meta description of approximately 140–160 characters.

3. **Primary Keyword**

4. **Secondary Keywords**
   Provide three to six relevant secondary keywords.

5. **Complete SEO Product Description**
   Write a complete article of approximately 500–700 words, including:

   * One opening paragraph
   * Three to four relevant H2 sections
   * A useful concluding paragraph

6. **Sources Consulted**
   List the website name, page title, and direct product-page URL for every source used.

7. **Unverified or Missing Information**
   Clearly list any important detail that could not be verified.

Do not include researched details in the article unless the correct product and variant have been identified with reasonable confidence.

At the end of the output, always include the following warning:

**Editor’s Warning:** Before publishing, verify all information about the ingredients, materials, specifications, benefits, package size, country of origin, suitable pets, usage instructions, feeding guidance, and storage requirements against the product packaging or the manufacturer’s official source. Do not publish any detail that is not explicitly included in the product form or verified through a reliable source.

PROMPT,
            'improve_existing_content' => <<<'PROMPT'
For this action, improve the existing content only. Preserve every supported fact already provided, remove repetition, and improve headings and readability. Do not add new facts. If there is no meaningful existing content, return a warning instead of replacing it with generic marketing copy.
PROMPT,
            'generate_seo_meta' => <<<'PROMPT'
For this action, suggest only focus_keyword, seo_title, and seo_description. Keep them factual and based on the product name, brand, category, and verified existing content.
PROMPT,
            'suggest_product_profile' => <<<'PROMPT'
For this action, suggest only category_id, pet_species_ids, advice_life_stages, and advice_needs when they are explicitly supported by the supplied product information. When uncertain, leave that field empty and add a Vietnamese warning.
PROMPT,
            'audit_seo' => <<<'PROMPT'
For this action, return no content suggestions. Return only a short Vietnamese audit of missing fields, duplicate/repetitive wording, or unclear SEO metadata that can be determined directly from the supplied form. Never audit external facts or brand consistency.
PROMPT,
            default => '',
        };

        return $basePrompt . "\n" . $actionPrompt;
    }

    private function normalize(array $suggestions, array $catalog): array
    {
        $categoryIds = collect($catalog['categories'])->pluck('id')->map(fn($id) => (int) $id)->all();
        $speciesIds = collect($catalog['pet_species'])->pluck('id')->map(fn($id) => (int) $id)->all();

        return [
            'suggestions' => [
                'short_description' => $this->text($suggestions['short_description'] ?? null, 500),
                'description' => $this->description($suggestions['description'] ?? null),
                'focus_keyword' => $this->text($suggestions['focus_keyword'] ?? null, 120),
                'seo_title' => $this->text($suggestions['seo_title'] ?? null, 255),
                'seo_description' => $this->text($suggestions['seo_description'] ?? null, 320),
                'category_id' => in_array((int) ($suggestions['category_id'] ?? 0), $categoryIds, true)
                    ? (int) $suggestions['category_id']
                    : null,
                'pet_species_ids' => $this->allowedIds($suggestions['pet_species_ids'] ?? [], $speciesIds),
                'advice_life_stages' => $this->allowedCodes($suggestions['advice_life_stages'] ?? [], $catalog['life_stages']),
                'advice_needs' => $this->allowedCodes($suggestions['advice_needs'] ?? [], $catalog['needs']),
            ],
            'audit' => $this->messages($suggestions['audit'] ?? [], 12),
            'warnings' => $this->messages($suggestions['warnings'] ?? [], 12),
        ];
    }

    private function normalizeProductDraft(array $suggestions, array $catalog): array
    {
        $result = $this->normalize($suggestions, $catalog);
        $brandIds = collect($catalog['brands'] ?? [])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $valueTypeById = collect($catalog['variant_types'] ?? [])
            ->flatMap(fn (array $type) => collect($type['values'] ?? [])
                ->mapWithKeys(fn (array $value) => [(int) $value['id'] => (int) $type['id']]))
            ->all();

        $variants = collect(is_array($suggestions['variants'] ?? null) ? $suggestions['variants'] : [])
            ->map(function ($variant) use ($valueTypeById): array {
                $variant = is_array($variant) ? $variant : [];
                $ids = collect(is_array($variant) && is_array($variant['value_ids'] ?? null) ? $variant['value_ids'] : [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => isset($valueTypeById[$id]))
                    ->unique()
                    ->values();

                return [
                    'value_ids' => $ids->all(),
                    'sku' => $this->sku($variant['sku'] ?? null),
                    'price' => $this->nonNegativeInteger($variant['price'] ?? null),
                    'sale_price' => $this->nonNegativeInteger($variant['sale_price'] ?? null),
                    'quantity' => $this->nonNegativeInteger($variant['quantity'] ?? null),
                    'weight_grams' => $this->nonNegativeInteger($variant['weight_grams'] ?? null, 50000),
                    'operation_note' => $this->text($variant['operation_note'] ?? null, 300),
                ];
            })
            ->filter(fn (array $variant) => $variant['value_ids'] !== [] || $variant['sku'] !== '' || $variant['price'] !== null || $variant['quantity'] !== null || $variant['weight_grams'] !== null)
            ->map(function (array $variant) use ($valueTypeById): array {
                $oneValuePerType = [];
                foreach ($variant['value_ids'] as $valueId) {
                    $oneValuePerType[$valueTypeById[$valueId]] ??= $valueId;
                }

                $variant['value_ids'] = array_values($oneValuePerType);
                if ($variant['price'] === null || $variant['sale_price'] === null || $variant['sale_price'] >= $variant['price']) {
                    $variant['sale_price'] = null;
                }
                if (($variant['sku'] !== '' || $variant['price'] !== null || $variant['quantity'] !== null || $variant['weight_grams'] !== null) && $variant['operation_note'] === '') {
                    $variant['operation_note'] = 'AI đề xuất — cần kiểm tra trước khi lưu.';
                }

                return $variant;
            })
            ->unique(fn (array $variant) => implode('-', $variant['value_ids']))
            ->take(30)
            ->values()
            ->all();

        $result['suggestions']['brand_id'] = in_array((int) ($suggestions['brand_id'] ?? 0), $brandIds, true)
            ? (int) $suggestions['brand_id']
            : null;
        $result['suggestions']['variants'] = $variants;

        return $result;
    }

    private function text(mixed $value, int $maxLength): string
    {
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    private function nonNegativeInteger(mixed $value, int $max = 100000000): ?int
    {
        if (! is_numeric($value) || (int) $value != $value) {
            return null;
        }

        $integer = (int) $value;

        return $integer >= 0 && $integer <= $max ? $integer : null;
    }

    private function sku(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return mb_substr(trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', strtoupper($value)) ?? ''), 0, 255);
    }

    private function description(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return $this->descriptionSanitizer->sanitize(mb_substr(trim($value), 0, 12000)) ?? '';
    }

    private function allowedIds(mixed $values, array $allowed): array
    {
        return collect(is_array($values) ? $values : [])
            ->map(fn($value) => (int) $value)
            ->filter(fn(int $value) => in_array($value, $allowed, true))
            ->unique()->values()->all();
    }

    private function allowedCodes(mixed $values, array $allowed): array
    {
        return collect(is_array($values) ? $values : [])
            ->filter(fn($value) => is_string($value) && in_array($value, $allowed, true))
            ->unique()->values()->all();
    }

    private function messages(mixed $messages, int $limit): array
    {
        return collect(is_array($messages) ? $messages : [])
            ->filter(fn($message): bool => is_string($message))
            ->map(fn(string $message) => mb_substr(trim($message), 0, 500))
            ->filter()
            ->take($limit)->values()->all();
    }

    private function stripCodeFence(string $content): string
    {
        return preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)) ?? $content;
    }
}
