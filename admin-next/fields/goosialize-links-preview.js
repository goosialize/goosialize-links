(() => {
  'use strict';

  const TAG = window.__GRAV_FIELD_TAG;

  if (!TAG) {
    throw new Error('Admin2 did not provide a custom field tag.');
  }

  const I18N = window.__GRAV_I18N;

  function t(key, fallback) {
    try {
      const translated = I18N?.t?.(`ICU.PLUGIN_GOOSIALIZE_LINKS.${key}`);
      return translated && !translated.includes('PLUGIN_GOOSIALIZE_LINKS')
        ? translated
        : fallback;
    } catch {
      return fallback;
    }
  }

  function apiUrl(path) {
    const server = String(window.__GRAV_API_SERVER_URL || '').replace(/\/+$/, '');
    const prefix = String(window.__GRAV_API_PREFIX || '/api/v1')
      .replace(/^\/?/, '/')
      .replace(/\/+$/, '');
    return `${server}${prefix}${path}`;
  }

  class GoosializeLinksPreview extends HTMLElement {
    connectedCallback() {
      this.render();
      this.installLayout();
      this.load();
      this.installCollectionLabels();
      this.installSaveRefresh();
    }

    disconnectedCallback() {
      this.restoreFetch?.();
      this.collectionObserver?.disconnect();
    }

    render() {
      this.innerHTML = `
        <style>
          .gl-preview-card{border:1px solid var(--border);border-radius:.75rem;background:var(--card);padding:1rem}
          .gl-preview-toolbar{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem}
          .gl-preview-toolbar select,.gl-preview-toolbar button,.gl-preview-toolbar a{min-height:2.25rem;border:1px solid var(--border);border-radius:.375rem;background:var(--background);color:var(--foreground);padding:.4rem .7rem;font:inherit;text-decoration:none}
          .gl-preview-frame{display:block;width:100%;max-width:390px;height:680px;margin:auto;border:8px solid var(--foreground);border-radius:1.5rem;background:#fff}
          @media(min-width:1024px){[data-goosialize-links-editor-layout]{display:grid!important;grid-template-columns:minmax(280px,35%) minmax(0,65%);gap:1.5rem;align-items:start}[data-goosialize-links-editor-layout]>*{grid-column:2}[data-goosialize-links-editor-layout]>.gl-preview-host{grid-column:1;grid-row:1/span 30;position:sticky;top:5rem}}
          @media(max-width:1023px){.gl-preview-frame{height:560px}}
          @media(max-width:480px){.gl-preview-card{padding:.75rem}.gl-preview-frame{height:520px;border-width:4px;border-radius:1rem}}
        </style>
        <div class="gl-preview-card">
          <div class="gl-preview-toolbar">
            <label hidden data-language-label>${t('PREVIEW_LANGUAGE', 'Preview language')}</label>
            <select hidden data-language aria-label="${t('PREVIEW_LANGUAGE', 'Preview language')}"></select>
            <button type="button" data-refresh>${t('REFRESH_PREVIEW', 'Refresh Preview')}</button>
            <a data-open target="_blank" rel="noopener noreferrer">${t('OPEN_PUBLIC_PAGE', 'Open Public Page')}</a>
          </div>
          <iframe class="gl-preview-frame" data-frame title="${t('PREVIEW', 'Preview')}" sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
          <p data-status role="status">${t('PREVIEW_LOADING', 'Loading preview…')}</p>
        </div>`;

      this.frame = this.querySelector('[data-frame]');
      this.select = this.querySelector('[data-language]');
      this.openLink = this.querySelector('[data-open]');
      this.status = this.querySelector('[data-status]');
      this.querySelector('[data-refresh]').addEventListener('click', () => this.refresh());
      this.select.addEventListener('change', () => this.applyLanguage());
      this.frame.addEventListener('load', () => {
        this.status.textContent = t('PREVIEW_READY', 'Preview ready');
      });
    }

    installLayout() {
      let node = this;
      while (node.parentElement && node.parentElement.children.length < 4) {
        node = node.parentElement;
      }
      const layout = node.parentElement;
      if (!layout) return;
      node.classList.add('gl-preview-host');
      layout.dataset.goosializeLinksEditorLayout = '';
    }

    async load() {
      try {
        const response = await fetch(apiUrl('/goosialize-links/editor-preview'), {
          headers: {
            Accept: 'application/json',
            ...(window.__GRAV_API_TOKEN ? {'X-API-Token': window.__GRAV_API_TOKEN} : {}),
          },
          credentials: 'same-origin',
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        this.languages = Array.isArray(data.languages) ? data.languages : [];
        this.select.replaceChildren(...this.languages.map((language) => {
          const option = document.createElement('option');
          option.value = language.code;
          option.textContent = language.label;
          return option;
        }));
        if (this.languages.length > 1) {
          this.select.hidden = false;
          this.querySelector('[data-language-label]').hidden = false;
        }
        this.applyLanguage();
      } catch {
        this.status.textContent = t('PREVIEW_ERROR', 'Preview could not be loaded.');
      }
    }

    async installCollectionLabels() {
      const actionTypes = {
        website: 'Website', instagram: 'Instagram', facebook: 'Facebook',
        tiktok: 'TikTok', youtube: 'YouTube', linkedin: 'LinkedIn', x: 'X',
        email: 'Email', phone: 'Phone', whatsapp: 'WhatsApp',
      };

      try {
        const response = await fetch(apiUrl('/config/plugins/goosialize-links'), {
          headers: {
            Accept: 'application/json',
            ...(window.__GRAV_API_TOKEN ? {'X-API-Token': window.__GRAV_API_TOKEN} : {}),
          },
          credentials: 'same-origin',
        });
        if (!response.ok) return;
        const payload = await response.json();
        const config = payload.data || payload;
        this.collectionLabels = new Map();

        for (const action of config.actions || []) {
          const label = String(action.label || '').trim()
            || actionTypes[String(action.type || '').toLowerCase()]
            || t('NEW_ACTION', 'New Action');
          if (action.id) this.collectionLabels.set(String(action.id), label);
        }
        for (const link of config.links || []) {
          const label = String(link.title || '').trim()
            || String(link.url || '').trim()
            || t('NEW_LINK', 'New Link');
          if (link.id) this.collectionLabels.set(String(link.id), label);
        }

        const synchronize = () => {
          const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
          let node;
          while ((node = walker.nextNode())) {
            const id = String(node.nodeValue || '').trim();
            const label = this.collectionLabels.get(id);
            if (label) node.nodeValue = node.nodeValue.replace(id, label);
          }
        };

        synchronize();
        this.collectionObserver = new MutationObserver(synchronize);
        this.collectionObserver.observe(document.body, {childList: true, subtree: true});
      } catch {
        // The form remains usable if presentation metadata cannot be loaded.
      }
    }

    selected() {
      return this.languages?.find((item) => item.code === this.select.value)
        || this.languages?.[0];
    }

    applyLanguage() {
      const selected = this.selected();
      if (!selected) return;
      const preview = new URL(selected.preview_path, window.location.origin);
      const publicUrl = new URL(selected.public_path, window.location.origin);
      if (preview.origin !== window.location.origin || publicUrl.origin !== window.location.origin) return;
      this.frame.src = preview.href;
      this.openLink.href = publicUrl.href;
    }

    refresh() {
      const selected = this.selected();
      if (!selected) return;
      const url = new URL(selected.preview_path, window.location.origin);
      url.searchParams.set('_preview_refresh', String(Date.now()));
      this.frame.src = url.href;
    }

    installSaveRefresh() {
      if (window.__GOOSIALIZE_LINKS_FETCH_WRAPPED) return;
      const original = window.fetch.bind(window);
      window.__GOOSIALIZE_LINKS_FETCH_WRAPPED = true;
      window.fetch = async (...args) => {
        const response = await original(...args);
        const request = args[0];
        const options = args[1] || {};
        const url = String(request?.url || request || '');
        const method = String(options.method || request?.method || 'GET').toUpperCase();
        if (response.ok && method === 'PATCH' && url.includes('/plugins/goosialize-links')) {
          document.querySelector(TAG)?.refresh();
        }
        return response;
      };
      this.restoreFetch = () => {
        window.fetch = original;
        window.__GOOSIALIZE_LINKS_FETCH_WRAPPED = false;
      };
    }
  }

  customElements.define(TAG, GoosializeLinksPreview);
})();
