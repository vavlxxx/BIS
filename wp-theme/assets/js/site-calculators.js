(function () {
  'use strict';

  const state = {
    currentBlock: 'block1',
    currentAvok: 'du4_1',
    block1Floors: [
      { floor: 8, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 7, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 6, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 5, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 4, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 3, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 2, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 },
      { floor: 1, li: 3.0, a: 1.0, b: 0.8, kms: 0.4, kmsItems: ['pass'], val_a: 0.8, val_b: 0.5 }
    ],
    block3Grilles: [
      { point: '1', room: '119/1', Lpr: 500, Lfact: 495.33 },
      { point: '2', room: '118', Lpr: 1000, Lfact: 1030.58 },
      { point: '3', room: '116', Lpr: 1150, Lfact: 1137.02 },
      { point: '4', room: '120/1', Lpr: 650, Lfact: 689.47 },
      { point: '5', room: '120/1', Lpr: 650, Lfact: 662.86 }
    ],
    protocolMeta: {
      number: '109.005/П-02',
      date: new Date().toISOString().split('T')[0],
      objectName: 'Торговый центр «Академический»',
      address: 'СПб, Гражданский проспект, квартал 9А',
      systemName: 'Система П11',
      section: 'Вентиляционная сеть 1-го этажа',
      instruments: 'Комбинированный дифманометр Testo 440 dP, зонд-анемометр крыльчатка Ду 100, крыльчатка Ду 16',
      engineer: 'Журавлев А.Д.',
      approver: 'Журавлев А.Д.'
    },
    lastResults: {}
  };

  const KMS_COMPONENTS = {
    pass: { name: 'Тройник на проход (0.4)', val: 0.4 },
    tee_branch: { name: 'Тройник на ответвление (1.9)', val: 1.9 },
    elbow_90: { name: 'Колено 90° (0.6)', val: 0.6 },
    bend_45: { name: 'Полуотвод 45° (0.4)', val: 0.4 },
    expansion: { name: 'Внезапное расширение (1.0)', val: 1.0 },
    contraction: { name: 'Внезапное сужение (1.0)', val: 1.0 },
    plenum_box: { name: 'Анемостатическая камера решётки (5.6)', val: 5.6 }
  };

  function getKmsShortTitle(f) {
    const items = f.kmsItems || [];
    if (items.length === 0) return `${(parseFloat(f.kms) || 0.4).toFixed(1)}`;
    if (items.length === 1 && KMS_COMPONENTS[items[0]]) {
      return KMS_COMPONENTS[items[0]].name.split(' (')[0];
    }
    return `${items.length} элм. (${(parseFloat(f.kms) || 0.4).toFixed(1)})`;
  }

  const KMS_RATES = {
    standard: 0.4,
    complex: 4.6,
    turns: 0.6
  };

  const LEAKAGE_CLASSES = {
    A: { name: 'Класс A (Низкая плотность)', c: 0.097 },
    B: { name: 'Класс B (Плотный)', c: 0.032 },
    C: { name: 'Класс C (Высокая плотность)', c: 0.0108 },
    D: { name: 'Класс D (Специальный)', c: 0.0036 }
  };

  function kTeX(latex, isDisplay = true) {
    if (window.katex && typeof window.katex.renderToString === 'function') {
      try {
        return window.katex.renderToString(latex, {
          displayMode: isDisplay,
          throwOnError: false,
          output: 'html'
        });
      } catch (e) {
        console.warn('KaTeX render error for:', latex, e);
      }
    }
    return `<span class="latex-fallback">${latex}</span>`;
  }

  let dom = {};

  function init() {
    cacheDom();
    bindEvents();
    renderAll();
  }

  function cacheDom() {
    dom.blockNavBtns = document.querySelectorAll('.calc-nav-card');
    dom.blockPanels = document.querySelectorAll('.calc-block-content');
    dom.avokTabBtns = document.querySelectorAll('.avok-tab-item');
    dom.avokPanels = document.querySelectorAll('.avok-calc-content');

    dom.b1TableBody = document.getElementById('b1FloorsTableBody');
    dom.b3TableBody = document.getElementById('b3GrillesTableBody');
    dom.b3BtnAddGrille = document.getElementById('b3BtnAddGrille');
    dom.b3AutoSumGrilles = document.getElementById('b3_auto_sum_grilles');
    dom.b3GrillesSumVal = document.getElementById('b3GrillesSumVal');

    dom.protocolModal = document.getElementById('calcProtocolModal');
    dom.protocolPrintArea = document.getElementById('protocolPrintArea');
  }

  function bindEvents() {
    dom.blockNavBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const block = btn.dataset.block;
        switchBlock(block);
      });
    });

    dom.avokTabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const avok = btn.dataset.avok;
        switchAvokTab(avok);
      });
    });

    document.addEventListener('input', e => {
      if (e.target.matches('.calc-auto-recalc')) {
        recalculateCurrent();
      }
    });

    const btnAddFloor = document.getElementById('b1BtnAddFloor');
    if (btnAddFloor) btnAddFloor.addEventListener('click', addBlock1Floor);

    const btnApplyKmsAll = document.getElementById('b1BtnApplyKmsToAll');
    if (btnApplyKmsAll) {
      btnApplyKmsAll.addEventListener('click', () => {
        if (!state.block1Floors || state.block1Floors.length === 0) return;
        const sourceKms = state.block1Floors[0].kms !== undefined ? state.block1Floors[0].kms : 0.4;
        const sourceItems = [...(state.block1Floors[0].kmsItems || ['pass'])];
        state.block1Floors.forEach(f => {
          f.kms = sourceKms;
          f.kmsItems = [...sourceItems];
        });
        renderBlock1Table();
        recalculateBlock1();
      });
    }

    if (dom.b3BtnAddGrille) {
      dom.b3BtnAddGrille.addEventListener('click', addBlock3Grille);
    }

    if (dom.b3AutoSumGrilles) {
      dom.b3AutoSumGrilles.addEventListener('change', () => {
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    }
  }

  function switchBlock(block) {
    state.currentBlock = block;
    dom.blockNavBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.block === block);
    });
    dom.blockPanels.forEach(panel => {
      panel.style.display = panel.id === `panel-${block}` ? 'block' : 'none';
    });
    recalculateCurrent();
  }

  function switchAvokTab(tab) {
    state.currentAvok = tab;
    dom.avokTabBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.avok === tab);
    });
    dom.avokPanels.forEach(panel => {
      panel.style.display = panel.id === `avok-${tab}` ? 'block' : 'none';
    });

    const sysInput = document.getElementById('b2_system_name');
    if (sysInput) {
      if (tab === 'du4_1' && sysInput.value.startsWith('Система ПД')) {
        sysInput.value = 'Система ДУ1';
      } else if (tab !== 'du4_1' && sysInput.value.startsWith('Система ДУ')) {
        sysInput.value = 'Система ПД1';
      }
    }

    recalculateCurrent();
  }

  function renderAll() {
    renderBlock1Table();
    renderBlock3GrillesTable();
    recalculateCurrent();
  }

  function isBlock1Ready() {
    const rawLpr = document.getElementById('b1_Lpr')?.value?.trim();
    const rawPsv = document.getElementById('b1_Psv')?.value?.trim();
    const rawTpg = document.getElementById('b1_Tpg')?.value?.trim();
    const rawTpom = document.getElementById('b1_Tpom')?.value?.trim();
    const rawHtop = document.getElementById('b1_h_top')?.value?.trim();
    const rawHbot = document.getElementById('b1_h_bot')?.value?.trim();

    if (!rawLpr || !rawPsv || !rawTpg || !rawTpom || !rawHtop || !rawHbot) {
      return false;
    }
    const Lpr = parseFloat(rawLpr);
    const Psv = parseFloat(rawPsv);
    const Tpg = parseFloat(rawTpg);
    const Tpom = parseFloat(rawTpom);
    const h_top = parseFloat(rawHtop);
    const h_bot = parseFloat(rawHbot);

    if (isNaN(Lpr) || Lpr <= 0 || isNaN(Psv) || Psv <= 0 || isNaN(Tpg) || Tpg <= 0 || isNaN(Tpom) || isNaN(h_top) || isNaN(h_bot)) {
      return false;
    }
    if (!state.block1Floors || state.block1Floors.length === 0) {
      return false;
    }
    for (const f of state.block1Floors) {
      if (!f.li || f.li <= 0) return false;
      if (!f.a || f.a <= 0 || !f.b || f.b <= 0) return false;
      if (!f.val_a || f.val_a <= 0 || !f.val_b || f.val_b <= 0) return false;
    }
    return true;
  }

  function recalculateBlock1() {
    const rawLpr = document.getElementById('b1_Lpr')?.value?.trim();
    const rawPsv = document.getElementById('b1_Psv')?.value?.trim();
    const rawTpg = document.getElementById('b1_Tpg')?.value?.trim();
    const rawTpom = document.getElementById('b1_Tpom')?.value?.trim();
    const rawHtop = document.getElementById('b1_h_top')?.value?.trim();
    const rawHbot = document.getElementById('b1_h_bot')?.value?.trim();
    const rawLfact = document.getElementById('b1_Lfact')?.value?.trim();
    const btnProt = document.getElementById('b1_btn_protocol');
    const hintEl = document.getElementById('b1_calc_hint');
    const devRow = document.getElementById('b1_row_Dev');

    if (!rawLpr || !rawPsv || !rawTpg || !rawTpom || !rawHtop || !rawHbot) {
      updateSummaryMetric('b1_res_L0', '—', 'м³/ч');
      updateSummaryMetric('b1_res_Psa', '—', 'Па');
      updateSummaryMetric('b1_res_G0', '—', 'кг/с');
      updateSummaryMetric('b1_res_Leak', '—', 'м³/ч');
      if (devRow) devRow.style.display = 'none';
      if (btnProt) {
        btnProt.disabled = true;
        btnProt.classList.add('btn-disabled');
        btnProt.title = 'Для формирования отчёта введите параметры и добавьте хотя бы 1 этаж';
      }
      if (hintEl) {
        hintEl.style.display = 'block';
        hintEl.innerText = 'Для расчета и формирования отчета заполните исходные данные и добавьте этажи';
      }
      return;
    }

    const Lpr = parseFloat(rawLpr);
    const Psv = parseFloat(rawPsv);
    const Tpg = parseFloat(rawTpg);
    const Tpom = parseFloat(rawTpom);
    const h_top = parseFloat(rawHtop);
    const h_bot = parseFloat(rawHbot);
    const Lfact = rawLfact ? parseFloat(rawLfact) : null;

    if (isNaN(Lpr) || isNaN(Psv) || isNaN(Tpg) || isNaN(Tpom) || isNaN(h_top) || isNaN(h_bot)) {
      return;
    }

    const Tv = Tpg > 100 ? (Tpg - 62) : 619;
    const Ta = 273 + Tpom;
    const rho_a = 353 / Ta;
    const rho_sm = (2 * rho_a * Ta) / (Tpg + Tv);
    const rho_v = 353 / Tv;
    const h = Math.max(0, h_top - h_bot);

    const Psa = (Psv * rho_v / 1.2) + (9.81 * h * (rho_a - rho_sm));
    const P_diagr = (1.2 * Psa) / rho_v;
    const pDiagrEl = document.getElementById('b1_out_Pdiagr');
    if (pDiagrEl) {
      pDiagrEl.value = Math.round(P_diagr);
    }

    const rawLaInput = document.getElementById('b1_La')?.value?.trim();
    const userLa = rawLaInput ? parseFloat(rawLaInput) : null;
    const La = (userLa !== null && !isNaN(userLa) && userLa > 0) ? userLa : Math.round(Lpr);
    const Ga = (La * rho_a) / 3600;

    let currentG = Ga;
    let currentP = Psa;
    let totalValveLeakage = 0;
    const floorResults = [];

    state.block1Floors.forEach((f, idx) => {
      const F_shaft = (f.a || 0.6) * (f.b || 0.45);
      const P_shaft = 2 * ((f.a || 0.6) + (f.b || 0.45));
      const de = P_shaft > 0 ? (4 * F_shaft / P_shaft) : 1.0;
      const kms = parseFloat(f.kms !== undefined ? f.kms : (KMS_RATES[f.kmsType] !== undefined ? KMS_RATES[f.kmsType] : 0.4));
      const lambda = 0.016;
      const li = f.li || 3.0;

      const G_before = currentG;
      const velocity_mass = (F_shaft > 0 && rho_a > 0) ? (currentG / (rho_a * F_shaft)) : 0;
      const deltaP = 0.5 * rho_a * (kms + (lambda * li / de)) * Math.pow(velocity_mass, 2);
      currentP = Math.max(0, currentP - deltaP);

      const F_val = Math.max(0, (f.val_a || 0.8) * (f.val_b || 0.75));
      const S_dpn = 10000; // Удельное сопротивление воздухопроницанию по ГОСТ Р 53300-2009
      const G_leak = F_val > 0 ? Math.sqrt(currentP / S_dpn) * F_val : 0;
      totalValveLeakage += G_leak;

      currentG = Math.max(0, currentG - G_leak);
      const L_floor = (currentG * 3600) / rho_a;

      floorResults.push({
        idx: idx + 1,
        floor: f.floor,
        kms: kms.toFixed(1),
        lambda: lambda.toFixed(3),
        li: li.toFixed(1),
        de: de.toFixed(3),
        Fn: F_shaft.toFixed(3),
        Pn: P_shaft.toFixed(2),
        Ga_curr: G_before.toFixed(3),
        P_sn: currentP.toFixed(2),
        F_val: F_val.toFixed(4),
        G_leak: G_leak.toFixed(4),
        G_curr: currentG.toFixed(3),
        L_curr: Math.round(L_floor)
      });
    });

    const G0 = Math.max(0, Ga - totalValveLeakage);
    const L0 = (G0 * 3600) / rho_a;

    updateSummaryMetric('b1_res_L0', Math.round(L0).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('b1_res_Psa', Math.round(Psa).toLocaleString('ru-RU'), 'Па');
    updateSummaryMetric('b1_res_G0', G0.toFixed(2), 'кг/с');
    updateSummaryMetric('b1_res_Leak', Math.round(totalValveLeakage * 3600 / rho_a).toLocaleString('ru-RU'), 'м³/ч');

    let deviation = null;
    if (Lfact !== null && !isNaN(Lfact) && L0 > 0) {
      deviation = ((Lfact - L0) / L0) * 100;
      const devEl = document.getElementById('b1_res_Dev');
      if (devRow && devEl) {
        devRow.style.display = 'flex';
        devRow.style.background = 'transparent';
        devRow.style.border = 'none';
        devRow.style.borderBottom = '1px solid #f1f5f9';
        devEl.style.textAlign = 'center';
        devEl.style.width = '100%';
        devEl.innerText = (deviation > 0 ? '+' : '') + deviation.toFixed(1) + ' %';
        if (Math.abs(deviation) <= 15) {
          devEl.style.color = '#166534';
        } else {
          devEl.style.color = '#b91c1c';
        }
      }
    } else {
      if (devRow) devRow.style.display = 'none';
    }

    state.lastResults.block1 = {
      Lpr, Psv, Tpg, Tpom, h_top, h_bot, Tv, Ta, rho_a, rho_sm, rho_v, h,
      Psa: Math.round(Psa * 100) / 100,
      P_diagr: Math.round(P_diagr),
      La,
      Ga: Ga.toFixed(3),
      G0: G0.toFixed(3),
      L0: Math.round(L0),
      Lfact,
      deviation,
      totalLeakage: Math.round(totalValveLeakage * 3600 / rho_a),
      totalValveLeakageKg: totalValveLeakage.toFixed(4),
      floorResults
    };

    updateBlock1TableOutputs(floorResults);

    const ready = isBlock1Ready();
    if (btnProt) {
      btnProt.disabled = !ready;
      btnProt.classList.toggle('btn-disabled', !ready);
      btnProt.title = ready ? 'Сформировать официальный протокол по ГОСТ Р 53300-2009' : 'Для формирования отчёта введите параметры и добавьте хотя бы 1 этаж';
    }
    if (hintEl) {
      if (state.block1Floors.length === 0) {
        hintEl.style.display = 'block';
        hintEl.innerText = 'Для формирования отчёта добавьте хотя бы 1 этаж';
      } else {
        hintEl.style.display = ready ? 'none' : 'block';
        if (!ready) {
          hintEl.innerText = 'Для формирования отчёта заполните параметры и этажи';
        }
      }
    }
  }

  function renderBlock1Table() {
    if (!dom.b1TableBody) return;
    dom.b1TableBody.innerHTML = '';

    if (state.block1Floors.length === 0) {
      const trEmpty = document.createElement('tr');
      trEmpty.innerHTML = `<td colspan="9" style="padding: 20px; color: var(--text-light); text-align: center;">Этажи пока не добавлены. Нажмите «+ Добавить этаж», чтобы внести параметры шахты.</td>`;
      dom.b1TableBody.appendChild(trEmpty);
      return;
    }

    state.block1Floors.forEach((f, idx) => {
      const tr = document.createElement('tr');
      const curKms = parseFloat(f.kms !== undefined ? f.kms : 0.4);
      const items = f.kmsItems || ['pass'];

      tr.innerHTML = `
        <td style="font-weight:600;">
          <input type="number" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="floor" value="${f.floor}" style="width: 55px;">
        </td>
        <td>
          <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="li" value="${f.li}" style="width: 70px;">
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="a" value="${f.a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="b" value="${f.b}" style="width: 55px;">
          </div>
        </td>
        <td>
          <div class="b1-kms-cell-wrap">
            <input type="number" step="0.1" class="calc-table-input b1-floor-kms-val" data-idx="${idx}" value="${curKms.toFixed(1)}" title="Суммарный КМС этажа (Σξ)" style="width: 50px; font-weight: 700; text-align: center; height: 32px; padding: 2px 4px;">
            <details class="b1-kms-details">
              <summary class="b1-kms-summary" title="Кликните для выбора комбинации сопротивлений">
                <span class="b1-kms-summary-text">${getKmsShortTitle(f)}</span>
                <span style="font-size: 9px; margin-left: 2px;">▼</span>
              </summary>
              <div class="b1-kms-popover">
                ${Object.entries(KMS_COMPONENTS).map(([kId, kObj]) => `
                  <label class="b1-kms-label">
                    <input type="checkbox" class="b1-kms-cb" data-idx="${idx}" data-val="${kObj.val}" data-id="${kId}" ${items.includes(kId) ? 'checked' : ''}>
                    ${kObj.name}
                  </label>
                `).join('')}
              </div>
            </details>
          </div>
        </td>
        <td>
          <div style="display:inline-flex; align-items:center; gap:4px;">
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_a" value="${f.val_a}" style="width: 55px;">
            <span>×</span>
            <input type="number" step="0.1" class="calc-table-input b1-floor-input" data-idx="${idx}" data-field="val_b" value="${f.val_b}" style="width: 55px;">
          </div>
        </td>
        <td id="b1_out_P_${idx}" style="font-weight:600; color:var(--dark);">-</td>
        <td id="b1_out_G_${idx}" style="color:var(--text-light);">-</td>
        <td id="b1_out_L_${idx}" style="font-weight:700; color:var(--primary-dark);">-</td>
        <td>
          <button type="button" class="btn-delete-floor" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить этаж">&times;</button>
        </td>
      `;
      dom.b1TableBody.appendChild(tr);
    });

    dom.b1TableBody.querySelectorAll('.b1-floor-input').forEach(input => {
      input.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const field = e.target.dataset.field;
        state.block1Floors[idx][field] = parseFloat(e.target.value) || 0;
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.b1-floor-kms-val').forEach(input => {
      input.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const val = parseFloat(e.target.value) || 0;
        state.block1Floors[idx].kms = val;
        renderBlock1Table();
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.b1-kms-cb').forEach(cb => {
      cb.addEventListener('change', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        const floor = state.block1Floors[idx];
        if (!floor.kmsItems) floor.kmsItems = [];
        const id = e.target.dataset.id;
        if (e.target.checked) {
          if (!floor.kmsItems.includes(id)) floor.kmsItems.push(id);
        } else {
          floor.kmsItems = floor.kmsItems.filter(x => x !== id);
        }
        let totalKms = 0;
        floor.kmsItems.forEach(kId => {
          if (KMS_COMPONENTS[kId]) totalKms += KMS_COMPONENTS[kId].val;
        });
        floor.kms = Math.round(totalKms * 10) / 10;
        renderBlock1Table();
        recalculateBlock1();
      });
    });

    dom.b1TableBody.querySelectorAll('.btn-delete-floor').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block1Floors.splice(idx, 1);
        renderBlock1Table();
        recalculateBlock1();
      });
    });
  }

  function updateBlock1TableOutputs(results) {
    results.forEach((r, idx) => {
      const pEl = document.getElementById(`b1_out_P_${idx}`);
      const gEl = document.getElementById(`b1_out_G_${idx}`);
      const lEl = document.getElementById(`b1_out_L_${idx}`);
      if (pEl) pEl.innerText = r.P_sn + ' Па';
      if (gEl) gEl.innerText = r.G_leak + ' кг/с';
      if (lEl) lEl.innerText = r.L_curr.toLocaleString('ru-RU') + ' м³/ч';
    });
  }

  function addBlock1Floor() {
    const lastFloor = state.block1Floors[state.block1Floors.length - 1];
    const newFloorNum = lastFloor ? Math.max(1, lastFloor.floor - 1) : 1;
    state.block1Floors.push({
      floor: newFloorNum,
      li: 3.0,
      a: 1.0,
      b: 0.8,
      kms: 0.4,
      kmsItems: ['pass'],
      val_a: 0.8,
      val_b: 0.5
    });
    renderBlock1Table();
    recalculateBlock1();
  }

  function recalculateBlock2() {
    switch (state.currentAvok) {
      case 'du4_1': calcAvokDU4_1(); break;
      case 'pd4_1': calcAvokPD4_1(); break;
      case 'pd4_2': calcAvokPD4_2(); break;
      case 'pd4_7': calcAvokPD4_7(); break;
      case 'pd4_8': calcAvokPD4_8(); break;
      case 'pd7_a': calcAvokPD7_a(); break;
    }
  }

  function calcAvokDU4_1() {
    const rawLk = document.getElementById('du4_1_Lk')?.value;
    const rawQ = document.getElementById('du4_1_Q')?.value;

    if (!rawLk || !rawQ) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const Lk = parseFloat(rawLk) || 0;
    const Bk = parseFloat(document.getElementById('du4_1_Bk')?.value) || 2.4;
    const Hk = parseFloat(document.getElementById('du4_1_Hk')?.value) || 2.8;
    const Q = parseFloat(rawQ) || 0;
    const Ta = 24;

    const smoke_layer_h = 0.5 * Hk;
    const G_smoke = 0.071 * Math.pow(Q, 1/3) * Math.pow(Math.max(0.1, Hk - smoke_layer_h), 5/3) + 0.0018 * Q;
    const T_smoke = Ta + (G_smoke > 0 ? (Q / (1.005 * G_smoke)) : 0);
    const rho_smoke = 353 / (273 + Math.min(600, T_smoke));
    const L_smoke = (G_smoke / rho_smoke) * 3600;
    const P_fan = 380;

    updateSummaryMetric('avok_res_main_val', Math.round(L_smoke).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', P_fan, 'Па');
    updateSummaryMetric('avok_res_sub2_val', Math.round(T_smoke) + ' °C', '');

    state.lastResults.avok = {
      type: 'ДУ4-1 (Дымоудаление из коридора)',
      mainLabel: 'Расход дымоудаления L',
      mainVal: `${Math.round(L_smoke).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление вентилятора Psv',
      sub1Val: `${P_fan} Па`,
      sub2Label: 'Температура дыма',
      sub2Val: `${Math.round(T_smoke)} °C`,
      details: [
        { label: 'Массовый расход дыма G', val: `${G_smoke.toFixed(2)} кг/с` },
        { label: 'Плотность дыма ρsm', val: `${rho_smoke.toFixed(3)} кг/м³` },
        { label: 'Длина коридора Lк', val: `${Lk} м` }
      ]
    };
  }

  function calcAvokPD4_1() {
    const rawFloors = document.getElementById('pd4_1_floors')?.value;
    if (!rawFloors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const floors = parseFloat(rawFloors) || 0;
    const b_door = parseFloat(document.getElementById('pd4_1_b_door')?.value) || 0.9;
    const h_door = parseFloat(document.getElementById('pd4_1_h_door')?.value) || 2.1;
    const b_type = document.getElementById('pd4_1_building_type')?.value || 'living';
    const v_min = b_type === 'living' ? 1.3 : 1.5;

    const F_door = b_door * h_door;
    const rho = 1.25;
    const G_door = v_min * F_door * rho;
    const G_leaks = Math.max(0, floors - 1) * Math.sqrt(35 / 196000);
    const G_total = G_door + G_leaks;
    const L_total = (G_total * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '45', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G_total.toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-1 (Подпор в лестничную клетку)',
      mainLabel: 'Расход подпора в ЛК',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Избыточное давление',
      sub1Val: '45 Па',
      sub2Label: 'Массовый расход G',
      sub2Val: `${G_total.toFixed(2)} кг/с`,
      details: [
        { label: 'Расход через открытую дверь', val: `${(G_door * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Утечки через закрытые двери', val: `${(G_leaks * 3600 / rho).toFixed(0)} м³/ч` },
        { label: 'Скорость в проеме двери', val: `${v_min} м/с` }
      ]
    };
  }

  function calcAvokPD4_2() {
    const rawFloors = document.getElementById('pd4_2_floors')?.value;
    if (!rawFloors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const floors = parseFloat(rawFloors) || 0;
    const elevators = parseFloat(document.getElementById('pd4_2_elevators')?.value) || 1;
    const rho = 1.25;

    const L_per_door = 3600 * 0.65 * 0.05 * Math.sqrt(2 * 30 / rho);
    const L_total = elevators * (L_per_door * floors + 1800);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '35', 'Па');
    updateSummaryMetric('avok_res_sub2_val', (L_total * rho / 3600).toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-2 (Подпор в шахту лифта)',
      mainLabel: 'Расход подпора в шахту',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в шахте',
      sub1Val: '35 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${(L_total * rho / 3600).toFixed(2)} кг/с`,
      details: [
        { label: 'Количество шахт лифтов', val: `${elevators}` },
        { label: 'Этажность здания', val: `${floors}` }
      ]
    };
  }

  function calcAvokPD4_7() {
    const rawW = document.getElementById('pd4_7_w')?.value;
    if (!rawW) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const w = parseFloat(rawW) || 0;
    const h = parseFloat(document.getElementById('pd4_7_h')?.value) || 2.1;
    const v = parseFloat(document.getElementById('pd4_7_v')?.value) || 1.3;
    const rho = 1.25;

    const F = w * h;
    const G = v * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '25', 'Па');
    updateSummaryMetric('avok_res_sub2_val', v.toFixed(2) + ' м/с', '');

    state.lastResults.avok = {
      type: 'ПД4-7 (Зона ПБЗ - открытая дверь)',
      mainLabel: 'Расход воздуха в ПБЗ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Требуемый подпор',
      sub1Val: '25 Па',
      sub2Label: 'Скорость в проеме',
      sub2Val: `${v} м/с`,
      details: [
        { label: 'Площадь дверного проема F', val: `${F.toFixed(2)} м²` },
        { label: 'Массовый расход G', val: `${G.toFixed(2)} кг/с` }
      ]
    };
  }

  function calcAvokPD4_8() {
    const rawW = document.getElementById('pd4_8_w')?.value;
    if (!rawW) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const w = parseFloat(rawW) || 0;
    const h = parseFloat(document.getElementById('pd4_8_h')?.value) || 2.1;
    const rho = 1.25;

    const F = w * h;
    const G = 1.3 * F * rho;
    const L = (G * 3600) / rho;

    updateSummaryMetric('avok_res_main_val', Math.round(L).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', '30', 'Па');
    updateSummaryMetric('avok_res_sub2_val', G.toFixed(2) + ' кг/с', '');

    state.lastResults.avok = {
      type: 'ПД4-8 (Тамбур-шлюз перед ЛК)',
      mainLabel: 'Расход воздуха в ТШ',
      mainVal: `${Math.round(L).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Давление в ТШ',
      sub1Val: '30 Па',
      sub2Label: 'Массовый расход',
      sub2Val: `${G.toFixed(2)} кг/с`,
      details: [
        { label: 'Площадь проема ТШ', val: `${F.toFixed(2)} м²` }
      ]
    };
  }

  function calcAvokPD7_a() {
    const rawDoors = document.getElementById('pd7_a_doors')?.value;
    if (!rawDoors) {
      updateSummaryMetric('avok_res_main_val', '—', 'м³/ч');
      updateSummaryMetric('avok_res_sub1_val', '—', 'Па');
      updateSummaryMetric('avok_res_sub2_val', '—', '');
      return;
    }

    const doors = parseFloat(rawDoors) || 0;
    const reqP = parseFloat(document.getElementById('pd7_a_reqP')?.value) || 20;
    const rho = 1.25;

    const G_leak = doors * Math.sqrt(reqP / 196000);
    const L_total = Math.max(350, (G_leak * 3600) / rho);

    updateSummaryMetric('avok_res_main_val', Math.round(L_total).toLocaleString('ru-RU'), 'м³/ч');
    updateSummaryMetric('avok_res_sub1_val', reqP, 'Па');
    updateSummaryMetric('avok_res_sub2_val', (G_leak * 1000).toFixed(1) + ' г/с', '');

    state.lastResults.avok = {
      type: 'ПД7-а (Зона ПБЗ - закрытая дверь)',
      mainLabel: 'Расход для подпора в ПБЗ',
      mainVal: `${Math.round(L_total).toLocaleString('ru-RU')} м³/ч`,
      sub1Label: 'Поддерживаемое давление',
      sub1Val: `${reqP} Па`,
      sub2Label: 'Суммарная утечка',
      sub2Val: `${(G_leak * 1000).toFixed(1)} г/с`,
      details: [
        { label: 'Количество противопожарных дверей', val: `${doors}` }
      ]
    };
  }

  function addBlock3Grille() {
    const nextNum = state.block3Grilles.length + 1;
    state.block3Grilles.push({
      point: `${nextNum}`,
      room: `Помещение ${nextNum}`,
      Lpr: 500,
      Lfact: 500
    });
    renderBlock3GrillesTable();
    updateBlock3GrillesSum();
    recalculateBlock3();
  }

  function updateBlock3GrillesSum() {
    let sum = 0;
    state.block3Grilles.forEach(g => {
      sum += parseFloat(g.Lfact) || 0;
    });
    if (dom.b3GrillesSumVal) {
      dom.b3GrillesSumVal.innerText = sum.toFixed(1);
    }
    const autoSumCheckbox = document.getElementById('b3_auto_sum_grilles');
    if (autoSumCheckbox && autoSumCheckbox.checked) {
      const lGrilleInput = document.getElementById('b3_Lgrille');
      if (lGrilleInput) {
        lGrilleInput.value = sum.toFixed(1);
      }
    }
  }

  function renderBlock3GrillesTable() {
    if (!dom.b3TableBody) return;
    dom.b3TableBody.innerHTML = '';

    state.block3Grilles.forEach((g, idx) => {
      const tr = document.createElement('tr');
      const lpr = parseFloat(g.Lpr) || 0;
      const lfact = parseFloat(g.Lfact) || 0;
      const dev = lpr > 0 ? (((lfact - lpr) / lpr) * 100).toFixed(2) : '—';
      const devColor = (dev !== '—' && Math.abs(parseFloat(dev)) <= 10) ? '#166534' : '#b91c1c';

      tr.innerHTML = `
        <td>
          <input type="text" class="calc-field-input calc-field-input--small b3-grille-point" data-idx="${idx}" value="${g.point || (idx + 1)}" style="text-align:center;">
        </td>
        <td>
          <input type="text" class="calc-field-input calc-field-input--small b3-grille-room" data-idx="${idx}" value="${g.room || ''}">
        </td>
        <td>
          <input type="number" class="calc-field-input calc-field-input--small b3-grille-lpr" data-idx="${idx}" value="${g.Lpr || ''}" step="10">
        </td>
        <td>
          <input type="number" class="calc-field-input calc-field-input--small b3-grille-lfact" data-idx="${idx}" value="${g.Lfact || ''}" step="0.1">
        </td>
        <td style="font-weight:700; color:${devColor};">
          ${dev !== '—' ? `${dev} %` : '—'}
        </td>
        <td>
          <button type="button" class="btn-delete-grille" data-idx="${idx}" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; padding:4px;" title="Удалить точку замера">&times;</button>
        </td>
      `;
      dom.b3TableBody.appendChild(tr);
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-point').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].point = e.target.value;
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-room').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].room = e.target.value;
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-lpr').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].Lpr = parseFloat(e.target.value) || 0;
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.b3-grille-lfact').forEach(inp => {
      inp.addEventListener('input', e => {
        const idx = parseInt(e.target.dataset.idx, 10);
        state.block3Grilles[idx].Lfact = parseFloat(e.target.value) || 0;
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    });

    dom.b3TableBody.querySelectorAll('.btn-delete-grille').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = parseInt(btn.dataset.idx, 10);
        state.block3Grilles.splice(idx, 1);
        renderBlock3GrillesTable();
        updateBlock3GrillesSum();
        recalculateBlock3();
      });
    });

    updateBlock3GrillesSum();
  }

  function recalculateBlock3() {
    const system = document.getElementById('b3_system')?.value || 'П11';
    const area = Math.max(0.001, parseFloat(document.getElementById('b3_area')?.value) || 145.925);
    const pressure = Math.max(1, parseFloat(document.getElementById('b3_pressure')?.value) || 116);
    const Lvent = parseFloat(document.getElementById('b3_Lvent')?.value) || 4111.2;
    const Lgrille = parseFloat(document.getElementById('b3_Lgrille')?.value) || 4015.27;
    const Lproject = parseFloat(document.getElementById('b3_Lproject')?.value) || 3950;
    const netResistance = parseFloat(document.getElementById('b3_net_resistance')?.value) || 180;
    const P_fan_total = parseFloat(document.getElementById('b3_P_fan_total')?.value) || 685;
    const frequency = parseFloat(document.getElementById('b3_frequency')?.value) || 50;

    // Утечки Lут = Lвент.ф - Lр-ки.ф (м³/ч)
    const L_leak = Lvent - Lgrille;
    // Невязка в процентах (по п. 7.5 ГОСТ 34060: (Lвент - Lреш) / Lвент * 100%)
    const deviation = Lvent > 0 ? ((L_leak / Lvent) * 100) : 0;
    // Удельные утечки fфакт = Lут / ΣAi (м³/(ч·м²))
    const f_fact = Math.max(0, L_leak / area);

    // Нормативные формулы пределов классов по ГОСТ 34060:
    const fA = 0.097 * Math.pow(pressure, 0.65);
    const fB = 0.032 * Math.pow(pressure, 0.65);
    const fC = 0.0108 * Math.pow(pressure, 0.65);
    const fD = 0.0036 * Math.pow(pressure, 0.65);

    // Допустимые утечки воздуха для классов:
    const Ld_A = fA * area;
    const Ld_B = fB * area;
    const Ld_C = fC * area;
    const Ld_D = fD * area;

    let assignedClass = 'Класс B (Плотный)';
    let assignedClassShort = 'В';
    let isPassedStandard = false;

    if (f_fact <= fD) {
      assignedClass = 'Класс D (Специальный)';
      assignedClassShort = 'D';
      isPassedStandard = true;
    } else if (f_fact <= fC) {
      assignedClass = 'Класс C (Высокая герметичность)';
      assignedClassShort = 'C';
      isPassedStandard = true;
    } else if (f_fact <= fB) {
      assignedClass = 'Класс B (Плотный)';
      assignedClassShort = 'В';
      isPassedStandard = true;
    } else if (f_fact <= fA) {
      assignedClass = 'Класс A (Нормальный)';
      assignedClassShort = 'А';
      isPassedStandard = true;
    } else {
      assignedClass = 'Не соответствует классу A (Негерметично)';
      assignedClassShort = 'Не соответствует';
      isPassedStandard = false;
    }

    updateSummaryMetric('b3_res_Leak', L_leak.toFixed(2), 'м³/ч');
    updateSummaryMetric('b3_res_f_fact', f_fact.toFixed(3), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_Deviation', deviation.toFixed(2), '%');
    updateSummaryMetric('b3_res_fA', fA.toFixed(3), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_fB', fB.toFixed(3), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_fC', fC.toFixed(3), 'м³/(ч·м²)');
    updateSummaryMetric('b3_res_fD', fD.toFixed(3), 'м³/(ч·м²)');

    const devRow = document.getElementById('b3_row_Deviation');
    if (devRow) {
      devRow.style.background = '';
      devRow.style.borderColor = '';
      const devEl = document.getElementById('b3_res_Deviation');
      if (devEl) {
        if (Math.abs(deviation) <= 8) {
          devEl.style.color = '#059669';
        } else if (Math.abs(deviation) <= 10) {
          devEl.style.color = '#d97706';
        } else {
          devEl.style.color = '#dc2626';
        }
      }
    }

    const classEl = document.getElementById('b3_res_Class');
    const statusTextEl = document.getElementById('b3_res_StatusText');
    const bannerEl = document.getElementById('b3_class_banner');

    if (classEl) {
      classEl.innerText = assignedClass;
    }
    if (statusTextEl && bannerEl) {
      if (isPassedStandard && Math.abs(deviation) <= 8) {
        statusTextEl.innerText = `Утечки в пределах нормы ГОСТ 34060 (${deviation.toFixed(2)}% ≤ 8%)`;
        statusTextEl.style.color = '#047857';
        bannerEl.style.background = '#ecfeff';
        bannerEl.style.borderColor = '#a5f3fc';
      } else if (isPassedStandard && Math.abs(deviation) <= 10) {
        statusTextEl.innerText = `В пределах допуска СП 73.13330 (±10%), но превышает 8% по ГОСТ`;
        statusTextEl.style.color = '#854d0e';
        bannerEl.style.background = '#fefce8';
        bannerEl.style.borderColor = '#fde047';
      } else {
        statusTextEl.innerText = `Внимание: превышение допустимой величины подсосов/утечек!`;
        statusTextEl.style.color = '#b91c1c';
        bannerEl.style.background = '#fef2f2';
        bannerEl.style.borderColor = '#fca5a5';
      }
    }

    state.lastResults.block3 = {
      system, area, pressure, Lvent, Lgrille, Lproject, netResistance, P_fan_total, frequency,
      L_leak, deviation, f_fact, fA, fB, fC, fD,
      Ld_A, Ld_B, Ld_C, Ld_D,
      assignedClass, assignedClassShort, isPassedStandard,
      grilles: JSON.parse(JSON.stringify(state.block3Grilles))
    };
  }

  function generateFanCurveSVG(La, Psa, Lpr, Psv, systemName) {
    const w = 640, h = 270;
    const padL = 70, padR = 35, padT = 25, padB = 45;
    const plotW = w - padL - padR;
    const plotH = h - padT - padB;

    const opLa = parseFloat(La) || parseFloat(Lpr) || 27500;
    const opPsa = parseFloat(Psa) || 183.12;
    const basePsv = parseFloat(Psv) || 250;

    // Давление в точке нулевого расхода P_max (строго выше рабочей точки)
    const P_max = Math.max(basePsv * 1.35, opPsa * 1.4, 300);
    // Плавная параболическая аэродинамическая характеристика вентилятора: P(L) = P_max - k * L^2
    const k = (P_max - opPsa) / (opLa * opLa);
    // Расход, при котором давление достигает 0
    const L_end = Math.sqrt(P_max / k);

    // Границы осей, округленные до аккуратных значений
    const maxL = Math.ceil((L_end * 1.08) / 5000) * 5000;
    const maxP = Math.ceil((P_max * 1.15) / 50) * 50;

    const scaleX = (l) => padL + (l / maxL) * plotW;
    const scaleY = (p) => padT + plotH - (p / maxP) * plotH;

    // Генерация 60 гладких точек строго до пересечения с осью X
    const steps = 60;
    const curvePts = [];
    for (let i = 0; i <= steps; i++) {
      const lVal = (i / steps) * L_end;
      const pVal = Math.max(0, P_max - k * (lVal * lVal));
      curvePts.push(`${scaleX(lVal).toFixed(1)},${scaleY(pVal).toFixed(1)}`);
    }
    const curvePath = 'M ' + curvePts.join(' L ');

    const opX = scaleX(opLa);
    const opY = scaleY(opPsa);

    // Сетка и засечки на оси X
    const xStep = maxL <= 30000 ? 5000 : 10000;
    let xTicks = '';
    for (let x = 0; x <= maxL; x += xStep) {
      const cx = scaleX(x);
      xTicks += `
        <line x1="${cx}" y1="${padT + plotH}" x2="${cx}" y2="${padT + plotH + 5}" stroke="#000000" stroke-width="1"/>
        <line x1="${cx}" y1="${padT}" x2="${cx}" y2="${padT + plotH}" stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="2,2"/>
        <text x="${cx}" y="${padT + plotH + 16}" text-anchor="middle" font-size="10" fill="#000000">${x}</text>
      `;
    }

    // Сетка и засечки на оси Y
    const yStep = maxP <= 350 ? 50 : 100;
    let yTicks = '';
    for (let y = 0; y <= maxP; y += yStep) {
      const cy = scaleY(y);
      yTicks += `
        <line x1="${padL - 5}" y1="${cy}" x2="${padL}" y2="${cy}" stroke="#000000" stroke-width="1"/>
        ${y > 0 ? `<line x1="${padL}" y1="${cy}" x2="${padL + plotW}" y2="${cy}" stroke="#e2e8f0" stroke-width="0.75" stroke-dasharray="2,2"/>` : ''}
        <text x="${padL - 8}" y="${cy + 3}" text-anchor="end" font-size="10" fill="#000000">${y}</text>
      `;
    }

    return `
      <svg viewBox="0 0 ${w} ${h}" style="width: 100%; height: auto; max-width: 640px; background: #ffffff; font-family: 'Times New Roman', serif;">
        <!-- Сетка и числовые деления -->
        ${xTicks}
        ${yTicks}

        <!-- Оси координат -->
        <line x1="${padL}" y1="${padT}" x2="${padL}" y2="${padT + plotH}" stroke="#000000" stroke-width="1.25"/>
        <line x1="${padL}" y1="${padT + plotH}" x2="${padL + plotW}" y2="${padT + plotH}" stroke="#000000" stroke-width="1.25"/>

        <!-- Подписи осей -->
        <text x="${padL + plotW / 2}" y="${h - 8}" text-anchor="middle" font-size="11" fill="#000000">Расход воздуха L, м³/ч</text>
        <text x="18" y="${padT + plotH / 2}" text-anchor="middle" font-size="11" fill="#000000" transform="rotate(-90 18 ${padT + plotH / 2})">Давление Psv, Па</text>

        <!-- Аэродинамическая кривая -->
        <path d="${curvePath}" fill="none" stroke="#0284c7" stroke-width="2.2"/>

        <!-- Пунктирные проекции рабочей точки -->
        <line x1="${padL}" y1="${opY}" x2="${opX}" y2="${opY}" stroke="#ef4444" stroke-dasharray="3,3" stroke-width="1.25"/>
        <line x1="${opX}" y1="${opY}" x2="${opX}" y2="${padT + plotH}" stroke="#ef4444" stroke-dasharray="3,3" stroke-width="1.25"/>

        <!-- Точка режима -->
        <circle cx="${opX}" cy="${opY}" r="4.5" fill="#ef4444" stroke="#ffffff" stroke-width="1"/>

        <!-- Выноска параметров -->
        <rect x="${Math.min(opX + 8, padL + plotW - 175)}" y="${Math.max(padT + 5, opY - 32)}" width="170" height="28" fill="#ffffff" stroke="#ef4444" stroke-width="1"/>
        <text x="${Math.min(opX + 14, padL + plotW - 169)}" y="${Math.max(padT + 17, opY - 20)}" font-size="10" fill="#000000">Рабочая точка (Ta):</text>
        <text x="${Math.min(opX + 14, padL + plotW - 169)}" y="${Math.max(padT + 27, opY - 10)}" font-size="9.5" fill="#334155">La = ${Math.round(opLa)} м³/ч, Psa = ${Math.round(opPsa)} Па</text>
      </svg>
    `;
  }

  function generateBlock3ProtocolHTML() {
    const meta = state.protocolMeta;
    const b3 = state.lastResults.block3 || {};

    const systemName = b3.system || meta.systemName || 'П11';
    const area = parseFloat(b3.area) || 145.925;
    const pressure = parseFloat(b3.pressure) || 116;
    const Lvent = parseFloat(b3.Lvent) || 4111.2;
    const Lgrille = parseFloat(b3.Lgrille) || 4015.27;
    const Lproject = parseFloat(b3.Lproject) || 3950;
    const netResistance = parseFloat(b3.netResistance) || 180;
    const P_fan_total = parseFloat(b3.P_fan_total) || 685;
    const frequency = parseFloat(b3.frequency) || 50;

    const L_leak = b3.L_leak !== undefined ? b3.L_leak : (Lvent - Lgrille);
    const deviation = b3.deviation !== undefined ? b3.deviation : (Lvent > 0 ? ((Lvent - Lgrille) / Lvent) * 100 : 0);
    const f_fact = b3.f_fact !== undefined ? b3.f_fact : (area > 0 ? L_leak / area : 0);

    const fA = b3.fA !== undefined ? b3.fA : (0.097 * Math.pow(pressure, 0.65));
    const fB = b3.fB !== undefined ? b3.fB : (0.032 * Math.pow(pressure, 0.65));
    const fC = b3.fC !== undefined ? b3.fC : (0.0108 * Math.pow(pressure, 0.65));
    const fD = b3.fD !== undefined ? b3.fD : (0.0036 * Math.pow(pressure, 0.65));

    const Ld_A = b3.Ld_A !== undefined ? b3.Ld_A : (fA * area);
    const Ld_B = b3.Ld_B !== undefined ? b3.Ld_B : (fB * area);
    const Ld_C = b3.Ld_C !== undefined ? b3.Ld_C : (fC * area);
    const Ld_D = b3.Ld_D !== undefined ? b3.Ld_D : (fD * area);

    const assignedClass = b3.assignedClass || 'Класс B (Плотный)';
    const assignedClassShort = b3.assignedClassShort || 'B';

    // Grilles measurement table
    const grilles = b3.grilles || state.block3Grilles || [];
    let table2Rows = '';
    let sumLpr = 0;
    let sumLfact = 0;

    if (grilles.length === 0) {
      table2Rows = '<tr><td colspan="5">Нет данных по замерам решёток</td></tr>';
    } else {
      grilles.forEach((g, idx) => {
        const lpr = parseFloat(g.Lpr) || 0;
        const lfact = parseFloat(g.Lfact) || 0;
        sumLpr += lpr;
        sumLfact += lfact;
        const dev = lpr > 0 ? (((lfact - lpr) / lpr) * 100).toFixed(1) : '—';
        table2Rows += `
          <tr>
            <td>${g.point || (idx + 1)}</td>
            <td style="text-align: left; padding-left: 8px;">${g.room || '—'}</td>
            <td>${lpr > 0 ? lpr.toFixed(1).replace('.', ',') : '—'}</td>
            <td>${lfact > 0 ? lfact.toFixed(1).replace('.', ',') : '—'}</td>
            <td>${dev !== '—' ? `${dev.replace('.', ',')} %` : '—'}</td>
          </tr>
        `;
      });
    }

    const overallGrilleDev = sumLpr > 0 ? (((sumLfact - sumLpr) / sumLpr) * 100).toFixed(1) : '—';

    // Highlight active class in table
    const isA = assignedClassShort === 'A';
    const isB = assignedClassShort === 'B';
    const isC = assignedClassShort === 'C';
    const isD = assignedClassShort === 'D';

    return `
      <div class="gost-sheet">
        <p class="gost-p-center">
          ${meta.objectName || 'Торговый комплекс «Академический»'}<br>
          ${meta.address || 'г. Москва'}
        </p>

        <p class="gost-p-center" style="margin: 18pt 0 6pt 0; font-weight: bold; font-size: 13pt;">
          ОТЧЁТ<br>
          по результатам проведения испытаний на герметичность воздуховодов<br>
          системы ${systemName} косвенным методом
        </p>

        <p class="gost-p-center" style="margin-bottom: 22pt; font-size: 10pt; color: #4b5563;">
          (на основании ГОСТ 34060-2017 и СП 60.13330-2020)
        </p>

        <p class="gost-section-heading">1 Цель проведения работ</p>

        <p class="gost-p">
          Цель проведения работ – выполнение комплекса работ по испытанию воздуховодов на герметичность косвенным методом.
        </p>
        <p class="gost-p">
          Ввиду высокой сложности и затруднённости проведения подготовительных мероприятий по установке заглушек на испытываемых участках вентиляционных сетей согласно методике ГОСТ 34060-2017, так как монтаж воздуховодов и воздухораспределительных устройств завершён в полном объёме, проведена оценка герметичности воздуховодов по методике испытаний с существующими вентустановками.
        </p>
        <p class="gost-p">
          Все работы проводятся в строгом соответствии с требованиями:
        </p>
        <p class="gost-p" style="margin-left: 1.25cm; text-indent: 0;">
          – <b>ГОСТ 34060-2017</b> «Инженерные сети зданий и сооружений внутренние. Испытание и наладка систем вентиляции и кондиционирования воздуха. Правила проведения и контроль выполнения работ»;<br>
          – <b>СП 73.13330.2016</b> «Внутренние санитарно-технические системы зданий»;<br>
          – <b>СП 60.13330.2020</b> «Отопление, вентиляция и кондиционирование воздуха. Актуализированная редакция СНиП 41-01-2003».
        </p>
        <p class="gost-p">
          Работы по испытанию на герметичность воздуховодов косвенным методом включают в себя проведение замеров давлений и расходов воздуха на вентустановках и на оконечных воздухораспределительных устройствах, а также проведение оценки по результатам измерений на класс герметичности.
        </p>

        <p class="gost-section-heading">2 Комплект измерительного оборудования</p>
        <p class="gost-p">
          Измерения проводились следующим комплектом поверенного оборудования:
        </p>
        <p class="gost-p" style="margin-left: 1.25cm; text-indent: 0;">
          1. <b>Testo 440 dP</b> – комбинированный дифференциальный манометр с подключаемыми зондами-анемометрами (внесён в Госреестр СИ РФ).<br>
          2. <b>Зонд-анемометр с механической крыльчаткой Ду 100</b> – для проведения замеров расходов воздуха на оконечных воздухораспределительных решётках и диффузорах.<br>
          3. <b>Зонд-анемометр с механической крыльчаткой Ду 16 / трубка Пито</b> – для измерения скоростей воздушного потока и статического давления в мерных сечениях воздуховодов.
        </p>

        <p class="gost-section-heading">3 Результаты измерений и испытаний</p>
        <p class="gost-p">
          В ходе испытаний на герметичность воздуховодов согласно программе проведения работ выполнены измерения:
        </p>
        <p class="gost-p" style="margin-left: 1.25cm; text-indent: 0;">
          • статическое давление в сети воздуховодов после вентустановки;<br>
          • фактический расход воздуха у вентилятора (в мерном сечении воздуховода);<br>
          • сумма фактических расходов воздуха по оконечным устройствам (решёткам).
        </p>
        <p class="gost-p">
          В ходе визуального обследования явных дефектов монтажа, повреждений изоляции и разрывов не выявлено. Фильтрующие элементы на момент испытаний не загрязнены.
        </p>

        <p class="gost-p--no-indent" style="text-align: left; margin-bottom: 4pt; font-style: italic;">Таблица 1 – Исходные проектные и фактические параметры системы ${systemName}</p>
        <table class="gost-table">
          <thead>
            <tr>
              <th style="width: 42%;">Параметр системы</th>
              <th style="width: 24%;">Проектное значение</th>
              <th style="width: 24%;">Фактическое значение</th>
              <th style="width: 10%;">Ед. изм.</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align: left; padding-left: 8px;">Расход воздуха</td>
              <td>${Lproject > 0 ? Lproject.toFixed(1).replace('.', ',') : '—'}</td>
              <td><b>${Lvent.toFixed(1).replace('.', ',')}</b></td>
              <td>м³/ч</td>
            </tr>
            <tr>
              <td style="text-align: left; padding-left: 8px;">Статическое давление сети p</td>
              <td>${netResistance > 0 ? netResistance.toFixed(1).replace('.', ',') : '—'}</td>
              <td><b>${pressure.toFixed(1).replace('.', ',')}</b></td>
              <td>Па</td>
            </tr>
            <tr>
              <td style="text-align: left; padding-left: 8px;">Развёрнутая площадь воздуховодов ΣAi</td>
              <td>—</td>
              <td><b>${area.toFixed(3).replace('.', ',')}</b></td>
              <td>м²</td>
            </tr>
            ${(P_fan_total > 0 || frequency > 0) ? `
              <tr>
                <td style="text-align: left; padding-left: 8px;">Давление вентилятора / Частота ПЧ</td>
                <td>—</td>
                <td>${P_fan_total > 0 ? `${P_fan_total} Па` : ''}${P_fan_total > 0 && frequency > 0 ? ' / ' : ''}${frequency > 0 ? `${frequency} Гц` : ''}</td>
                <td>—</td>
              </tr>
            ` : ''}
          </tbody>
        </table>

        <p class="gost-p--no-indent" style="text-align: left; margin: 14pt 0 4pt 0; font-style: italic;">Таблица 2 – Ведомость замеров расходов по оконечным устройствам (решёткам)</p>
        <table class="gost-table">
          <thead>
            <tr>
              <th style="width: 12%;">№ точки</th>
              <th style="width: 42%;">Наименование зоны / помещения</th>
              <th style="width: 16%;">Lпр, м³/ч</th>
              <th style="width: 16%;">Lфакт, м³/ч</th>
              <th style="width: 14%;">Невязка, %</th>
            </tr>
          </thead>
          <tbody>
            ${table2Rows}
          </tbody>
          <tfoot>
            <tr style="font-weight: bold; background: #fdfdfd;">
              <td colspan="2" style="text-align: right; padding-right: 10px;">Итого по решёткам:</td>
              <td>${sumLpr > 0 ? sumLpr.toFixed(1).replace('.', ',') : '—'}</td>
              <td>${sumLfact > 0 ? sumLfact.toFixed(1).replace('.', ',') : Lgrille.toFixed(1).replace('.', ',')}</td>
              <td>${overallGrilleDev !== '—' ? `${overallGrilleDev.replace('.', ',')} %` : '—'}</td>
            </tr>
          </tfoot>
        </table>

        <p class="gost-section-heading">4 Определение класса герметичности воздуховодов системы ${systemName}</p>

        <p class="gost-p">
          После проведения всех необходимых замеров проводится оценка класса герметичности воздуховодов согласно пункту 7 ГОСТ 34060-2017 и приложению «М» СП 60.13330-2020.
        </p>

        <p class="gost-p">
          Величина подсосов / утечек воздуха по вентиляционной сети $\\Delta_{\\text{ут}}$ (%) определяется по формуле (1):
        </p>
        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`\\Delta_{\\text{ут}} = \\frac{L_{\\text{вент.ф}} - L_{\\text{р-ки.ф}}}{L_{\\text{вент.ф}}} \\cdot 100\\% = \\frac{${Lvent.toFixed(1).replace('.', ',')} - ${Lgrille.toFixed(1).replace('.', ',')}}{${Lvent.toFixed(1).replace('.', ',')}} \\cdot 100\\% = ${deviation.toFixed(2).replace('.', ',')}\\%`)}
          </div>
          <span class="gost-formula-num">(1)</span>
        </div>
        <p class="gost-note">
          Согласно п. 7.5 ГОСТ 34060-2017, $\\Delta_{\\text{ут}}$ не должна превышать 8% (допустимое отклонение по СП 73.13330.2016 составляет $\\pm 10\\%$).
        </p>

        <p class="gost-p">
          Величина фактических утечек воздуха $L_{\\text{ут}}$ (м³/ч) при проведении испытаний косвенным методом определяется по формуле (2):
        </p>
        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`L_{\\text{ут}} = L_{\\text{вент.ф}} - L_{\\text{р-ки.ф}} = ${Lvent.toFixed(1).replace('.', ',')} - ${Lgrille.toFixed(1).replace('.', ',')} = ${L_leak.toFixed(2).replace('.', ',')} \\text{ м}^3/\\text{ч}`)}
          </div>
          <span class="gost-formula-num">(2)</span>
        </div>

        <p class="gost-p">
          Допустимые потери или подсосы воздуха через неплотности в воздуховодах $L_d$ (м³/ч) не должны превышать расхода, рассчитанного по формуле (3):
        </p>
        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`L_d = f \\cdot \\Sigma A_i`)}
          </div>
          <span class="gost-formula-num">(3)</span>
        </div>
        <p class="gost-note">
          где $\\Sigma A_i = ${area.toFixed(3).replace('.', ',')} \\text{ м}^2$ – общая развёрнутая площадь воздуховодов системы;<br>
          $f$ – предельные удельные потери или подсосы, м³/(ч·м²), приходящиеся на 1 м² площади воздуховодов, определяемые в зависимости от статического давления $p = ${pressure.toFixed(1).replace('.', ',')} \\text{ Па}$:
        </p>

        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`\\text{для класса герметичности А: } f_A = 0{,}097 \\cdot p^{0{,}65} = 0{,}097 \\cdot ${pressure.toFixed(1).replace('.', ',')}^{0{,}65} = ${fA.toFixed(3).replace('.', ',')} \\text{ м}^3/(\\text{ч}\\cdot\\text{м}^2)`)}
          </div>
          <span class="gost-formula-num">(4)</span>
        </div>

        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`\\text{для класса герметичности В: } f_B = 0{,}032 \\cdot p^{0{,}65} = 0{,}032 \\cdot ${pressure.toFixed(1).replace('.', ',')}^{0{,}65} = ${fB.toFixed(3).replace('.', ',')} \\text{ м}^3/(\\text{ч}\\cdot\\text{м}^2)`)}
          </div>
          <span class="gost-formula-num">(5)</span>
        </div>

        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`\\text{для класса герметичности С: } f_C = 0{,}0108 \\cdot p^{0{,}65} = 0{,}0108 \\cdot ${pressure.toFixed(1).replace('.', ',')}^{0{,}65} = ${fC.toFixed(3).replace('.', ',')} \\text{ м}^3/(\\text{ч}\\cdot\\text{м}^2)`)}
          </div>
          <span class="gost-formula-num">(6)</span>
        </div>

        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`\\text{для класса герметичности D: } f_D = 0{,}0036 \\cdot p^{0{,}65} = 0{,}0036 \\cdot ${pressure.toFixed(1).replace('.', ',')}^{0{,}65} = ${fD.toFixed(3).replace('.', ',')} \\text{ м}^3/(\\text{ч}\\cdot\\text{м}^2)`)}
          </div>
          <span class="gost-formula-num">(7)</span>
        </div>

        <p class="gost-p">
          Фактические удельные потери (утечки) воздуха $f_{\\text{факт}}$ (м³/(ч·м²)) составляют:
        </p>
        <div class="gost-formula-row">
          <div class="gost-formula-math">
            ${kTeX(`f_{\\text{факт}} = \\frac{L_{\\text{ут}}}{\\Sigma A_i} = \\frac{${L_leak.toFixed(2).replace('.', ',')}}{${area.toFixed(3).replace('.', ',')}} = ${f_fact.toFixed(3).replace('.', ',')} \\text{ м}^3/(\\text{ч}\\cdot\\text{м}^2)`)}
          </div>
          <span class="gost-formula-num">(8)</span>
        </div>

        <p class="gost-p--no-indent" style="text-align: left; margin: 14pt 0 4pt 0; font-style: italic;">Таблица 3 – Нормативные критерии классов герметичности по ГОСТ 34060-2017</p>
        <table class="gost-table">
          <thead>
            <tr>
              <th rowspan="2" style="width: 14%;">Класс герметичности</th>
              <th colspan="2" style="width: 28%;">Предельный перепад давления Ps, Па</th>
              <th rowspan="2" style="width: 20%;">Формула fmax, м³/(ч·м²)</th>
              <th rowspan="2" style="width: 20%;">Предел f при p = ${pressure.toFixed(1).replace('.', ',')} Па</th>
              <th rowspan="2" style="width: 18%;">Предельный расход Ld, м³/ч</th>
            </tr>
            <tr>
              <th style="font-size: 9.5pt;">Положительное</th>
              <th style="font-size: 9.5pt;">Отрицательное</th>
            </tr>
          </thead>
          <tbody>
            <tr style="${isA ? 'background: #f0fdf4; font-weight: bold;' : ''}">
              <td><b>А</b></td>
              <td>500</td>
              <td>500</td>
              <td>0,097 · p^0,65</td>
              <td>${fA.toFixed(3).replace('.', ',')}</td>
              <td>${Ld_A.toFixed(1).replace('.', ',')}</td>
            </tr>
            <tr style="${isB ? 'background: #f0fdf4; font-weight: bold;' : ''}">
              <td><b>В</b></td>
              <td>1000</td>
              <td>750</td>
              <td>0,032 · p^0,65</td>
              <td>${fB.toFixed(3).replace('.', ',')}</td>
              <td>${Ld_B.toFixed(1).replace('.', ',')}</td>
            </tr>
            <tr style="${isC ? 'background: #f0fdf4; font-weight: bold;' : ''}">
              <td><b>С</b></td>
              <td>2000</td>
              <td>750</td>
              <td>0,0108 · p^0,65</td>
              <td>${fC.toFixed(3).replace('.', ',')}</td>
              <td>${Ld_C.toFixed(1).replace('.', ',')}</td>
            </tr>
            <tr style="${isD ? 'background: #f0fdf4; font-weight: bold;' : ''}">
              <td><b>D</b></td>
              <td>2000</td>
              <td>750</td>
              <td>0,0036 · p^0,65</td>
              <td>${fD.toFixed(3).replace('.', ',')}</td>
              <td>${Ld_D.toFixed(1).replace('.', ',')}</td>
            </tr>
          </tbody>
        </table>

        <p class="gost-p--no-indent" style="text-align: left; margin: 14pt 0 4pt 0; font-style: italic;">Таблица 4 – Сводная ведомость оценки герметичности воздуховодов системы</p>
        <table class="gost-table">
          <thead>
            <tr>
              <th style="width: 11%;">Система</th>
              <th style="width: 11%;">ΣAi, м²</th>
              <th style="width: 12%;">Lвент.ф, м³/ч</th>
              <th style="width: 12%;">Lр-ки.ф, м³/ч</th>
              <th style="width: 10%;">Δут, %</th>
              <th style="width: 11%;">Lут, м³/ч</th>
              <th style="width: 9%;">p, Па</th>
              <th style="width: 12%;">fфакт, м³/(ч·м²)</th>
              <th style="width: 12%;">Фактический класс</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><b>${systemName}</b></td>
              <td>${area.toFixed(3).replace('.', ',')}</td>
              <td>${Lvent.toFixed(1).replace('.', ',')}</td>
              <td>${Lgrille.toFixed(1).replace('.', ',')}</td>
              <td style="font-weight: bold; color: ${Math.abs(deviation) <= 8 ? '#166534' : (Math.abs(deviation) <= 10 ? '#854d0e' : '#b91c1c')};">
                ${deviation.toFixed(2).replace('.', ',')}%
              </td>
              <td>${L_leak.toFixed(2).replace('.', ',')}</td>
              <td>${pressure.toFixed(1).replace('.', ',')}</td>
              <td><b>${f_fact.toFixed(3).replace('.', ',')}</b></td>
              <td style="font-weight: bold; color: #166534;">
                Класс «${assignedClassShort}»
              </td>
            </tr>
          </tbody>
        </table>

        <p class="gost-section-heading">5 Выводы</p>
        <p class="gost-p">
          В результате проведения комплекса испытаний воздуховодов системы <b>${systemName}</b> на герметичность косвенным методом установлено следующее:
        </p>
        <p class="gost-p" style="margin-left: 1.25cm; text-indent: 0;">
          1) Явных мест негерметичностей и дефектов монтажа системы ${systemName} не выявлено. Сеть воздуховодов смонтирована с надлежащим качеством. Фильтрующие элементы на момент испытаний не загрязнены. Система работает исправно.<br><br>
          2) Утечки воздуха по системе составляют <b>${L_leak.toFixed(2).replace('.', ',')} м³/ч</b> (величина невязки <b>${deviation.toFixed(2).replace('.', ',')}%</b>), что ${Math.abs(deviation) <= 8 ? '<b>соответствует допустимым нормам</b> ГОСТ 34060-2017 (не более 8%) и СП 73.13330.2016 (±10%)' : (Math.abs(deviation) <= 10 ? '<b>соответствует допустимым отклонениям</b> СП 73.13330.2016 (±10%), но превышает рекомендуемые 8% по п. 7.5 ГОСТ 34060-2017' : '<b>превышает допустимые отклонения</b> СП 73.13330.2016 и ГОСТ 34060-2017')}.<br><br>
          3) Фактические удельные утечки воздуха составляют <b>${f_fact.toFixed(3).replace('.', ',')} м³/(ч·м²)</b> при статическом давлении <b>${pressure.toFixed(1).replace('.', ',')} Па</b>. Класс герметичности воздуховодов, определённый на основании выполненных измерений и расчётов по ГОСТ 34060-2017 и СП 60.13330-2020, соответствует <b>«${assignedClass}»</b>.
        </p>

        <!-- ГОСТ-штамп -->
        <table class="gost-stamp-table">
          <tr>
            <td style="width:12%;">Изм.</td>
            <td style="width:10%;">1</td>
            <td style="width:18%;">Шифр проекта:</td>
            <td colspan="2">${meta.number || '01/ИНГ-2025'}</td>
            <td rowspan="4" style="width:34%; text-align:center; vertical-align:middle;">
              ООО «Баланс Инженерных Систем»<br>
              Испытательная лаборатория<br>
              М.П.
            </td>
          </tr>
          <tr>
            <td>Разраб.</td>
            <td>${meta.engineer || 'Журавлев А.Д.'}</td>
            <td>Объект:</td>
            <td colspan="2">${meta.objectName || 'ТК «Академический»'}</td>
          </tr>
          <tr>
            <td>Пров.</td>
            <td>${meta.approver || 'Журавлев А.Д.'}</td>
            <td>Система:</td>
            <td colspan="2">${systemName}</td>
          </tr>
          <tr>
            <td>Утв.</td>
            <td>${meta.approver || 'Журавлев А.Д.'}</td>
            <td>Стадия / Лист:</td>
            <td style="width:15%;">И / Лист 1</td>
            <td style="width:15%;">Дата: ${meta.date}</td>
          </tr>
        </table>
      </div>
    `;
  }

  function generateProtocolHTML() {
    const meta = state.protocolMeta;

    if (state.currentBlock === 'block3') {
      return generateBlock3ProtocolHTML();
    }

    if (state.currentBlock === 'block1') {
      const res = state.lastResults.block1 || {};
      const Lpr = res.Lpr || 27500;
      const Psv = res.Psv || 250;
      const Tpg = res.Tpg || 760;
      const Tpom = res.Tpom || 20;
      const Tv = res.Tv || (Tpg - 62);
      const Ta = res.Ta || (273 + Tpom);
      const rho_a = res.rho_a || (353 / Ta);
      const rho_sm = res.rho_sm || ((2 * rho_a * Ta) / (Tpg + Tv));
      const rho_v = res.rho_v || (353 / Tv);
      const h_top = res.h_top !== undefined ? res.h_top : 10.0;
      const h_bot = res.h_bot !== undefined ? res.h_bot : -1.0;
      const h = Math.max(0, h_top - h_bot);
      const Psa = res.Psa !== undefined ? res.Psa : 365.52;
      const La = res.La || Lpr;
      const Ga = parseFloat(res.Ga) || ((La * rho_a) / 3600);
      const G0 = parseFloat(res.G0) || (Ga * 0.95);
      const L0 = res.L0 || Math.round((G0 * 3600) / rho_a);
      const rawLfactInput = document.getElementById('b1_Lfact')?.value?.trim();
      const parsedLfactInput = rawLfactInput ? parseFloat(rawLfactInput) : null;
      const Lfact = (res.Lfact !== null && res.Lfact !== undefined && !isNaN(res.Lfact))
        ? Math.round(res.Lfact)
        : (parsedLfactInput !== null && !isNaN(parsedLfactInput) ? Math.round(parsedLfactInput) : null);
      let effectiveDeviation = (res.deviation !== null && res.deviation !== undefined && !isNaN(res.deviation))
        ? res.deviation
        : null;
      if (effectiveDeviation === null && Lfact !== null && !isNaN(Lfact) && L0 > 0) {
        effectiveDeviation = ((Lfact - L0) / L0) * 100;
      }

      let table1Rows = '';
      const floors = res.floorResults || [];
      if (floors.length === 0) {
        table1Rows = '<tr><td colspan="11">Нет данных по закрытым клапанам</td></tr>';
      } else {
        floors.forEach((r, idx) => {
          table1Rows += `
            <tr>
              <td>${idx + 1}</td>
              <td>${r.kms || '0,4'}</td>
              <td>${(r.lambda || '0,016').toString().replace('.', ',')}</td>
              <td>${(r.li || '3,0').toString().replace('.', ',')}</td>
              <td>${(r.de || '—').toString().replace('.', ',')}</td>
              <td>${(r.Fn || '—').toString().replace('.', ',')}</td>
              <td>${(r.Pn || '—').toString().replace('.', ',')}</td>
              <td>${(r.Ga_curr || '—').toString().replace('.', ',')}</td>
              <td>${(r.P_sn || '—').toString().replace('.', ',')}</td>
              <td>${(r.F_val || '—').toString().replace('.', ',')}</td>
              <td>${(r.G_leak || '—').toString().replace('.', ',')}</td>
            </tr>
          `;
        });
      }

      const firstFloor = floors[0] || { P_sn: '198,10', G_leak: '0,081' };
      const sumGleak = floors.reduce((acc, f) => acc + (parseFloat(f.G_leak) || 0), 0);
      const chartSvg = generateFanCurveSVG(La, Psa, Lpr, Psv, meta.systemName);

      return `
        <div class="gost-sheet">
          <p class="gost-p-center">
            ${meta.objectName || 'Торговый центр «Академический»'}<br>
            ${meta.address || 'СПб, Гражданский проспект, квартал 9А'}
          </p>

          <p class="gost-p-center" style="margin: 16pt 0 10pt 0;">
            Расчётное определение значений требуемого расхода воздуха через открытое дымоприёмное устройство при приёмо-сдаточных и периодических испытаниях противодымной вентиляции
          </p>

          <p class="gost-p-center" style="margin-bottom: 20pt;">
            ${meta.systemName || 'Система ДУ1'}
          </p>

          <p class="gost-section-heading">1 Исходные проектные данные и условия испытаний</p>

          <p class="gost-p">
            Целью выполнения расчёта является определение расхода воздуха для наиболее удалённого от вентилятора дымоприемного устройства системы вытяжной противодымной вентиляции при фактической температуре воздуха в защищаемом помещении при проведении испытаний, так как оценка фактического расхода воздуха с проектными значениями не допускается требованиями ГОСТ Р 53300-2009 «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний».
          </p>

          <p class="gost-p">
            Методика проведения расчёта, изложенная в приложении Б ГОСТ Р 53300-2009 «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний», прилагается.
          </p>

          <p class="gost-p">
            Расход воздуха подлежит расчетному определению для наиболее удаленного от вентилятора дымоприёмного устройства испытываемой системы вытяжной противодымной вентиляции при фактической температуре в защищаемом помещении в момент проведения испытаний.
          </p>

          <p class="gost-p">
            Характеристики системы приняты согласно проектной документации:<br>
            Наиболее удалённое дымоприёмное устройство от вентилятора расположено на ${meta.section || 'цокольном этаже'}.<br>
            Lпр = ${Lpr} м³/ч – проектный объёмный расход вентилятора,<br>
            Psv = ${Psv} Па – фактическое давление, создаваемое вентилятором,<br>
            Тпг = ${Tpg} К – температура продуктов горения, удаляемых из помещения,<br>
            Тv = ${Tv} К – температура продуктов горения, перемещаемых вентилятором,<br>
            Тa = ${Tpom} °C = ${Ta} К – температура воздуха в помещении на момент проведения испытаний,<br>
            Нуст. = ${h_top.toFixed(3).replace('.', ',')} м – высотная отметка установки вентилятора,<br>
            Нкл. = ${h_bot.toFixed(3).replace('.', ',')} м – высотная отметка расположения наиболее удалённого дымоприёмного устройства.
          </p>

          <p class="gost-section-heading">2 Расчёт аэродинамических параметров системы по методике ГОСТ Р 53300-2009</p>

          <!-- Formula (1) -->
          <p class="gost-p">Среднюю плотность газа в вытяжном канале определяем по формуле (1):</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`\\rho_{sm} = \\frac{2 \\cdot \\rho_a \\cdot T_a}{T_{\\text{пг}} + T_v} = \\frac{2 \\cdot ${rho_a.toFixed(3).replace('.', ',')} \\cdot ${Ta}}{${Tpg} + ${Tv}} = ${rho_sm.toFixed(4).replace('.', ',')} \\text{ кг/м}^3`)}
            </div>
            <span class="gost-formula-num">(1)</span>
          </div>
          <p class="gost-note">
            где ${kTeX(`\\rho_a = \\frac{353}{T_a} = \\frac{353}{${Ta}} = ${rho_a.toFixed(3).replace('.', ',')} \\text{ кг/м}^3`, false)} – плотность воздуха при температуре ${Tpom} °C.
          </p>

          <!-- Formula (2) -->
          <p class="gost-p">Вычисляем давление разряжения в вытяжном канале перед вентилятором по формуле (2):</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`P_{sa} = \\frac{P_{sv} \\cdot \\rho_v}{1{,}2} + g \\cdot h \\cdot (\\rho_a - \\rho_{sm}) = \\frac{${Psv} \\cdot ${rho_v.toFixed(3).replace('.', ',')}}{1{,}2} + 9{,}81 \\cdot ${h.toFixed(1).replace('.', ',')} \\cdot (${rho_a.toFixed(3).replace('.', ',')} - ${rho_sm.toFixed(4).replace('.', ',')}) = ${Psa.toFixed(2).replace('.', ',')} \\text{ Па}`)}
            </div>
            <span class="gost-formula-num">(2)</span>
          </div>
          <p class="gost-note">
            где ${kTeX(`\\rho_v = \\frac{353}{T_v} = \\frac{353}{${Tv}} = ${rho_v.toFixed(3).replace('.', ',')} \\text{ кг/м}^3`, false)} – плотность перемещаемых дымовых газов при температуре Tv;<br>
            ${kTeX(`h = H_{\\text{уст}} - H_{\\text{кл}} = ${h_top.toFixed(1).replace('.', ',')} - (${h_bot.toFixed(1).replace('.', ',')}) = ${h.toFixed(1).replace('.', ',')} \\text{ м}`, false)} – разность уровней расположения вентилятора и открытого ДПУ.
          </p>

          <!-- Formula (3) -->
          <p class="gost-p">По формуле (3) вычисляем соотношение параметров в скобках:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`L_a = f\\left( \\frac{1{,}2 \\cdot P_{sa}}{\\rho_v} \\right) = f(${Math.round(1.2 * Psa / rho_v)})`)}
            </div>
            <span class="gost-formula-num">(3)</span>
          </div>
          <p class="gost-p">
            Используя аэродинамическую характеристику вентилятора (рисунок 1), определяем приближенное значение объёмного расхода воздуха, перемещаемого им при температуре Ta:<br>
            La ≈ ${Math.round(La)} м³/ч.
          </p>

          <!-- Formula (4) -->
          <p class="gost-p">По формуле (4) определяем массовый расход воздуха перед вентилятором:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`G_a = \\frac{\\rho_a \\cdot L_a}{3600} = \\frac{${rho_a.toFixed(3).replace('.', ',')} \\cdot ${Math.round(La)}}{3600} = ${Ga.toFixed(3).replace('.', ',')} \\text{ кг/с}`)}
            </div>
            <span class="gost-formula-num">(4)</span>
          </div>

          <p class="gost-section-heading">3 Расчёт подсосов воздуха через закрытые противопожарные клапаны</p>

          <!-- Formula (5) -->
          <p class="gost-p">По формуле (5) определяем разрежение в вытяжном канале перед ближайшим к вентилятору закрытым противопожарным клапаном:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`P_{sn} = P_{sa} - 0{,}5 \\cdot \\rho_a \\cdot \\left( \\Sigma\\xi + \\frac{\\lambda_n \\cdot l_n}{d_{en}} \\right) \\cdot \\left( \\frac{G_a}{\\rho_a \\cdot F_n} \\right)^2 = ${(firstFloor.P_sn || '198,10').toString().replace('.', ',')} \\text{ Па}`)}
            </div>
            <span class="gost-formula-num">(5)</span>
          </div>

          <!-- Formula (6) -->
          <p class="gost-p">По формуле (6) вычисляем подсос воздуха через ближайшее к вентилятору закрытое дымоприёмное устройство:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`\\Delta G_{dpn} = F_{dpn} \\cdot \\left( \\frac{P_{sn}}{S_{dpn}} \\right)^{0{,}5} = ${(firstFloor.G_leak || '0,081').toString().replace('.', ',')} \\text{ кг/с}`)}
            </div>
            <span class="gost-formula-num">(6)</span>
          </div>

          <!-- Formula (7) & (8) -->
          <p class="gost-p">По формуле (7) определяем разрежение в вытяжном канале у i-го закрытого противопожарного клапана при температуре Ta:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`P_{si} = P_{sn} - 0{,}5 \\cdot \\rho_a \\cdot \\left( \\Sigma\\xi + \\frac{\\lambda_i \\cdot l_i}{d_{ei}} \\right) \\cdot \\left( \\frac{G_i}{\\rho_a \\cdot F_i} \\right)^2`)}
            </div>
            <span class="gost-formula-num">(7)</span>
          </div>

          <p class="gost-p">По формуле (8) вычисляем подсос воздуха через i-е закрытое дымоприёмное устройство:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`\\Delta G_{dpi} = F_{dpi} \\cdot \\left( \\frac{P_{si}}{S_{dpi}} \\right)^{0{,}5}`)}
            </div>
            <span class="gost-formula-num">(8)</span>
          </div>

          <p class="gost-note">
            где:<br>
            ${kTeX(`\\lambda_n\\,(\\lambda_i) = 0{,}016`, false)} – коэффициент сопротивления трения вытяжного канала;<br>
            ln (li) – длина участка вытяжного канала, принимается по данным проектной документации;<br>
            ${kTeX(`d_{en}\\,(d_{ei}) = \\frac{4 \\cdot F}{P}`, false)} – эквивалентный гидравлический диаметр вытяжного канала;<br>
            F – площадь проходного сечения вытяжного канала, м2;<br>
            P – периметр проходного сечения вытяжного канала, м;<br>
            Fdpn (Fdpi) – площадь проходного сечения закрытого противопожарного клапана, м2;<br>
            Sdpn (Sdpi) = 10 000 м3/кг – удельное сопротивление воздухопроницанию закрытого противопожарного клапана.
          </p>

          <!-- Table 1 -->
          <p class="gost-p--no-indent">Расчёт параметров Psn, ΔGdpn сведён в таблицу 1:</p>
          <p class="gost-p--no-indent" style="text-align: left; margin-bottom: 4pt;">таблица 1</p>
          <table class="gost-table">
            <thead>
              <tr>
                <th>№ клапана по удалению от вентилятора</th>
                <th>КМС</th>
                <th>λn (λi)</th>
                <th>ln (li), м</th>
                <th>den, м</th>
                <th>Fn (Fi), м2</th>
                <th>Pn (Pi), м</th>
                <th>Ga (Gi), кг/с</th>
                <th>Psn (Psi), Па</th>
                <th>Площадь клапана Fdpn, м2</th>
                <th>ΔGdpn, кг/с</th>
              </tr>
            </thead>
            <tbody>
              ${table1Rows}
            </tbody>
          </table>

          <p class="gost-section-heading">4 Определение требуемого расхода воздуха через открытое дымоприёмное устройство</p>

          <!-- Formula (9) -->
          <p class="gost-p">По формуле (9) вычисляем массовый расход воздуха, удаляемого через открытое дымоприёмное устройство:</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`G_0 = G_a - (\\Delta G_{dpn} + \\Sigma\\Delta G_{dpi}) = ${Ga.toFixed(3).replace('.', ',')} - ${sumGleak.toFixed(4).replace('.', ',')} = ${G0.toFixed(3).replace('.', ',')} \\text{ кг/с}`)}
            </div>
            <span class="gost-formula-num">(9)</span>
          </div>

          <!-- Formula (10) -->
          <p class="gost-p">Требуемое значение расхода воздуха через открытое дымоприемное устройство испытываемой системы вытяжной противодымной вентиляции определяем по формуле (10):</p>
          <div class="gost-formula-row">
            <div class="gost-formula-math">
              ${kTeX(`L_0 = \\frac{3600 \\cdot G_0}{\\rho_a} = \\frac{3600 \\cdot ${G0.toFixed(3).replace('.', ',')}}{${rho_a.toFixed(3).replace('.', ',')}} = ${Math.round(L0)} \\text{ м}^3/\\text{ч}`)}
            </div>
            <span class="gost-formula-num">(10)</span>
          </div>

          <!-- Fact comparison -->
          ${(Lfact !== null && effectiveDeviation !== null && !isNaN(effectiveDeviation)) ? `
            <p class="gost-p">
              Фактический расход воздуха через наиболее удалённое от вентилятора открытое дымоприёмное устройство ${kTeX(`L_{\\text{ф}} = ${Math.round(Lfact)} \\text{ м}^3/\\text{ч}`, false)}.<br>
              Отклонение фактических показателей по расходу воздуха от определённых по расчёту:
            </p>
            <div class="gost-formula-row">
              <div class="gost-formula-math">
                ${kTeX(`\\delta = \\left( \\frac{L_{\\text{ф}} - L_0}{L_0} \\right) \\cdot 100\\% = \\left( \\frac{${Math.round(Lfact)} - ${Math.round(L0)}}{${Math.round(L0)}} \\right) \\cdot 100\\% = ${effectiveDeviation > 0 ? '+' : ''}${effectiveDeviation.toFixed(1).replace('.', ',')}\\%`)}
              </div>
            </div>
          ` : `
            <p class="gost-p">
              Фактический расход воздуха через наиболее удалённое от вентилятора открытое дымоприёмное устройство Lф = ${Lfact ? Math.round(Lfact) + ' м³/ч' : '_______ м³/ч'}.
            </p>
          `}

          <p class="gost-section-heading">5 Заключение и выводы по результатам испытаний</p>
          <p class="gost-p">
            1. В соответствии с ГОСТ Р 53300-2009 «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний», определено требуемое значение расхода воздуха через открытое дымоприёмное устройство для систем противодымной вытяжной вентиляции.<br>
            2. В соответствии с ГОСТ Р 53300-2009 «Противодымная защита зданий и сооружений. Методы приёмосдаточных и периодических испытаний», отклонение фактических показателей по расходу воздуха от определённых по расчёту, допускается не более 15%.<br>
            3. ${(effectiveDeviation !== null && !isNaN(effectiveDeviation)) ? (Math.abs(effectiveDeviation) <= 15 ? `Фактическое отклонение составляет ${effectiveDeviation > 0 ? '+' : ''}${effectiveDeviation.toFixed(1).replace('.', ',')}%. Система работает удовлетворительно.` : `Фактическое отклонение составляет ${effectiveDeviation > 0 ? '+' : ''}${effectiveDeviation.toFixed(1).replace('.', ',')}%. Отклонение превышает допустимые 15%, система работает неудовлетворительно.`) : 'Система работает удовлетворительно.'}
          </p>

          <p class="gost-section-heading">6 Аэродинамическая характеристика вентилятора</p>
          <p class="gost-p--no-indent" style="text-align: center; margin: 10pt 0 6pt 0;">
            Рисунок 1. Аэродинамическая характеристика вентилятора системы ${meta.systemName || 'ДУ1'}
          </p>
          <div class="gost-chart-box">
            ${chartSvg}
          </div>

          <!-- Stamp Table -->
          <table class="gost-stamp-table">
            <tr>
              <td style="width:12%;">Изм.</td>
              <td style="width:10%;">1</td>
              <td style="width:18%;">Шифр проекта:</td>
              <td colspan="2">${meta.number || '109.005/П-02'}</td>
              <td rowspan="4" style="width:34%; text-align:center; vertical-align:middle;">
                ООО «Баланс Инженерных Систем»<br>
                Испытательная лаборатория<br>
                М.П.
              </td>
            </tr>
            <tr>
              <td>Разраб.</td>
              <td>${meta.engineer || 'Иванов И.И.'}</td>
              <td>Объект:</td>
              <td colspan="2">${meta.objectName || 'ТК «Академический»'}</td>
            </tr>
            <tr>
              <td>Пров.</td>
              <td>${meta.approver || 'Петров П.П.'}</td>
              <td>Система:</td>
              <td colspan="2">${meta.systemName || 'Система ДУ1'}</td>
            </tr>
            <tr>
              <td>Утв.</td>
              <td>${meta.approver || 'Петров П.П.'}</td>
              <td>Стадия / Лист:</td>
              <td style="width:15%;">И / Лист 1</td>
              <td style="width:15%;">Дата: ${meta.date}</td>
            </tr>
          </table>
        </div>
      `;
    }

    // Fallback for Block 2 (AVOK)
    const res = state.lastResults.avok || {};
    const b2Sys = document.getElementById('b2_system_name')?.value?.trim() || meta.systemName || 'Система ДУ1';
    const b2Obj = document.getElementById('b2_object_name')?.value?.trim() || meta.objectName || 'Торговый центр «Академический»';
    const b2Addr = document.getElementById('b2_address')?.value?.trim() || meta.address || '';
    const b2Eng = document.getElementById('b2_engineer')?.value?.trim() || meta.engineer || 'Иванов И.И.';

    const protocolTitle = `ПРОТОКОЛ РАСЧЕТА СИСТЕМЫ ПРОТИВОДЫМНОЙ ВЕНТИЛЯЦИИ (${res.type || 'АВОК'})`;
    
    let detailsRows = '';
    (res.details || []).forEach(d => {
      detailsRows += `<tr><td class="field-name">${d.label}:</td><td><b>${d.val}</b></td></tr>`;
    });

    const tableHtml = `
      <table class="protocol-table-info">
        <tr><td class="field-name">Объект / Адрес:</td><td>${b2Obj}${b2Addr ? ` (${b2Addr})` : ''}</td><td class="field-name">Дата расчета:</td><td>${meta.date}</td></tr>
        <tr><td class="field-name">Наименование системы:</td><td>${b2Sys}</td><td class="field-name">Номер протокола:</td><td>№ ${meta.number}</td></tr>
        <tr><td class="field-name">Нормативная база:</td><td>Рекомендации АВОК 5.5.1</td><td class="field-name">Выполнил:</td><td>${b2Eng}</td></tr>
      </table>
      <h4 style="margin:14px 0 6px; font-size:12px; text-transform:uppercase;">Результаты расчета параметров противодымной вентиляции:</h4>
      <table class="protocol-table-info">
        <tr><td class="field-name">${res.mainLabel || 'Расход воздуха'}:</td><td><b style="font-size:14px;">${res.mainVal || '—'}</b></td></tr>
        <tr><td class="field-name">${res.sub1Label || 'Давление'}:</td><td><b>${res.sub1Val || '—'}</b></td></tr>
        <tr><td class="field-name">${res.sub2Label || 'Показатель'}:</td><td><b>${res.sub2Val || '—'}</b></td></tr>
        ${detailsRows}
      </table>
    `;
    const conclusionText = `Заключение: Расчетные параметры системы противодымной вентиляции ${b2Sys} соответствуют требованиям нормативов АВОК и СП 7.13130.`;

    return `
      <div class="protocol-sheet-container">
        <div class="protocol-sheet-header">
          <div class="protocol-org-name">
            ООО «Баланс Инженерных Систем»
            <div class="protocol-org-sub">ИНН: 7700000000 | biscorp.ru | office@bis-rf.ru</div>
          </div>
          <div class="protocol-stamp-box">
            <b>УТВЕРЖДАЮ:</b><br>
            Руководитель лаборатории<br>
            <div class="sign-underline" style="width:140px; margin:4px 0 2px auto;"></div>
            <span>/ ${meta.approver} /</span>
          </div>
        </div>

        <div class="protocol-main-title">${protocolTitle}</div>
        <div class="protocol-number-date">Протокол № ${meta.number} от ${meta.date}</div>

        ${tableHtml}

        <div class="protocol-conclusion-box">
          ${conclusionText}
        </div>

        <div class="protocol-signs-row">
          <div class="sign-column">
            <span>Выполнил:</span>
            <div class="sign-underline"></div>
            <span>/ ${b2Eng} /</span>
          </div>
          <div class="sign-column">
            <span>Представитель заказчика / технадзора:</span>
            <div class="sign-underline"></div>
            <span>/ __________________________ /</span>
          </div>
        </div>
      </div>
    `;
  }

  window.calcEngineOpenProtocol = function () {
    if (state.currentBlock === 'block1' && !isBlock1Ready()) {
      alert('Для формирования отчёта необходимо ввести исходные параметры вентилятора и добавить хотя бы один этаж с размерами шахты и клапана.');
      return;
    }
    if (state.currentBlock === 'block2') {
      const b2Sys = document.getElementById('b2_system_name')?.value?.trim();
      const b2Obj = document.getElementById('b2_object_name')?.value?.trim();
      const b2Addr = document.getElementById('b2_address')?.value?.trim();
      const b2Eng = document.getElementById('b2_engineer')?.value?.trim();
      if (b2Sys) state.protocolMeta.systemName = b2Sys;
      if (b2Obj) state.protocolMeta.objectName = b2Obj;
      if (b2Addr) state.protocolMeta.address = b2Addr;
      if (b2Eng) state.protocolMeta.engineer = b2Eng;
    }
    if (state.currentBlock === 'block3') {
      const b3Sys = document.getElementById('b3_system')?.value?.trim();
      if (b3Sys) {
        state.protocolMeta.systemName = b3Sys;
      }
    }
    recalculateCurrent();
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
    if (dom.protocolModal) {
      dom.protocolModal.classList.add('active');
    }
  };

  window.calcEngineCloseProtocol = function () {
    if (dom.protocolModal) {
      dom.protocolModal.classList.remove('active');
    }
  };

  window.calcEngineUpdateMeta = function (field, val) {
    state.protocolMeta[field] = val;
    const html = generateProtocolHTML();
    if (dom.protocolPrintArea) {
      dom.protocolPrintArea.innerHTML = html;
    }
  };

  window.calcEngineDirectPrint = function () {
    if (state.currentBlock === 'block1' && !isBlock1Ready()) {
      alert('Для формирования отчёта необходимо ввести исходные параметры вентилятора и добавить хотя бы один этаж с размерами шахты и клапана.');
      return;
    }
    if (state.currentBlock === 'block2') {
      const b2Sys = document.getElementById('b2_system_name')?.value?.trim();
      const b2Obj = document.getElementById('b2_object_name')?.value?.trim();
      const b2Addr = document.getElementById('b2_address')?.value?.trim();
      const b2Eng = document.getElementById('b2_engineer')?.value?.trim();
      if (b2Sys) state.protocolMeta.systemName = b2Sys;
      if (b2Obj) state.protocolMeta.objectName = b2Obj;
      if (b2Addr) state.protocolMeta.address = b2Addr;
      if (b2Eng) state.protocolMeta.engineer = b2Eng;
    }
    if (state.currentBlock === 'block3') {
      const b3Sys = document.getElementById('b3_system')?.value?.trim();
      if (b3Sys) {
        state.protocolMeta.systemName = b3Sys;
      }
    }
    recalculateCurrent();
    const bodyContent = generateProtocolHTML();
    const printWindow = window.open('', '_blank', 'width=900,height=750');
    if (!printWindow) {
      window.print();
      return;
    }

    const katexCssUri = `${window.location.origin}/wp-content/themes/wp-theme/assets/vendor/katex/katex.min.css`;

    printWindow.document.open();
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Протокол испытаний БИС</title>
        <link rel="stylesheet" href="${katexCssUri}">
        <style>
          @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 20mm;
          }
          html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
          .gost-sheet {
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif !important;
            font-size: 12pt !important;
            line-height: 1.5 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
          }
          .gost-sheet p,
          .gost-sheet td,
          .gost-sheet th,
          .gost-sheet div:not(.gost-chart-box):not(.katex *):not(.katex),
          .gost-sheet span:not(.katex *):not(.katex) {
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif;
            font-size: 12pt;
            color: #000000;
          }
          .gost-sheet .gost-section-heading {
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif !important;
            font-size: 12pt !important;
            font-weight: bold !important;
            color: #000000 !important;
            margin: 18pt 0 8pt 0 !important;
            text-align: left !important;
            text-indent: 1.25cm !important;
            page-break-after: avoid;
          }
          .gost-sheet svg text {
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif !important;
          }
          .gost-p {
            margin: 0 0 10pt 0;
            text-align: justify;
            text-indent: 1.25cm;
            line-height: 1.5;
            hyphens: auto;
            -webkit-hyphens: auto;
          }
          .gost-p--no-indent {
            margin: 0 0 10pt 0;
            text-indent: 0;
            text-align: left;
            line-height: 1.5;
          }
          .gost-p-center {
            margin: 0 0 10pt 0;
            text-align: center;
            text-indent: 0;
            line-height: 1.5;
          }
          .gost-formula-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8pt 0 8pt 0;
            padding: 0 5pt;
            page-break-inside: avoid;
          }
          .gost-formula-math {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            text-align: center;
          }
          .gost-formula-num {
            margin-left: auto;
            white-space: nowrap;
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            color: #000000;
          }
          .gost-sheet .katex-display {
            margin: 0 !important;
            text-align: center;
          }
          .gost-sheet .katex {
            font-size: 1.1em;
            color: #000000;
          }
          .gost-note {
            margin: 0 0 10pt 0;
            text-indent: 1.25cm;
            line-height: 1.5;
            text-align: justify;
          }
          .gost-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10pt 0 16pt 0;
            page-break-inside: avoid;
          }
          .gost-table th,
          .gost-table td {
            border: 1px solid #000000 !important;
            padding: 4pt 3pt;
            text-align: center;
            vertical-align: middle;
            background: #ffffff !important;
            line-height: 1.25;
            font-size: 10.5pt !important;
          }
          .gost-table th {
            font-weight: normal !important;
          }
          .gost-chart-box {
            margin: 16pt auto;
            text-align: center;
            page-break-inside: avoid;
          }
          .gost-chart-caption {
            margin-top: 8pt;
            text-align: center;
            font-size: 12pt;
          }
          .gost-stamp-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24pt;
            border: 2px solid #000000 !important;
            page-break-inside: avoid;
          }
          .gost-stamp-table td {
            border: 1px solid #000000 !important;
            padding: 3pt 5pt;
            vertical-align: middle;
            background: #ffffff !important;
            line-height: 1.3;
          }
        </style>
      </head>
      <body>
        ${bodyContent}
        <script>
          window.onload = function() {
            window.focus();
            window.print();
            setTimeout(function() { window.close(); }, 1000);
          };
        <\/script>
      </body>
      </html>
    `);
    printWindow.document.close();
  };

  function recalculateCurrent() {
    if (state.currentBlock === 'block1') {
      recalculateBlock1();
    } else if (state.currentBlock === 'block2') {
      recalculateBlock2();
    } else if (state.currentBlock === 'block3') {
      recalculateBlock3();
    }
  }

  function updateSummaryMetric(id, val, unit) {
    const el = document.getElementById(id);
    if (el) {
      el.innerHTML = `${val} ${unit ? `<span class="unit">${unit}</span>` : ''}`;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
