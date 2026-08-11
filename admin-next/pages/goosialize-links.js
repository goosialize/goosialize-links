(() => {
  'use strict';

  const TAG = window.__GRAV_PAGE_TAG;

  if (!TAG) {
    throw new Error(
      'Admin2 did not provide a page component tag.'
    );
  }

  const I18N = window.__GRAV_I18N;

  function t(key, fallback) {
    try {
      if (
        I18N &&
        typeof I18N.t === 'function'
      ) {
        const translated =
          I18N.t(key);

        if (
          typeof translated === 'string' &&
          translated.trim() !== '' &&
          translated !== key
        ) {
          const finalKey =
            key.split('.').pop() || '';

          const humanizedKey =
            finalKey
              .toLowerCase()
              .replaceAll('_', ' ')
              .trim();

          const humanizedValue =
            translated
              .toLowerCase()
              .trim();

          if (
            !translated.includes(
              'PLUGIN_GOOSIALIZE_LINKS'
            ) &&
            humanizedValue !== humanizedKey
          ) {
            return translated;
          }
        }
      }
    } catch {
      // Safe fallback below.
    }

    return fallback;
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

    const suffix =
      String(path || '').startsWith('/')
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

  function sameOriginUrl(value) {
    const url = new URL(
      String(value || ''),
      window.location.origin
    );

    if (
      url.origin !==
      window.location.origin
    ) {
      throw new Error(
        'A same-origin QR URL was expected.'
      );
    }

    return url.href;
  }

  function el(
    tag,
    text = '',
    attrs = {}
  ) {
    const node =
      document.createElement(tag);

    if (text !== '') {
      node.textContent =
        String(text);
    }

    for (
      const [key, value]
      of Object.entries(attrs)
    ) {
      if (
        value !== null &&
        value !== undefined
      ) {
        node.setAttribute(
          key,
          String(value)
        );
      }
    }

    return node;
  }

  function metricIcon(
    iconClass,
    tone,
    fallback
  ) {
    const box = el('div');

    box.style.display = 'flex';
    box.style.alignItems = 'center';
    box.style.justifyContent = 'center';

    box.style.width = '3rem';
    box.style.height = '3rem';
    box.style.flexShrink = '0';

    box.style.borderRadius =
      '0.75rem';

    const tones = {
      blue: [
        'color-mix(in srgb, #3b82f6 10%, transparent)',
        '#3b82f6',
      ],
      violet: [
        'color-mix(in srgb, #8b5cf6 10%, transparent)',
        '#8b5cf6',
      ],
      teal: [
        'color-mix(in srgb, #14b8a6 10%, transparent)',
        '#14b8a6',
      ],
      amber: [
        'color-mix(in srgb, #f59e0b 10%, transparent)',
        '#f59e0b',
      ],
    };

    const selected =
      tones[tone] || tones.blue;

    box.style.background =
      selected[0];

    box.style.color =
      selected[1];

    const icon =
      document.createElement('i');

    icon.className =
      `fa-solid ${iconClass}`;

    icon.setAttribute(
      'aria-hidden',
      'true'
    );

    icon.style.fontSize =
      '1.1rem';

    const fallbackNode =
      el(
        'span',
        fallback
      );

    fallbackNode.style.display =
      'none';

    fallbackNode.style.fontSize =
      '1.1rem';

    fallbackNode.style.fontWeight =
      '700';

    box.append(
      icon,
      fallbackNode
    );

    requestAnimationFrame(
      () => {
        const style =
          window.getComputedStyle(icon);

        const hasIcon =
          style &&
          style.fontFamily &&
          style.fontFamily.toLowerCase()
            .includes('awesome');

        if (!hasIcon) {
          icon.style.display =
            'none';

          fallbackNode.style.display =
            'inline';
        }
      }
    );

    return box;
  }


  function card(
    label,
    value,
    iconClass,
    tone,
    fallback
  ) {
    const article = el('article');

    article.style.display = 'flex';
    article.style.alignItems = 'center';

    article.style.gap =
      '0.875rem';

    article.style.padding =
      '0.7rem 0.9rem';

    article.style.border =
      '1px solid var(--border)';

    article.style.background =
      'var(--card)';

    article.style.borderRadius =
      '0.5rem';

    article.style.minWidth =
      '0';

    const content = el('div');

    content.style.minWidth =
      '0';

    const metric = el(
      'div',
      value
    );

    metric.style.fontSize =
      '1.75rem';

    metric.style.fontWeight =
      '600';

    metric.style.lineHeight =
      '1.2';

    metric.style.fontVariantNumeric =
      'tabular-nums';

    metric.style.color =
      'var(--foreground)';

    const caption = el(
      'div',
      label
    );

    caption.style.marginTop =
      '0.1rem';

    caption.style.fontSize =
      '0.72rem';

    caption.style.color =
      'var(--muted-foreground)';

    content.append(
      metric,
      caption
    );

    article.append(
      metricIcon(
        iconClass,
        tone,
        fallback
      ),
      content
    );

    return article;
  }


  function createStatsGrid(summary) {
    const grid = el('section');

    grid.style.display = 'grid';

    grid.style.gridTemplateColumns =
      'repeat(auto-fit, minmax(210px, 1fr))';

    grid.style.gap =
      '0.75rem';

    grid.style.marginBottom =
      '1rem';

    grid.append(
      card(
        t(
          'PLUGIN_GOOSIALIZE_LINKS.PAGE_VIEWS',
          'Page Views'
        ),
        summary.page_views,
        'fa-eye',
        'blue',
        '◉'
      ),
      card(
        t(
          'PLUGIN_GOOSIALIZE_LINKS.TOTAL_CLICKS',
          'Total Clicks'
        ),
        summary.total_clicks,
        'fa-hand-pointer',
        'violet',
        '↗'
      ),
      card(
        t(
          'PLUGIN_GOOSIALIZE_LINKS.QR_VISITS',
          'QR Visits'
        ),
        summary.qr_visits,
        'fa-qrcode',
        'teal',
        '▦'
      ),
      card(
        'CTR',
        `${Number(summary.ctr).toFixed(2)}%`,
        'fa-percent',
        'amber',
        '%'
      )
    );

    return grid;
  }


  function createChart(timeline) {
    const wrapper = el('section');

    wrapper.style.display = 'flex';
    wrapper.style.flexDirection = 'column';
    wrapper.style.minHeight = '270px';
    wrapper.style.padding = '1rem 1.1rem';

    wrapper.style.border =
      '1px solid var(--border)';

    wrapper.style.background =
      'var(--card)';

    wrapper.style.borderRadius =
      '0.5rem';

    wrapper.style.marginBottom =
      '1rem';

    const header = el('div');

    header.style.display = 'flex';
    header.style.alignItems = 'center';

    header.style.justifyContent =
      'space-between';

    header.style.gap = '1rem';

    header.style.marginBottom =
      '0.75rem';

    const headingBlock = el('div');

    const heading = el(
      'h2',
      t(
        'PLUGIN_GOOSIALIZE_LINKS.PERFORMANCE_OVER_TIME',
        'Performance over time'
      )
    );

    heading.style.margin = '0';
    heading.style.fontSize = '0.875rem';
    heading.style.fontWeight = '600';

    heading.style.color =
      'var(--foreground)';

    const period = el(
      'p',
      'Last 7 days'
    );

    period.style.margin =
      '0.125rem 0 0';

    period.style.fontSize =
      '0.6875rem';

    period.style.color =
      'var(--muted-foreground)';

    headingBlock.append(
      heading,
      period
    );

    const chartData =
      Array.isArray(timeline)
        ? timeline
        : [];

    const pageViews =
      chartData.reduce(
        (sum, row) =>
          sum + Number(row.page_views || 0),
        0
      );

    const totalClicks =
      chartData.reduce(
        (sum, row) =>
          sum + Number(row.total_clicks || 0),
        0
      );

    const qrVisits =
      chartData.reduce(
        (sum, row) =>
          sum + Number(row.qr_visits || 0),
        0
      );

    const totals = el('div');

    totals.style.display = 'flex';
    totals.style.alignItems = 'center';
    totals.style.gap = '0.75rem';

    const totalBlock = (
      value,
      label
    ) => {
      const node = el('div');

      node.style.textAlign =
        'end';

      const number = el(
        'div',
        value
      );

      number.style.fontSize =
        '1.125rem';

      number.style.fontWeight =
        '600';

      number.style.fontVariantNumeric =
        'tabular-nums';

      const caption = el(
        'div',
        label
      );

      caption.style.fontSize =
        '0.6875rem';

      caption.style.color =
        'var(--muted-foreground)';

      node.append(
        number,
        caption
      );

      return node;
    };

    const divider = () => {
      const node = el('div');

      node.style.width = '1px';
      node.style.height = '1.6rem';

      node.style.background =
        'var(--border)';

      return node;
    };

    totals.append(
      totalBlock(
        pageViews,
        'Views'
      ),
      divider(),
      totalBlock(
        totalClicks,
        'Clicks'
      ),
      divider(),
      totalBlock(
        qrVisits,
        'QR'
      )
    );

    header.append(
      headingBlock,
      totals
    );

    const ns =
      'http://www.w3.org/2000/svg';

    const svg =
      document.createElementNS(
        ns,
        'svg'
      );

    svg.setAttribute(
      'viewBox',
      '0 0 700 275'
    );

    svg.setAttribute(
      'preserveAspectRatio',
      'none'
    );

    svg.style.display = 'block';
    svg.style.width = '100%';
    svg.style.height = '100%';

    svg.style.minHeight =
      '190px';

    const defs =
      document.createElementNS(
        ns,
        'defs'
      );

    const gradient =
      document.createElementNS(
        ns,
        'linearGradient'
      );

    gradient.id =
      'goosialize-links-area-gradient';

    gradient.setAttribute('x1', '0');
    gradient.setAttribute('x2', '0');
    gradient.setAttribute('y1', '0');
    gradient.setAttribute('y2', '1');

    const top =
      document.createElementNS(
        ns,
        'stop'
      );

    top.setAttribute(
      'offset',
      '0%'
    );

    top.setAttribute(
      'stop-color',
      'var(--primary)'
    );

    top.setAttribute(
      'stop-opacity',
      '0.30'
    );

    const bottom =
      document.createElementNS(
        ns,
        'stop'
      );

    bottom.setAttribute(
      'offset',
      '100%'
    );

    bottom.setAttribute(
      'stop-color',
      'var(--primary)'
    );

    bottom.setAttribute(
      'stop-opacity',
      '0.02'
    );

    gradient.append(
      top,
      bottom
    );

    const clip =
      document.createElementNS(
        ns,
        'clipPath'
      );

    clip.id =
      'goosialize-links-chart-reveal';

    const clipRect =
      document.createElementNS(
        ns,
        'rect'
      );

    clipRect.setAttribute(
      'x',
      '0'
    );

    clipRect.setAttribute(
      'y',
      '0'
    );

    clipRect.setAttribute(
      'width',
      '0'
    );

    clipRect.setAttribute(
      'height',
      '275'
    );

    clip.append(
      clipRect
    );

    defs.append(
      gradient,
      clip
    );

    svg.append(
      defs
    );

    [
      0,
      0.25,
      0.5,
      0.75,
      1,
    ].forEach(
      (tick) => {
        const line =
          document.createElementNS(
            ns,
            'line'
          );

        const yy =
          250 -
          tick * 220;

        line.setAttribute(
          'x1',
          '40'
        );

        line.setAttribute(
          'y1',
          String(yy)
        );

        line.setAttribute(
          'x2',
          '695'
        );

        line.setAttribute(
          'y2',
          String(yy)
        );

        line.setAttribute(
          'stroke',
          'currentColor'
        );

        line.setAttribute(
          'stroke-opacity',
          '0.08'
        );

        line.setAttribute(
          'stroke-dasharray',
          '3 3'
        );

        svg.append(
          line
        );
      }
    );

    const chartGroup =
      document.createElementNS(
        ns,
        'g'
      );

    chartGroup.setAttribute(
      'clip-path',
      'url(#goosialize-links-chart-reveal)'
    );

    svg.append(
      chartGroup
    );

    const chartMax = Math.max(
      1,
      ...chartData.flatMap(
        (row) => [
          Number(row.page_views || 0),
          Number(row.total_clicks || 0),
          Number(row.qr_visits || 0),
        ]
      )
    );

    const buildPoints = (
      key
    ) => {
      const step =
        650 /
        Math.max(
          chartData.length - 1,
          1
        );

      return chartData.map(
        (row, index) => ({
          x:
            45 +
            index * step,

          y:
            250 -
            (
              Number(row[key] || 0)
              /
              chartMax
            ) *
            220,

          row,
        })
      );
    };

    const buildCurve = (
      points
    ) => {
      return points.map(
        (
          point,
          index
        ) => {
          if (index === 0) {
            return (
              `M ${point.x},${point.y}`
            );
          }

          const previous =
            points[index - 1];

          const cpx =
            (
              previous.x +
              point.x
            ) / 2;

          return (
            `C ${cpx},${previous.y} ` +
            `${cpx},${point.y} ` +
            `${point.x},${point.y}`
          );
        }
      ).join(' ');
    };

    const series = [
      {
        key: 'page_views',
        opacity: 1,
        width: 2,
        dash: '',
        area: true,
        label: 'Page Views',
      },
      {
        key: 'total_clicks',
        opacity: 0.58,
        width: 1.5,
        dash: '5 4',
        area: false,
        label: 'Total Clicks',
      },
      {
        key: 'qr_visits',
        opacity: 0.42,
        width: 1.5,
        dash: '2 4',
        area: false,
        label: 'QR Visits',
      },
    ];

    series.forEach(
      (definition) => {
        const points =
          buildPoints(
            definition.key
          );

        if (
          points.length === 0
        ) {
          return;
        }

        const linePath =
          buildCurve(
            points
          );

        if (
          definition.area
        ) {
          const area =
            document.createElementNS(
              ns,
              'path'
            );

          area.setAttribute(
            'd',
            (
              `${linePath} ` +
              `L ${
                points[
                  points.length - 1
                ].x
              },250 ` +
              `L ${points[0].x},250 Z`
            )
          );

          area.setAttribute(
            'fill',
            'url(#goosialize-links-area-gradient)'
          );

          chartGroup.append(
            area
          );
        }

        const path =
          document.createElementNS(
            ns,
            'path'
          );

        path.setAttribute(
          'd',
          linePath
        );

        path.setAttribute(
          'fill',
          'none'
        );

        path.setAttribute(
          'stroke',
          'var(--primary)'
        );

        path.setAttribute(
          'stroke-width',
          String(definition.width)
        );

        path.setAttribute(
          'stroke-opacity',
          String(definition.opacity)
        );

        path.setAttribute(
          'vector-effect',
          'non-scaling-stroke'
        );

        if (
          definition.dash
        ) {
          path.setAttribute(
            'stroke-dasharray',
            definition.dash
          );
        }

        chartGroup.append(
          path
        );

        points.forEach(
          (point) => {
            const circle =
              document.createElementNS(
                ns,
                'circle'
              );

            circle.setAttribute(
              'cx',
              String(point.x)
            );

            circle.setAttribute(
              'cy',
              String(point.y)
            );

            circle.setAttribute(
              'r',
              '3'
            );

            circle.setAttribute(
              'fill',
              'var(--primary)'
            );

            circle.setAttribute(
              'fill-opacity',
              String(definition.opacity)
            );

            const title =
              document.createElementNS(
                ns,
                'title'
              );

            title.textContent =
              `${point.row.date}: ` +
              `${point.row[definition.key]} ` +
              definition.label;

            circle.append(
              title
            );

            chartGroup.append(
              circle
            );
          }
        );
      }
    );

    const chartContainer =
      el('div');

    chartContainer.style.position =
      'relative';

    chartContainer.style.flex =
      '1';

    chartContainer.style.marginTop =
      '0.25rem';

    chartContainer.append(
      svg
    );

    wrapper.append(
      header,
      chartContainer
    );



    const startReveal =
      () => {
        if (
          !wrapper.isConnected
        ) {
          requestAnimationFrame(
            startReveal
          );

          return;
        }



        wrapper.dataset
          .goosializeAnimationState =
          'running';

        const duration =
          1150;

        let startTime = null;

        const easeOutCubic =
          (value) =>
            1 -
            Math.pow(
              1 - value,
              3
            );

        const frame =
          (timestamp) => {
            if (
              startTime === null
            ) {
              startTime =
                timestamp;
            }

            const elapsed =
              timestamp -
              startTime;

            const progress =
              Math.min(
                1,
                elapsed /
                duration
              );

            const eased =
              easeOutCubic(
                progress
              );

            clipRect.setAttribute(
              'width',
              String(
                700 * eased
              )
            );

            if (
              progress < 1
            ) {
              requestAnimationFrame(
                frame
              );

              return;
            }

            clipRect.setAttribute(
              'width',
              '700'
            );

            wrapper.dataset
              .goosializeAnimationState =
              'done';
          };

        requestAnimationFrame(
          frame
        );
      };

    requestAnimationFrame(
      startReveal
    );

    return wrapper;
  }




  function createTopList(
    title,
    items
  ) {
    const section = el('section');

    section.style.display =
      'flex';

    section.style.flexDirection =
      'column';

    section.style.minHeight =
      '145px';

    section.style.padding =
      '1rem';

    section.style.border =
      '1px solid var(--border)';

    section.style.background =
      'var(--card)';

    section.style.borderRadius =
      '0.5rem';

    const heading = el(
      'h3',
      title
    );

    heading.style.margin =
      '0 0 0.75rem';

    heading.style.fontSize =
      '0.875rem';

    heading.style.fontWeight =
      '600';

    heading.style.color =
      'var(--foreground)';

    section.append(heading);

    if (
      !Array.isArray(items) ||
      items.length === 0
    ) {
      const empty = el('div');

      empty.style.display =
        'flex';

      empty.style.flexDirection =
        'column';

      empty.style.alignItems =
        'center';

      empty.style.justifyContent =
        'center';

      empty.style.flex =
        '1';

      empty.style.textAlign =
        'center';

      empty.style.padding =
        '0.6rem 1rem';

      const icon = el(
        'div',
        '↗'
      );

      icon.style.fontSize =
        '1.15rem';

      icon.style.marginBottom =
        '0.35rem';

      icon.style.opacity =
        '0.55';

      const label = el(
        'div',
        'No analytics yet.'
      );

      label.style.fontSize =
        '0.82rem';

      label.style.fontWeight =
        '500';

      label.style.color =
        'var(--foreground)';

      const helper = el(
        'div',
        'Rankings will appear after clicks are recorded.'
      );

      helper.style.marginTop =
        '0.2rem';

      helper.style.fontSize =
        '0.7rem';

      helper.style.color =
        'var(--muted-foreground)';

      empty.append(
        icon,
        label,
        helper
      );

      section.append(empty);

      return section;
    }

    const list = el('div');

    list.style.display =
      'flex';

    list.style.flexDirection =
      'column';

    list.style.gap =
      '0.5rem';

    items.forEach(
      (item, index) => {
        const row = el('div');

        row.style.display =
          'grid';

        row.style.gridTemplateColumns =
          '1.5rem minmax(0,1fr) auto';

        row.style.alignItems =
          'center';

        row.style.gap =
          '0.65rem';

        const rank = el(
          'span',
          String(index + 1)
        );

        rank.style.fontSize =
          '0.7rem';

        rank.style.color =
          'var(--muted-foreground)';

        const label = el(
          'span',
          item.label || item.id || '—'
        );

        label.style.overflow =
          'hidden';

        label.style.textOverflow =
          'ellipsis';

        label.style.whiteSpace =
          'nowrap';

        label.style.fontSize =
          '0.8rem';

        const value = el(
          'strong',
          String(item.clicks || 0)
        );

        value.style.fontSize =
          '0.8rem';

        value.style.fontVariantNumeric =
          'tabular-nums';

        row.append(
          rank,
          label,
          value
        );

        list.append(row);
      }
    );

    section.append(list);

    return section;
  }

  function createBreakdownGrid(data) {
    const grid = el('section');

    grid.style.display = 'grid';

    grid.style.gridTemplateColumns =
      'repeat(3, minmax(0, 1fr))';

    if (
      window.matchMedia &&
      window.matchMedia(
        '(max-width: 900px)'
      ).matches
    ) {
      grid.style.gridTemplateColumns =
        '1fr';
    }

    grid.style.gap =
      '0.75rem';

    grid.style.marginBottom =
      '0.75rem';

    const qr =
      createQrPanel(
        data.qr
      );

    const links =
      createTopList(
        t(
          'PLUGIN_GOOSIALIZE_LINKS.TOP_LINKS',
          'Top Links'
        ),
        data.top_links
      );

    const actions =
      createTopList(
        t(
          'PLUGIN_GOOSIALIZE_LINKS.TOP_ACTIONS',
          'Top Actions'
        ),
        data.top_actions
      );

    grid.append(
      qr,
      links,
      actions
    );

    return grid;
  }


  function createQrPanel(qr) {
    const section = el('section');

    section.style.display =
      'flex';

    section.style.flexDirection =
      'column';

    section.style.minHeight =
      '145px';

    section.style.padding =
      '1rem';

    section.style.border =
      '1px solid var(--border)';

    section.style.background =
      'var(--card)';

    section.style.borderRadius =
      '0.5rem';

    const heading = el(
      'h3',
      'QR Code'
    );

    heading.style.margin =
      '0 0 0.75rem';

    heading.style.fontSize =
      '0.875rem';

    heading.style.fontWeight =
      '600';

    heading.style.color =
      'var(--foreground)';

    const content = el('div');

    content.style.display =
      'grid';

    content.style.gridTemplateColumns =
      '112px minmax(0, 1fr)';

    content.style.alignItems =
      'center';

    content.style.gap =
      '0.8rem';

    content.style.flex =
      '1';

    const previewShell =
      el('div');

    previewShell.style.width =
      '112px';

    previewShell.style.height =
      '112px';

    previewShell.style.padding =
      '0.35rem';

    previewShell.style.boxSizing =
      'border-box';

    previewShell.style.background =
      '#fff';

    previewShell.style.border =
      '1px solid var(--border)';

    previewShell.style.borderRadius =
      '0.35rem';

    const image =
      document.createElement('img');

    image.src =
      sameOriginUrl(
        qr.preview_url
      );

    image.alt =
      t(
        'PLUGIN_GOOSIALIZE_LINKS.QR_PREVIEW_ALT',
        'Goosialize Links QR code'
      );

    image.style.display =
      'block';

    image.style.width =
      '100%';

    image.style.height =
      '100%';

    image.style.objectFit =
      'contain';

    previewShell.append(image);

    const details = el('div');

    details.style.display =
      'grid';

    details.style.gap =
      '0.5rem';

    details.style.minWidth =
      '0';

    const metaRow = (
      label,
      value,
      mono = false
    ) => {
      const block = el('div');

      const key = el(
        'div',
        label
      );

      key.style.fontSize =
        '0.65rem';

      key.style.fontWeight =
        '600';

      key.style.marginBottom =
        '0.08rem';

      key.style.color =
        'var(--muted-foreground)';

      const val = el(
        'div',
        String(value ?? '—')
      );

      val.style.fontSize =
        '0.72rem';

      val.style.color =
        'var(--foreground)';

      val.style.overflowWrap =
        'anywhere';

      if (mono) {
        val.style.fontFamily =
          'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace';

        val.style.fontSize =
          '0.66rem';
      }

      block.append(
        key,
        val
      );

      return block;
    };

    details.append(
      metaRow(
        'Identifier',
        qr.qr_id,
        true
      ),
      metaRow(
        'Tracked URL',
        qr.tracked_url,
        true
      ),
      metaRow(
        'Visits',
        qr.qr_visits
      )
    );

    content.append(
      previewShell,
      details
    );

    section.append(
      heading,
      content
    );

    return section;
  }


  function validatePayload(data) {
    if (
      !data ||
      typeof data !== 'object'
    ) {
      throw new Error(
        'Invalid analytics dashboard response.'
      );
    }

    if (
      !data.summary ||
      !data.qr ||
      !Array.isArray(data.timeline) ||
      !Array.isArray(data.top_links) ||
      !Array.isArray(data.top_actions)
    ) {
      throw new Error(
        'Unexpected analytics dashboard payload.'
      );
    }

    if (
      data.qr.qr_id !==
      'qr_primary'
    ) {
      throw new Error(
        'Unexpected QR identifier.'
      );
    }

    return data;
  }

  class GoosializeLinksPage
    extends HTMLElement {

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

    _status(message, error = false) {
      const p =
        el('p', message);

      p.setAttribute(
        'role',
        error ? 'alert' : 'status'
      );

      this.replaceChildren(p);
    }

    async _load() {
      this._controller?.abort();

      this._controller =
        new AbortController();

      this._status(
        'Loading analytics...'
      );

      try {
        const response =
          await fetch(
            apiUrl(
              '/goosialize-links/dashboard'
            ),
            {
              method: 'GET',
              headers: apiHeaders(),
              credentials:
                'same-origin',
              cache: 'no-store',
              signal:
                this._controller.signal,
            }
          );

        const payload =
          await response.json();

        if (!response.ok) {
          throw new Error(
            payload?.error?.message ||
            'Analytics could not be loaded.'
          );
        }

        this._render(
          validatePayload(payload)
        );
      } catch (error) {
        if (
          error?.name ===
          'AbortError'
        ) {
          return;
        }

        this._status(
          error instanceof Error
            ? error.message
            : 'Analytics could not be loaded.',
          true
        );
      }
    }

    _render(data) {
      const root =
        el('div');

      root.style.width =
        '100%';

      root.style.maxWidth =
        '1500px';

      const intro =
        el(
          'p',
          'Performance overview for your public Goosialize Links page.'
        );

      intro.style.opacity =
        '.72';

      intro.style.marginTop =
        '0';

      intro.style.marginBottom =
        '1rem';

      root.append(
        intro,
        createStatsGrid(
          data.summary
        ),
        createChart(
          data.timeline
        ),
        createBreakdownGrid(
          data
        ),

      );

      this.replaceChildren(root);
    }
  }

  if (
    !customElements.get(TAG)
  ) {
    customElements.define(
      TAG,
      GoosializeLinksPage
    );
  }
})();
