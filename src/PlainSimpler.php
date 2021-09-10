<?php

// http://unicode.org/charts/
// http://userguide.icu-project.org/transforms/general
// http://www.unicode.org/reports/tr35/tr35-39/tr35-general.html

namespace infoxy\utext;
class PlainSimpler {
    
  // Basic latin substitutions + cyrillic  short-i saver for ru 
  protected const PAT_BASE = [
    'Æ', 'æ', 'ẞ', 'ß', 'Œ', 'œ', 'Ø', 'ø', "N\u{303}", "n\u{303}",
    "И\u{306}","и\u{306}"
    ];
  protected const REP_BASE = [
    'Ae','ae','Ss','ss','Oe','oe','Oe','oe',  'Nn',       'nn',
    'Й',       'й'
    ];
  
  // Umlauts, Ao, O-slash replacements for  de, sv, da, no,nn,nb
  protected const PAT_NORDIC = ["A\u{30A}","a\u{30A}","A\u{308}","a\u{308}","O\u{308}","o\u{308}","U\u{308}","u\u{308}"];
  protected const REP_NORDIC = ['Aa',      'aa',      'Ae',      'ae',      'Oe',      'oe',      'Ue',      'ue'];

  // Regex pattern for combined diacritical marks
  protected const PREG_DIA = '/[\x{300}-\x{36F}\x{1AB0}-\x{1AFF}\x{1DC0}-\x{1DFF}\x{20D0}-\x{20FF}\x{FE20}-\x{FE2F}]+/u';

  
  /**
   * Constructor
   * Initialize transliterator
   */
//  public function __construct() { }

  public static function simplify($plain, $langid=''){
    $pat = self::PAT_BASE;
    $rep = self::REP_BASE;
    if (in_array($langid, ['de', 'sv', 'da', 'no', 'nn', 'nb'])) {
      $pat = array_merge($pat, self::PAT_NORDIC);
      $rep = array_merge($rep, self::REP_NORDIC);
    }
    // Decode ALL entities (include quotes) to plain chars => NFKD
    // => replace digraphs, save some diacritics
    // => remove other diacritics
    // => produce NFC
    $plain = html_entity_decode($plain, ENT_QUOTES|ENT_HTML5, 'UTF-8');
    $nfkd = \Normalizer::normalize($plain,\Normalizer::FORM_KD);
    $r = str_replace($pat,$rep,$nfkd);
    $r = preg_replace(self::PREG_DIA, '', $r);
    return \Normalizer::normalize($r,\Normalizer::FORM_C);
  }

}
