<?php

declare(strict_types=1);

namespace Divengine\Codegen\Template;

use LogicException;

/**
 * The Div dialect used by the Divengine Codegen templates.
 *
 * Div's default dialect uses "{", "}", "[$", "?$", "<?" and similar
 * sequences, which are everywhere in generated PHP and SQL. In this dialect
 * every Div tag is wrapped in backticks, a character that never appears in
 * the generated output nor in the models, so everything else in a template
 * is literal text:
 *
 *     `{$name}`                     a value
 *     `[$fields]` ... `[/$fields]`  a loop
 *     `?$flag` ... `$flag?`         rendered when flag is truthy
 *     `!$flag` ... `$flag!`         rendered when flag is falsy
 *     `@else@`                      else branch of a conditional
 *     `<!--{ ... }-->`              template comment (not rendered)
 *
 * Div reads its dialect from global constants, defined once before the
 * engine is loaded. {@see self::install()} defines them and refuses to run
 * when another dialect was already defined.
 */
final class Dialect
{
    /** The character that delimits every Div tag of this dialect. */
    public const SIGIL = '`';

    /** @var array<string, string> Div tag constant => value */
    private const TAGS = [
        'DIV_TAG_VAR_MEMBER_DELIMITER' => '.',
        'DIV_TAG_REPLACEMENT_PREFIX' => '`{',
        'DIV_TAG_REPLACEMENT_SUFFIX' => '}`',
        'DIV_TAG_MULTI_MODIFIERS_PREFIX' => '`{$',
        'DIV_TAG_MULTI_MODIFIERS_OPERATOR' => '|',
        'DIV_TAG_MULTI_MODIFIERS_SEPARATOR' => '|',
        'DIV_TAG_MULTI_MODIFIERS_SUFFIX' => '|}`',
        'DIV_TAG_SUBMATCH_SEPARATOR' => ':',
        'DIV_TAG_MODIFIER_SIMPLE' => '$',
        'DIV_TAG_MODIFIER_CAPITALIZE_FIRST' => '^',
        'DIV_TAG_MODIFIER_CAPITALIZE_WORDS' => '^^',
        'DIV_TAG_MODIFIER_UPPERCASE' => '^^^',
        'DIV_TAG_MODIFIER_LOWERCASE' => '_',
        'DIV_TAG_MODIFIER_LENGTH' => '%',
        'DIV_TAG_MODIFIER_COUNT_WORDS' => '%%',
        'DIV_TAG_MODIFIER_COUNT_SENTENCES' => '%%%',
        'DIV_TAG_MODIFIER_COUNT_PARAGRAPHS' => '%%%%',
        'DIV_TAG_MODIFIER_ENCODE_URL' => '&',
        'DIV_TAG_MODIFIER_ENCODE_RAW_URL' => '&&',
        'DIV_TAG_MODIFIER_ENCODE_JSON' => 'json:',
        'DIV_TAG_MODIFIER_HTML_ENTITIES' => 'html:',
        'DIV_TAG_MODIFIER_NL2BR' => 'br:',
        'DIV_TAG_MODIFIER_TRUNCATE' => '~',
        'DIV_TAG_MODIFIER_WORDWRAP' => '/',
        'DIV_TAG_MODIFIER_SUBSTRING_SEPARATOR' => ',',
        'DIV_TAG_MODIFIER_SINGLE_QUOTES' => "'",
        'DIV_TAG_MODIFIER_JS' => 'js:',
        'DIV_TAG_MODIFIER_FORMAT' => '',
        'DIV_TAG_DATE_FORMAT_PREFIX' => '`{/',
        'DIV_TAG_DATE_FORMAT_SUFFIX' => '/}`',
        'DIV_TAG_DATE_FORMAT_SEPARATOR' => ':',
        'DIV_TAG_NUMBER_FORMAT_PREFIX' => '`{#',
        'DIV_TAG_NUMBER_FORMAT_SUFFIX' => '#}`',
        'DIV_TAG_NUMBER_FORMAT_SEPARATOR' => ':',
        'DIV_TAG_FORMULA_BEGIN' => '`(#',
        'DIV_TAG_FORMULA_END' => '#)`',
        'DIV_TAG_FORMULA_FORMAT_SEPARATOR' => ':',
        'DIV_TAG_SUBPARSER_BEGIN_PREFIX' => '`{',
        'DIV_TAG_SUBPARSER_BEGIN_SUFFIX' => '}`',
        'DIV_TAG_SUBPARSER_END_PREFIX' => '`{/',
        'DIV_TAG_SUBPARSER_END_SUFFIX' => '}`',
        'DIV_TAG_IGNORE_BEGIN' => '`{ignore}`',
        'DIV_TAG_IGNORE_END' => '`{/ignore}`',
        'DIV_TAG_COMMENT_BEGIN' => '`<!--{',
        'DIV_TAG_COMMENT_END' => '}-->`',
        'DIV_TAG_TXT_BEGIN' => '`{txt}`',
        'DIV_TAG_TXT_END' => '`{/txt}`',
        'DIV_TAG_TXT_WIDTH_SEPARATOR' => '=>',
        'DIV_TAG_STRIP_BEGIN' => '`{strip}`',
        'DIV_TAG_STRIP_END' => '`{/strip}`',
        'DIV_TAG_LOOP_BEGIN_PREFIX' => '`[$',
        'DIV_TAG_LOOP_BEGIN_SUFFIX' => ']`',
        'DIV_TAG_LOOP_END_PREFIX' => '`[/$',
        'DIV_TAG_LOOP_END_SUFFIX' => ']`',
        'DIV_TAG_EMPTY' => '`@empty@`',
        'DIV_TAG_BREAK' => '`@break@`',
        'DIV_TAG_LOOP_VAR_SEPARATOR' => '=>',
        'DIV_TAG_ITERATION_BEGIN_PREFIX' => '`[:',
        'DIV_TAG_ITERATION_BEGIN_SUFFIX' => ':]`',
        'DIV_TAG_ITERATION_END' => '`[/]`',
        'DIV_TAG_ITERATION_PARAM_SEPARATOR' => ',',
        'DIV_TAG_CONDITIONAL_TRUE_BEGIN_PREFIX' => '`?$',
        'DIV_TAG_CONDITIONAL_TRUE_BEGIN_SUFFIX' => '`',
        'DIV_TAG_CONDITIONAL_TRUE_END_PREFIX' => '`$',
        'DIV_TAG_CONDITIONAL_TRUE_END_SUFFIX' => '?`',
        'DIV_TAG_CONDITIONAL_FALSE_BEGIN_PREFIX' => '`!$',
        'DIV_TAG_CONDITIONAL_FALSE_BEGIN_SUFFIX' => '`',
        'DIV_TAG_CONDITIONAL_FALSE_END_PREFIX' => '`$',
        'DIV_TAG_CONDITIONAL_FALSE_END_SUFFIX' => '!`',
        'DIV_TAG_ELSE' => '`@else@`',
        'DIV_TAG_CONDITIONS_BEGIN_PREFIX' => '`{?(',
        'DIV_TAG_CONDITIONS_BEGIN_SUFFIX' => ')?}`',
        'DIV_TAG_CONDITIONS_END' => '`{/?}`',
        'DIV_TAG_TPLVAR_BEGIN' => '`{=',
        'DIV_TAG_TPLVAR_END' => '=}`',
        'DIV_TAG_TPLVAR_ASSIGN_OPERATOR' => ':',
        'DIV_TAG_TPLVAR_PROTECTOR' => '*',
        'DIV_TAG_DEFAULT_REPLACEMENT_BEGIN' => '`{@',
        'DIV_TAG_DEFAULT_REPLACEMENT_END' => '@}`',
        'DIV_TAG_INCLUDE_BEGIN' => '`{% ',
        'DIV_TAG_INCLUDE_END' => ' %}`',
        'DIV_TAG_PREPROCESSED_BEGIN' => '`{%% ',
        'DIV_TAG_PREPROCESSED_END' => ' %%}`',
        'DIV_TAG_PREPROCESSED_SEPARATOR' => ':',
        'DIV_TAG_CAPSULE_BEGIN_PREFIX' => '`[[',
        'DIV_TAG_CAPSULE_BEGIN_SUFFIX' => '',
        'DIV_TAG_CAPSULE_END_PREFIX' => '',
        'DIV_TAG_CAPSULE_END_SUFFIX' => ']]`',
        'DIV_TAG_MULTI_REPLACEMENT_BEGIN_PREFIX' => '`{:',
        'DIV_TAG_MULTI_REPLACEMENT_BEGIN_SUFFIX' => '}`',
        'DIV_TAG_MULTI_REPLACEMENT_END_PREFIX' => '`{:/',
        'DIV_TAG_MULTI_REPLACEMENT_END_SUFFIX' => '}`',
        'DIV_TAG_FRIENDLY_BEGIN' => '`<!--|',
        'DIV_TAG_FRIENDLY_END' => '|-->`',
        'DIV_TAG_AGGREGATE_FUNCTION_COUNT' => 'count',
        'DIV_TAG_AGGREGATE_FUNCTION_MAX' => 'max',
        'DIV_TAG_AGGREGATE_FUNCTION_MIN' => 'min',
        'DIV_TAG_AGGREGATE_FUNCTION_SUM' => 'sum',
        'DIV_TAG_AGGREGATE_FUNCTION_AVG' => 'avg',
        'DIV_TAG_AGGREGATE_FUNCTION_SEPARATOR' => ':',
        'DIV_TAG_AGGREGATE_FUNCTION_PROPERTY_SEPARATOR' => '-',
        'DIV_TAG_LOCATION_BEGIN' => '`(( ',
        'DIV_TAG_LOCATION_END' => ' ))`',
        'DIV_TAG_LOCATION_CONTENT_BEGIN_PREFIX' => '`{{',
        'DIV_TAG_LOCATION_CONTENT_BEGIN_SUFFIX' => '',
        'DIV_TAG_LOCATION_CONTENT_END_PREFIX' => '',
        'DIV_TAG_LOCATION_CONTENT_END_SUFFIX' => '}}`',
        'DIV_TAG_MACRO_BEGIN' => '`<?',
        'DIV_TAG_MACRO_END' => '?>`',
        'DIV_TAG_SPECIAL_REPLACE_NEW_LINE' => '`{\n}`',
        'DIV_TAG_SPECIAL_REPLACE_CAR_RETURN' => '`{\r}`',
        'DIV_TAG_SPECIAL_REPLACE_HORIZONTAL_TAB' => '`{\t}`',
        'DIV_TAG_SPECIAL_REPLACE_VERTICAL_TAB' => '`{\v}`',
        'DIV_TAG_SPECIAL_REPLACE_NEXT_PAGE' => '`{\f}`',
        'DIV_TAG_SPECIAL_REPLACE_DOLLAR_SYMBOL' => '`{\$}`',
        'DIV_TAG_SPECIAL_REPLACE_SPACE' => '`{\s}`',
        'DIV_TAG_TEASER_BREAK' => '`<!--break-->`',
    ];

    /**
     * Define the dialect constants. Must run before divengine\div is loaded.
     */
    public static function install(): void
    {
        foreach (self::TAGS as $name => $value) {
            if (defined($name)) {
                if (constant($name) !== $value) {
                    throw new LogicException("Div dialect conflict: $name is already defined");
                }
                continue;
            }
            define($name, $value);
        }
        // Templates are files of this tool; Div must not look for data files
        // or templates relative to the working directory.
        defined('PACKAGES') || define('PACKAGES', dirname(__DIR__, 2) . '/templates/');
        defined('DIV_REPO') || define('DIV_REPO', dirname(__DIR__, 2) . '/templates/');
    }

    /**
     * @return array<string, string>
     */
    public static function tags(): array
    {
        return self::TAGS;
    }
}
