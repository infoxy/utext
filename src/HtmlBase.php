<?php
namespace infoxy\utext;
/**
 * Collection of static functions to helps HTML DOM manipulations
 */
class HtmlBase {
    
    // Save DOMElement content to string and vice versa ----
    /**
     * Export element contents to HTML string.
     * $e DOMElement to export
     * return HTML string.
     */
    public static function toText($e){
      $dom = $e->ownerDocument;
      $html = [];
      foreach ($e->childNodes as $n) {
        $html[] = $dom->saveHTML($n);
      }
      return implode('',$html);
    }
    /**
     * Create new DOMDocument from content-only HTML string.
     * $s: html string without <html><header> and <body>
     * Return body DOMElement.
     */
    public static function toDom($s){
      $html = '<!doctype html><html><head><meta charset="utf-8"></head><body>'
       .$s.'</body></html>';
      $dom = new \DOMDocument;
      $dom->loadHTML($html);
      $body = $dom->getElementsByTagName('body')[0];
      return $body;
    }

    // Class manipulation ----------------------------------
    /**
     * Validate class string $s
     * Return true then $s contain only alphanumerics,'-','_' and spaces.
     */
    public static function classCheck($s) {
      return preg_match('/^[\w- ]$/', $s);
    }
    /**
     * Convert class attribute string $s to array of classes.
     */
    public static function classArray($s) {
      return array_filter(explode(' ', $s));
    }
    /** 
     * Regex pattern for class string or array of classes.
     * Pattern usage: preg_match($pat, $str) return true
     *   if $str contains any of pattern classes;
     */
    public static function classPat($classes) {
      if (!is_array($classes)) $classes = static::classArray($classes);
      if (empty($classes)) return '';
      $pat = [];
      foreach ($classes as $c) $pat[] = '(?:^| )'.$c.'(?: |$)';
      $pat = '/'.implode('|', $pat).'/';
      return $pat;
    }

    // DOM structure manipulation helpers ------------------
    /**
     * Strip tag, reattach children to it's parent
     * $e: parented DOMElement to strip
     * Return: (DOMNode) first reattached child 
     *    or NULL (no child or element don't have parent)
     */
    static public function tagStrip($e)
    {
      if (empty($e->parentNode)) return NULL;
      $frag = $e->ownerDocument->createDocumentFragment();
      $next = $e->firstChild;
      while ($next) {
        $n = $next;
        $next = $n->nextSibling;
        $frag->appendChild($n);
      }
      $child = $frag->firstChild;
      $e->parentNode->replaceChild($frag, $e);
      return $child;
    }
    /**
     * Wrap tag
     * $e: DOMElement to wrap
     * $tag: (string) wrapper tag name
     * Return: newly created DOMElement
     */
    static public function tagWrap($e, $tag)
    {
      $wrap = $e->ownerDocument->createElement($tag);
      if (!empty($e->parentNode)) {
        $wrap = $e->parentNode->insertBefore($wrap,$e);
        $e->parentNode->removeChild($e);
      }
      $wrap->appendChild($e);
      return $wrap;
    }
    /**
     * Replace element with specified tag and reattach children to it.
     *   id, class, lang, dir attributes are also copied to new element.
     * $e: DOMElement to replace
     * $tag: (string) new tag
     * Note: attributes copied as is
     * Return: newly created DOMElement
     */
    static public function tagReplace($e, $tag) 
    {
      $r = $e->ownerDocument->createElement($tag);
      if ($e->hasAttribute('id'))
        $r->setAttribute('id', $e->getAttribute('id'));
      if ($e->hasAttribute('class'))
        $r->setAttribute('class', $e->getAttribute('class'));
      if ($e->hasAttribute('lang'))
        $r->setAttribute('lang', $e->getAttribute('lang'));
      if ($e->hasAttribute('dir'))
        $r->setAttribute('dir', $e->getAttribute('dir'));
      while (!empty($e->firstChild)) {
        $r->appendChild($e->removeChild($e->firstChild));
      }
      if (!empty($e->parentNode)) {
        $e->parentNode->replaceChild($r, $e);
      }
      return $r;
    }
    /**
     * Wrap element's children with specified tag 
     * $e: DOMElement to wrap children
     * $tag: (string) wrapper tag name
     * Return: newly created DOMElement
     */
    static public function contentWrap($e, $tag)
    {
      $wrap = $e->ownerDocument->createElement($tag);
      $wrap = $e->insertBefore($wrap, $e->firstChild);
      $next = $wrap->nextSibling;
      while ($next) {
        $n = $next;
        $next = $n->nextSibling;
        $wrap->insertBefore($n);
      }
      return $wrap;
    }
    
}
