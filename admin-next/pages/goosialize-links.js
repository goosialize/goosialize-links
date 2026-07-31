(() => {
  'use strict';

  const TAG = window.__GRAV_PAGE_TAG;

  if (!TAG) {
    throw new Error(
      'Admin2 did not provide a page component tag.'
    );
  }

  function apiUrl(path) {
    const server = String(
      window.__GRAV_API_SERVER_URL || ''
    ).replace(/\/+$/, '');

    const prefix = String(
      window.__GRAV_API_PREFIX || '/api/v1'
    )
      .replace(/^\/?/, '/')
      .replace(/\/+$/, '');

    const suffix = String(path || '').startsWith('/')
      ? String(path)
      : `/${String(path || '')}`;

    return `${server}${prefix}${suffix}`;
  }

  function apiHeaders() {
    const headers = {
      Accept: 'application/json',
    };

    if (window.__GRAV_API_TOKEN) {
      headers['X-API-Token'] =
        window.__GRAV_API_TOKEN;
    }

    return headers;
  }

  const REQUIRED_FIELDS = [
    'qr_id',
    'tracked_url',
    'preview_url',
    'png_download_url',
    'svg_download_url',
    'qr_visits',
  ];

  function createElement(tag, text = '') {
    const element = document.createElement(tag);

    if (text !== '') {
      element.textContent = text;
    }

    return element;
  }

  function sameOriginUrl(value) {
    const url = new URL(
      String(value || ''),
      window.location.origin
    );

    if (url.origin !== window.location.origin) {
      throw new Error('A same-origin QR URL was expected.');
    }

    return url.href;
  }

  function responseMessage(payload, fallback) {
    const message = payload?.error?.message;

    return typeof message === 'string' && message.trim()
      ? message.trim()
      : fallback;
  }

  function validatePayload(payload) {
    if (!payload || typeof payload !== 'object') {
      throw new Error('Invalid QR administration response.');
    }

    const keys = Object.keys(payload).sort();
    const expected = [...REQUIRED_FIELDS].sort();

    if (
      keys.length !== expected.length ||
      keys.some((key, index) => key !== expected[index])
    ) {
      throw new Error('Unexpected QR administration fields.');
    }

    if (payload.qr_id !== 'qr_primary') {
      throw new Error('Unexpected QR identifier.');
    }

    if (
      !Number.isInteger(payload.qr_visits) ||
      payload.qr_visits < 0
    ) {
      throw new Error('Invalid QR visit count.');
    }

    if (
      payload.png_download_url !==
        '/goosialize-links/qr/download/png' ||
      payload.svg_download_url !==
        '/goosialize-links/qr/download/svg'
    ) {
      throw new Error('Unexpected QR download endpoint.');
    }

    return {
      ...payload,
      tracked_url: sameOriginUrl(payload.tracked_url),
      preview_url: sameOriginUrl(payload.preview_url),
    };
  }

  class GoosializeLinksQrPage extends HTMLElement {
    constructor() {
      super();
      this._controller = null;
    }

    connectedCallback() {
      this._load();
    }

    disconnectedCallback() {
      this._controller?.abort();
      this._controller = null;
    }

    _replace(node) {
      this.replaceChildren(node);
    }

    _renderStatus(message, isError = false) {
      const status = createElement('p', message);
      status.setAttribute('role', isError ? 'alert' : 'status');
      status.setAttribute('aria-live', 'polite');
      this._replace(status);
    }

    async _load() {
      this._controller?.abort();
      this._controller = new AbortController();
      this._renderStatus('Loading QR code...');

      try {
        const response = await fetch(
          apiUrl('/goosialize-links/qr'),
          {
            method: 'GET',
            headers: apiHeaders(),
            credentials: 'same-origin',
            cache: 'no-store',
            signal: this._controller.signal,
          }
        );

        let payload = {};

        try {
          payload = await response.json();
        } catch {
          payload = {};
        }

        if (!response.ok) {
          throw new Error(
            responseMessage(
              payload,
              'QR code data could not be loaded.'
            )
          );
        }

        this._renderData(validatePayload(payload));
      } catch (error) {
        if (error?.name === 'AbortError') {
          return;
        }

        this._renderStatus(
          error instanceof Error
            ? error.message
            : 'QR code data could not be loaded.',
          true
        );
      }
    }

    _renderData(data) {
      const section = createElement('section');
      section.setAttribute(
        'aria-labelledby',
        'goosialize-links-qr-title'
      );

      const title = createElement('h2', 'QR Code');
      title.id = 'goosialize-links-qr-title';
      section.append(title);

      section.append(
        createElement(
          'p',
          'This QR code opens the tracked public Goosialize Links page.'
        )
      );

      const image = document.createElement('img');
      image.src = data.preview_url;
      image.alt =
        'QR code for the tracked Goosialize Links URL.';
      image.width = 320;
      image.height = 320;
      section.append(image);

      const details = createElement('dl');

      const addDetail = (label, value) => {
        details.append(createElement('dt', label));
        const description = createElement('dd');
        description.append(createElement('code', String(value)));
        details.append(description);
      };

      addDetail('QR identifier', data.qr_id);
      addDetail('Tracked URL', data.tracked_url);
      addDetail('QR visits', data.qr_visits);
      section.append(details);

      this._replace(section);
    }


  }

  if (!customElements.get(TAG)) {
    customElements.define(TAG, GoosializeLinksQrPage);
  }
})();
