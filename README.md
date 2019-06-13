# utext

Tiny set of PHP text utility classes.

Class list (all classes placed in `\infoxy\utext namespace`):

  - `PlainText`: implements plain text filter and corresponded utilities.
  - IdnaURL: (NOT RELEASED YET) International domain names normalization and humanization class.
  - LiteText: (NOT RELEASED YET) DOM normalizer to simplified subset of HTML.


## Purpose and intro

All editors, copywriters, users have different skills in html and unicode. Somebody type text in notepads, anothers -- in word processors or in some advanced publishing platforms, all can made copy-paste from foreign sources and so on.

As result in real life: many pieces of simple utf-8 text (in site's database for example) can be very different in formatting and technical quality:

  - can contain invalid utf-8 byte sequences;
  - can be mixture of composed and decomposed unicode chars;
  - with or without encoded html entities;
  - with or without denormalized whitespaces;
  - with special spaces (spations, fixed-width spaces) that can be nice for printable papers, but really bad things then copypasted on the web pages; 
  - with special dashes, hyphens or other symbols that can be unreleased in the used fonts.

This makes pieces of text harder to search and ugly to look. PlainText filter can be used to transform plain text in some more normalized and clean form (based on specified options) and also provide some additional services. 
See `PlainText` section for details.


## PlainText

There list of filtration options  Also PlainText optionally provide some additional services:

Transform plain text in some more normalized and clean form (based on specified options). 

There list of filtration options  Also PlainText optionally provide some additional services:

  - Dash patterns in "TeX"-like style (`--`,`---`) that helps usage of en-dash and em-dash;
  - Shy pattern in "TeX"-like style (`\-`) for soft-hyphens;
  - Language-specific replacements for straight double quotes;
  - Fast replacement for 





options listed in "logical pipeline" order:

**filter_utf8**
Bypass only correct utf8 chars, strip out any invalid char sequences.

Note: there exists static method `PlainText::filter_utf8($s)` that can be used explicitly.

**newline_tags**
Insert `\r\n` before every `<`. Useful with *strip_tags* to produce non-word-jouned plain text from html or xml. For example: `"<em>yellow</em><b>green</b>"` will lead to `"yellow green"` instead of `"yellowgreen"`.

**strip_tags**
Stip tags. Can be used with *newline_tags*.

**decode_entities**
Decode html entities. All encoded entities like `&mdash;` or `&#x222B;` will be decoded to appropriate unicode chars.
Keep in mind: as usual, not all charcodes supported by your fonts.

**lang_quotes**
Replace double quotes with language-specific ones.
This is simple language-based quote marks substitutor.
Supported language ids are: `en`, `de`, `ru`, `fr`.
Other cases falls to `en` in current release.

**simplify_dashes**
Simplify dashes to hyphen-minus (u+2D), en-dash (u+2013) or em-dash (u+2014).
Many fonts do not have full unicode set of dashes. This option can be used to produce:
  - hyphen-minus from u+2010 (HYPHEN), u+2011 (NON-BREAKING HYPHEN)
  - en-dash from u+2012 (FIGURE DASH),
  - em-dash from u+2015 (HORIZONTAL BAR), u+2E3A (TWO-EM DASH), u+2E3B (THREE-EM DASH).

Note: Mathematical minus u+2212 (MINUS SIGN) and language-specific hyphens/dashes leave unchanged.

**shy_pattern**
Use shy pattern in "TeX" style: soft-hyphen (u+AD) from `\-`.

**dash_patterns**
Use dash patterns. This options define usage of emdash and endash patterns in "TeX" style:
  - en-dash from `--`,
  - em-dash from `---`.

**replace_triple_dots**
Replace triple dots with ellipsis. It can be used in conjunction with *trim_dots*.

**replace_quotes**
Replace straight single and double quotes with curly quotes. 
  - `'` to u+2019 (hi-9 quote-mark),
  - `"` to u+201D (hi-99 quote-mark).

**replace_specials**
Replace special chars with safe fallbacks.
A bit ugly yet bulletproof solution agains html special chars. Replace:
  - `&` to `+` (plus sign),
  - `<` to u+2039 (left-pointing single angular quote-mark),
  - `>` to u+203A (right-pointing single angular quote-mark).

Note: Fullwidth chars (like fullwidth ampersand) are not widely supported by fonts, so we do not use it as replacements.

**simplify_spaces**
Simplify spaces. Replace nbsp and spations with simple spaces.

**collapse_spaces**
Replace sequence of whitespaces with single space.

**trim**
Trim leading and trailing whitespaces.

**trim_dots**
Trim leading and trailing dots. Can be useful for titles and description fields in some scenarios.
Use in conjunction with *replace_triple_dots* to trim dots, but not triple dots.

**normalize**
Normalize unicode string to one of normalization form. `Normalization Form C` aka `NFC` is default form.







Basic filtering example:
```
use \infoxy\utext\PlainText;

// create object and set options
$f = new PlainText;
$f->seOptions[]->setLangId(



options

Service's *filter($s)* function do most of works. 
So, list of filter options is main thing to know about utext.plain service, and consequently for using widgets and formatters. 

There are list of options in "logical pipeline" order:

**filter_utf8**
Bypass only correct utf8 chars, strip out any invalid char sequences.

**newline_tags**
Insert `\r\n` before every `<`. Useful with *strip_tags* to produce non-word-jouned plain text from html or xml. For example: `"<em>yellow</em><b>green</b>"` will lead to `"yellow green"` instead of `"yellowgreen"`.

**strip_tags** 
Stip tags. Can be used with *newline_tags*.

**decode_entities**
Decode html entities. All encoded entities like `&mdash;` or `&#x222B;` will be decoded to appropriate unicode chars.
Keep in mind charcodes supported by your fonts.

**lang_quotes**
Replace double quotes with language-specific ones.
This is simple language-based quote marks substitutor.
Supported language ids are: `en`, `de`, `ru`, `fr`.
Other cases falls to `en` in current release.

**simplify_dashes**
Simplify dashes to hyphen-minus (u+2D), en-dash (u+2013) or em-dash (u+2014).
Many fonts do not have full unicode set of dashes. This option can be used to produce:
  - hyphen-minus from u+2010 (HYPHEN), u+2011 (NON-BREAKING HYPHEN)
  - en-dash from u+2012 (FIGURE DASH),
  - em-dash from u+2015 (HORIZONTAL BAR), u+2E3A (TWO-EM DASH), u+2E3B (THREE-EM DASH).

Note: Mathematical minus u+2212 (MINUS SIGN) and language-specific hyphens/dashes leave unchanged.

**shy_pattern**
Use shy pattern in "TeX" style: soft-hyphen (u+AD) from `\-`.

**dash_patterns**
Use dash patterns. This options define usage of emdash and endash patterns in "TeX" style:
  - en-dash from `--`,
  - em-dash from `---`.

**replace_triple_dots**
Replace triple dots with ellipsis. It can be used in conjunction with *trim_dots*.

**replace_quotes**
Replace straight single and double quotes with curly quotes. 
  - `'` to u+2019 (hi-9 quote-mark),
  - `"` to u+201D (hi-99 quote-mark).

**replace_specials**
Replace special chars with safe fallbacks.
A bit ugly yet bulletproof solution agains html special chars. Replace:
  - `&` to `+` (plus sign),
  - `<` to u+2039 (left-pointing single angular quote-mark),
  - `>` to u+203A (right-pointing single angular quote-mark).

Note: Fullwidth chars (like fullwidth ampersand) are not widely supported by fonts, so we do not use it as replacements.

**simplify_spaces**
Simplify spaces. Replace nbsp and spations with simple spaces.

**collapse_spaces**
Replace sequence of whitespaces with single space.

**trim**
Trim leading and trailing whitespaces.

**trim_dots**
Trim leading and trailing dots. Can be useful for titles and description fields in some scenarios.
Use in conjunction with *replace_triple_dots* to trim dots, but not triple dots.

**normalize**
Normalize unicode string to one of normalization form. `Normalization Form C` aka `NFC` is default form.


## Widgets

Filtering in widgets allow to polish input strings before saving. So, saved string always stay clean and normalized is some aspects.

Widget support special escapement that allow to restore patterns before pass data to widget
and re-filter strings with [clously] similar results every time then data come back. 

In other hand filtration can perform multiple times on the same data (every time on node editing, for example) and we need to be careful about it.
Because of that widget implements two important things:
This lead to on-the-fly pattern and html-entities usage: patterns will not be saved in resulting data.


## Formatters

Supported field types:
    `string`, `string_long` - full string formatter;
    `text`, `text_long`, `text_with_summary` - trimmed or summary formatter;

Entities decode and patterns usage in formatters are really different then from thous in widgets: patterns must be in string data itself.
Note that widgets regenerate patterns on the fly based on chars in string data.


## Dash patterns and entities

Patterns feature can be implement in two main ways:

  - as formatter option: patterns and entities in source text 
  - as widget option: text saved as normalized (in some ways) string without patterns, but turn to pattern and back on the fly.

In patterns used, then `\-`, `--`, `---` are reserved for their use in text.



## utext.plain service usage
Example:
```
    $plain_source = '   Some  source    text &emdash; entities, denormalized spaces, leading spaces, ets...  ';
    
    // Get service
    $filter = \Drupal::service('utext.plain');
    
    // Get current language id, needed only for 'replace_quotes' option
    $lang_id = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
    // Set up options
    $options = [
        'filter_utf8' => true,
        'decode_entities' => true,
        'simplify_spaces' => true,
        'collapse_spaces' => true,
        'trim' => true,
    ];
    $filter->setLangId($lang_id)->setOptions($options);
    
    // Perform filtering
    $plain_filtered = $filter->filter($plain_source);
```
