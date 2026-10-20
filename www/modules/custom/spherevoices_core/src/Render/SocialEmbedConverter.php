<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Render;

use Drupal\Component\Utility\Html;

/**
 * Transforme les liens de posts sociaux collés dans le corps d'un article en embeds.
 *
 * Seuls les paragraphes qui ne contiennent QUE le lien (ou l'URL brute) sont
 * convertis : un lien au milieu d'une phrase, ou un lien dont le libellé est un
 * texte rédigé (« Voir notre post »), reste un simple lien.
 *
 * Réseaux pris en charge : X / Twitter, Instagram, TikTok, Facebook. Le HTML
 * produit est le balisage officiel de chaque réseau (blockquote / div) ; les
 * scripts sont chargés à la demande par js/social_embeds.js. Le lien reste dans
 * le balisage comme repli (script bloqué, consentement refusé, JS désactivé).
 *
 * Tout ce qui est injecté dans le HTML est reconstruit à partir de segments
 * validés (identifiants numériques / alphanumériques) puis échappé : l'URL
 * saisie n'est jamais recopiée telle quelle, sauf pour Facebook (hôte vérifié,
 * échappée).
 */
final class SocialEmbedConverter {

  /**
   * Sous-chaînes dont la présence déclenche l'analyse (sinon retour immédiat).
   */
  private const HOST_HINTS = [
    'twitter.com',
    'x.com',
    'instagram.com',
    'tiktok.com',
    'facebook.com',
    'fb.watch',
    'fb.com',
  ];

  /**
   * Convertit les paragraphes « lien seul » vers un post social en embeds.
   *
   * @param string $html
   *   HTML du corps (déjà filtré).
   *
   * @return string
   *   HTML avec les embeds ; identique à l'entrée s'il n'y a rien à convertir.
   */
  public static function convert(string $html): string {
    if ($html === '' || !self::mayContainSocialUrl($html)) {
      return $html;
    }
    $out = preg_replace_callback(
      '~<p\b[^>]*>(.*?)</p>~isu',
      static function (array $m): string {
        $url = self::extractStandaloneUrl($m[1]);
        if ($url === NULL) {
          return $m[0];
        }
        return self::buildEmbed($url) ?? $m[0];
      },
      $html
    );
    return is_string($out) ? $out : $html;
  }

