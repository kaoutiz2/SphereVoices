/**
 * @file
 * Bandeau alterné : indices TradingView (30 s) puis capitales mondiales (30 s).
 *
 * Capitales : heure locale via Intl.DateTimeFormat, température via Open-Meteo
 * (API gratuite, sans clé).
 */
(function (Drupal, once) {
  'use strict';

  // Capitales affichées avec leur position géographique et leur fuseau horaire.
  var CITIES = [
    { name: 'Paris',    lat: 48.8566,  lon:   2.3522,  tz: 'Europe/Paris'    },
    { name: 'New York', lat: 40.7128,  lon: -74.0060,  tz: 'America/New_York' },
    { name: 'Londres',  lat: 51.5074,  lon:  -0.1278,  tz: 'Europe/London'   },
    { name: 'Moscou',   lat: 55.7558,  lon:  37.6173,  tz: 'Europe/Moscow'   },
    { name: 'Dubaï',    lat: 25.2048,  lon:  55.2708,  tz: 'Asia/Dubai'      },
    { name: 'Pékin',    lat: 39.9042,  lon: 116.4074,  tz: 'Asia/Shanghai'   },
    { name: 'Tokyo',    lat: 35.6762,  lon: 139.6503,  tz: 'Asia/Tokyo'      },
  ];

  var SWITCH_MS = 30000;  // 30 s entre bourse et capitales
  var TEMP_TTL  = 600000; // 10 min entre deux mises à jour météo

  var tapeConfig = JSON.stringify({
    symbols: [
      { proName: 'Euronext:PX1',    title: 'CAC 40'  },
      { proName: 'INDEX:DEU40',     title: 'DAX'      },
      { proName: 'FX_IDC:EURUSD',   title: 'EUR/USD'  },
      { proName: 'FOREXCOM:SPX500', title: 'S&P 500'  }
    ],
    showSymbolLogo: true,
    colorTheme:    'light',
    isTransparent: true,
    displayMode:   'adaptive',
    locale:        'fr'
  });

  Drupal.behaviors.spherevoicesCoreBourseTicker = {
    attach: function (context) {
      once('spherevoices-bourse-ticker', '.js-bourse-ticker-mount', context).forEach(function (el) {
        initTradingView(el);
        initCapitals(el);
        startAlternating(el);
      });
    }
  };

  /* ------------------------------------------------------------------ */
  /* TradingView                                                          */
  /* ------------------------------------------------------------------ */
  function initTradingView(el) {
    var outer = el.querySelector('.tradingview-widget-container');
    var host  = el.querySelector('.tradingview-widget-container__widget');
    if (!outer || !host || el.querySelector('script[data-tv-bourse]')) {
      return;
    }
    var s = document.createElement('script');
    s.type  = 'text/javascript';
    s.src   = 'https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js';
    s.async = true;
    s.setAttribute('data-tv-bourse', '1');
    s.text  = tapeConfig;
    outer.appendChild(s);
  }

  /* ------------------------------------------------------------------ */
  /* Panneau capitales                                                    */
  /* ------------------------------------------------------------------ */
  function initCapitals(el) {
    var inner = el.querySelector('.js-capitals-inner');
    if (!inner) return;

    buildDOM(inner);
    fetchTemps(inner);

    // Horloge : mise à jour toutes les secondes.
    setInterval(function () { tickTimes(inner); }, 1000);
    // Météo : rafraîchissement toutes les 10 min.
    setInterval(function () { fetchTemps(inner); }, TEMP_TTL);
  }

  /** Construit le DOM des capitales (deux passes identiques pour la boucle CSS). */
  function buildDOM(inner) {
    var pass = CITIES.map(function (c) {
      return '<span class="capital-item" data-city="' + escAttr(c.name) + '" data-tz="' + escAttr(c.tz) + '">'
        + '<span class="capital-item__city">' + escHTML(c.name) + '</span>'
        + '<span class="capital-item__time js-capital-time"></span>'
        + '<span class="capital-item__temp js-capital-temp">--°</span>'
        + '</span>';
    }).join('');

    inner.innerHTML =
      '<div class="capitals-pass">' + pass + '</div>'
      + '<div class="capitals-pass" aria-hidden="true">' + pass + '</div>';

    tickTimes(inner);
  }

  /** Met à jour l'heure affichée pour chaque capitale. */
  function tickTimes(inner) {
    var now = new Date();
    inner.querySelectorAll('.capital-item[data-tz]').forEach(function (item) {
      var el = item.querySelector('.js-capital-time');
      if (!el) return;
      el.textContent = new Intl.DateTimeFormat('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: item.getAttribute('data-tz')
      }).format(now);
    });
  }

  /** Récupère la température courante pour chaque ville (Open-Meteo, sans clé). */
  function fetchTemps(inner) {
    CITIES.forEach(function (city) {
      fetch(
        'https://api.open-meteo.com/v1/forecast'
        + '?latitude=' + city.lat
        + '&longitude=' + city.lon
        + '&current_weather=true'
      )
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var t = data && data.current_weather && data.current_weather.temperature;
          if (t === null || t === undefined) return;
          var str = Math.round(t) + '°';
          inner.querySelectorAll(
            '.capital-item[data-city="' + escAttr(city.name) + '"] .js-capital-temp'
          ).forEach(function (el) {
            el.textContent = str;
          });
        })
        .catch(function () {});
    });
  }

  /* ------------------------------------------------------------------ */
  /* Alternance bourse ↔ capitales                                        */
  /* ------------------------------------------------------------------ */
  function startAlternating(el) {
    var boursePanel   = el.querySelector('.js-ticker-bourse');
    var capitalsPanel = el.querySelector('.js-ticker-capitals');
    if (!boursePanel || !capitalsPanel) return;

    var showBourse = true;

    setInterval(function () {
      showBourse = !showBourse;
      boursePanel.classList.toggle('is-active', showBourse);
      capitalsPanel.classList.toggle('is-active', !showBourse);
    }, SWITCH_MS);
  }

  /* ------------------------------------------------------------------ */
  /* Helpers                                                              */
  /* ------------------------------------------------------------------ */
  function escAttr(s) {
    return String(s).replace(/["&<>]/g, function (c) {
      return { '"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c];
    });
  }

  function escHTML(s) {
    return String(s).replace(/[&<>]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c];
    });
  }

})(Drupal, once);
