/**
 * @file
 * Charge le widget TradingView sans passer par du JSON dans Twig (évite l’échappement HTML).
 */
(function (Drupal, once) {
  'use strict';

  var tapeConfig = JSON.stringify({
    symbols: [
      { proName: 'Euronext:PX1', title: 'CAC 40' },
      { proName: 'INDEX:DEU40', title: 'DAX' },
      { proName: 'FX_IDC:EURUSD', title: 'EUR/USD' },
      { proName: 'FOREXCOM:SPX500', title: 'S&P 500' }
    ],
    showSymbolLogo: true,
    colorTheme: 'light',
    isTransparent: true,
    displayMode: 'adaptive',
    locale: 'fr'
  });

  Drupal.behaviors.spherevoicesCoreBourseTicker = {
    attach: function (context) {
      once('spherevoices-bourse-ticker', '.js-bourse-ticker-mount', context).forEach(function (el) {
        var outer = el.querySelector('.tradingview-widget-container');
        var host = el.querySelector('.tradingview-widget-container__widget');
        if (!outer || !host || el.querySelector('script[data-tv-bourse]')) {
          return;
        }
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js';
        script.async = true;
        script.setAttribute('data-tv-bourse', '1');
        script.text = tapeConfig;
        outer.appendChild(script);
      });
    }
  };
})(Drupal, once);
