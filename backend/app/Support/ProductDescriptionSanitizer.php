<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class ProductDescriptionSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'em', 'h2', 'h3', 'li', 'ol', 'p', 'strong', 'u', 'ul',
    ];

    private const DROP_WITH_CONTENT = ['iframe', 'math', 'object', 'script', 'style', 'svg'];

    public function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="product-description-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = $document->getElementById('product-description-root');

        if (! $root) {
            return null;
        }

        $this->cleanChildren($root);

        $cleanHtml = '';
        foreach ($root->childNodes as $child) {
            $cleanHtml .= $document->saveHTML($child);
        }

        $cleanHtml = trim($cleanHtml);

        return $cleanHtml !== '' ? $cleanHtml : null;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        for ($node = $parent->firstChild; $node !== null;) {
            $next = $node->nextSibling;

            if ($node->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($node);
                $node = $next;

                continue;
            }

            if ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                        $parent->removeChild($node);
                        $node = $next;

                        continue;
                    }

                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }

                    $parent->removeChild($node);
                    $node = $next;

                    continue;
                }

                $this->cleanAttributes($node);
                $this->cleanChildren($node);
            }

            $node = $next;
        }
    }

    private function cleanAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $allowed = ($tag === 'a' && in_array($name, ['href', 'target', 'rel'], true))
                || ($name === 'class' && in_array($tag, ['p', 'h2', 'h3'], true));

            if (! $allowed) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($element->hasAttribute('class')) {
            $classes = array_filter(
                preg_split('/\s+/', $element->getAttribute('class')) ?: [],
                fn (string $class): bool => preg_match('/^ql-align-(center|right|justify)$/', $class) === 1,
            );

            $classes === []
                ? $element->removeAttribute('class')
                : $element->setAttribute('class', implode(' ', $classes));
        }

        if ($tag === 'a') {
            $href = trim($element->getAttribute('href'));

            if (! $this->isSafeUrl($href)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            }
        }
    }

    private function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
