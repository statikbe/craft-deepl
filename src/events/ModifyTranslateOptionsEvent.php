<?php

namespace statikbe\deepl\events;

use yii\base\Event;

/**
 * Fired by `ApiService` immediately before a request is sent to DeepL.
 *
 * Listeners may mutate `$options` and/or `$customInstructions` to influence
 * the outgoing request. `$sourceLang`, `$targetLang` and `$text` are provided
 * for context (so listeners can branch on language pair or content).
 *
 * @see \statikbe\deepl\services\ApiService::EVENT_BEFORE_TRANSLATE
 * @see https://developers.deepl.com/docs/best-practices/custom-instructions
 */
class ModifyTranslateOptionsEvent extends Event
{
    /** Source language as Craft passes it (e.g. `en`, `nl-BE`). */
    public string $sourceLang = '';

    /** Target language as Craft passes it. */
    public string $targetLang = '';

    /**
     * The text being translated. A `string` for single-string translation,
     * an `array<string>` when called from the batch flush. Provided for
     * inspection only — modifying it will not change what is sent to DeepL.
     */
    public string|array $text = '';

    /**
     * The full DeepL options bag (`tag_handling`, `glossary`, …). Listeners
     * may add, remove, or overwrite any key supported by
     * `\DeepL\TranslateTextOptions`.
     *
     * @var array<string, mixed>
     */
    public array $options = [];

    /**
     * Convenience bucket for DeepL custom instructions. Strings appended here
     * are merged into `$options['custom_instructions']` (capped at 10) right
     * before the API call. Each instruction must be ≤300 characters.
     *
     * @var array<int, string>
     */
    public array $customInstructions = [];
}
