<?php
namespace infoxy\utext;
class PlainFilter {
    protected $quotes;  // lang => [ open => '', close => '' ]
    protected $lang_id; // current language id ('en', 'ru', 'de', 'fr')
    // Normalizer settings
    protected $norm_form;   // (string|empty) 'NFC','NFD','NFKC', 'NFKD'
                            // or empty if normalization not available
    protected $norm_forms;  // (keyed array) of normalizer constants map 
                            // see: https://www.php.net/manual/en/class.normalizer.php
    
    // Replacer settings
    protected $pat;         // (array) pattern
    protected $repl;        // (array) replacements, both as utf-8 encoded bytes
    
    // Spaces settings (regex pattern to replace with ' ')
    protected $spaces;
    // Trim settings (string)
    protected $trims;
    // filter switches (all bool)
    protected $use_utf8;
    protected $use_newline_tags;
    protected $use_strip_tags;
    protected $use_decode;
    protected $use_lang_quotes;
    protected $use_normalizer;
    public function __construct() {
        $this->quotes = [
            'en' => [ 'open' => " \xE2\x80\x9C", "close" => "\xE2\x80\x9D " ], //hi-66 hi-99: u+201C,u+201D with outer spaces
            'de' => [ 'open' => " \xE2\x80\x9E", "close" => "\xE2\x80\x9C " ], //lo-99 hi-66: u+201E,u+201C with outer spaces
            'ru' => [ 'open' => " \xC2\xAB",  'close' => "\xC2\xBB "  ], // double angular quotes u+AB,u+BB with outer spaces
            'fr' => [ 'open' => " \xC2\xAB ", 'close' => " \xC2\xBB " ], // double angular quotes u+AB,u+BB with outer and inner spaces
            ];
        $this->lang_id = 'en';
        
        $this->setOptions();
        
        // Setup normalizer
        // PHP Normalizer class: https://www.php.net/manual/en/class.normalizer.php
        // Unicode normalization forms: http://unicode.org/reports/tr15/
        // Nice article about NFC: http://www.macchiato.com/unicode/nfc-faq 
        if (class_exists('\Normalizer')) {
            $this->norm_form = 'NFC';
            $this->norm_forms = [
                'NFC'  => \Normalizer::FORM_C,
                'NFD'  => \Normalizer::FORM_D,
                'NFKC' => \Normalizer::FORM_KC,
                'NFKD' => \Normalizer::FORM_KD
                ];
        } else {
            $this->norm_form = NULL;
            $this->norm_forms = [];
        }
    }
    // Setters and getters -------------------------------------
    
    /**
     * Set language id for language-specific processing
     * Return $this for chaining
     */
    public function setLangId($lang_id = NULL) {
        $this->lang_id = (empty($lang_id)) ? 'en' : $lang_id;
        return $this;
    }
    
