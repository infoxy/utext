# utext

Tiny set of PHP text utility classes.

  - [Purposes and intro](#purposes-and-intro)
  
**Requirements:** 
  - PHP intl extension or polyfills for [Normalizer class](https://www.php.net/manual/en/class.normalizer.php) and [idna functions](https://www.php.net/manual/en/ref.intl.idn.php).

**Class list (all classes placed in `\infoxy\utext namespace`):**

  - [PlainFilter](#plainfilter): Plain text filter and corresponded utilities.
  - [HtmlBase](#htmlbase): Collection of static functions for DOMDocument manipulations.
  - [IdnaURL](#idnaurl): International domain names normalization and humanization class.

First two classes can be used as standalone, and latter based on its.

## Purposes and intro

All editors, copywriters, users have different skills in html and unicode. Somebody type text in notepads, anothers type in word processors or in some advanced publishing platforms, all can made copy-paste from foreign sources and so on.

As result in real life: many pieces of simple utf-8 text (in site's database for example) can be very different in formatting and technical quality:

  - can contain invalid utf-8 byte sequences;
  - can be mixture of composed and decomposed unicode chars;
  - with or without encoded html entities;
  - with or without denormalized whitespaces;
  - with special spaces (spations, fixed-width spaces) that can be nice for printable papers, but really bad things then copypasted on the web pages; 
  - with special dashes, hyphens or other symbols that can be unreleased in the used fonts.

This makes pieces of text harder to search and ugly to look. PlainFilter filter can be used to transform plain text in some more normalized and clean form (based on specified options) and also provide some additional services like tags stripper and pattern usage. See [PlainFilter](#plainfilter) section for details.


## PlainFilter

### Basic filtration

```
use \infoxy\utext\PlainFilter

$pf = new PlainFilter;
$pf -> setLangId('ru')  // language for quote filter
    -> setOptions([     // set filter options
        'filter_utf8' => true,
        'decode_entities' => true,
        'lang_quotes' => true,   // replace " with language-specific quotes
        'replace_quotes => true, // replase ' and " to curly form
        'simplify_spaces' => true,
        'collapse_spaces' => true,
        'trim' => true,
        'normalize' => true,
    ]);
$filtered_string = $pf->filter($input_string);
```

There are list of filter options in "logical pipeline" order:

**filter_utf8**
Bypass only correct utf8 chars, strip out any invalid byte sequences.

Note: there exists static method `PlainFilter::filter_utf8($s)` that can be used explicitly.

**newline_tags**
Insert `\r\n` before every `<`. Useful with *strip_tags* to produce non-word-jouned plain text from html or xml. For example: `"<em>yellow</em><b>green</b>"` will lead to `"yellow green"` instead of `"yellowgreen"`.

**strip_tags**
Stip tags. Can be used with *newline_tags*.

**decode_entities**
Decode html entities. All encoded entities like `&mdash;` or `&#x222B;` will be decoded to appropriate unicode chars.

**lang_quotes**
Replace double quotes with language-specific ones.
This is simple language-based quote marks substitutor.

Supported language ids are: `en`, `de`, `ru`, `fr`.
Other cases falls to `en` in current release.

Note 1: In this release lang_quotes just lookup to word boundaries, so some nested and spaced double quotes can be handled incorrectly. But even now this can be really helpful text authoring tool.

Note 2: This only option that neded language-specific settings with `setLangId()`.

Note 3: lang_quotes option can produce additional spaces around quotes when its needed by language rules.

**simplify_dashes**
Simplify dashes to hyphen-minus (u+2D), en-dash (u+2013) or em-dash (u+2014).
Many fonts do not have full unicode set of dashes. This option can be used to produce:
  - hyphen-minus from u+2010 (hyphen), u+2011 (non-breaking hyphen)
  - en-dash from u+2012 (figure dash),
  - em-dash from u+2015 (horizontal bar), u+2E3A (two-em dash), u+2E3B (three-em dash).

Note: Mathematical minus u+2212 (minus sign) and language-specific hyphens/dashes leave unchanged.

**shy_pattern**
Replace shy pattern in "TeX" style `\-` with u+AD (soft-hyphen).

**dash_patterns**
Replace dash patterns. This options define usage of em-dash and en-dash patterns in "TeX" style:
  - `--` to u+2013 (en-dash),
  - `---` to u+2014 (em-dash).

**replace_triple_dots**
Replace triple dots with u+2026 (ellipsis). It can be used in conjunction with *trim_dots*.

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
Simplify spaces. Replace whitespaces, u+A0 (nbsp), u+2000 to u+200A (fixed width spaces), u+202F (narrow nbsp), u+205F (medium math space) with simple spaces.

**collapse_spaces**
Replace sequence of whitespaces with single space.

**zebra_spaces**
Replace pair of spaces with nbsp+space.

Note: collapse_spaces have precedence over zebra_spaces by its logics.

**trim**
Trim leading and trailing whitespaces.

**trim_dots**
Trim leading and trailing dots. Can be useful for titles and description fields in some scenarios.
Use in conjunction with *replace_triple_dots* to trim dots, but not triple dots.

**normalize**
Normalize unicode string to one of normalization form. `Normalization Form C` aka `NFC` is default form.

Most of filtration options are the flags for PlainFilter::setOptions(). But some addinional settings can be done with special setters and getters:

`setLangId($lang_id = NULL)`: set language for language-specific options, default is 'en'.
`getLangId()`: get language for language-specific options.

`setNormalForm($nf = 'NFC')`: set unicode normalization form. Can be 'NFC', 'NFD', 'NFKC', 'NFKD'. Default is 'NFC'. 
`getNormalForm()`: get current unicode normalization form.


### Filter escaping

Filter escaping provide ability for filter string multiple times, for example in edit-by-user scenarios. Main things in escaping are restoring `&amp;` entity for ampersand and/or restore patterns then needed. Re-filtered string will not be damaged by double-decoding.

```
use \infoxy\utext\PlainFilter;

// Note: same options as for filter, 
// but language-specific and normalizer settings not needed
$opt = [
    'filter_utf8' => true,
    'decode_entities' => true,
    'lang_quotes' => true,   // replace " with language-specific quotes
    'replace_quotes => true, // replase ' and " to curly form
    'simplify_spaces' => true,
    'collapse_spaces' => true,
    'trim' => true,
    'normalize' => true,
];

// Call static escape_filter()
$prepared_string = PlainFilter::escape_filter($input_string, $opt);
```


## HtmlBase
Collection of static functions for DOMDocument manipulation. So you do not need to create HtmlBase objects to use methods.

Note: `toText()` and `toDom()` is focused on import/export in-body html tags, not for full documents with embedded scripts, styles and CDATA sections.

### String to DOM and back conversions

**HtmlBase::toDom($s)**
Create HTML DOMDocument from string $s, that defines body content for created document.
Return DOMElement body for created document.

**HtmlBase::toText($e)**
Export content of DOMElement $e into the string.
Return html as string.

### Class checking

**HtmlBase::classCheck($s)**
Check then string $s is acceptable as class list. 
In current version it means that $s contain mixture of alphanumerics, '-', underscore and space.
Return TRUE if check passed or FALSE in other case.

**HtmlBase::classArray($s)**
Explode string $s to class names.
Return array of string (or empty array if no classes).

**HtmlBase::classPat($classes)**
Generate pattern to match against specified classes.
$classes: array of class names or string of class names.

#### Usage example

```
$pat = HtmlBase::classPat('class1 class2 class3');
foreach ($nodes as $n) {
  if ($n->hasAttribute('class') && preg_match($pat, $n->getAttribute('class')) {
     // class matched with any of ones in pattern
     // DoSomething($n);
  }
}
```

## IdnaURL

... in progress ...

