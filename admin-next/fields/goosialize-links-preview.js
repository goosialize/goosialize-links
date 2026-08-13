(() => {
  'use strict';

  const TAG = window.__GRAV_FIELD_TAG;

  if (!TAG) {
    throw new Error('Admin2 did not provide a custom field tag.');
  }

  const I18N = window.__GRAV_I18N;

  function t(key) {
    try {
      const translated = I18N?.t?.(`ICU.PLUGIN_GOOSIALIZE_LINKS.${key}`);
      const humanized = key.toLowerCase().replaceAll('_', ' ');
      return translated
        && !translated.includes('PLUGIN_GOOSIALIZE_LINKS')
        && translated.toLowerCase() !== humanized
        ? translated
        : '';
    } catch {
      return '';
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
      this.previewToken = [...crypto.getRandomValues(new Uint8Array(16))]
        .map((value) => value.toString(16).padStart(2, '0')).join('');
      this.render();
      this.installLayout();
      this.load().then(async () => {
        await this.installCollectionLabels();
        this.refineAdminPresentation();
        this.installRealtimePreview();
      });
      this.installSaveRefresh();
    }

    disconnectedCallback() {
      this.restoreFetch?.();
      this.collectionObserver?.disconnect();
      this.formObserver?.disconnect();
      clearTimeout(this.previewDebounce);
      this.removeRealtimeListeners?.();
      this.clearPreviewState(true);
    }

    render() {
      this.innerHTML = `
        <style>
          .gl-preview-host{display:block!important}
          .gl-preview-host>div:first-child{display:none}
          .gl-preview-frame{display:block;width:100%;max-width:420px;height:700px;margin-inline:auto;background:#fff}
          .gl-preview-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.375rem}
          .gl-preview-status{display:inline-flex;align-items:center;gap:.4rem;width:fit-content;border:1px solid var(--border);border-radius:9999px;background:var(--muted);padding:.25rem .625rem}
          .gl-preview-status::before{content:"";width:.45rem;height:.45rem;border-radius:9999px;background:#22c55e;flex:none}
          .gl-preview-status[data-state="dirty"]::before,.gl-preview-status[data-state="loading"]::before{background:#f59e0b}
          .gl-preview-status[data-state="error"]::before{background:#ef4444}
          @media(min-width:1024px){[data-goosialize-links-editor-layout]{display:grid!important;grid-template-columns:minmax(340px,35fr) minmax(0,65fr);gap:1.25rem;align-items:start}[data-goosialize-links-editor-layout]>*{grid-column:2}[data-goosialize-links-editor-layout]>.gl-preview-host{grid-column:1;grid-row:1/span 30;position:sticky;top:5.5rem;max-height:calc(100vh - 6.5rem);overflow:auto;scrollbar-width:thin}}
          @media(max-width:1023px){.gl-preview-frame{height:620px}}
          @media(max-width:480px){.gl-preview-frame{height:540px}}
        </style>
        <section class="rounded-lg border border-border bg-card p-4 shadow-sm space-y-4" aria-labelledby="gl-preview-title">
          <header class="space-y-1">
            <h2 id="gl-preview-title" class="text-sm font-semibold text-foreground" data-i18n="LIVE_PREVIEW">${t('LIVE_PREVIEW')}</h2>
            <p class="text-xs text-muted-foreground" data-i18n="PREVIEW_HELP">${t('PREVIEW_HELP')}</p>
          </header>
          <div class="space-y-1.5" hidden data-language-field>
            <label class="text-sm font-medium text-foreground" for="gl-preview-language" data-i18n="PREVIEW_LANGUAGE">${t('PREVIEW_LANGUAGE')}</label>
            <select class="flex h-9 w-full rounded-md border border-border bg-background px-3 py-1 text-sm text-foreground shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" id="gl-preview-language" data-language></select>
          </div>
          <div class="gl-preview-toolbar">
            <button class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-border bg-background px-2.5 text-xs font-medium text-foreground shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" type="button" data-refresh><i class="fa-solid fa-rotate" aria-hidden="true"></i><span data-i18n="REFRESH_PREVIEW">${t('REFRESH_PREVIEW')}</span></button>
            <a class="inline-flex h-8 items-center justify-center gap-2 whitespace-nowrap rounded-md border border-border bg-background px-3 text-xs font-medium text-foreground no-underline shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" data-open target="_blank" rel="noopener noreferrer">
              <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
              <span data-i18n="OPEN_PUBLIC_PAGE">${t('OPEN_PUBLIC_PAGE')}</span>
            </a>
          </div>
          <div class="overflow-hidden rounded-md border border-border bg-muted/30 p-2">
            <iframe class="gl-preview-frame rounded-md border-0" data-frame title="${t('PREVIEW')}" sandbox="allow-same-origin allow-scripts allow-forms"></iframe>
          </div>
          <p class="gl-preview-status text-xs text-muted-foreground" data-status data-state="loading" role="status" data-i18n="PREVIEW_LOADING">${t('PREVIEW_LOADING')}</p>
        </section>`;

      this.frame = this.querySelector('[data-frame]');
      this.select = this.querySelector('[data-language]');
      this.openLink = this.querySelector('[data-open]');
      this.status = this.querySelector('[data-status]');
      this.querySelector('[data-refresh]').addEventListener('click', () => this.refresh());
      this.select.addEventListener('change', () => this.applyLanguage());
      this.frame.addEventListener('load', () => {
        this.setStatus(
          this.hasUnsavedPreview ? 'PREVIEW_UNSAVED' : 'PREVIEW_READY',
          this.hasUnsavedPreview ? 'dirty' : 'ready'
        );
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
        if (/^[a-f0-9]{32}$/.test(String(data.token || ''))) {
          this.previewToken = data.token;
        }
        this.translations = data.translations || {};
        this.applyTranslations();
        this.languages = Array.isArray(data.languages) ? data.languages : [];
        this.select.replaceChildren(...this.languages.map((language) => {
          const option = document.createElement('option');
          option.value = language.code;
          option.textContent = language.label;
          return option;
        }));
        if (this.languages.length > 1) {
          this.querySelector('[data-language-field]').hidden = false;
        }
        this.applyLanguage();
      } catch {
        this.setStatus('PREVIEW_ERROR', 'error');
      }
    }

    setStatus(key, state) {
      this.status.textContent = this.translate(key);
      this.status.dataset.state = state;
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
        this.savedConfig = structuredClone(config);
        this.draftConfig = structuredClone(config);
        this.collectionLabels = new Map();
        this.languageLabels = new Map(
          (this.languages || []).map((language) => [language.code, language.label])
        );

        for (const action of config.actions || []) {
          const label = String(action.label || '').trim()
            || actionTypes[String(action.type || '').toLowerCase()]
            || this.translate('NEW_ACTION');
          if (action.id) this.collectionLabels.set(String(action.id), label);
        }
        for (const link of config.links || []) {
          const label = String(link.title || '').trim()
            || String(link.url || '').trim()
            || this.translate('NEW_LINK');
          if (link.id) this.collectionLabels.set(String(link.id), label);
        }

        const synchronize = () => {
          const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
          let node;
          while ((node = walker.nextNode())) {
            const id = String(node.nodeValue || '').trim();
            const label = this.collectionLabels.get(id);
            const languageLabel = this.languageLabels.get(id.toLowerCase());
            if (label) node.nodeValue = node.nodeValue.replace(id, label);
            else if (languageLabel && id === node.nodeValue.trim()) {
              node.nodeValue = node.nodeValue.replace(id, languageLabel);
            }
          }
          this.updateSectionPresentation(config, actionTypes);
        };

        synchronize();
        this.collectionObserver = new MutationObserver(synchronize);
        this.collectionObserver.observe(document.body, {childList: true, subtree: true});
      } catch {
        // The form remains usable if presentation metadata cannot be loaded.
      }
    }

    updateSectionPresentation(config, actionTypes) {
      const fieldsets = [...document.querySelectorAll('.rounded-xl')];
      const findSection = (label) => fieldsets.find((item) =>
        String(item.innerText || '').trim().startsWith(label));
      const actions = Array.isArray(this.draftConfig?.actions) ? this.draftConfig.actions : (config.actions || []);
      const links = Array.isArray(this.draftConfig?.links) ? this.draftConfig.links : (config.links || []);
      const enabled = (value) => value !== false && value !== 0 && value !== '0';
      const setHeading = (section, value, marker) => {
        if (!section) return;
        const heading = [...section.querySelectorAll('h2,h3,h4,legend')].find((item) =>
          String(item.textContent || '').trim().startsWith(marker));
        const label = heading && [...heading.querySelectorAll('span')].find((item) =>
          String(item.textContent || '').trim().startsWith(marker) && !item.querySelector('svg'));
        if (label && label.textContent !== value) label.textContent = value;
      };
      const actionSection = findSection(this.translate('ACTIONS_SECTION')) || findSection('Actions');
      const linkSection = findSection(this.translate('LINKS_SECTION')) || findSection('Links');
      setHeading(actionSection, `${this.translate('ACTIONS_SECTION')} · ${actions.length}`, this.translate('ACTIONS_SECTION'));
      setHeading(linkSection, `${this.translate('LINKS_SECTION')} · ${links.filter((item) => enabled(item.enabled)).length} / 8`, this.translate('LINKS_SECTION'));

      const summaries = (section, items, formatter) => {
        if (!section) return;
        const candidates = [...section.querySelectorAll('[data-list-row] > div:first-child > span.flex-1')];
        candidates.slice(0, items.length).forEach((item, index) => {
          const summary = formatter(items[index]);
          if (item.textContent !== summary) item.textContent = summary;
        });
      };
      summaries(actionSection, actions, (action) => {
        const label = String(action.label || '').trim() || actionTypes[String(action.type || '').toLowerCase()] || this.translate('NEW_ACTION');
        const type = actionTypes[String(action.type || '').toLowerCase()] || String(action.type || '');
        return `${label} · ${type} · ${this.translate(enabled(action.enabled) ? 'ENABLED_SUMMARY' : 'DISABLED_SUMMARY')}`;
      });
      summaries(linkSection, links, (link) => {
        let destination = String(link.url || '').trim();
        try { destination = new URL(destination).hostname || destination; } catch { /* Keep safe display value. */ }
        const parts = [String(link.title || '').trim() || this.translate('NEW_LINK'), destination];
        parts.push(this.translate(enabled(link.enabled) ? 'ENABLED_SUMMARY' : 'DISABLED_SUMMARY'));
        if (enabled(link.new_tab)) parts.push(this.translate('NEW_TAB_SUMMARY'));
        return parts.filter(Boolean).join(' · ');
      });
    }

    refineAdminPresentation() {
      const notice = [...document.querySelectorAll('p,div')].find((item) =>
        String(item.textContent || '').trim() === this.translate('NATIVE_PAGE_NOTICE_HELP'));
      if (notice && !notice.querySelector('[data-manage-public-page]')) {
        const link = document.createElement('a');
        link.dataset.managePublicPage = '';
        link.href = `${String(window.__GRAV_ADMIN_BASE || '/admin').replace(/\/$/, '')}/pages/bio`;
        link.className = 'inline-flex items-center gap-1 font-medium underline underline-offset-4';
        link.textContent = this.translate('MANAGE_PUBLIC_PAGE');
        notice.append(document.createTextNode(' '), link);
      }
      const metadataCard = [...document.querySelectorAll('.rounded-xl.border')].find((item) => {
        const content = String(item.textContent || '');
        return content.includes('Goosialize Ltd') && content.includes('Documentation');
      });
      if (metadataCard) {
        metadataCard.classList.remove('p-5', 'p-6');
        metadataCard.classList.add('p-3');
        metadataCard.querySelectorAll('.mb-4,.mt-4').forEach((item) => {
          item.classList.remove('mb-4', 'mt-4');
          item.classList.add('mb-2');
        });
      }
      const form = this.closest('form') || document.querySelector('form');
      if (form) {
        form.classList.add('space-y-3');
        form.querySelectorAll('.rounded-xl').forEach((section) => section.classList.add('shadow-sm'));
      }
    }

    selected() {
      return this.languages?.find((item) => item.code === this.select.value)
        || this.languages?.[0];
    }

    translate(key) {
      return this.translations?.[key] || t(key);
    }

    applyTranslations() {
      for (const element of this.querySelectorAll('[data-i18n]')) {
        element.textContent = this.translate(element.dataset.i18n);
      }
      this.frame.title = this.translate('PREVIEW');
    }

    applyLanguage() {
      const selected = this.selected();
      if (!selected) return;
      const preview = new URL(selected.preview_path, window.location.origin);
      const publicUrl = new URL(selected.public_path, window.location.origin);
      if (preview.origin !== window.location.origin || publicUrl.origin !== window.location.origin) return;
      preview.searchParams.set('goosialize-links-preview-token', this.previewToken);
      this.frame.src = preview.href;
      this.openLink.href = publicUrl.href;
    }

    refresh() {
      const selected = this.selected();
      if (!selected) return;
      const url = new URL(selected.preview_path, window.location.origin);
      url.searchParams.set('goosialize-links-preview-token', this.previewToken);
      url.searchParams.set('_preview_refresh', String(Date.now()));
      this.setStatus('PREVIEW_REFRESHING', 'loading');
      this.frame.src = url.href;
    }

    installSaveRefresh() {
      if (window.__GOOSIALIZE_LINKS_FETCH_WRAPPED) return;
      const original = window.fetch.bind(window);
      const configUrl = new URL(apiUrl('/config/plugins/goosialize-links'), window.location.origin);
      window.__GOOSIALIZE_LINKS_FETCH_WRAPPED = true;
      window.fetch = async (...args) => {
        const response = await original(...args);
        const request = args[0];
        const options = args[1] || {};
        const url = String(request?.url || request || '');
        const method = String(options.method || request?.method || 'GET').toUpperCase();
        let requestUrl;
        try {
          requestUrl = new URL(url, window.location.origin);
        } catch {
          return response;
        }
        const isConfigSave = method === 'PATCH'
          && requestUrl.origin === configUrl.origin
          && requestUrl.pathname === configUrl.pathname;
        if (response.ok && isConfigSave) {
          const preview = document.querySelector(TAG);
          await preview?.clearPreviewState();
          if (preview) {
            preview.hasUnsavedPreview = false;
            preview.setStatus('PREVIEW_READY', 'ready');
            await preview.installCollectionLabels();
            preview.applyLanguage();
          }
        }
        if (response.status === 422 && isConfigSave) {
          try {
            const payload = await response.clone().json();
            const fieldMessage = Array.isArray(payload?.errors)
              ? payload.errors.find((error) => typeof error?.message === 'string')?.message
              : '';
            const message = String(fieldMessage || payload?.detail || '').trim();
            if (message) window.__GRAV_TOAST?.error?.(message, {duration: 0});
          } catch {
            // Preserve Admin2's existing generic failure handling for malformed responses.
          }
        }
        return response;
      };
      this.restoreFetch = () => {
        window.fetch = original;
        window.__GOOSIALIZE_LINKS_FETCH_WRAPPED = false;
      };
    }

    installRealtimePreview() {
      const schedule = (event) => {
        if (event.target instanceof HTMLInputElement && event.target.type === 'file') {
          this.setStatus('PREVIEW_IMAGE_AFTER_SAVE', 'dirty');
          return;
        }
        const path = this.fieldPath(event.target);
        if (!path) return;
        this.setDraftValue(path, this.fieldValue(event.target));
        if (this.savedConfig) this.updateSectionPresentation(this.savedConfig, {
          website: 'Website', instagram: 'Instagram', facebook: 'Facebook',
          tiktok: 'TikTok', youtube: 'YouTube', linkedin: 'LinkedIn', x: 'X',
          email: 'Email', phone: 'Phone', whatsapp: 'WhatsApp',
        });
        clearTimeout(this.previewDebounce);
        this.setStatus('PREVIEW_UPDATING', 'loading');
        this.previewDebounce = setTimeout(() => this.updatePreview(), 350);
      };
      document.addEventListener('input', schedule, true);
      document.addEventListener('change', schedule, true);
      this.removeRealtimeListeners = () => {
        document.removeEventListener('input', schedule, true);
        document.removeEventListener('change', schedule, true);
      };
    }

    fieldPath(field) {
      if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) return null;
      const raw = String(field.name || field.getAttribute('name') || '');
      const parts = [...raw.matchAll(/([^.[\]]+)/g)].map((match) => match[1]);
      const start = parts.findIndex((part) => ['profile', 'appearance', 'actions', 'links'].includes(part));
      if (start < 0) return this.nativeFieldPath(field);
      const path = parts.slice(start).map((part) => /^\d+$/.test(part) ? Number(part) : part);
      const key = path.at(-1);
      const allowed = path[0] === 'appearance' && ['theme', 'accent', 'button_shape'].includes(key)
        || path[0] === 'profile' && key === 'website_url'
        || path[0] === 'actions' && ['id', 'enabled', 'type', 'value'].includes(key)
        || path[0] === 'links' && ['id', 'enabled', 'url', 'new_tab'].includes(key);
      return allowed ? path : null;
    }

    nativeFieldPath(field) {
      if (field === this.select) return null;
      if (field instanceof HTMLSelectElement) {
        const values = [...field.options].map((option) => option.value);
        const same = (expected) => expected.length === values.length
          && expected.every((value) => values.includes(value));
        if (same(['light', 'dark', 'sunrise'])) return ['appearance', 'theme'];
        if (same(['yellow', 'blue', 'coral', 'green', 'purple'])) return ['appearance', 'accent'];
        if (same(['square', 'rounded', 'pill'])) return ['appearance', 'button_shape'];
        if (values.includes('website') && values.includes('instagram') && values.includes('email')) {
          const section = this.fieldSection(field, 'Actions');
          const controls = section ? [...section.querySelectorAll('select')].filter((item) =>
            [...item.options].some((option) => option.value === 'instagram')) : [];
          const index = controls.indexOf(field);
          return index >= 0 ? ['actions', index, 'type'] : null;
        }
      }
      if (field instanceof HTMLInputElement && field.type === 'url') {
        const actionSection = this.fieldSection(field, 'Actions');
        if (actionSection) {
          const controls = [...actionSection.querySelectorAll('input[type="url"],input[type="email"],input[type="tel"],input[type="text"]')];
          const index = controls.indexOf(field);
          return index >= 0 ? ['actions', index, 'value'] : null;
        }
        const linkSection = this.fieldSection(field, 'Links');
        if (linkSection) {
          const index = [...linkSection.querySelectorAll('input[type="url"]')].indexOf(field);
          return index >= 0 ? ['links', index, 'url'] : null;
        }
        return ['profile', 'website_url'];
      }
      return null;
    }

    fieldSection(field, title) {
      let node = field.parentElement;
      while (node && node !== document.body) {
        if (node.classList.contains('rounded-xl') && String(node.innerText || '').trim().startsWith(title)) return node;
        node = node.parentElement;
      }
      return null;
    }

    fieldValue(field) {
      if (field instanceof HTMLInputElement && field.type === 'checkbox') return field.checked;
      if (field instanceof HTMLInputElement && field.type === 'radio') return field.checked ? field.value : undefined;
      if (field.value === 'true' || field.value === '1') return true;
      if (field.value === 'false' || field.value === '0') return false;
      return field.value;
    }

    setDraftValue(path, value) {
      if (value === undefined || !this.draftConfig) return;
      let target = this.draftConfig;
      for (let index = 0; index < path.length - 1; index++) {
        const key = path[index];
        const next = path[index + 1];
        if (target[key] == null) target[key] = typeof next === 'number' ? [] : {};
        target = target[key];
      }
      target[path.at(-1)] = value;
    }

    previewPayload() {
      const pick = (source, keys) => Object.fromEntries(keys
        .filter((key) => Object.hasOwn(source || {}, key))
        .map((key) => [key, source[key]]));
      return {
        profile: pick(this.draftConfig?.profile, ['website_url']),
        appearance: pick(this.draftConfig?.appearance, ['theme', 'accent', 'button_shape']),
        actions: (this.draftConfig?.actions || []).map((item) => pick(item, ['id', 'enabled', 'type', 'value'])),
        links: (this.draftConfig?.links || []).map((item) => pick(item, ['id', 'enabled', 'url', 'new_tab'])),
      };
    }

    async updatePreview() {
      try {
        const response = await fetch(apiUrl('/goosialize-links/editor-preview/state'), {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(window.__GRAV_API_TOKEN ? {'X-API-Token': window.__GRAV_API_TOKEN} : {}),
          },
          credentials: 'same-origin',
          body: JSON.stringify({token: this.previewToken, config: this.previewPayload()}),
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        this.hasUnsavedPreview = true;
        this.applyLanguage();
      } catch {
        this.setStatus('PREVIEW_ERROR', 'error');
      }
    }

    async clearPreviewState(keepalive = false) {
      try {
        await fetch(apiUrl(`/goosialize-links/editor-preview/state?token=${this.previewToken}`), {
          method: 'DELETE',
          headers: {
            Accept: 'application/json',
            ...(window.__GRAV_API_TOKEN ? {'X-API-Token': window.__GRAV_API_TOKEN} : {}),
          },
          credentials: 'same-origin',
          keepalive,
        });
      } catch {
        // The random token becomes unreachable after navigation even if cleanup cannot complete.
      }
    }
  }

  customElements.define(TAG, GoosializeLinksPreview);
})();