    /**
     * Return current language id
     */
    public function getLangId() { return $this->lang_id; }
    /**
     * Set Unicode normalization form (used in filter()).
     * $nf: (string) one of { 'NFC', 'NFD', 'NFKC', 'NFKD' }
     * Set to 'NFC' for any other values;
     * Return $this for chaining
     */
    public function setNormalForm($nf = 'NFC') {
        if (empty($this->norm_form)) return $this;
        $this->norm_form = 
            (array_key_exists($nf, $this->norm_forms))? $nf : 'NFC';
        return $this;
    }
    /**
     * Return current normalization form, NULL if normalizer not available.
     */
    public function getNormalForm() {
        return (empty($this->$norm_form))? NULL : $norm_form;
    }
    /**
     * Set filter options
     * $opt: (keyed array) option list
     * Return $this for chaining
     */
    public function setOptions($opt=NULL) {
        if (empty($opt)) {
            $this->spaces = $this->trims = $this->pat = $this->repl = NULL;    
            $this->use_utf8 =
            $this->use_newline_tags =
            $this->use_strip_tags =
            $this->use_decode =
            $this->use_lang_quotes =
            $this->use_normalizer = FALSE;
            return $this;
        }
        // switches
        $this->use_utf8 = !empty($opt['filter_utf8']);
        $this->use_newline_tags = !empty($opt['newline_tags']);
        $this->use_strip_tags = !empty($opt['strip_tags']);
        $this->use_decode = !empty($opt['decode_entities']);
        $this->use_lang_quotes = !empty($opt['lang_quotes']);
        $this->use_normalizer = !empty($opt['normalize']);
        // chars and patterns replacement
        $repl = $pat = [];
        if (!empty($opt['simplify_dashes'])) {
            // [ figure dash, horizontal bar, two-em dash, three-em dash, hyphen, non-breaking hyphen ]
            $pat = array_merge($pat, [ "\xE2\x80\x92", "\xE2\x80\x95", "\xE2\xB8\xBA", "\xE2\xB8\xBB", "\xE2\x80\x90", "\xE2\x80\x90" ]);
            // to [ en dash, em dash, hyphen-minus ]
            $repl= array_merge($repl,[ "\xE2\x80\x93", "\xE2\x80\x94", "\xE2\x80\x94", "\xE2\x80\x94", '-', '-' ]);
        }
        if (!empty($opt['shy_pattern'])) {
            $pat[] = '\-';
            $repl[]= "\xC2\xAD";
        }
        if (!empty($opt['dash_patterns'])) {
            $pat[] = '---';
            $repl[]= "\xE2\x80\x94"; // emdash
            $pat[] = '--';
            $repl[]= "\xE2\x80\x93"; // endash
        }
        if (!empty($opt['replace_triple_dots'])) {
            $pat[] = '...';
            $repl[]= "\xE2\x80\xA6"; // ellipsis
        }
        if (!empty($opt['replace_quotes'])) { 
            $pat[] = "'";
            $repl[]= "\xE2\x80\x99"; // u+2019
            $pat[] = '"';
            $repl[]= "\xE2\x80\x9D"; // u+201D
        }
        if (!empty($opt['replace_specials'])) {
            $pat[] = '<';
            $repl[]= "\xE2\x80\xB9"; // u+2039
            $pat[] = '>';
            $repl[]= "\xE2\x80\xBA"; // u+203A
            $pat[] = '&';
            $repl[]= '+';
        }
        if (!empty($pat)) {
            $this->pat = $pat;
            $this->repl= $repl;
        } else $this->pat = $this->repl = NULL;
        
        // space regex pattern
        // whitespaces, nbsp 00A0, spations 2000-200A, narrow nbsp u+202F, 
        // medium math space u+205F
        $this->spaces = (empty($opt['simplify_spaces'])
            ? ((empty($opt['collapse_spaces']))
                ? NULL
                : '/(?:\s)+/' )
            : ((empty($opt['collapse_spaces']))
                ? '/[\s\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}]/u'
                : '/[\s\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}]+/u' )
                );
        
        // trim spaces and dots
        $this->trims = (empty($opt['trim']))
            ? ((empty($opt['trim_dots']))
                ? NULL
                : '.' )
            : ((empty($opt['trim_dots']))
                ? " \t\n\r\0\x0B"
                : " \t\n\r\0\x0B." );
        return $this;
    }
    // Filters -------------------------------------------------
    /**
     * Bypass correct utf-8 byte sequence and drop incorrect bytes
     * Based on this recommendations: https://www.w3.org/International/questions/qa-forms-utf-8.en
     * And notes in this code: https://stackoverflow.com/questions/1401317/remove-non-utf8-characters-from-string
     * $s (string or stringable object) source plain text
     * Return: filtered string
     */
    public static function filter_utf8($s) {
        $pat = "/((?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})+)|./";
               //    ^ ASCII without ctrls   ^non-overlong 2-byte   ^exclude overlong 3-byte   ^straight 3-bytes                 ^exclude surrogates        ^planes 1-3                   ^planes 4-15              ^plane 16                        ^anything wrong
        $repl = '$1';
        $out = preg_replace($pat,$repl,(string)$s);
        return ((is_string($out)) ? $out : '');
    }
    /**
     * Replace double quotes with language special quotes
     * $s: string with decoded entities
     * Return: filtered string
     */
    protected function filter_quotes($s) {
        $lang_id = (array_key_exists($this->lang_id, $this->quotes)) ? $this->lang_id : 'en';
        $pat = [ "/\"\b/u", "/\b\"/u", '/"/u'];
        $open = $this->quotes[$lang_id]['open'];
        $close = $this->quotes[$lang_id]['close'];
        $repl = [ $open, $close, $close ];
        return preg_replace($pat,$repl, $s);
    }
    /**
     * Filter span of text
     * $s: (string or stringable object) source plain text
     * Return: filtered string
     */
    public function filter($s) {
        $s = (string)$s;
        if ($this->use_utf8) $s = self::filter_utf8($s);
        if ($this->use_newline_tags) $s = str_replace('<',"\r\n<", $s);
        if ($this->use_strip_tags) $s = strip_tags($s);
        if ($this->use_decode)
            $s = html_entity_decode($s, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        if ($this->use_lang_quotes) $s = $this->filter_quotes($s);
        if (!empty($this->pat)) $s = str_replace($this->pat, $this->repl, $s);
        if (!empty($this->spaces)) $s = preg_replace($this->spaces, ' ', $s);
        if (!empty($this->trims)) $s = trim($s, $this->trims);
        if ($this->use_normalizer && !empty($this->norm_form))
            $s = \Normalizer::normalize($s, 
                $this->norm_forms[$this->norm_form]);
        return $s;
    }
    /**
     * Escape filtered string: prepare string to re-filter again (for edit, ets).
     * $s: (string) source string;
     * $opt: same options as for $this->setOptions($opt)->filter($s); 
     * Return prepared string.
     *
     * Note: This function do not use normalization or language specific processing,
     * so you do not need to set up language-specific and normalization settings.
     */
    public static function escape_filter($s, $opt) {
        if (!empty($opt)) {
            $pat = $repl = [];
            // restore shy and dashes
            if (!empty($opt['dash_patterns'])) {
                if (!empty($opt['simplify_dashes'])) {
                    $pat = [ "\xE2\x80\x92", "\xE2\x80\x95", "\xE2\xB8\xBA", "\xE2\xB8\xBB" ];
                    $repl= [ "--", "---", "---", "---" ];
                }
                $pat[] = "\xE2\x80\x94";
                $repl[]= '---';
                $pat[] = "\xE2\x80\x93";
                $repl[]= '--';
            }
            if (!empty($opt['shy_pattern'])) {
                $pat[] = "\xC2\xAD";
                $repl[]= '\-';
            }
            // escape ampersand
            if (!empty($opt['decode_entities'])) {
                $pat[] = '&';
                $repl[]= '&amp;';
                // backward replacement for & followed by space
                $pat[] = '&amp; ';
                $repl[]= '& ';
            }
            if (!empty($pat)) $s = str_replace($pat, $repl, $s);
        }
        return $s;
    }
}