  /**
   * Filtre rapide : le HTML mentionne-t-il un réseau pris en charge ?
   */
  private static function mayContainSocialUrl(string $html): bool {
    foreach (self::HOST_HINTS as $hint) {
      if (stripos($html, $hint) !== FALSE) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Texte brut d'un fragment : sans balises, NBSP et espaces superflus.
   */
  private static function plainText(string $inner): string {
    $text = preg_replace('~<br\s*/?>~i', ' ', $inner) ?? $inner;
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\xc2\xa0", "\xe2\x80\x8b", "\xef\xbb\xbf"], ' ', $text);
    return trim($text);
  }

  /**
   * Le texte ressemble-t-il à une URL / un nom de domaine (et non à un libellé) ?
   */
  private static function looksLikeUrlText(string $text): bool {
    return (bool) (preg_match('~^(?:https?://|www\.)\S+$~iu', $text)
      || preg_match('~^[a-z0-9-]+(?:\.[a-z0-9-]+)+/\S*$~iu', $text));
  }

  /**
   * URL d'un paragraphe composé uniquement d'un lien / d'une URL, sinon NULL.
   */
  private static function extractStandaloneUrl(string $inner): ?string {
    $plain = self::plainText($inner);
    if ($plain === '') {
      return NULL;
    }
    $anchors = preg_match_all('~<a\b~i', $inner);
    if ($anchors > 1) {
      return NULL;
    }
    if ($anchors === 1) {
      // Un seul lien, et le paragraphe n'est que ce lien (texte = une URL).
      if (!self::looksLikeUrlText($plain)
        || !preg_match('~<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1~isu', $inner, $h)) {
        return NULL;
      }
      return trim(html_entity_decode($h[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    return preg_match('~^https?://\S+$~iu', $plain) ? $plain : NULL;
  }

  /**
   * Balisage d'embed pour une URL, ou NULL si le réseau / le format n'est pas géré.
   */
  private static function buildEmbed(string $url): ?string {
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
      return NULL;
    }
    $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
    if (!in_array($scheme, ['http', 'https'], TRUE)) {
      return NULL;
    }
    $host = strtolower((string) $parts['host']);
    $host = (string) preg_replace('~^(?:www|m|mobile|web)\.~', '', $host);
    $path = (string) ($parts['path'] ?? '');
    $query = [];
    parse_str((string) ($parts['query'] ?? ''), $query);

    return match (TRUE) {
      $host === 'twitter.com', $host === 'x.com' => self::twitter($path),
      $host === 'instagram.com' => self::instagram($path),
      $host === 'tiktok.com', str_ends_with($host, '.tiktok.com') => self::tiktok($host, $path, strtolower((string) $parts['host'])),
      $host === 'facebook.com', $host === 'fb.com', $host === 'fb.watch' => self::facebook($host, $path, $query),
      default => NULL,
    };
  }

  /**
   * Enveloppe commune : conteneur typé lu par js/social_embeds.js.
   */
  private static function wrap(string $network, string $inner): string {
    return '<div class="sv-social-embed sv-social-embed--' . $network . '" data-sv-social="' . $network . '">'
      . $inner . '</div>';
  }

  /**
   * Lien de repli affiché tant que (ou si) l'embed n'est pas chargé.
   */
  private static function fallbackLink(string $href, string $label): string {
    return '<a href="' . Html::escape($href) . '" target="_blank" rel="noopener noreferrer">'
      . Html::escape($label) . '</a>';
  }

  /**
   * X / Twitter : …/{compte}/status/{id}, …/i/web/status/{id}.
   */
  private static function twitter(string $path): ?string {
    if (!preg_match('~^/(?:(?<user>[A-Za-z0-9_]{1,15})|i(?:/web)?)/status(?:es)?/(?<id>\d{5,25})(?:/|$)~', $path, $m)) {
      return NULL;
    }
    $user = ($m['user'] ?? '') !== '' ? $m['user'] : 'i';
    $href = 'https://twitter.com/' . $user . '/status/' . $m['id'];
    return self::wrap(
      'twitter',
      '<blockquote class="twitter-tweet" data-dnt="true" data-lang="fr">'
      . self::fallbackLink($href, (string) t('Voir le post sur X'))
      . '</blockquote>'
    );
  }

  /**
   * Instagram : /p/{code}, /reel/{code}, /tv/{code} (avec ou sans /{compte}).
   */
  private static function instagram(string $path): ?string {
    if (!preg_match('~^/(?:[A-Za-z0-9_.]+/)?(?<type>p|reels?|tv)/(?<code>[A-Za-z0-9_-]{5,})~', $path, $m)) {
      return NULL;
    }
    $type = str_starts_with($m['type'], 'reel') ? 'reel' : $m['type'];
    $href = 'https://www.instagram.com/' . $type . '/' . $m['code'] . '/';
    return self::wrap(
      'instagram',
      '<blockquote class="instagram-media" data-instgrm-permalink="' . Html::escape($href)
      . '?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14" data-instgrm-captioned>'
      . self::fallbackLink($href, (string) t('Voir la publication sur Instagram'))
      . '</blockquote>'
    );
  }

  /**
   * TikTok : /@{compte}/video/{id}, m.tiktok.com/v/{id}.html, ou lien court
   * (vm.tiktok.com/xxx, tiktok.com/t/xxx) résolu via l'oEmbed TikTok.
   */
  private static function tiktok(string $host, string $path, string $rawHost): ?string {
    $id = NULL;
    $cite = NULL;
    if ($host === 'tiktok.com' && preg_match('~^/@(?<user>[A-Za-z0-9_.]+)/video/(?<id>\d{8,25})~', $path, $m)) {
      $id = $m['id'];
      $cite = 'https://www.tiktok.com/@' . $m['user'] . '/video/' . $m['id'];
    }
    elseif ($host === 'tiktok.com' && preg_match('~^/v/(?<id>\d{8,25})(?:\.html)?$~', $path, $m)) {
      $id = $m['id'];
      $cite = 'https://www.tiktok.com/embed/v2/' . $m['id'];
    }
    elseif ((in_array($rawHost, ['vm.tiktok.com', 'vt.tiktok.com'], TRUE) && preg_match('~^/[A-Za-z0-9]{6,}/?$~', $path))
      || preg_match('~^/t/[A-Za-z0-9]{6,}/?$~', $path)) {
      // Lien court : l'identifiant de la vidéo n'est connu que de TikTok.
      $clean = 'https://' . $rawHost . rtrim($path, '/') . '/';
      $resolved = self::resolveTikTok($clean);
      if ($resolved !== NULL) {
        $id = $resolved['id'];
        $cite = $resolved['cite'];
      }
    }
    if ($id === NULL || $cite === NULL) {
      return NULL;
    }
    return self::wrap(
      'tiktok',
      '<blockquote class="tiktok-embed" cite="' . Html::escape($cite) . '" data-video-id="' . Html::escape($id)
      . '" style="max-width:605px;min-width:325px;"><section>'
      . self::fallbackLink($cite, (string) t('Voir la vidéo sur TikTok'))
      . '</section></blockquote>'
    );
  }

  /**
   * Résout un lien court TikTok via l'oEmbed public ; résultat mis en cache.
   *
   * @return array{id: string, cite: string}|null
   *   NULL si le lien ne peut pas être résolu (réseau, vidéo supprimée…) : le
   *   lien reste alors un simple lien. Les échecs sont mis en cache 10 minutes.
   */
  private static function resolveTikTok(string $url): ?array {
    $cid = 'spherevoices_core:social:tiktok:' . hash('sha256', $url);
    $cache = \Drupal::cache();
    if ($hit = $cache->get($cid)) {
      return $hit->data ?: NULL;
    }
    $data = [];
    try {
      $response = \Drupal::httpClient()->request('GET', 'https://www.tiktok.com/oembed', [
        'query' => ['url' => $url],
        'timeout' => 3,
        'connect_timeout' => 2,
        'headers' => ['Accept' => 'application/json'],
      ]);
      $json = json_decode((string) $response->getBody(), TRUE);
      if (is_array($json)) {
        // Format actuel : embed_product_id + iframe player/v1/{id} ; ancien
        // format : blockquote avec data-video-id.
        $embedHtml = (string) ($json['html'] ?? '');
        $id = (string) ($json['embed_product_id'] ?? '');
        if (!preg_match('~^\d{8,25}$~', $id)) {
          $id = '';
          if (preg_match('~/player/v1/(\d{8,25})~', $embedHtml, $im)
            || preg_match('~data-video-id="(\d{8,25})"~', $embedHtml, $im)) {
            $id = $im[1];
          }
        }
        if ($id !== '') {
          // L'oEmbed ne donne pas le compte : le lien de repli reste celui saisi.
          $data = ['id' => $id, 'cite' => $url];
        }
      }
    }
    catch (\Throwable $e) {
      // Réseau indisponible, timeout, vidéo introuvable : on garde le lien.
    }
    $cache->set($cid, $data, time() + ($data !== [] ? 30 * 86400 : 600));
    return $data !== [] ? $data : NULL;
  }

  /**
   * Facebook : publications (post/photo/permalink/share) et vidéos (reel/watch).
   *
   * @param array<string, mixed> $query
   *   Paramètres de l'URL d'origine.
   */
  private static function facebook(string $host, string $path, array $query): ?string {
    $isVideo = $host === 'fb.watch'
      || (bool) preg_match('~(?:/videos?/|^/watch/?$|/reels?/|^/share/[vr]/)~i', $path);
    $isPost = (bool) preg_match(
      '~(?:/posts/|/permalink\.php|/story\.php|/photos?(?:/|\.php)|/pfbid|^/share/p/|^/groups/[^/]+/(?:posts|permalink)/)~i',
      $path
    );
    if (!$isVideo && !$isPost) {
      // Profil, page, événement… : pas d'embed de publication possible.
      return NULL;
    }
    if (!preg_match('#^/[A-Za-z0-9._~%/+-]*$#', $path)) {
      return NULL;
    }
    // On ne conserve que les paramètres qui identifient réellement le contenu.
    $kept = [];
    foreach (['v', 'story_fbid', 'id', 'fbid', 'set'] as $key) {
      if (isset($query[$key]) && is_string($query[$key]) && preg_match('~^[A-Za-z0-9._:-]{1,80}$~', $query[$key])) {
        $kept[$key] = $query[$key];
      }
    }
    $base = $host === 'fb.watch' ? 'https://fb.watch' : 'https://www.facebook.com';
    $href = $base . $path . ($kept !== [] ? '?' . http_build_query($kept) : '');
    $cls = $isVideo ? 'fb-video' : 'fb-post';
    $attrs = $isVideo
      ? ' data-allowfullscreen="true" data-show-text="false"'
      : ' data-show-text="true"';
    $label = $isVideo ? (string) t('Voir la vidéo sur Facebook') : (string) t('Voir la publication sur Facebook');
    return self::wrap(
      'facebook',
      '<div class="' . $cls . '" data-href="' . Html::escape($href) . '" data-width="500"' . $attrs . '>'
      . '<blockquote cite="' . Html::escape($href) . '" class="fb-xfbml-parse-ignore">'
      . self::fallbackLink($href, $label)
      . '</blockquote></div>'
    );
  }

}
